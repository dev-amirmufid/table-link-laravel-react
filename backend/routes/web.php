<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('swagger-ui');
});

// Swagger UI
Route::get('/api/docs', function () {
    return view('swagger-ui');
});
