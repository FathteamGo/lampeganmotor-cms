<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VehicleController; // Pastikan namespace ini benar
use App\Services\WhatsAppService;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API routes for vehicles
Route::get('/vehicles', [VehicleController::class, 'index']);
Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show']);



