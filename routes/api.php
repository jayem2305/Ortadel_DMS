<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\AuthController;
use App\Models\User;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;

// Groups & Users
Route::apiResource('groups', GroupController::class);
Route::get('users/last-id', [UserController::class, 'getLastUserId']);
Route::apiResource('users', UserController::class);
Route::apiResource('roles', RoleController::class);
Route::apiResource('permissions', PermissionController::class);


// Auth routes
Route::post('/login', [AuthController::class, 'login']);

Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email_hash',
        'password' => 'required|string|min:6',
    ]);

    $validated['password'] = Hash::make($validated['password']);
    $validated['email_hash'] = hash('sha256', $validated['email']);

    $user = User::create($validated);

    return response()->json([
        'message' => 'User created successfully',
        'user' => $user,
    ]);
});

// Logout
Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();
    return response()->json(['message' => 'Logged out']);
});
