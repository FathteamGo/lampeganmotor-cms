<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Models\Request;

// Halaman welcome (default Laravel)
Route::get('/', function () {
    return view('welcome');
});

// Halaman utama (Landing Page)
Route::get('/', [LandingController::class, 'index'])->name('landing.index');

// Halaman detail motor
Route::get('/vehicles/{vehicle}', [LandingController::class, 'show'])->name('landing.show');

// Form jual motor
Route::get('/jual-motor-anda', [LandingController::class, 'sellForm'])->name('landing.sell.form');
Route::post('/jual-motor-anda', [LandingController::class, 'sellSubmit'])->name('landing.sell.submit');

Route::get('/ajax/models-by-brand/{brand}', [LandingController::class, 'modelsByBrand'])
    ->name('ajax.models.byBrand');

// API untuk ambil model by brand (AJAX)
Route::get('/api/models-by-brand/{brand}', [LandingController::class, 'modelsByBrand'])
    ->name('sell.models-by-brand');

// Halaman tambahan
Route::get('/tentang', fn () => view('frontend.about'))->name('landing.about');
Route::get('/kontak', fn () => view('frontend.contact'))->name('landing.contact');

Route::get('/sell/models-by-brand/{brand}', [LandingController::class, 'modelsByBrand'])
    ->whereNumber('brand')
    ->name('sell.models-by-brand');



Route::post('/language/switch', function (Request $request) {
    session(['locale' => $request->locale]);
    return back();
})->name('language.switch');

