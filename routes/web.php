<?php

use Illuminate\Support\Facades\Route;

// Catch-all route for Vue SPA
// This must be the LAST route to avoid catching API routes
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
