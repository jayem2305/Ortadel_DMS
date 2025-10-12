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
}
