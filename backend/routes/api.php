<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

// Public routes - Dashboard (no auth required)
Route::prefix('v1')->group(function () {
    // GOD QUERY - Single endpoint for all dashboard data
    Route::get('/dashboard/analytics', [AnalyticsController::class, 'analytics']);
    
    // Clear cache
    Route::post('/dashboard/cache/clear', [AnalyticsController::class, 'clearCache']);

    // Transaction endpoints
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);
});

// Admin Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes - Admin only
Route::prefix('auth')->middleware('jwt.auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});
