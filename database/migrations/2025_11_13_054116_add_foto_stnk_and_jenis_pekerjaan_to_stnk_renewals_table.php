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
    Schema::table('stnk_renewals', function (Blueprint $table) {
        $table->string('foto_stnk')->nullable();
        $table->string('jenis_pekerjaan')->nullable();
    });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stnk_renewals', function (Blueprint $table) {
            //
        });
    }
};
