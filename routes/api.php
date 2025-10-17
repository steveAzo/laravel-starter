<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/**
 * API Routes
 * 
 * All routes here are automatically prefixed with /api
 * So /signup becomes /api/signup when you call it
 */

Route::get('/hello', function () {
    return response()->json([
        'message' => 'Yo Bro, Laravel API is ready'
        ]);
});

// ============================================
// PUBLIC ROUTES (No authentication required)
// ============================================

Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// ============================================
// PROTECTED ROUTES (Authentication required)
// ============================================
// These routes require a valid Bearer token in the Authorization header
// Format: Authorization: Bearer {your_token_here}

Route::middleware('auth:sanctum')->group(function () {
    // User profile management
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::get('/profile', [AuthController::class, 'getProfile']);
    
    // Logout (revokes current token)
    Route::post('/logout', [AuthController::class, 'logout']);
});