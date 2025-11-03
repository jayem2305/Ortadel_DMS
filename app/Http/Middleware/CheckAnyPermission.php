<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAnyPermission
{
    /**
     * Handle an incoming request.
     * Check if user has ANY of the specified permissions (OR logic)
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

        if (!$user->hasAnyPermission($permissions)) {
            return response()->json([
                'message' => 'Access denied. You do not have any of the required permissions.',
                'required_permissions' => $permissions,
                'user_role' => $user->role?->name ?? 'No role assigned'
            ], 403);
        }

        return $next($request);
    }
}
