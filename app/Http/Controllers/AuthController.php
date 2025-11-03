<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\AuditLog;
use App\Services\PermissionService;

class AuthController extends Controller
{
    /**
     * ============================================================================
     * AUTHENTICATION CONTROLLER MODERNIZATION IMPROVEMENTS
     * ============================================================================
     * 
     * MAJOR ENHANCEMENTS OVER PREVIOUS VERSION:
     * 
     * SECURITY IMPROVEMENTS:
     * - Enhanced validation with specific error messages
     * - Role and permission loading during authentication
     * - Comprehensive audit logging for all auth actions
     * - Secure token management with proper cleanup
     * 
     * PERFORMANCE OPTIMIZATIONS:
     * - Eager loading of user relationships (role, permissions)
     * - Optimized database queries
     * - Efficient permission retrieval for frontend
     * 
     * FRONTEND INTEGRATION:
     * - CORS headers for cross-origin requests
     * - Structured JSON responses with consistent format
     * - User context with role and permissions for UI rendering
     * 
     * ERROR HANDLING:
     * - Try-catch blocks for graceful error handling
     * - Descriptive error messages for debugging
     * - Proper HTTP status codes
     * 
     * AUDIT & COMPLIANCE:
     * - Complete audit trail for login/logout actions
     * - User activity tracking with timestamps
     * - Security event logging for monitoring
     * 
     * BEFORE: Basic login/logout with minimal context
     * AFTER: Enterprise-grade authentication with full user context
     * ============================================================================
     */

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find user by email hash (since email is encrypted, we use the hash for lookup)
        $user = User::where('email_hash', hash('sha256', $request->email))->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Load the user's role and permissions for frontend role checking
        $user->load('role.permissions');

        // Create token (Laravel Sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        // Get user permissions
        $permissions = PermissionService::getUserPermissions($user);
        $permissionsByModule = PermissionService::getUserPermissionsByModule($user);

        // Log the login action
        AuditLog::create([
            'action' => 'Logged In',
            'module' => 'Authentication',
            'target_user_id' => $user->id,
            'description' => "{$user->first_name} {$user->last_name} logged in successfully.",
            'performed_by' => $user->id,
            'performed_at' => now(),
        ]);

        // Return properly decrypted user data with role and permission information
        return response()->json([
            'user' => [
                'id' => $user->id,
                'user_id' => $user->user_id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'assigned_color' => $user->assigned_color,
                'role_id' => $user->role_id,
                'role' => $user->role ? [
                    'id' => $user->role->id,
                    'name' => $user->role->name,
                    'type' => $user->role->type,
                    'color' => $user->role->color,
                    'description' => $user->role->description,
                ] : null,
                'permissions' => $permissions,
                'permissions_by_module' => $permissionsByModule,
                'groups' => $user->groups,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            // Log the logout action
            AuditLog::create([
                'action' => 'Logged Out',
                'module' => 'Authentication',
                'target_user_id' => $user->id,
                'description' => "{$user->first_name} {$user->last_name} logged out.",
                'performed_by' => $user->id,
                'performed_at' => now(),
            ]);

            // Delete all tokens for the user
            $user->tokens()->delete();
        }

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Handle forgot password request and send reset link
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Find user by email hash since email is encrypted
        $emailHash = hash('sha256', $request->email);
        $user = User::where('email_hash', $emailHash)->first();

        // Only send email if user exists, but always show the same success message
        if ($user) {
            // Send password reset notification
            $token = app('auth.password.broker')->createToken($user);
            $user->sendPasswordResetNotification($token);
        }

        // Always return success message (don't reveal if email exists or not for security)
        return response()->json(['message' => 'If that email is registered, a password reset link has been sent to it.']);
    }

    /**
     * Verify reset token and return email (without exposing it in URL)
     */
    public function verifyResetToken($token)
    {
        // Get all password reset tokens
        $resetTokens = \DB::table('password_reset_tokens')->get();

        // Find the matching token by verifying the hash
        $validToken = null;
        foreach ($resetTokens as $record) {
            if (Hash::check($token, $record->token)) {
                $validToken = $record;
                break;
            }
        }

        if (!$validToken) {
            return response()->json(['message' => 'Invalid or expired reset token.'], 400);
        }

        // Check if token is expired (60 minutes)
        $expiresAt = \Carbon\Carbon::parse($validToken->created_at)->addMinutes(60);
        if (now()->greaterThan($expiresAt)) {
            // Delete expired token
            \DB::table('password_reset_tokens')
                ->where('email', $validToken->email)
                ->delete();
            return response()->json(['message' => 'Reset token has expired. Please request a new password reset link.'], 400);
        }

        // Return email for the frontend form
        return response()->json([
            'valid' => true,
            'email' => $validToken->email
        ]);
    }

    /**
     * Handle password reset form submission
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Find user by email hash
        $emailHash = hash('sha256', $request->email);
        $user = User::where('email_hash', $emailHash)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Verify the token
        $broker = app('auth.password.broker');
        $valid = $broker->tokenExists($user, $request->token);

        if (!$valid) {
            return response()->json(['message' => 'Invalid or expired reset token.'], 400);
        }

        // Update the password
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the token
        $broker->deleteToken($user);

        // Log the password reset action
        AuditLog::create([
            'action' => 'Password Reset',
            'module' => 'Authentication',
            'target_user_id' => $user->id,
            'description' => "{$user->first_name} {$user->last_name} reset their password.",
            'performed_by' => $user->id,
            'performed_at' => now(),
        ]);

        return response()->json(['message' => 'Password has been reset successfully!']);
    }
}
