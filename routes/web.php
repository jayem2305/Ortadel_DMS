<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\XanoController;

Route::get('/xano-data', [XanoController::class, 'getData']);


Route::get('/{any}', function () {
    return view('welcome'); // or 'app' if you created resources/views/app.blade.php
})->where('any', '.*');


Route::get('/contacts', function () {
    return view('contacts');
});
