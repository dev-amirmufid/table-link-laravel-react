<?php

use Illuminate\Support\Facades\Route;

// Swagger UI - only for API docs
Route::get('/api/docs', function () {
    return view('swagger-ui');
});
