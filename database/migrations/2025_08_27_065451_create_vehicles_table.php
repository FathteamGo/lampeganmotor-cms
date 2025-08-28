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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_model_id')->constrained('vehicle_models');
            $table->foreignId('type_id')->constrained('types');
            $table->foreignId('color_id')->constrained('colors');
            $table->foreignId('year_id')->constrained('years');

            $table->string('vin')->unique(); // Vehicle Identification Number / Nomor Rangka
            $table->string('engine_number')->unique(); // Nomor Mesin

            $table->string('license_plate')->unique()->nullable(); // Nomor Polisi
            $table->string('bpkb_number')->unique()->nullable(); // Nomor BPKB

            $table->decimal('purchase_price', 15, 2); // Harga Beli
            $table->decimal('sale_price', 15, 2)->nullable(); // Harga Jual

            $table->enum('status', ['available', 'sold', 'in_repair', 'hold'])->default('hold');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
