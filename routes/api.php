<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\MaryamController;
use App\Services\WhatsAppService;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API routes for vehicles
Route::get('/vehicles', [VehicleController::class, 'index']);
Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show']);

// Maryam CS API — private access (Cecep & Bos Iqbal only)
Route::prefix('maryam')->group(function () {
    Route::get('/dashboard', [MaryamController::class, 'dashboard']);
    Route::get('/sales', [MaryamController::class, 'sales']);
    Route::get('/vehicles', [MaryamController::class, 'vehicles']);
    Route::get('/weekly-report', [MaryamController::class, 'weeklyReport']);
    Route::get('/weekly-summary', [MaryamController::class, 'weeklySummary']);
});



