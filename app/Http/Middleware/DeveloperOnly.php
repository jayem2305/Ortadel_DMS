<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeveloperOnly
{
    /**
     * Handle an incoming request.
     * Only allow users with Developer role to access permissions
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Unauthorized. Please log in.'
            ], 401);
        }

        $user = Auth::user();
        
        // Load the user's role if not already loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }

        // Check if user has Developer role
        if (!$user->role || $user->role->name !== 'Developer') {
            return response()->json([
                'message' => 'Access denied. Only developers can manage permissions.',
                'required_role' => 'Developer',
                'current_role' => $user->role ? $user->role->name : 'No role assigned'
            ], 403);
        }

        return $next($request);
    }
}