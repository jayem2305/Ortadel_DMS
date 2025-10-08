<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Intervention\Image\ImageManager;
class FileController extends Controller
{
    public function index()
    {
        $files = File::all()->map(function ($file) {
            // Convert file size to a readable format
            $size = $file->file_size ?? 0;
            if ($size >= 1073741824) {
                $fileSize = round($size / 1073741824, 2) . ' GB';
            } elseif ($size >= 1048576) {
                $fileSize = round($size / 1048576, 2) . ' MB';
            } elseif ($size >= 1024) {
                $fileSize = round($size / 1024, 2) . ' KB';
            } elseif ($size > 1) {
                $fileSize = $size . ' bytes';
            } elseif ($size == 1) {
                $fileSize = '1 byte';
            } else {
                $fileSize = '0 bytes';
            }
            return [
                'id' => $file->id,
                'name' => $file->name,
                'org_filename' => $file->org_filename,
                'file' => Crypt::decryptString($file->getRawOriginal('file')),
                'file_type' => $file->file_type,
                'folder_id' => $file->folder_id,
                'file_size' => $fileSize,
                'status' => $file->status ?? 'Released',
                'created_at' => $file->created_at ? $file->created_at->toDateTimeString() : null,
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

        return response()->json([
            'status' => 'success',
            'data' => $file,
        ]);
    }
    public function update(Request $request, $id)
    {
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

        return response()->json([
            'status' => 'success',
            'message' => 'File updated successfully.',
            'data' => $file,
        ]);
    }

    /**
     * 🗑 Delete file
     */
    public function destroy($id)
    {
        $file = File::find($id);

        if (!$file) {
            return response()->json([
                'status' => 'error',
                'message' => 'File not found.',
            ], 404);
        }

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

        return response()->json([
            'status' => 'success',
            'message' => 'File deleted successfully.',
        ]);
    }

    /**
     * 👀 Preview or download file
     */
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
        // Decode JSON inputs
        $request->merge([
            'reviewer_groups' => json_decode($request->input('reviewer_groups'), true),
            'reviewer_individual' => json_decode($request->input('reviewer_individual'), true),
            'reviewer_role' => json_decode($request->input('reviewer_role'), true),
            'approver_groups' => json_decode($request->input('approver_groups'), true),
            'approver_individual' => json_decode($request->input('approver_individual'), true),
            'approver_role' => json_decode($request->input('approver_role'), true),
            'keywords' => json_decode($request->input('keywords'), true),
            'categories' => json_decode($request->input('categories'), true),
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
                    // fallback: save as is
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
        $data['status'] = ($hasReviewers || $hasApprovers) ? 'Pending' : 'Released';

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

        return response()->json([
            'status' => 'success',
            'message' => 'File created successfully.',
            'data' => $fileRecord,
        ]);
    }

}