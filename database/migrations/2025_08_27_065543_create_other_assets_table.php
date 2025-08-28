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
        Schema::create('other_assets', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Komputer Kasir, Peralatan Bengkel
            $table->text('description')->nullable();
            $table->decimal('value', 15, 2); // Nilai aset saat ini
            $table->date('acquisition_date'); // Tanggal perolehan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_assets');
    }
};
