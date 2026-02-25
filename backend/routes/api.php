<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes - Authentication
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes - Authentication required
Route::prefix('auth')->middleware('jwt.auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

// Protected routes - Dashboard & Transactions (JWT)
Route::prefix('v1')->middleware('jwt.auth')->group(function () {
    // Dashboard endpoints
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
    Route::get('/dashboard/trends', [DashboardController::class, 'trends']);
    Route::get('/dashboard/trending-items', [DashboardController::class, 'trendingItems']);
    Route::get('/dashboard/top-buyers', [DashboardController::class, 'topBuyers']);
    Route::get('/dashboard/top-sellers', [DashboardController::class, 'topSellers']);
    Route::get('/dashboard/user-type-distribution', [DashboardController::class, 'userTypeDistribution']);
    Route::get('/dashboard/user-classification', [DashboardController::class, 'userClassification']);
    Route::post('/dashboard/cache/clear', [DashboardController::class, 'clearCache']);

    // Transaction endpoints
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);
});

// Public user info (optional auth)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('jwt.auth');
