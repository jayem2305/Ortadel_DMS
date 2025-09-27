<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    // List roles
    public function index()
    {
        $roles = Role::with(['permissions', 'creator', 'updater'])->get();

        // Decrypt fields before sending
        $roles->transform(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,           // decrypted automatically via accessor
                'type' => $role->type,
                'color' => $role->color,
                'description' => $role->description,
                'permissions' => $role->permissions->map(function ($perm) {
                    return [
                        'id' => $perm->id,
                        'name' => $perm->name,
                        'module' => $perm->module,
                        'description' => $perm->description,
                    ];
                }),
            ];
        });

        return response()->json($roles);
    }


    // Show single role
    public function show(Role $role)
    {
        $role->load(['permissions', 'creator', 'updater']);

        $role->name = decrypt($role->name);
        $role->type = decrypt($role->type);
        $role->color = decrypt($role->color);
        if ($role->description) {
            $role->description = decrypt($role->description);
        }

        $role->permissions->transform(function ($permission) {
            $permission->module = decrypt($permission->module);
            $permission->name = decrypt($permission->name);
            if ($permission->description) {
                $permission->description = decrypt($permission->description);
            }
            return $permission;
        });

        if ($role->creator) {
            $role->creator->name = decrypt($role->creator->name);
        }
        if ($role->updater) {
            $role->updater->name = decrypt($role->updater->name);
        }

        return response()->json($role);
    }

    // Store new role
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'color' => 'required|string',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id', // ✅ only IDs
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'color' => $validated['color'],
            'description' => $validated['description'] ?? null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        if (!empty($validated['permissions'])) {
            $pivotData = [];
            foreach ($validated['permissions'] as $pid) {
                $pivotData[$pid] = [
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ];
            }
            $role->permissions()->attach($pivotData);
        }

        return response()->json($role->load(['permissions', 'creator', 'updater']), 201);
    }

    // Update role
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'color' => 'required|string',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id', // ✅ only IDs
        ]);

        $role->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'color' => $validated['color'],
            'description' => $validated['description'] ?? null,
            'updated_by' => Auth::id(),
        ]);

        if (isset($validated['permissions'])) {
            $pivotData = [];
            foreach ($validated['permissions'] as $pid) {
                $pivotData[$pid] = [
                    'created_by' => $role->created_by,
                    'updated_by' => Auth::id(),
                ];
            }
            $role->permissions()->sync($pivotData);
        }

        return response()->json($role->load(['permissions', 'creator', 'updater']));
    }

    // Delete role
    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(['message' => 'Role deleted successfully']);
    }
}
