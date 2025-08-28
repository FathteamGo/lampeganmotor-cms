<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->string('description'); // Deskripsi sumber pemasukan, cth: "Servis Ganti Oli NMAX"
            $table->foreignId('category_id')->nullable()->constrained('categories');
            // $table->string('category')->default('Lain-lain'); // Kategori: Jasa Servis, Penjualan Sparepart, Komisi, Jasa Dokumen
            $table->decimal('amount', 15, 2); // Jumlah pendapatan
            $table->date('income_date'); // Tanggal pendapatan diterima
            $table->foreignId('customer_id')->nullable()->constrained('customers'); // Opsional: jika pendapatan terkait pelanggan tertentu
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
