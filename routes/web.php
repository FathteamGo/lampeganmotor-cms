<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Exports\AssetReportExport;
use App\Exports\VehiclesExport;
use App\Http\Controllers\Admin\SalesSummaryExportController;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\AssetReportController;
use App\Http\Controllers\FrontPostBlogController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\TesReportInsight;
use App\Http\Controllers\WeeklyReportController;

// Halaman welcome (default Laravel)
Route::get('/', function () {
    return view('welcome');
});

Route::get('/sales/{sale}/invoice-cash', [InvoiceController::class, 'cash'])
    ->name('sales.invoice.cash');


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
    ->name('sell.models-by-brand-ajax');

// Halaman tambahan
Route::get('/tentang', fn() => view('frontend.about'))->name('landing.about');
Route::get('/kontak', fn() => view('frontend.contact'))->name('landing.contact');


Route::get('/sell/models-by-brand/{brand}', [LandingController::class, 'modelsByBrand'])
    ->whereNumber('brand')
    ->name('sell.models-by-brand');

Route::get('/blog', [LandingController::class, 'allBlogs'])->name('blog.all');
Route::get('/blog/{slug}', [FrontPostBlogController::class, 'show'])->name('blog.show');
Route::get('/blog/kategori/{id}', [FrontPostBlogController::class, 'category'])->name('blog.category');



Route::get('/inventory/export/excel', function () {
    return Excel::download(new VehiclesExport, 'vehicles.xlsx');
})->name('inventory.export.excel');

//sales summary export
Route::get('/admin/sales-summaries/export', [SalesSummaryExportController::class, 'export'])
    ->name('sales-summaries.export');


Route::get('/export/asset-report', function () {
    return Excel::download(new AssetReportExport, 'laporan-asset.xlsx');
})->name('export.asset-report');


Route::get('/set-locale/{locale}', function ($locale) {
    if (in_array($locale, ['id', 'en'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('set-locale');

Route::get('/test-report', [TesReportInsight::class, 'index']);

Route::get('/debug-queue', function () {
    return [
        'queue_connection_env' => env('QUEUE_CONNECTION'),
        'queue_connection_config' => config('queue.default'),
        'db_connection' => config('database.default'),
    ];
});

Route::get('/test3', function () {
    $start = microtime(true);
    \App\Jobs\GenerateWeeklyReportJob::dispatch();
    $time = microtime(true) - $start;
    return "Job Dispatched in {$time} seconds! Check the terminal worker now.";
});

Route::get('/test1', function () {
    // Generate a weekly report asynchronously in the background via queue
    \App\Jobs\GenerateWeeklyReportJob::dispatch();

    return response('Hana AI sedang mempersiapkan laporan mingguan Lampegan Motor di latar belakang, Bos! Laporan akan segera dikirimkan ke nomor WhatsApp Anda. Silakan ditunggu ya, Bos! 🌸');
});

Route::get('/test2', function () {
    // Generate a 30-day strategic recap asynchronously in the background via queue
    \App\Jobs\Generate30DayInsightJob::dispatch();

    return response('Hana AI sedang memproses analisis bisnis mendalam selama 30 hari ke belakang. Insight strategis akan segera mendarat di WhatsApp Bos! 🌸');
});

// Route untuk cron job - menjalankan weekly report + 30-day insight sekaligus
Route::get('/send_report_ai_agent', function () {
    // Dispatch weekly report job
    \App\Jobs\GenerateWeeklyReportJob::dispatch();

    // Dispatch 30-day insight job (delayed 30 seconds to avoid rate limiting on AI/WA)
    \App\Jobs\Generate30DayInsightJob::dispatch()->delay(now()->addSeconds(30));

    \Illuminate\Support\Facades\Log::info('Cron: send_report_ai_agent triggered - both jobs dispatched.');

    return response()->json([
        'status' => 'success',
        'message' => 'Weekly report dan 30-day insight telah di-dispatch ke queue.',
        'dispatched_at' => now()->toDateTimeString(),
    ]);
});


Route::post('/weekly-report/{report}/dismiss', [WeeklyReportController::class, 'dismiss'])
    ->name('weekly-report.dismiss')
    ->middleware('auth');


