<?php

namespace App\Http\Controllers;

use App\Http\Requests\FolderRequest;
use App\Models\Folder;
use App\Models\AuditLog;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FolderController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * List all folders with hierarchy
     */
    public function index(): JsonResponse
    {
        try {
            // Check if user has permission to view folders
            if (!$this->permissionService->hasPermission(Auth::user(), 'Document Management', 'View Folders')) {
                return response()->json([
                    'message' => 'Access denied. You do not have permission to view folders.',
                ], 403);
            }

            $folders = Folder::with(['parent', 'children', 'files'])->get()->map(function ($folder) {
                return [
                    'id' => $folder->id,
                    'name' => $folder->name,
                    'description' => $folder->description,
                    'parent_id' => $folder->parent_id,
                    'path' => $folder->path,
                    'status' => $folder->status,
                    'parent' => $folder->parent ? [
                        'id' => $folder->parent->id,
                        'name' => $folder->parent->name,
                    ] : null,
                    'children_count' => $folder->children->count(),
                    'files_count' => $folder->files->count(),
                    'created_by' => $folder->created_by,
                    'updated_by' => $folder->updated_by,
                    'created_at' => $folder->created_at,
                    'updated_at' => $folder->updated_at,
                ];
            });

            return response()->json([
                'folders' => $folders
            ])
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch folders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new folder
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Check if user has permission to create folders
            if (!$this->permissionService->hasPermission(Auth::user(), 'Document Management', 'Create Folders')) {
                return response()->json([
                    'message' => 'Access denied. You do not have permission to create folders.',
                ], 403);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'parent_id' => 'nullable|exists:folders,id',
                'path' => 'nullable|string',
                //'status' => 'required|in:active,inactive', no need to indicate this part Default value always Active
            ]);

            // If no folders exist, root folder has parent_id = null
            $parentId = $request->parent_id;
            if (Folder::count() === 0) {
                $parentId = null;
            }

            $folder = Folder::create([
                'name' => $request->name,
                'description' => $request->description,
                'parent_id' => $parentId,
                'path' => $request->path,
                'status' => $request->status ?? 'active',
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // Log the action
            AuditLog::create([
                'action' => 'Created Folder',
                'module' => 'File Management',
                'target_user_id' => null,
                'description' => "Created folder: {$folder->name}",
                'performed_by' => Auth::id(),
                'performed_at' => now(),
            ]);

            return response()->json([
                'message' => 'Folder created successfully',
                'folder' => [
                    'id' => $folder->id,
                    'name' => $folder->name,
                    'description' => $folder->description,
                    'parent_id' => $folder->parent_id,
                    'path' => $folder->path,
                    'status' => $folder->status ?? 'active',
                    'created_by' => $folder->created_by,
                    'updated_by' => $folder->updated_by,
                    'created_at' => $folder->created_at,
                    'updated_at' => $folder->updated_at,
                ]
            ], 201)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create folder',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a specific folder with details
     */
    public function show(Folder $folder): JsonResponse
    {
        try {
            // Check if user has permission to view folders
            if (!$this->permissionService->hasPermission(Auth::user(), 'Document Management', 'View Folders')) {
                return response()->json([
                    'message' => 'Access denied. You do not have permission to view folders.',
                ], 403);
            }

            $folder->load(['parent', 'children', 'files']);

            return response()->json([
                'folder' => [
                    'id' => $folder->id,
                    'name' => $folder->name,
                    'description' => $folder->description,
                    'parent_id' => $folder->parent_id,
                    'path' => $folder->path,
                    'status' => $folder->status,
                    'parent' => $folder->parent ? [
                        'id' => $folder->parent->id,
                        'name' => $folder->parent->name,
                    ] : null,
                    'children' => $folder->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'status' => $child->status,
                        ];
                    }),
                    'files' => $folder->files->map(function ($file) {
                        return [
                            'id' => $file->id,
                            'name' => $file->name,
                            'file_size' => $file->file_size,
                            'file_type' => $file->file_type,
                            'status' => $file->status,
                        ];
                    }),
                    'created_by' => $folder->created_by,
                    'updated_by' => $folder->updated_by,
                    'created_at' => $folder->created_at,
                    'updated_at' => $folder->updated_at,
                ]
            ])
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Folder not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update an existing folder
     */
    public function update(Request $request, Folder $folder): JsonResponse
    {
        try {
            // Check if user has permission to update folders
            if (!$this->permissionService->hasPermission(Auth::user(), 'Document Management', 'Edit Folders')) {
                return response()->json([
                    'message' => 'Access denied. You do not have permission to update folders.',
                ], 403);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'parent_id' => 'nullable|exists:folders,id',
                'path' => 'nullable|string',
                'status' => 'required|in:active,inactive',
            ]);

            $oldName = $folder->name;

            $folder->update([
                'name' => $request->name,
                'description' => $request->description,
                'parent_id' => $request->parent_id,
                'path' => $request->path,
                'status' => $request->status,
                'updated_by' => Auth::id(),
            ]);

            // Log the action
            AuditLog::create([
                'action' => 'Updated Folder',
                'module' => 'File Management',
                'target_user_id' => null,
                'description' => "Updated folder: {$oldName} -> {$folder->name}",
                'performed_by' => Auth::id(),
                'performed_at' => now(),
            ]);

            return response()->json([
                'message' => 'Folder updated successfully',
                'folder' => [
                    'id' => $folder->id,
                    'name' => $folder->name,
                    'description' => $folder->description,
                    'parent_id' => $folder->parent_id,
                    'path' => $folder->path,
                    'status' => $folder->status,
                    'updated_by' => $folder->updated_by,
                    'updated_at' => $folder->updated_at,
                ]
            ])
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update folder',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a folder
     */
    public function destroy(Folder $folder): JsonResponse
    {
        try {
            // Check if user has permission to delete folders
            if (!$this->permissionService->hasPermission(Auth::user(), 'Document Management', 'Delete Folders')) {
                return response()->json([
                    'message' => 'Access denied. You do not have permission to delete folders.',
                ], 403);
            }

            // Check if folder has children or files
            if ($folder->children()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete folder. Folder contains subfolders.',
                ], 422);
            }

            if ($folder->files()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete folder. Folder contains files.',
                ], 422);
            }

            $folderName = $folder->name;

            // Log the action before deletion
            AuditLog::create([
                'action' => 'Deleted Folder',
                'module' => 'File Management',
                'target_user_id' => null,
                'description' => "Deleted folder: {$folderName}",
                'performed_by' => Auth::id(),
                'performed_at' => now(),
            ]);

            $folder->delete();

            return response()->json([
                'message' => 'Folder deleted successfully',
            ])
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete folder',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
