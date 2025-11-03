<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use App\Models\AuditLog;
use App\Services\PermissionService;
use App\Services\NotificationService;
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
            $groups = Group::with(['users'])->withCount('users')->get()->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name, // Will be decrypted by accessor
                    'description' => $group->description, // Will be decrypted by accessor
                    'status' => $group->status,
                    'assigned_color' => $group->assigned_color,
                    'logo' => $group->logo ? asset('storage/' . $group->logo) : null,
                    'users_count' => $group->users_count,
                    'users' => $group->users->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'first_name' => $user->first_name,
                            'last_name' => $user->last_name,
                            'email' => $user->email,
                        ];
                    }),
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
            \Log::info('[GroupController] Store method called');
            \Log::info('[GroupController] Request has logo file: ' . ($request->hasFile('logo') ? 'YES' : 'NO'));
            if ($request->hasFile('logo')) {
                \Log::info('[GroupController] Logo file details: ', [
                    'name' => $request->file('logo')->getClientOriginalName(),
                    'size' => $request->file('logo')->getSize(),
                    'mime' => $request->file('logo')->getMimeType(),
                ]);
            }

            // If frontend sent users as a JSON string (older clients), decode to array so validation passes
            if ($request->has('users') && is_string($request->users)) {
                $decoded = json_decode($request->users, true);
                if (is_array($decoded)) {
                    $request->merge(['users' => $decoded]);
                }
            }
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) {
                        // Check if encrypted name already exists
                        $exists = Group::all()->first(function ($group) use ($value) {
                            return strtolower($group->name) === strtolower($value);
                        });
                        if ($exists) {
                            $fail('A group with this name already exists.');
                        }
                    }
                ],
                'description' => 'nullable|string',
                // status is optional at creation; default to 'active' if not provided
                'status' => 'nullable|in:active,inactive',
                'assigned_color' => 'nullable|string|max:7', // hex color
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'users' => 'nullable|array',
                'users.*' => 'integer|exists:users,id',
            ], [
                'logo.image' => 'The logo must be an image file.',
                'logo.mimes' => 'The logo must be a file of type: jpeg, png, jpg, gif.',
                'logo.max' => 'The logo file size must not exceed 2MB.',
                'name.required' => 'Group name is required.',
                'users.*.exists' => 'One or more selected users do not exist.',
            ]);

            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('groups', 'public');
                \Log::info('[GroupController] Logo saved to: ' . $logoPath);
            } else {
                \Log::info('[GroupController] No logo file in request');
            }

            $group = Group::create([
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status ?? 'active',
                'assigned_color' => $request->assigned_color ?? '#000000',
                'logo' => $logoPath,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // Attach users if provided with audit columns
            if ($request->has('users') && is_array($request->users)) {
                $userData = [];
                foreach ($request->users as $userId) {
                    $userData[$userId] = [
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                $group->users()->attach($userData);
            }

            // Refresh the model to get the properly decrypted values
            $group->refresh();

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
                    'logo' => $group->logo ? asset('storage/' . $group->logo) : null,
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
     * Show a specific group with its users
     */
    public function show(Group $group)
    {
        try {
            $group->load(['users']);

            return response()->json([
                'group' => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                    'status' => $group->status,
                    'assigned_color' => $group->assigned_color,
                    'logo' => $group->logo ? asset('storage/' . $group->logo) : null,
                    'users' => $group->users->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'first_name' => $user->first_name,
                            'last_name' => $user->last_name,
                            'email' => $user->email,
                            'status' => $user->status,
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
            // If frontend sent users as a JSON string (older clients), decode to array so validation passes
            if ($request->has('users') && is_string($request->users)) {
                $decoded = json_decode($request->users, true);
                if (is_array($decoded)) {
                    $request->merge(['users' => $decoded]);
                }
            }
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) use ($group) {
                        // Check if encrypted name already exists (excluding current group)
                        $exists = Group::where('id', '!=', $group->id)->get()->first(function ($g) use ($value) {
                            return strtolower($g->name) === strtolower($value);
                        });
                        if ($exists) {
                            $fail('A group with this name already exists.');
                        }
                    }
                ],
                'description' => 'nullable|string',
                'status' => 'required|in:active,inactive',
                'assigned_color' => 'nullable|string|max:7', // hex color
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'users' => 'nullable|array',
                'users.*' => 'integer|exists:users,id',
            ], [
                'logo.image' => 'The logo must be an image file.',
                'logo.mimes' => 'The logo must be a file of type: jpeg, png, jpg, gif.',
                'logo.max' => 'The logo file size must not exceed 2MB.',
                'name.required' => 'Group name is required.',
                'users.*.exists' => 'One or more selected users do not exist.',
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

            // Sync users if provided with audit columns
            if ($request->has('users')) {
                $userData = [];
                foreach (($request->users ?? []) as $userId) {
                    $userData[$userId] = [
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                $group->users()->sync($userData);
            }

            // Refresh the model to get the properly decrypted values
            $group->refresh();

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
                    'logo' => $group->logo ? asset('storage/' . $group->logo) : null,
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
            $groupName = $group->name;
            $usersCount = $group->users()->count();

            // Detach all users from the group (only removes relationship, doesn't delete users)
            if ($usersCount > 0) {
                $group->users()->detach();
            }

            // Delete logo if exists
            if ($group->logo) {
                Storage::disk('public')->delete($group->logo);
            }

            // Log the action before deletion
            AuditLog::create([
                'action' => 'Deleted Group',
                'module' => 'Group Management',
                'target_user_id' => null,
                'description' => "Deleted group: {$groupName}" . ($usersCount > 0 ? " (unassigned {$usersCount} users)" : ""),
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

            // 🔔 Send notifications to added users
            $notificationService = app(NotificationService::class);
            foreach ($users as $user) {
                $notificationService->groupUpdated(
                    $user->user_id,
                    $group->name,
                    'added',
                    Auth::user()->name ?? 'Administrator'
                );
            }

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

            // 🔔 Send notifications to removed users
            $notificationService = app(NotificationService::class);
            foreach ($users as $user) {
                $notificationService->groupUpdated(
                    $user->user_id,
                    $group->name,
                    'removed',
                    Auth::user()->name ?? 'Administrator'
                );
            }

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

    /**
     * Get all users in a specific group
     */
    public function getUsersInGroup($id)
    {
        try {
            $group = Group::with('users')->findOrFail($id);

            $users = $group->users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'user_id' => $user->user_id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                ];
            });

            return response()->json([
                'success' => true,
                'users' => $users
            ])
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users in group',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
