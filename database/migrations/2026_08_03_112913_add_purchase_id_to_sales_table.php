<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ikat setiap sale ke pembelian yang jadi modalnya.
     *
     * Satu motor bisa dibeli berkali-kali (buyback), jadi tanpa kolom ini
     * perhitungan laba selalu memakai pembelian pertama — harga siklus lama.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('sales', 'purchase_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->foreignId('purchase_id')->nullable()->after('vehicle_id')
                      ->constrained('purchases')->nullOnDelete();
            });
        }

        // Backfill: pembelian terakhir sebelum (atau pada) tanggal jual.
        // Kalau tidak ada yang mendahului tanggal jual, pakai pembelian TERBARU —
        // bukan yang tertua, karena itu harga beli dari siklus yang sudah lewat.
        DB::table('sales')
            ->whereNull('purchase_id')
            ->chunkById(200, function ($sales) {
                foreach ($sales as $sale) {
                    $purchase = null;

                    if ($sale->sale_date) {
                        $purchase = DB::table('purchases')
                            ->where('vehicle_id', $sale->vehicle_id)
                            ->whereDate('purchase_date', '<=', $sale->sale_date)
                            ->orderByDesc('purchase_date')
                            ->orderByDesc('id')
                            ->first();
                    }

                    if (!$purchase) {
                        $purchase = DB::table('purchases')
                            ->where('vehicle_id', $sale->vehicle_id)
                            ->orderByDesc('purchase_date')
                            ->orderByDesc('id')
                            ->first();
                    }

                    if ($purchase) {
                        DB::table('sales')
                            ->where('id', $sale->id)
                            ->update(['purchase_id' => $purchase->id]);
                    }
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales', 'purchase_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropForeign(['purchase_id']);
                $table->dropColumn('purchase_id');
            });
        }
    }
};
