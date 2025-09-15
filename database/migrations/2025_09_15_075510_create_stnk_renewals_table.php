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
       Schema::create('stnk_renewals', function (Blueprint $table) {
            $table->id();

            $table->date('tgl')->default(now()); // tanggal default today
            $table->string('license_plate')->unique()->nullable(); // nomor polisi
            $table->string('atas_nama_stnk'); // nama di STNK
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete(); // customer select
            $table->decimal('total_pajak_jasa', 15, 2)->default(0); // total pajak + jasa
            $table->date('diambil_tgl')->nullable(); // tanggal diambil
            $table->decimal('pembayaran_ke_samsat', 15, 2)->default(0);
            $table->decimal('dp', 15, 2)->default(0);
            $table->decimal('sisa_pembayaran', 15, 2)->default(0);
            $table->decimal('margin_total', 15, 2)->default(0); // total pajak + jasa - pembayaran + jasa
            $table->enum('status', ['pending','progress','done'])->default('pending');

            $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stnk_renewals');
    }
};
