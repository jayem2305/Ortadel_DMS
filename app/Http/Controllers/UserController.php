<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Store a new user in the database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    if (User::where('email_hash', hash('sha256', $value))->exists()) {
                        $fail('The ' . $attribute . ' has already been taken.');
                    }
                }
            ],
            'password' => 'required|string|confirmed|min:8',
            'assigned_color' => 'nullable|string',
            'role_id' => 'required|integer|exists:roles,id',
            'groups' => 'nullable|array',
        ]);

        $user = User::create([
            'user_id' => $validated['user_id'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => $validated['password'], // auto-hashed by cast
            'assigned_color' => $validated['assigned_color'] ?? null,
            'role_id' => $validated['role_id'],
            'groups' => $validated['groups'] ?? [],
            'created_by' => Auth::id(),
            'last_updated_by' => Auth::id(),
        ]);

        // 🔹 Log action
        AuditLog::create([
            'action' => 'Created',
            'module' => 'USER',
            'target_user_id' => $user->id,
            'description' => "A new user ({$user->first_name} {$user->last_name}) was created successfully.",
            'performed_by' => Auth::id(),
            'performed_at' => now(),
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ]);
    }

    /**
     * List all users
     */
    public function index()
    {
        return response()->json(User::with('role')->get());
    }

    /**
     * Show single user
     */
    public function show(User $user)
    {
        $user->load('role');
        return response()->json($user);
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name' => ['sometimes', 'string', 'max:100'],
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', 'min:8'],
            'assigned_color' => ['nullable', 'string', 'max:20'],
            'role_id' => ['sometimes', 'integer', 'exists:roles,id'],
            'groups' => ['nullable', 'array'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['last_updated_by'] = Auth::id();

        $user->update($validated);
        $user->load('role');

        // 🔹 Log action
        AuditLog::create([
            'action' => 'Updated',
            'module' => 'USER',
            'target_user_id' => $user->id,
            'description' => "User ({$user->first_name} {$user->last_name}) was updated.",
            'performed_by' => Auth::id(),
            'performed_at' => now(),
        ]);

        return response()->json([
            'message' => 'User updated successfully!',
            'user' => $user,
        ]);
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        $userName = "{$user->first_name} {$user->last_name}";
        $userId = $user->id;

        $user->delete();

        // 🔹 Log action
        AuditLog::create([
            'action' => 'Deleted',
            'module' => 'USER',
            'target_user_id' => $userId,
            'description' => "User ($userName) was deleted.",
            'performed_by' => Auth::id(),
            'performed_at' => now(),
        ]);

        return response()->json(['message' => 'User deleted successfully!']);
    }

    /**
     * Get the last assigned user_id (for incremental DMS_xxxx codes)
     */
    public function getLastUserId()
    {
        $lastUser = User::orderBy('id', 'desc')->first();

        $lastId = 0;
        if ($lastUser) {
            $lastId = (int) str_replace('DMS_', '', $lastUser->user_id);
        }

        return response()->json([
            'last_id' => $lastId,
            'next_user_id' => 'DMS_' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT)
        ]);
    }
}
