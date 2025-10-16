<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/hello', function () {
    return response()->json([
        'message' => 'Yo Bro, Laravel API is ready'
        ]);
});

// Authentication routes
Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);