<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAllPermissions
{
    /**
     * Handle an incoming request.
     * Check if user has ALL of the specified permissions (AND logic)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (!$user->hasAllPermissions($permissions)) {
            return response()->json([
                'message' => 'Access denied. You do not have all required permissions.',
                'required_permissions' => $permissions,
                'user_role' => $user->role?->name ?? 'No role assigned'
            ], 403);
        }

        return $next($request);
    }
}
