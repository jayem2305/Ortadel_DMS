<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    /**
     * Check if the authenticated user is a developer
     */
    private function checkDeveloperAccess()
    {
        $user = Auth::user();
        
        if (!$user) {
            abort(401, 'Unauthorized. Please log in.');
        }

        // Load role if not already loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }

        if (!$user->role || $user->role->name !== 'Developer') {
            abort(403, 'Access denied. Only developers can manage permissions.');
        }
    }

    /**
     * List all permissions (decrypted)
     * Only accessible by developers
     */
    public function index()
    {
        $this->checkDeveloperAccess();

        try {
            $permissions = Permission::all()->map(function ($perm) {
                return [
                    'id' => $perm->id,
                    'module' => $perm->module, // Will be decrypted by accessor
                    'name' => $perm->name, // Will be decrypted by accessor
                    'description' => $perm->description, // Will be decrypted by accessor
                    'created_by' => $perm->created_by ?? null,
                    'updated_by' => $perm->updated_by ?? null,
                    'created_at' => $perm->created_at,
                    'updated_at' => $perm->updated_at,
                ];
            });

            return response()->json($permissions)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Store a new permission (encrypt fields)
     * Only accessible by developers
     */
    public function store(Request $request)
    {
        $this->checkDeveloperAccess();

        $request->validate([
            'module' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $permission = Permission::create([
                'module' => $request->module,
                'name' => $request->name,
                'description' => $request->description,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // Log the action
            AuditLog::create([
                'action' => 'Created Permission',
                'module' => 'Permission Management',
                'target_user_id' => null,
                'description' => "Created permission: {$permission->name} in module {$permission->module}",
                'performed_by' => Auth::id(),
                'performed_at' => now(),
            ]);

            return response()->json([
                'message' => 'Permission created successfully',
                'permission' => [
                    'id' => $permission->id,
                    'module' => $permission->module,
                    'name' => $permission->name,
                    'description' => $permission->description,
                    'created_by' => $permission->created_by,
                    'updated_by' => $permission->updated_by,
                    'created_at' => $permission->created_at,
                    'updated_at' => $permission->updated_at,
                ]
            ], 201)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a specific permission
     * Only accessible by developers
     */
    public function show(Permission $permission)
    {
        $this->checkDeveloperAccess();

        try {
            return response()->json([
                'permission' => [
                    'id' => $permission->id,
                    'module' => $permission->module,
                    'name' => $permission->name,
                    'description' => $permission->description,
                    'created_by' => $permission->created_by,
                    'updated_by' => $permission->updated_by,
                    'created_at' => $permission->created_at,
                    'updated_at' => $permission->updated_at,
                ]
            ])
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Permission not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update an existing permission (encrypt fields)
     * Only accessible by developers
     */
    public function update(Request $request, Permission $permission)
    {
        $this->checkDeveloperAccess();

        $request->validate([
            'module' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $oldName = $permission->name;
            $oldModule = $permission->module;

            $permission->update([
                'module' => $request->module,
                'name' => $request->name,
                'description' => $request->description,
                'updated_by' => Auth::id(),
            ]);

            // Log the action
            AuditLog::create([
                'action' => 'Updated Permission',
                'module' => 'Permission Management',
                'target_user_id' => null,
                'description' => "Updated permission: {$oldModule}.{$oldName} -> {$permission->module}.{$permission->name}",
                'performed_by' => Auth::id(),
                'performed_at' => now(),
            ]);

            return response()->json([
                'message' => 'Permission updated successfully',
                'permission' => [
                    'id' => $permission->id,
                    'module' => $permission->module,
                    'name' => $permission->name,
                    'description' => $permission->description,
                    'updated_by' => $permission->updated_by,
                    'updated_at' => $permission->updated_at,
                ]
            ])
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a permission
     * Only accessible by developers
     */
    public function destroy(Permission $permission)
    {
        $this->checkDeveloperAccess();

        try {
            $permissionName = $permission->name;
            $permissionModule = $permission->module;

            // Log the action before deletion
            AuditLog::create([
                'action' => 'Deleted Permission',
                'module' => 'Permission Management',
                'target_user_id' => null,
                'description' => "Deleted permission: {$permissionModule}.{$permissionName}",
                'performed_by' => Auth::id(),
                'performed_at' => now(),
            ]);

            $permission->delete();

            return response()->json([
                'message' => 'Permission deleted successfully',
            ])
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
