<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\AuditLog;
class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find user by email (decrypted)
        $user = User::where('email_hash', hash('sha256', $request->email))->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Create token (Laravel Sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        AuditLog::create([
            'action' => 'Logged In',
            'module' => 'Login',
            'target_user_id' => $user->id,
            'description' => "{$user->first_name} {$user->last_name} is Active Today.",
            'performed_by' => $user->id, // system
            'performed_at' => now(),
        ]);

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }
}
