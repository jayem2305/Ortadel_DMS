<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\XanoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\BatchFileUploadController;
// Auth route
Route::post('/login', [AuthController::class, 'login']);
Route::get('/audit-logs', [AuditLogController::class, 'index']);
// routes/api.php
Route::post('/files/update-expired', [FileController::class, 'updateExpiredFilesApi']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('users/last-id', [UserController::class, 'getLastUserId']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('groups', GroupController::class);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);
    Route::apiResource('folders', FolderController::class);
    Route::apiResource('file', FileController::class);
    Route::apiResource('batchfile', BatchFileUploadController::class);


});

// Catch-all route for Vue SPA
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');

Route::get('/contacts', function () {
    return view('contacts');
});
