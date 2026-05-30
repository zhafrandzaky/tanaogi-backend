<?php

use App\Http\Controllers\Api\V1\AccommodationController;
use App\Http\Controllers\Api\V1\Admin;
use App\Http\Controllers\Api\V1\Admin\Blacklist;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\DestinationController;
use App\Http\Controllers\Api\V1\RegencyController;
use App\Http\Controllers\Api\V1\VehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'status'  => 'ok',
    'service' => 'TanaOgi API',
]));

// Auth
Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/auth/logout', [AuthController::class, 'logout']);

// Public endpoints
Route::get('/regencies', [RegencyController::class, 'index']);
Route::get('/destinations', [DestinationController::class, 'index']);
Route::get('/destinations/{slug}', [DestinationController::class, 'show']);
Route::get('/vehicles', [VehicleController::class, 'index']);
Route::get('/accommodations', [AccommodationController::class, 'index']);

// Admin endpoints
Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Regencies
    Route::apiResource('regencies', Admin\RegencyController::class);
    Route::patch('regencies/{id}/toggle-active', [Admin\RegencyController::class, 'toggleActive']);

    // Destinations
    Route::apiResource('destinations', Admin\DestinationController::class);
    Route::patch('destinations/{id}/toggle-active', [Admin\DestinationController::class, 'toggleActive']);

    // Vehicles
    Route::apiResource('vehicles', Admin\VehicleController::class);
    Route::patch('vehicles/{id}/toggle-active', [Admin\VehicleController::class, 'toggleActive']);

    // Drivers
    Route::apiResource('drivers', Admin\DriverController::class);
    Route::patch('drivers/{id}/toggle-active', [Admin\DriverController::class, 'toggleActive']);

    // Driver Orders
    Route::apiResource('driver-orders', Admin\DriverOrderController::class);
    Route::patch('driver-orders/{id}/confirm', [Admin\DriverOrderController::class, 'confirm']);
    Route::patch('driver-orders/{id}/complete', [Admin\DriverOrderController::class, 'complete']);
    Route::patch('driver-orders/{id}/cancel', [Admin\DriverOrderController::class, 'cancel']);

    // Accommodations
    Route::apiResource('accommodations', Admin\AccommodationController::class);
    Route::patch('accommodations/{id}/toggle-active', [Admin\AccommodationController::class, 'toggleActive']);

    // Users
    Route::apiResource('users', Admin\UserController::class);

    // Settings
    Route::get('settings', [Admin\SettingController::class, 'index']);
    Route::put('settings/{key}', [Admin\SettingController::class, 'update']);

    // Maintenance
    Route::get('maintenance', [Admin\MaintenanceController::class, 'status']);
    Route::post('maintenance/toggle', [Admin\MaintenanceController::class, 'toggle']);

    // Blacklist / Whitelist
    Route::prefix('blacklist')->group(function () {
        Route::apiResource('ips', Blacklist\BlacklistIpController::class);
        Route::apiResource('phones', Blacklist\BlacklistPhoneController::class);
    });

    Route::prefix('whitelist')->group(function () {
        Route::apiResource('ips', Blacklist\WhitelistIpController::class);
        Route::apiResource('phones', Blacklist\WhitelistPhoneController::class);
    });
});
