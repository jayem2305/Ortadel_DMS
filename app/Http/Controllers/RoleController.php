<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    /**
     * List all roles with permissions
     */
    public function index()
    {
        try {
            $roles = Role::with(['permissions', 'creator', 'updater'])->get();

            // Transform to ensure consistent data format (accessors handle decryption)
            $roles = $roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name, // Will be decrypted by accessor
                    'type' => $role->type, // Will be decrypted by accessor
                    'color' => $role->color, // Will be decrypted by accessor
                    'description' => $role->description, // Will be decrypted by accessor
                    'scope' => $role->scope ?? 'global', // Will be decrypted by accessor if exists
                    'assign_to_groups' => $role->assign_to_groups ?? true,
                    'assign_to_users' => $role->assign_to_users ?? true,
                    'created_by' => $role->created_by,
                    'updated_by' => $role->updated_by,
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at,
                    'permissions' => $role->permissions->map(function ($perm) {
                        return [
                            'id' => $perm->id,
                            'name' => $perm->name, // Will be decrypted by accessor
                            'module' => $perm->module, // Will be decrypted by accessor
                            'description' => $perm->description, // Will be decrypted by accessor
                        ];
                    }),
                ];
            });

            return response()->json($roles)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show single role with permissions
     */
    public function show(Role $role)
    {
        try {
            $role->load(['permissions', 'creator', 'updater']);

            return response()->json([
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'type' => $role->type,
                    'color' => $role->color,
                    'description' => $role->description,
                    'scope' => $role->scope ?? 'global',
                    'assign_to_groups' => $role->assign_to_groups ?? true,
                    'assign_to_users' => $role->assign_to_users ?? true,
                    'created_by' => $role->created_by,
                    'updated_by' => $role->updated_by,
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at,
                    'permissions' => $role->permissions->map(function ($perm) {
                        return [
                            'id' => $perm->id,
                            'name' => $perm->name,
                            'module' => $perm->module,
                            'description' => $perm->description,
                        ];
                    }),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Role not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Store new role
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:system,custom',
            'color' => 'required|string|max:7', // hex color
            'description' => 'nullable|string',
            'scope' => 'nullable|string|in:global,local',
            'assign_to_groups' => 'boolean',
            'assign_to_users' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        try {
            $role = Role::create([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'color' => $validated['color'],
                'description' => $validated['description'] ?? null,
                'scope' => $validated['scope'] ?? 'global',
                'assign_to_groups' => $validated['assign_to_groups'] ?? true,
                'assign_to_users' => $validated['assign_to_users'] ?? true,
                'created_by' => Auth::id() ?? 1,
                'updated_by' => Auth::id() ?? 1,
            ]);

            // Attach permissions using existing role_permission pivot table
            if (!empty($validated['permissions'])) {
                $pivotData = [];
                foreach ($validated['permissions'] as $permissionId) {
                    $pivotData[$permissionId] = [
                        'created_by' => Auth::id() ?? 1,
                        'updated_by' => Auth::id() ?? 1,
                    ];
                }
                $role->permissions()->attach($pivotData);
            }

            // Load relationships for response
            $role->load('permissions');

            // Log the action
            AuditLog::create([
                'action' => 'Created Role',
                'module' => 'Role Management',
                'target_user_id' => null,
                'description' => "Created role: {$role->name} ({$role->type})",
                'performed_by' => Auth::id() ?? 1,
                'performed_at' => now(),
            ]);

            return response()->json([
                'message' => 'Role created successfully',
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'type' => $role->type,
                    'color' => $role->color,
                    'description' => $role->description,
                    'scope' => $role->scope,
                    'assign_to_groups' => $role->assign_to_groups,
                    'assign_to_users' => $role->assign_to_users,
                    'permissions' => $role->permissions->map(function ($perm) {
                        return [
                            'id' => $perm->id,
                            'name' => $perm->name,
                            'module' => $perm->module,
                            'description' => $perm->description,
                        ];
                    }),
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update role
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:system,custom',
            'color' => 'required|string|max:7',
            'description' => 'nullable|string',
            'scope' => 'nullable|string|in:global,local',
            'assign_to_groups' => 'boolean',
            'assign_to_users' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        try {
            $oldName = $role->name;

            $role->update([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'color' => $validated['color'],
                'description' => $validated['description'] ?? null,
                'scope' => $validated['scope'] ?? 'global',
                'assign_to_groups' => $validated['assign_to_groups'] ?? true,
                'assign_to_users' => $validated['assign_to_users'] ?? true,
                'updated_by' => Auth::id() ?? 1,
            ]);

            // Sync permissions using existing role_permission pivot table
            if (isset($validated['permissions'])) {
                $pivotData = [];
                foreach ($validated['permissions'] as $permissionId) {
                    $pivotData[$permissionId] = [
                        'created_by' => $role->created_by,
                        'updated_by' => Auth::id() ?? 1,
                    ];
                }
                $role->permissions()->sync($pivotData);
            }

            // Load relationships for response
            $role->load('permissions');

            // Log the action
            AuditLog::create([
                'action' => 'Updated Role',
                'module' => 'Role Management',
                'target_user_id' => null,
                'description' => "Updated role: {$oldName} -> {$role->name}",
                'performed_by' => Auth::id() ?? 1,
                'performed_at' => now(),
            ]);

            return response()->json([
                'message' => 'Role updated successfully',
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'type' => $role->type,
                    'color' => $role->color,
                    'description' => $role->description,
                    'scope' => $role->scope,
                    'assign_to_groups' => $role->assign_to_groups,
                    'assign_to_users' => $role->assign_to_users,
                    'permissions' => $role->permissions->map(function ($perm) {
                        return [
                            'id' => $perm->id,
                            'name' => $perm->name,
                            'module' => $perm->module,
                            'description' => $perm->description,
                        ];
                    }),
                    'updated_at' => $role->updated_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete role
     */
    public function destroy(Role $role)
    {
        try {
            $roleName = $role->name;

            // Detach all permissions
            $role->permissions()->detach();

            // Log the action before deletion
            AuditLog::create([
                'action' => 'Deleted Role',
                'module' => 'Role Management',
                'target_user_id' => null,
                'description' => "Deleted role: {$roleName}",
                'performed_by' => Auth::id() ?? 1,
                'performed_at' => now(),
            ]);

            $role->delete();

            return response()->json(['message' => 'Role deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete role',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
