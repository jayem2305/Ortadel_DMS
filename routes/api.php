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
use App\Http\Controllers\FolderController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\BatchFileUploadController;
// use App\Http\Controllers\TestController;

// Test API route (uncomment when TestController is created)
// Route::get('test-data', [TestController::class, 'apiData']);

// Auth routes (no auth required)
Route::post('/login', [AuthController::class, 'login']);

// Test route to check user authentication
Route::get('/test-login', function () {
    $user = User::where('email_hash', hash('sha256', 'admin@ortadel.com'))->first();
    if ($user) {
        return response()->json([
            'found_user' => true,
            'user_id' => $user->user_id,
            'email' => $user->email,
            'password_check' => Hash::check('password', $user->password),
            'password_length' => strlen($user->password),
        ]);
    }
    return response()->json(['found_user' => false]);
});

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

// Protected routes - require authentication
Route::middleware('auth:sanctum')->group(function () {
    
    // Logout
    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    });

    // User Management Routes - Protected by permissions
    Route::middleware('permission:View Users')->group(function () {
        Route::get('users', [UserController::class, 'index']);
        Route::get('users/last-id', [UserController::class, 'getLastUserId']); // Move specific route before parameterized route
        Route::get('users/{user}', [UserController::class, 'show']);
    });
    
    Route::middleware('permission:Create Users')->group(function () {
        Route::post('users', [UserController::class, 'store']);
    });
    
    Route::middleware('permission:Edit Users')->group(function () {
        Route::put('users/{user}', [UserController::class, 'update']);
        Route::patch('users/{user}', [UserController::class, 'update']);
    });
    
    Route::middleware('permission:Delete Users')->group(function () {
        Route::delete('users/{user}', [UserController::class, 'destroy']);
    });

    // Group Management Routes - Protected by permissions  
    Route::middleware('permission:View Groups')->group(function () {
        Route::get('groups', [GroupController::class, 'index']);
        Route::get('groups/{group}', [GroupController::class, 'show']);
    });
    
    Route::middleware('permission:Create Groups')->group(function () {
        Route::post('groups', [GroupController::class, 'store']);
    });
    
    Route::middleware('permission:Edit Groups')->group(function () {
        Route::put('groups/{group}', [GroupController::class, 'update']);
        Route::patch('groups/{group}', [GroupController::class, 'update']);
    });
    
    Route::middleware('permission:Delete Groups')->group(function () {
        Route::delete('groups/{group}', [GroupController::class, 'destroy']);
    });

    // Role Management Routes - Protected by permissions
    Route::middleware('permission:View Roles')->group(function () {
        Route::get('roles', [RoleController::class, 'index']);
        Route::get('roles/{role}', [RoleController::class, 'show']);
    });
    
    Route::middleware('permission:Create Roles')->group(function () {
        Route::post('roles', [RoleController::class, 'store']);
    });
    
    Route::middleware('permission:Edit Roles')->group(function () {
        Route::put('roles/{role}', [RoleController::class, 'update']);
        Route::patch('roles/{role}', [RoleController::class, 'update']);
    });
    
    Route::middleware('permission:Delete Roles')->group(function () {
        Route::delete('roles/{role}', [RoleController::class, 'destroy']);
    });

    // File Management Routes - Protected by permissions
    Route::middleware('permission:View Files')->group(function () {
        Route::get('file', [FileController::class, 'index']);
        Route::get('file/{file}', [FileController::class, 'show']);
    });
    
    Route::middleware('permission:Create Files')->group(function () {
        Route::post('file', [FileController::class, 'store']);
    });
    
    Route::middleware('permission:Edit Files')->group(function () {
        Route::put('file/{file}', [FileController::class, 'update']);
        Route::patch('file/{file}', [FileController::class, 'update']);
    });
    
    Route::middleware('permission:Delete Files')->group(function () {
        Route::delete('file/{file}', [FileController::class, 'destroy']);
    });

    // Folder/Document Management Routes - Protected by permissions
    Route::middleware('permission:View Folders')->group(function () {
        Route::get('folders', [FolderController::class, 'index']);
        Route::get('folders/{folder}', [FolderController::class, 'show']);
    });
    
    Route::middleware('permission:Create Folders')->group(function () {
        Route::post('folders', [FolderController::class, 'store']);
    });
    
    Route::middleware('permission:Edit Folders')->group(function () {
        Route::put('folders/{folder}', [FolderController::class, 'update']);
        Route::patch('folders/{folder}', [FolderController::class, 'update']);
    });
    
    Route::middleware('permission:Delete Folders')->group(function () {
        Route::delete('folders/{folder}', [FolderController::class, 'destroy']);
    });

    // Batch File Upload Routes - Protected by permissions
    Route::middleware('permission:Upload Files')->group(function () {
        Route::get('batchfile', [BatchFileUploadController::class, 'index']);
        Route::post('batchfile', [BatchFileUploadController::class, 'store']);
        Route::get('batchfile/{batchfile}', [BatchFileUploadController::class, 'show']);
        Route::put('batchfile/{batchfile}', [BatchFileUploadController::class, 'update']);
        Route::delete('batchfile/{batchfile}', [BatchFileUploadController::class, 'destroy']);
    });

    // Permissions - Only accessible by developers (special case)
    Route::middleware(['developer.only'])->group(function () {
        Route::apiResource('permissions', PermissionController::class);
    });

    // Audit Logs - View Logs permission required
    Route::middleware('permission:View Logs')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
    });
});
