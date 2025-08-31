<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;

Route::get('/', function () {
    return view('welcome');
});

// Route for the Homepage (Landing Page)
Route::get('/', [LandingController::class, 'index'])->name('landing.index');

// Route for the Vehicle Detail Page
// We use {vehicle} for Route Model Binding, which is a Laravel best practice.
Route::get('/vehicles/{vehicle}', [LandingController::class, 'show'])->name('landing.show');

// Routes for the "Sell Your Motorcycle" Form
Route::get('/sell', [LandingController::class, 'sellForm'])->name('landing.sell.form');
Route::post('/sell', [LandingController::class, 'sellSubmit'])->name('landing.sell.submit');

Route::get('/tentang', function () {
    return view('frontend.about'); // Buat file about.blade.php
})->name('landing.about');

Route::get('/jual-motor-anda', function () {
    return view('frontend.sell-form'); // Buat file sell-form.blade.php
})->name('landing.sell.form');
Route::get('/kontak', function () {
    return view('frontend.contact'); // Buat file contact.blade.php
})->name('landing.contact');
