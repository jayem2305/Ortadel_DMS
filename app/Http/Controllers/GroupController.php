<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use App\Models\AuditLog;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GroupController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * List all groups with users count
     */
    public function index()
    {
        try {
            $groups = Group::withCount('users')->get()->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name, // Will be decrypted by accessor
                    'description' => $group->description, // Will be decrypted by accessor
                    'status' => $group->status,
                    'assigned_color' => $group->assigned_color,
                    'logo' => $group->logo,
                    'users_count' => $group->users_count,
                    'created_by' => $group->created_by,
                    'updated_by' => $group->updated_by,
                    'created_at' => $group->created_at,
                    'updated_at' => $group->updated_at,
                ];
            });

            return response()->json($groups)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch groups',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new group
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:active,inactive',
                'assigned_color' => 'nullable|string|max:7', // hex color
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'users' => 'nullable|array',
                'users.*' => 'integer|exists:users,id',
                'roles' => 'nullable|array',
                'roles.*' => 'integer|exists:roles,id',
            ]);

            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('groups', 'public');
            }

            $group = Group::create([
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status,
                'assigned_color' => $request->assigned_color ?? '#000000',
                'logo' => $logoPath,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // Attach users if provided
            if ($request->has('users') && is_array($request->users)) {
                $group->users()->attach($request->users);
            }

            // Attach roles if provided
            if ($request->has('roles') && is_array($request->roles)) {
                $group->roles()->attach($request->roles);
            }

            // Log the action
            AuditLog::create([
                'action' => 'Created Group',
                'module' => 'Group Management',
                'target_user_id' => null,
                'description' => "Created group: {$group->name}",
                'performed_by' => Auth::id(),
                'performed_at' => now(),
            ]);

            return response()->json([
                'message' => 'Group created successfully',
                'group' => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                    'status' => $group->status,
                    'assigned_color' => $group->assigned_color,
                    'logo' => $group->logo,
                    'created_by' => $group->created_by,
                    'updated_by' => $group->updated_by,
                    'created_at' => $group->created_at,
                    'updated_at' => $group->updated_at,
                ]
            ], 201)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create group',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a specific group with its users and roles
     */
    public function show(Group $group)
    {
        try {
            $group->load([
                'users' => function($query) {
                    $query->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.status');
                },
                'roles' => function($query) {
                    $query->select('roles.id', 'roles.name', 'roles.description', 'roles.color');
                }
            ]);

            return response()->json([
                'group' => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                    'status' => $group->status,
                    'assigned_color' => $group->assigned_color,
                    'logo' => $group->logo,
                    'users' => $group->users->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'first_name' => $user->first_name,
                            'last_name' => $user->last_name,
                            'email' => $user->email,
                            'status' => $user->status,
                        ];
                    }),
                    'roles' => $group->roles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                            'description' => $role->description,
                            'color' => $role->color,
                        ];
                    }),
                    'created_by' => $group->created_by,
                    'updated_by' => $group->updated_by,
                    'created_at' => $group->created_at,
                    'updated_at' => $group->updated_at,
                ]
            ])
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Group not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update an existing group
     */
    public function update(Request $request, Group $group)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:active,inactive',
                'assigned_color' => 'nullable|string|max:7', // hex color
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'users' => 'nullable|array',
                'users.*' => 'integer|exists:users,id',
                'roles' => 'nullable|array',
                'roles.*' => 'integer|exists:roles,id',
            ]);

            $oldName = $group->name;
            $logoPath = $group->logo;

            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($group->logo) {
                    Storage::disk('public')->delete($group->logo);
                }
                $logoPath = $request->file('logo')->store('groups', 'public');
            }

            $group->update([
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status,
                'assigned_color' => $request->assigned_color ?? $group->assigned_color,
                'logo' => $logoPath,
                'updated_by' => Auth::id(),
            ]);

            // Sync users if provided
            if ($request->has('users')) {
                $group->users()->sync($request->users ?? []);
            }

            // Sync roles if provided
            if ($request->has('roles')) {
                $group->roles()->sync($request->roles ?? []);
            }

            // Log the action
            AuditLog::create([
                'action' => 'Updated Group',
                'module' => 'Group Management',
                'target_user_id' => null,
                'description' => "Updated group: {$oldName} -> {$group->name}",
                'performed_by' => Auth::id(),
                'performed_at' => now(),
            ]);

            return response()->json([
                'message' => 'Group updated successfully',
                'group' => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                    'status' => $group->status,
                    'assigned_color' => $group->assigned_color,
                    'logo' => $group->logo,
                    'updated_by' => $group->updated_by,
                    'updated_at' => $group->updated_at,
                ]
            ])
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update group',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a group
     */
    public function destroy(Group $group)
    {
        try {
            // Check if group has users
            if ($group->users()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete group. Group has assigned users.',
                ], 422);
            }

            $groupName = $group->name;

            // Delete logo if exists
            if ($group->logo) {
                Storage::disk('public')->delete($group->logo);
            }

            // Log the action before deletion
            AuditLog::create([
                'action' => 'Deleted Group',
                'module' => 'Group Management',
                'target_user_id' => null,
                'description' => "Deleted group: {$groupName}",
                'performed_by' => Auth::id(),
                'performed_at' => now(),
            ]);

            $group->delete();

            return response()->json([
                'message' => 'Group deleted successfully',
            ])
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete group',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add users to a group
     */
    public function addUsers(Request $request, Group $group)
    {
        try {
            // Check if user has permission to update groups
            if (!$this->permissionService->hasPermission(Auth::user(), 'Group Management', 'update')) {
                return response()->json([
                    'message' => 'Access denied. You do not have permission to modify group membership.',
                ], 403);
            }

            $request->validate([
                'user_ids' => 'required|array',
                'user_ids.*' => 'exists:users,id',
            ]);

            $users = User::whereIn('id', $request->user_ids)->get();
            $group->users()->syncWithoutDetaching($request->user_ids);

            // Log the action
            $userNames = $users->pluck('first_name')->implode(', ');
            AuditLog::create([
                'action' => 'Added Users to Group',
                'module' => 'Group Management',
                'target_user_id' => null,
                'description' => "Added users ({$userNames}) to group: {$group->name}",
                'performed_by' => Auth::id(),
                'performed_at' => now(),
            ]);

            return response()->json([
                'message' => 'Users added to group successfully',
            ])
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add users to group',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove users from a group
     */
    public function removeUsers(Request $request, Group $group)
    {
        try {
            // Check if user has permission to update groups
            if (!$this->permissionService->hasPermission(Auth::user(), 'Group Management', 'update')) {
                return response()->json([
                    'message' => 'Access denied. You do not have permission to modify group membership.',
                ], 403);
            }

            $request->validate([
                'user_ids' => 'required|array',
                'user_ids.*' => 'exists:users,id',
            ]);

            $users = User::whereIn('id', $request->user_ids)->get();
            $group->users()->detach($request->user_ids);

            // Log the action
            $userNames = $users->pluck('first_name')->implode(', ');
            AuditLog::create([
                'action' => 'Removed Users from Group',
                'module' => 'Group Management',
                'target_user_id' => null,
                'description' => "Removed users ({$userNames}) from group: {$group->name}",
                'performed_by' => Auth::id(),
                'performed_at' => now(),
            ]);

            return response()->json([
                'message' => 'Users removed from group successfully',
            ])
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove users from group',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
