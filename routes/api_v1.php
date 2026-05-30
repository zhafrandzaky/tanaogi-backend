<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'status'  => 'ok',
    'service' => 'TanaOgi API',
]));

// Public endpoints — akan diisi per task
// Auth endpoints — akan diisi per task
// Admin endpoints — akan diisi per task
