<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;
use App\Models\AuditLog;
use App\Models\SupportingFile;
use App\Services\PermissionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Intervention\Image\ImageManager;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;
use App\Services\NotificationService;

class FileController extends Controller
{
    protected $permissionService;
    protected $notificationService;

    public function __construct(PermissionService $permissionService, NotificationService $notificationService)
    {
        $this->permissionService = $permissionService;
        $this->notificationService = $notificationService;
    }
    public function updateExpiredFilesApi()
    {
        $today = Carbon::today();

        $files = File::where('status', '!=', 'Expired')
            ->whereDate('expiration_date', '<=', $today)
            ->get();

        foreach ($files as $file) {
            $file->status = 'Expired';
            $file->save();

            AuditLog::create([
                'action' => 'Update',
                'module' => 'FILE',
                'target_user_id' => $file->id,
                'description' => "SYSTEM: File {$file->name} has been marked as Expired.",
                'performed_by' => 1, // system
                'performed_at' => now(),
            ]);
        }

        return response()->json([
            'message' => count($files) . ' file(s) updated to Expired.',
        ]);
    }
    private function safeDecrypt($value)
    {
        if (!$value)
            return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            Log::warning("Failed to decrypt value: " . $e->getMessage());
            return $value; // fallback: return raw encrypted string
        }
    }

    public function index()
    {
        // Check if user has permission to view files
        if (!$this->permissionService->hasPermission(Auth::user(), 'Document Management', 'View Files')) {
            return response()->json([
                'message' => 'Access denied. You do not have permission to view files.',
            ], 403);
        }

        $files = File::whereIn('status', ['Released', 'Pending'])->get()->map(function ($file) {
            // File size formatting
            $size = $file->file_size ?? 0;
            if ($size >= 1073741824)
                $fileSize = round($size / 1073741824, 2) . ' GB';
            elseif ($size >= 1048576)
                $fileSize = round($size / 1048576, 2) . ' MB';
            elseif ($size >= 1024)
                $fileSize = round($size / 1024, 2) . ' KB';
            elseif ($size > 1)
                $fileSize = $size . ' bytes';
            elseif ($size == 1)
                $fileSize = '1 byte';
            else
                $fileSize = '0 bytes';
            Log::info("Decrypting file : " . $file);
            return [
                'id' => $file->id,
                'name' => $this->safeDecrypt($file->getRawOriginal('name')),
                'org_filename' => $this->safeDecrypt($file->getRawOriginal('org_filename')),
                'file' => $this->safeDecrypt($file->getRawOriginal('file')),
                'file_path' => $file->file_path, // Decrypted file path for storage URL
                'file_url' => $file->file_url,   // Full URL to access file
                'file_type' => $this->safeDecrypt($file->getRawOriginal('file_type')),
                'folder_id' => $file->folder_id,
                'related_document' => $file->related_document,
                'file_size' => $fileSize,
                'status' => $file->status ?? 'Released',
                'created_at' => $file->created_at ? $file->created_at->toDateTimeString() : null,
                'expiration_date' => $file->expiration_date ? $file->expiration_date->toDateTimeString() : null,
                'updated_at' => $file->updated_at ? $file->updated_at->toDateTimeString() : null,
            ];
        });


        return response()->json(['data' => $files]);
    }

    public function show($id)
    {
        $file = File::find($id);

        if (!$file) {
            return response()->json([
                'status' => 'error',
                'message' => 'File not found.',
            ], 404);
        }

        // Safely return decrypted data
        $decrypted = [];
        foreach ($file->getAttributes() as $key => $value) {
            $decrypted[$key] = $file->{$key}; // triggers your decryption accessor
        }

        return response()->json([
            'status' => 'success',
            'data' => $decrypted,
        ]);
    }

    public function update(Request $request, $id)
    {
        // Check if user has permission to edit files
        if (!$this->permissionService->hasPermission(Auth::user(), 'Document Management', 'Edit Files')) {
            return response()->json([
                'message' => 'Access denied. You do not have permission to edit files.',
            ], 403);
        }

        $file = File::find($id);

        if (!$file) {
            return response()->json([
                'status' => 'error',
                'message' => 'File not found.',
            ], 404);
        }

        $data = $request->validate([
            'name' => 'nullable|string',
            'description' => 'nullable|string',
            'expiration_date' => 'nullable|date',
            'status' => 'nullable|string',
        ]);

        $file->update($data);

        AuditLog::create([
            'action' => 'Updated',
            'module' => 'FILE',
            'target_user_id' => $file->id,
            'description' => Auth::user()->name . " updated the file: {$file->name}.",
            'performed_by' => Auth::id(),
            'performed_at' => now(),
        ]);

        // 🔔 Send notifications for document update
        // Notify all users who have access to this document
        // Get reviewers and approvers from the file's assignments
        $notifyUserIds = [];

        if ($file->assign_reviewer) {
            $reviewers = json_decode($file->assign_reviewer, true);

            // Get users from reviewer groups
            if (!empty($reviewers['groups'])) {
                foreach ($reviewers['groups'] as $groupId) {
                    try {
                        $response = app('App\Http\Controllers\GroupController')->getUsersInGroup($groupId);
                        $users = json_decode($response->getContent(), true);
                        if (isset($users['users'])) {
                            foreach ($users['users'] as $user) {
                                $notifyUserIds[] = $user['user_id'];
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning("Failed to get users from group {$groupId}: " . $e->getMessage());
                    }
                }
            }

            // Get individual reviewers
            if (!empty($reviewers['individual'])) {
                $notifyUserIds = array_merge($notifyUserIds, $reviewers['individual']);
            }
        }

        if ($file->assign_approver) {
            $approvers = json_decode($file->assign_approver, true);

            // Get users from approver groups
            if (!empty($approvers['groups'])) {
                foreach ($approvers['groups'] as $groupId) {
                    try {
                        $response = app('App\Http\Controllers\GroupController')->getUsersInGroup($groupId);
                        $users = json_decode($response->getContent(), true);
                        if (isset($users['users'])) {
                            foreach ($users['users'] as $user) {
                                $notifyUserIds[] = $user['user_id'];
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning("Failed to get users from group {$groupId}: " . $e->getMessage());
                    }
                }
            }

            // Get individual approvers
            if (!empty($approvers['individual'])) {
                $notifyUserIds = array_merge($notifyUserIds, $approvers['individual']);
            }
        }

        // Remove duplicates and the updater
        $notifyUserIds = array_unique($notifyUserIds);
        $notifyUserIds = array_filter($notifyUserIds, fn($id) => $id != Auth::id());

        // Send document updated notifications
        if (!empty($notifyUserIds)) {
            foreach ($notifyUserIds as $userId) {
                $this->notificationService->documentUpdated(
                    $userId,
                    $file->name,
                    Auth::user()->name,
                    $file->id
                );
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'File updated successfully.',
            'data' => $file,
        ]);
    }
    public function moveFolder(Request $request, $id)
    {
        $folderId = $request->input('folder_id');
        $file = File::findOrFail($id);
        $file->folder_id = $folderId;
        $file->save();
        // Audit log
        AuditLog::create([
            'action' => 'Updated',
            'module' => 'FILE',
            'target_user_id' => $file->id,
            'description' => Auth::user()->name . " moved the file '{$file->name}' to folder ID {$file['folder_id']}.",
            'performed_by' => Auth::id(),
            'performed_at' => now(),
        ]);
        return response()->json(['success' => true, 'folder_id' => $folderId]);
    }
    public function toggleLock(Request $request, $id)
    {
        $file = File::findOrFail($id);
        $file->locked = $request->input('locked', !$file->locked); // toggle if not specified
        $file->save();

        // Optional: audit log
        AuditLog::create([
            'action' => 'Updated',
            'module' => 'FILE',
            'target_user_id' => $file->id,
            'description' => Auth::user()->name . " " . ($file->locked ? "locked" : "unlocked") . " the file '{$file->name}'.",
            'performed_by' => Auth::id(),
            'performed_at' => now(),
        ]);

        return response()->json(['success' => true, 'locked' => $file->locked]);
    }
    public function updateRelatedDocuments(Request $request, $id)
    {
        $file = File::findOrFail($id);

        // 🟩 Get new related IDs from request
        $incomingIds = $request->input('related_file_ids', []);

        // Normalize to array
        $existingIds = $file->related_document ?? [];
        if (!is_array($existingIds)) {
            $existingIds = json_decode($existingIds, true) ?? [];
        }

        // 🟦 Determine added & removed IDs
        $addedIds = array_diff($incomingIds, $existingIds);
        $removedIds = array_diff($existingIds, $incomingIds);

        // 🟧 Save final updated list
        $finalIds = array_values(array_unique($incomingIds));
        $file->related_document = $finalIds;
        $file->save();

        // 🟩 Fetch added/removed files for decrypted names
        $addedFiles = File::whereIn('id', $addedIds)->get();
        $removedFiles = File::whereIn('id', $removedIds)->get();

        $addedNames = $addedFiles->pluck('name')->toArray();
        $removedNames = $removedFiles->pluck('name')->toArray();

        // 🟨 Build readable lists
        $addedList = implode(', ', $addedNames);
        $removedList = implode(', ', $removedNames);

        // 🟪 Build description dynamically
        $descriptionParts = [];

        if (!empty($addedNames)) {
            $descriptionParts[] = "Added a File: ($addedList)";
        }

        if (!empty($removedNames)) {
            $descriptionParts[] = "Removed a File: ($removedList)";
        }

        // Only create log if something actually changed
        if (!empty($descriptionParts)) {
            $description = Auth::user()->name .
                " updated related documents for the file '{$file->name}'. " .
                implode(' ', $descriptionParts);

            AuditLog::create([
                'action' => 'Updated',
                'module' => 'FILE',
                'target_user_id' => $file->id,
                'description' => $description,
                'performed_by' => Auth::id(),
                'performed_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'related_files' => $file->related_document
        ]);
    }


    public function updateStatus(Request $request, $id)
    {
        $type = $request->input('type', 'file'); // 'file' or 'supporting'
        $status = $request->input('status', 'Inactive');

        if ($type === 'file') {
            // Update main file
            $file = File::findOrFail($id);
            $file->status = $status;
            $file->save();

            // Audit log
            AuditLog::create([
                'action' => 'Updated',
                'module' => 'FILE',
                'target_user_id' => $file->id,
                'description' => Auth::user()->name . " updated '{$file->name}' status to {$status}.",
                'performed_by' => Auth::id(),
                'performed_at' => now(),
            ]);

        } elseif ($type === 'supporting') {
            // Update supporting file
            $file = SupportingFile::findOrFail($id);
            $file->status = $status;
            $file->save();

            // Audit log
            AuditLog::create([
                'action' => 'Updated',
                'module' => 'SUPPORTING_FILE',
                'target_user_id' => $file->id,
                'description' => Auth::user()->name . " updated supporting file '{$file->name}' status to {$status}.",
                'performed_by' => Auth::id(),
                'performed_at' => now(),
            ]);
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid type'], 400);
        }

        return response()->json(['success' => true, 'status' => $status]);
    }

    /**
     * 🗑 Delete file
     */
    public function destroy($id)
    {
        // Check if user has permission to delete files
        if (!$this->permissionService->hasPermission(Auth::user(), 'Document Management', 'Delete Files')) {
            return response()->json([
                'message' => 'Access denied. You do not have permission to delete files.',
            ], 403);
        }

        $file = File::find($id);

        if (!$file) {
            return response()->json([
                'status' => 'error',
                'message' => 'File not found.',
            ], 404);
        }

        // Get file info before deletion for notifications
        $fileName = $file->name;
        $fileAssignReviewer = $file->assign_reviewer;
        $fileAssignApprover = $file->assign_approver;

        if ($file->file && Storage::disk('public')->exists($file->file)) {
            Storage::disk('public')->delete($file->file);
        }

        $file->delete();

        AuditLog::create([
            'action' => 'Deleted',
            'module' => 'FILE',
            'target_user_id' => $id,
            'description' => Auth::user()->name . " deleted a file record.",
            'performed_by' => Auth::id(),
            'performed_at' => now(),
        ]);

        // 🔔 Send notifications for document deletion
        // Notify all users who had access to this document
        $notifyUserIds = [];

        if ($fileAssignReviewer) {
            $reviewers = json_decode($fileAssignReviewer, true);

            // Get users from reviewer groups
            if (!empty($reviewers['groups'])) {
                foreach ($reviewers['groups'] as $groupId) {
                    try {
                        $response = app('App\Http\Controllers\GroupController')->getUsersInGroup($groupId);
                        $users = json_decode($response->getContent(), true);
                        if (isset($users['users'])) {
                            foreach ($users['users'] as $user) {
                                $notifyUserIds[] = $user['user_id'];
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning("Failed to get users from group {$groupId}: " . $e->getMessage());
                    }
                }
            }

            // Get individual reviewers
            if (!empty($reviewers['individual'])) {
                $notifyUserIds = array_merge($notifyUserIds, $reviewers['individual']);
            }
        }

        if ($fileAssignApprover) {
            $approvers = json_decode($fileAssignApprover, true);

            // Get users from approver groups
            if (!empty($approvers['groups'])) {
                foreach ($approvers['groups'] as $groupId) {
                    try {
                        $response = app('App\Http\Controllers\GroupController')->getUsersInGroup($groupId);
                        $users = json_decode($response->getContent(), true);
                        if (isset($users['users'])) {
                            foreach ($users['users'] as $user) {
                                $notifyUserIds[] = $user['user_id'];
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning("Failed to get users from group {$groupId}: " . $e->getMessage());
                    }
                }
            }

            // Get individual approvers
            if (!empty($approvers['individual'])) {
                $notifyUserIds = array_merge($notifyUserIds, $approvers['individual']);
            }
        }

        // Remove duplicates and the deleter
        $notifyUserIds = array_unique($notifyUserIds);
        $notifyUserIds = array_filter($notifyUserIds, fn($id) => $id != Auth::id());

        // Send document deleted notifications
        if (!empty($notifyUserIds)) {
            foreach ($notifyUserIds as $userId) {
                $this->notificationService->documentDeleted(
                    $userId,
                    $fileName,
                    Auth::user()->name
                );
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'File deleted successfully.',
        ]);
    }

    public function preview($id)
    {
        $file = File::find($id);

        if (!$file) {
            return response()->json(['message' => 'File not found.'], 404);
        }

        $path = $file->decrypted_path;

        if (!$path || !file_exists($path)) {
            return response()->json(['message' => 'File not found on disk.'], 404);
        }

        return response()->file($path);
    }

    public function store(Request $request)
    {
        // Check if user has permission to create files
        if (!$this->permissionService->hasPermission(Auth::user(), 'Document Management', 'Create Files')) {
            return response()->json([
                'message' => 'Access denied. You do not have permission to create files.',
            ], 403);
        }

        $request->merge([
            'reviewer_groups' => is_array($request->input('reviewer_groups'))
                ? $request->input('reviewer_groups')
                : json_decode($request->input('reviewer_groups'), true),

            'reviewer_individual' => is_array($request->input('reviewer_individual'))
                ? $request->input('reviewer_individual')
                : json_decode($request->input('reviewer_individual'), true),

            'reviewer_role' => is_array($request->input('reviewer_role'))
                ? $request->input('reviewer_role')
                : json_decode($request->input('reviewer_role'), true),

            'approver_groups' => is_array($request->input('approver_groups'))
                ? $request->input('approver_groups')
                : json_decode($request->input('approver_groups'), true),

            'approver_individual' => is_array($request->input('approver_individual'))
                ? $request->input('approver_individual')
                : json_decode($request->input('approver_individual'), true),

            'approver_role' => is_array($request->input('approver_role'))
                ? $request->input('approver_role')
                : json_decode($request->input('approver_role'), true),

            'keywords' => is_array($request->input('keywords'))
                ? $request->input('keywords')
                : json_decode($request->input('keywords'), true),

            'categories' => is_array($request->input('categories'))
                ? $request->input('categories')
                : json_decode($request->input('categories'), true),
        ]);


        // Validation
        $data = $request->validate([
            'name' => 'nullable|string',
            'description' => 'nullable|string',
            'expiration_date' => 'nullable|date',
            'owner_name' => 'required|string',
            'folder_id' => 'nullable|integer',
            'reviewer_groups' => 'nullable|array',
            'reviewer_individual' => 'nullable|array',
            'reviewer_role' => 'nullable|array',
            'approver_groups' => 'nullable|array',
            'approver_individual' => 'nullable|array',
            'approver_role' => 'nullable|array',
            'keywords' => 'nullable|array',
            'categories' => 'nullable|array',
            'version' => 'nullable|string',
            'version_description' => 'nullable|string',
            'file' => 'nullable|file',
        ]);

        // Combine reviewer & approver assignments
        $assignReviewer = [
            'groups' => $data['reviewer_groups'] ?? [],
            'individual' => $data['reviewer_individual'] ?? [],
            'roles' => $data['reviewer_role'] ?? [],
        ];
        $assignApprover = [
            'groups' => $data['approver_groups'] ?? [],
            'individual' => $data['approver_individual'] ?? [],
            'roles' => $data['approver_role'] ?? [],
        ];

        $data['assign_reviewer'] = json_encode($assignReviewer);
        $data['assign_approver'] = json_encode($assignApprover);

        // File upload and compression (pure PHP)
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $filename = time() . '_' . pathinfo($originalName, PATHINFO_FILENAME);
            $extension = strtolower($file->getClientOriginalExtension());
            $mime = $file->getMimeType();

            $storageDir = storage_path('app/public/files/');
            if (!file_exists($storageDir)) {
                mkdir($storageDir, 0755, true);
            }

            $diskPath = $storageDir . $filename . '.' . $extension;

            // --- IMAGE FILES ---
            if (str_contains($mime, 'image')) {
                if (class_exists(\Imagick::class)) {
                    $image = new \Imagick($file->getRealPath());
                    $image->setImageCompression(\Imagick::COMPRESSION_JPEG);
                    $image->setImageCompressionQuality(75);
                    $image->stripImage();
                    $image->resizeImage(1024, 0, \Imagick::FILTER_LANCZOS, 1);
                    $image->writeImage($diskPath);
                    $image->destroy();
                } else {
                    // GD fallback if Imagick is not available
                    $img = imagecreatefromstring(file_get_contents($file->getRealPath()));
                    $width = imagesx($img);
                    $height = imagesy($img);
                    $newWidth = 1024;
                    $newHeight = ($height / $width) * $newWidth;
                    $resized = imagescale($img, $newWidth, $newHeight);
                    imagejpeg($resized, $diskPath, 75);
                    imagedestroy($img);
                    imagedestroy($resized);
                }
            }

            // --- PDF FILES ---
            elseif ($mime === 'application/pdf') {
                // Check if Imagick is available for PDF compression
                if (extension_loaded('imagick')) {
                    try {
                        $pdf = new \Imagick();
                        $pdf->setResolution(100, 100);
                        $pdf->readImage($file->getRealPath());
                        $pdf->setImageCompression(\Imagick::COMPRESSION_JPEG);
                        $pdf->setImageCompressionQuality(80);
                        $pdf->writeImages($storageDir . $filename . '.pdf', true);
                        $pdf->destroy();
                        $diskPath = $storageDir . $filename . '.pdf';
                    } catch (\Exception $e) {
                        // If Imagick fails, fall back to direct storage
                        $file->storeAs('files', $filename . '.' . $extension, 'public');
                        $diskPath = $storageDir . $filename . '.' . $extension;
                    }
                } else {
                    // Imagick not available, save PDF directly
                    $file->storeAs('files', $filename . '.' . $extension, 'public');
                    $diskPath = $storageDir . $filename . '.' . $extension;
                }
            }

            // --- OFFICE FILES ---
            elseif (in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'csv', 'ppt', 'pptx'])) {
                $file->storeAs('files', $filename . '.' . $extension, 'public');
                $diskPath = $storageDir . $filename . '.' . $extension;
            }

            // --- OTHER FILES ---
            else {
                $file->storeAs('files', $filename . '.' . $extension, 'public');
                $diskPath = $storageDir . $filename . '.' . $extension;
            }

            // Save file info
            $data['file'] = 'files/' . basename($diskPath);
            $data['org_filename'] = $originalName;
            $data['file_type'] = $mime;
            $data['file_size'] = file_exists($diskPath) ? filesize($diskPath) : 0;
        }

        // Default name
        if (empty($data['name']) && isset($data['org_filename'])) {
            $data['name'] = pathinfo($data['org_filename'], PATHINFO_FILENAME);
        }

        // Determine file status
        $hasReviewers = !empty($assignReviewer['groups']) || !empty($assignReviewer['individual']);
        $hasApprovers = !empty($assignApprover['groups']) || !empty($assignApprover['individual']);
        $today = Carbon::today();
        $expiration = isset($data['expiration_date']) ? Carbon::parse($data['expiration_date']) : null;
        if ($expiration && $expiration->lessThanOrEqualTo($today)) {
            $data['status'] = 'Expired';
        } else {
            $data['status'] = ($hasReviewers || $hasApprovers) ? 'Pending' : 'Released';
        }

        // Create record
        $fileRecord = File::create($data);

        AuditLog::create([
            'action' => 'Created',
            'module' => 'FILE',
            'target_user_id' => $fileRecord->id,
            'description' => Auth::user()->name . " created a new file: {$fileRecord->name}.",
            'performed_by' => Auth::id(),
            'performed_at' => now(),
        ]);

        // 🔔 Send notifications for new document upload
        // Notify all reviewers about the new document that needs their review
        $reviewerUserIds = [];

        // Get users from reviewer groups
        if (!empty($assignReviewer['groups'])) {
            foreach ($assignReviewer['groups'] as $groupId) {
                try {
                    $response = app('App\Http\Controllers\GroupController')->getUsersInGroup($groupId);
                    $users = json_decode($response->getContent(), true);
                    if (isset($users['users'])) {
                        foreach ($users['users'] as $user) {
                            $reviewerUserIds[] = $user['user_id'];
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to get users from group {$groupId}: " . $e->getMessage());
                }
            }
        }

        // Get users from reviewer individuals
        if (!empty($assignReviewer['individual'])) {
            $reviewerUserIds = array_merge($reviewerUserIds, $assignReviewer['individual']);
        }

        // Get users from reviewer roles
        if (!empty($assignReviewer['roles'])) {
            foreach ($assignReviewer['roles'] as $roleId) {
                try {
                    $response = app('App\Http\Controllers\RoleController')->getUsersWithRole($roleId);
                    $users = json_decode($response->getContent(), true);
                    if (isset($users['users'])) {
                        foreach ($users['users'] as $user) {
                            $reviewerUserIds[] = $user['user_id'];
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to get users from role {$roleId}: " . $e->getMessage());
                }
            }
        }

        // Remove duplicates and the file creator
        $reviewerUserIds = array_unique($reviewerUserIds);
        $reviewerUserIds = array_filter($reviewerUserIds, fn($id) => $id != Auth::id());

        // Send approval request notifications to all reviewers
        if (!empty($reviewerUserIds)) {
            foreach ($reviewerUserIds as $userId) {
                $this->notificationService->approvalRequest(
                    $userId,
                    $fileRecord->name,
                    Auth::user()->name,
                    $fileRecord->id
                );
            }
        }

        // Get all approvers
        $approverUserIds = [];

        // Get users from approver groups
        if (!empty($assignApprover['groups'])) {
            foreach ($assignApprover['groups'] as $groupId) {
                try {
                    $response = app('App\Http\Controllers\GroupController')->getUsersInGroup($groupId);
                    $users = json_decode($response->getContent(), true);
                    if (isset($users['users'])) {
                        foreach ($users['users'] as $user) {
                            $approverUserIds[] = $user['user_id'];
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to get users from group {$groupId}: " . $e->getMessage());
                }
            }
        }

        // Get users from approver individuals
        if (!empty($assignApprover['individual'])) {
            $approverUserIds = array_merge($approverUserIds, $assignApprover['individual']);
        }

        // Get users from approver roles
        if (!empty($assignApprover['roles'])) {
            foreach ($assignApprover['roles'] as $roleId) {
                try {
                    $response = app('App\Http\Controllers\RoleController')->getUsersWithRole($roleId);
                    $users = json_decode($response->getContent(), true);
                    if (isset($users['users'])) {
                        foreach ($users['users'] as $user) {
                            $approverUserIds[] = $user['user_id'];
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to get users from role {$roleId}: " . $e->getMessage());
                }
            }
        }

        // Remove duplicates and the file creator
        $approverUserIds = array_unique($approverUserIds);
        $approverUserIds = array_filter($approverUserIds, fn($id) => $id != Auth::id());

        // Send approval request notifications to all approvers
        if (!empty($approverUserIds)) {
            foreach ($approverUserIds as $userId) {
                $this->notificationService->approvalRequest(
                    $userId,
                    $fileRecord->name,
                    Auth::user()->name,
                    $fileRecord->id
                );
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'File created successfully.',
            'data' => $fileRecord,
        ]);
    }
    public function updateAttachment(Request $request, SupportingFile $file)
    {
        Log::info('Incoming updateAttachment data:', $request->all());

        // Check permission
        if (!$this->permissionService->hasPermission(Auth::user(), 'Document Management', 'Edit Files')) {
            return response()->json([
                'message' => 'Access denied. You do not have permission to update files.',
            ], 403);
        }

        // Decode JSON arrays safely
        $request->merge([
            'reviewer_groups' => is_array($request->input('reviewer_groups'))
                ? $request->input('reviewer_groups')
                : json_decode($request->input('reviewer_groups'), true) ?? [],

            'reviewer_individual' => is_array($request->input('reviewer_individual'))
                ? $request->input('reviewer_individual')
                : json_decode($request->input('reviewer_individual'), true) ?? [],

            'reviewer_role' => is_array($request->input('reviewer_role'))
                ? $request->input('reviewer_role')
                : json_decode($request->input('reviewer_role'), true) ?? [],

            'approver_groups' => is_array($request->input('approver_groups'))
                ? $request->input('approver_groups')
                : json_decode($request->input('approver_groups'), true) ?? [],

            'approver_individual' => is_array($request->input('approver_individual'))
                ? $request->input('approver_individual')
                : json_decode($request->input('approver_individual'), true) ?? [],

            'approver_role' => is_array($request->input('approver_role'))
                ? $request->input('approver_role')
                : json_decode($request->input('approver_role'), true) ?? [],

            'keywords' => is_array($request->input('keywords'))
                ? $request->input('keywords')
                : json_decode($request->input('keywords'), true),
            'category' => is_array($request->input('category'))
                ? $request->input('category')
                : json_decode($request->input('category'), true),
        ]);

        // Validate request
        $data = $request->validate([
            'name' => 'nullable|string',
            'description' => 'nullable|string',
            'expiration_date' => 'nullable|date',
            'owner_name' => 'required|string',
            'file_id' => 'nullable|integer',
            'reviewer_groups' => 'nullable|array',
            'reviewer_individual' => 'nullable|array',
            'reviewer_role' => 'nullable|array',
            'approver_groups' => 'nullable|array',
            'approver_individual' => 'nullable|array',
            'approver_role' => 'nullable|array',
            'keywords' => 'nullable|array',
            'categories' => 'nullable|array',
            'version' => 'nullable|integer',
            'version_description' => 'nullable|string',
            'file' => $request->hasFile('file')
                ? 'file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,ppt,pptx,csv,txt|max:51200'
                : 'nullable',
            'locked' => 'nullable|boolean',
        ]);

        // Combine reviewer & approver assignments into single JSONs
        $assignReviewer = [
            'groups' => $data['reviewer_groups'] ?? [],
            'individual' => $data['reviewer_individual'] ?? [],
            'roles' => $data['reviewer_role'] ?? [],
        ];
        $assignApprover = [
            'groups' => $data['approver_groups'] ?? [],
            'individual' => $data['approver_individual'] ?? [],
            'roles' => $data['approver_role'] ?? [],
        ];

        $data['assign_reviewer'] = json_encode($assignReviewer);
        $data['assign_approver'] = json_encode($assignApprover);

        // Handle file upload
        if ($request->hasFile('file')) {
            $fileInput = $request->file('file');
            $originalName = $fileInput->getClientOriginalName();
            $filename = time() . '_' . pathinfo($originalName, PATHINFO_FILENAME);
            $extension = strtolower($fileInput->getClientOriginalExtension());
            $mime = $fileInput->getMimeType();

            $storageDir = storage_path('app/public/files/');
            if (!file_exists($storageDir))
                mkdir($storageDir, 0755, true);

            $diskPath = $storageDir . $filename . '.' . $extension;

            // --- IMAGE ---
            if (str_contains($mime, 'image')) {
                if (class_exists(\Imagick::class)) {
                    $image = new \Imagick($fileInput->getRealPath());
                    $image->setImageCompression(\Imagick::COMPRESSION_JPEG);
                    $image->setImageCompressionQuality(75);
                    $image->stripImage();
                    $image->resizeImage(1024, 0, \Imagick::FILTER_LANCZOS, 1);
                    $image->writeImage($diskPath);
                    $image->destroy();
                } else {
                    $img = imagecreatefromstring(file_get_contents($fileInput->getRealPath()));
                    $width = imagesx($img);
                    $height = imagesy($img);
                    $newWidth = 1024;
                    $newHeight = ($height / $width) * $newWidth;
                    $resized = imagescale($img, $newWidth, $newHeight);
                    imagejpeg($resized, $diskPath, 75);
                    imagedestroy($img);
                    imagedestroy($resized);
                }
            }
            // --- PDF ---
            elseif ($mime === 'application/pdf') {
                try {
                    $pdf = new \Imagick();
                    $pdf->setResolution(100, 100);
                    $pdf->readImage($fileInput->getRealPath());
                    $pdf->setImageCompression(\Imagick::COMPRESSION_JPEG);
                    $pdf->setImageCompressionQuality(80);
                    $pdf->writeImages($storageDir . $filename . '.pdf', true);
                    $pdf->destroy();
                    $diskPath = $storageDir . $filename . '.pdf';
                } catch (\Exception $e) {
                    $fileInput->storeAs('files', $filename . '.' . $extension, 'public');
                    $diskPath = $storageDir . $filename . '.' . $extension;
                }
            }
            // --- OFFICE or OTHER FILES ---
            else {
                $fileInput->storeAs('files', $filename . '.' . $extension, 'public');
                $diskPath = $storageDir . $filename . '.' . $extension;
            }

            // Update file info
            $data['file'] = 'files/' . basename($diskPath);
            $data['org_filename'] = $originalName;
            $data['file_type'] = $mime;
            $data['file_size'] = file_exists($diskPath) ? filesize($diskPath) : 0;

            // Increment version only if file uploaded
            $data['version'] = ($file->version ?? 0) + 1;
            Log::info("Incrementing version to: " . $data['version']);
        } else {
            $data['version'] = $file->version ?? 1;
        }

        // Default name if missing
        if (empty($data['name']) && isset($data['org_filename'])) {
            $data['name'] = pathinfo($data['org_filename'], PATHINFO_FILENAME);
        }

        // Determine file status
        $hasReviewers = !empty($assignReviewer['groups']) || !empty($assignReviewer['individual']);
        $hasApprovers = !empty($assignApprover['groups']) || !empty($assignApprover['individual']);
        $today = \Carbon\Carbon::today();
        $expiration = isset($data['expiration_date']) ? \Carbon\Carbon::parse($data['expiration_date']) : null;
        if ($expiration && $expiration->lessThanOrEqualTo($today)) {
            $data['status'] = 'Expired';
        } else {
            $data['status'] = ($hasReviewers || $hasApprovers) ? 'Pending' : 'Released';
        }

        // 🧹 Remove request-only fields (not DB columns)
        unset(
            $data['reviewer_groups'],
            $data['reviewer_individual'],
            $data['reviewer_role'],
            $data['approver_groups'],
            $data['approver_individual'],
            $data['approver_role'],
            $data['categories']
        );

        // ✅ Update model cleanly
        $file->fill($data);
        $file->updated_by = Auth::id();
        $file->save();

        // Audit log
        AuditLog::create([
            'action' => 'Updated',
            'module' => 'FILE',
            'target_user_id' => $file->id,
            'description' => Auth::user()->name . " updated file: {$file->name}.",
            'performed_by' => Auth::id(),
            'performed_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'File updated successfully.',
            'data' => $file,
        ]);
    }


    public function uploadAttachment(Request $request, $id)
    {
        $fileRecord = File::findOrFail($id);

        if ($request->hasFile('attachments')) {
            $uploadedFiles = $request->file('attachments');
            $savedFiles = [];

            foreach ($uploadedFiles as $file) {
                $originalName = $file->getClientOriginalName();
                $filename = time() . '_' . pathinfo($originalName, PATHINFO_FILENAME);
                $extension = strtolower($file->getClientOriginalExtension());
                $mime = $file->getMimeType();

                $storageDir = storage_path('app/public/attachments/');
                if (!file_exists($storageDir)) {
                    mkdir($storageDir, 0755, true);
                }

                $diskPath = $storageDir . $filename . '.' . $extension;

                // --- IMAGE FILES ---
                if (str_contains($mime, 'image')) {
                    if (class_exists(\Imagick::class)) {
                        $image = new \Imagick($file->getRealPath());
                        $image->setImageCompression(\Imagick::COMPRESSION_JPEG);
                        $image->setImageCompressionQuality(75);
                        $image->stripImage();
                        $image->resizeImage(1024, 0, \Imagick::FILTER_LANCZOS, 1);
                        $image->writeImage($diskPath);
                        $image->destroy();
                    } else {
                        $img = imagecreatefromstring(file_get_contents($file->getRealPath()));
                        $width = imagesx($img);
                        $height = imagesy($img);
                        $newWidth = 1024;
                        $newHeight = ($height / $width) * $newWidth;
                        $resized = imagescale($img, $newWidth, $newHeight);
                        imagejpeg($resized, $diskPath, 75);
                        imagedestroy($img);
                        imagedestroy($resized);
                    }
                }
                // --- PDF FILES ---
                elseif ($mime === 'application/pdf') {
                    try {
                        $pdf = new \Imagick();
                        $pdf->setResolution(100, 100);
                        $pdf->readImage($file->getRealPath());
                        $pdf->setImageCompression(\Imagick::COMPRESSION_JPEG);
                        $pdf->setImageCompressionQuality(80);
                        $pdf->writeImages($diskPath, true);
                        $pdf->destroy();
                    } catch (\Exception $e) {
                        $file->storeAs('attachments', $filename . '.' . $extension, 'public');
                        $diskPath = $storageDir . $filename . '.' . $extension;
                    }
                }
                // --- OFFICE OR OTHER FILES ---
                else {
                    $file->storeAs('attachments', $filename . '.' . $extension, 'public');
                    $diskPath = $storageDir . $filename . '.' . $extension;
                }

                // Save record in supporting_files table with encryption
                $supporting = SupportingFile::create([
                    'name' => Crypt::encryptString($originalName),
                    'file' => Crypt::encryptString('attachments/' . basename($diskPath)),
                    'file_id' => $fileRecord->id,
                    'status' => 'Released',
                    'owner_name' => Crypt::encryptString($request->user()->first_name . " " . $request->user()->last_name ?? 'Unknown'),
                    'file_type' => Crypt::encryptString($mime),
                    'file_size' => file_exists($diskPath) ? filesize($diskPath) : 0,
                    'created_by' => $request->user()->id ?? null,
                    'org_filename' => Crypt::encryptString($filename . '.' . $extension)

                ]);

                $savedFiles[] = [
                    'id' => $supporting->id,
                    'name' => $originalName,
                    'url' => asset('storage/attachments/' . basename($diskPath))
                ];
                AuditLog::create([
                    'action' => 'Created',
                    'module' => 'FILE',
                    'target_user_id' => $fileRecord->id,
                    'description' => Auth::user()->name . " created a new Supporting file at: {$originalName}.",
                    'performed_by' => Auth::id(),
                    'performed_at' => now(),
                ]);
            }

            return response()->json($savedFiles);
        }


        return response()->json(['message' => 'No files uploaded'], 400);
    }

    public function attachments($fileId)
    {
        // Fetch main file and decrypt automatically
        $mainFile = File::findOrFail($fileId);

        // Fetch supporting files (they auto-decrypt via model getters)
        $supportingFiles = SupportingFile::where('file_id', $fileId)
            ->where('status', 'Released')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'main_file' => $mainFile->toArray(),       // auto-decrypted
                'supporting_files' => $supportingFiles->map(fn($f) => $f->toArray()), // auto-decrypted
            ],
        ]);
    }




}