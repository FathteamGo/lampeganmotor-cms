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
    Schema::create('whatsapp_numbers', function (Blueprint $table) {
        $table->id();
        $table->string('name');      // Contoh: Admin 1, Admin 2
        $table->string('number');    // Nomor WA (misal 6281234567890)
        $table->boolean('is_active')->default(true); // Aktif/tidak
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_numbers');
    }
};
