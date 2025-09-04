<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Exports\AssetReportExport;
use App\Exports\VehiclesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\AssetReportController;



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




Route::get('/inventory/export/excel', function () {
    return Excel::download(new VehiclesExport, 'vehicles.xlsx');
})->name('inventory.export.excel');




Route::get('/export/asset-report', function () {
    return Excel::download(new AssetReportExport, 'laporan-asset.xlsx');
})->name('export.asset-report');


