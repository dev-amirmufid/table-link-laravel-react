<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

// Public routes - Only these two endpoints are used
Route::prefix('v1')->group(function () {
    // GOD QUERY - Single endpoint for all dashboard data
    Route::get('/dashboard/analytics', [AnalyticsController::class, 'analytics']);
    
    // Transaction endpoints
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);
});
