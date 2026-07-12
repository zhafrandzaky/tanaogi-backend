<?php

use App\Http\Controllers\Api\V1\AccommodationController;
use App\Http\Controllers\Api\V1\Admin;
use App\Http\Controllers\Api\V1\Admin\Blacklist;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\DestinationController;
use App\Http\Controllers\Api\V1\MaintenanceController;
use App\Http\Controllers\Api\V1\RegencyController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\VehicleController;
use App\Http\Controllers\Api\V1\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'status'  => 'ok',
    'service' => 'TanaOgi API',
]));

// Auth
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/auth/google/redirect', [AuthController::class, 'googleRedirect']);
Route::post('/auth/google/callback', [AuthController::class, 'googleCallback']);
Route::middleware('auth:sanctum')->post('/auth/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->post('/reviews', [ReviewController::class, 'store']);

// Public endpoints
Route::get('/reviews', [ReviewController::class, 'index']); // <-- INI RUTE GET YANG BARU DITAMBAHKAN
Route::get('/regencies', [RegencyController::class, 'index']);
Route::get('/destinations', [DestinationController::class, 'index']);
Route::get('/destinations/{slug}', [DestinationController::class, 'show']);
Route::get('/vehicles', [VehicleController::class, 'index']);
Route::get('/accommodations', [AccommodationController::class, 'index']);
Route::get('/destinations/{slug}/accommodations', [AccommodationController::class, 'byDestination']);
Route::get('/settings/whatsapp', [SettingController::class, 'whatsapp']);
Route::get('/maintenance/status', [MaintenanceController::class, 'status']);


// Admin endpoints
Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Regencies
    Route::apiResource('regencies', Admin\RegencyController::class);
    Route::patch('regencies/{id}/toggle-active', [Admin\RegencyController::class, 'toggleActive']);

    // Destinations
    Route::apiResource('destinations', Admin\DestinationController::class);
    Route::patch('destinations/{id}/toggle-active', [Admin\DestinationController::class, 'toggleActive']);
    Route::post('destinations/{id}/images', [Admin\DestinationController::class, 'uploadImages']);
    Route::delete('destinations/{id}/images/{imageId}', [Admin\DestinationController::class, 'deleteImage']);

    // Vehicles
    Route::apiResource('vehicles', Admin\VehicleController::class);
    Route::patch('vehicles/{id}/toggle-active', [Admin\VehicleController::class, 'toggleActive']);

    // Drivers
    Route::apiResource('drivers', Admin\DriverController::class);
    Route::patch('drivers/{id}/toggle-active', [Admin\DriverController::class, 'toggleActive']);
    Route::get('drivers/{id}/schedule', [Admin\DriverController::class, 'schedule']);

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
    Route::put('settings', [Admin\SettingController::class, 'update']);

    // Maintenance
    Route::get('maintenance/status', [Admin\MaintenanceController::class, 'status']);
    Route::post('maintenance/enable', [Admin\MaintenanceController::class, 'enable']);
    Route::post('maintenance/disable', [Admin\MaintenanceController::class, 'disable']);

    // Blacklist
    Route::prefix('blacklist')->group(function () {
        Route::apiResource('ips', Blacklist\BlacklistIpController::class)->except(['update'])->names([
            'index'   => 'blacklist.ips.index',
            'store'   => 'blacklist.ips.store',
            'show'    => 'blacklist.ips.show',
            'destroy' => 'blacklist.ips.destroy',
        ]);
        Route::post('ips/{id}/unban', [Blacklist\BlacklistIpController::class, 'unban'])->name('blacklist.ips.unban');
        Route::apiResource('phones', Blacklist\BlacklistPhoneController::class)->except(['update'])->names([
            'index'   => 'blacklist.phones.index',
            'store'   => 'blacklist.phones.store',
            'show'    => 'blacklist.phones.show',
            'destroy' => 'blacklist.phones.destroy',
        ]);
        Route::post('phones/{id}/unban', [Blacklist\BlacklistPhoneController::class, 'unban'])->name('blacklist.phones.unban');
    });

    // Whitelist
    Route::prefix('whitelist')->group(function () {
        Route::apiResource('ips', Blacklist\WhitelistIpController::class)->except(['update', 'show'])->names([
            'index'   => 'whitelist.ips.index',
            'store'   => 'whitelist.ips.store',
            'destroy' => 'whitelist.ips.destroy',
        ]);
        Route::apiResource('phones', Blacklist\WhitelistPhoneController::class)->except(['update', 'show'])->names([
            'index'   => 'whitelist.phones.index',
            'store'   => 'whitelist.phones.store',
            'destroy' => 'whitelist.phones.destroy',
        ]);
    });
});