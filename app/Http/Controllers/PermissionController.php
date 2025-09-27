<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Collection;

class PermissionController extends Controller
{
    /**
     * List all permissions (decrypted)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $permissions = Permission::all()->map(function ($perm) {
            // Attempt decryption, fallback if fails
            try {
                $name = Crypt::decryptString($perm->name);
            } catch (\Exception $e) {
                $name = $perm->name; // fallback if not encrypted
            }

            try {
                $module = Crypt::decryptString($perm->module);
            } catch (\Exception $e) {
                $module = $perm->module; // fallback if not encrypted
            }

            try {
                $description = $perm->description ? Crypt::decryptString($perm->description) : null;
            } catch (\Exception $e) {
                $description = $perm->description; // fallback if not encrypted
            }

            return [
                'id' => $perm->id,
                'module' => $module,
                'name' => $name,
                'description' => $description,
                'created_by' => $perm->created_by,
                'updated_by' => $perm->updated_by,
                'created_at' => $perm->created_at,
                'updated_at' => $perm->updated_at,
            ];
        });

        return response()->json($permissions);
    }
    /**
     * Store a new permission (encrypt fields)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'module' => 'required|string',
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $permission = Permission::create([
            'module' => Crypt::encryptString($request->module),
            'name' => Crypt::encryptString($request->name),
            'description' => $request->description ? Crypt::encryptString($request->description) : null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        /** @var object{id: int, module: string, name: string, description: string|null, created_by: int|null, updated_by: int|null, created_at: \Illuminate\Support\Carbon, updated_at: \Illuminate\Support\Carbon} */
        $response = (object) [
            'id' => $permission->id,
            'module' => $request->module,
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
            'created_at' => $permission->created_at,
            'updated_at' => $permission->updated_at,
        ];

        return response()->json($response);
    }

    /**
     * Update an existing permission (encrypt fields)
     *
     * @param Request $request
     * @param Permission $permission
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'module' => 'required|string',
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $permission->update([
            'module' => Crypt::encryptString($request->module),
            'name' => Crypt::encryptString($request->name),
            'description' => $request->description ? Crypt::encryptString($request->description) : null,
            'updated_by' => Auth::id(),
        ]);

        /** @var object{id: int, module: string, name: string, description: string|null, updated_by: int|null, updated_at: \Illuminate\Support\Carbon} */
        $response = (object) [
            'id' => $permission->id,
            'module' => $request->module,
            'name' => $request->name,
            'description' => $request->description,
            'updated_by' => Auth::id(),
            'updated_at' => $permission->updated_at,
        ];

        return response()->json($response);
    }

    /**
     * Delete a permission
     *
     * @param Permission $permission
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        /** @var object{message: string} */
        $response = (object) [
            'message' => 'Permission deleted successfully',
        ];

        return response()->json($response);
    }
}
