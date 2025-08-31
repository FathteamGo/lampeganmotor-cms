<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// [DIPERBAIKI] Gunakan nama class yang sesuai dengan nama file
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // [DIPERBAIKI] Tambahkan '$table->' di setiap baris
            $table->text('description')->nullable()->after('status');
            $table->decimal('dp_percentage', 5, 2)->nullable()->after('description');
            $table->text('engine_specification')->nullable()->after('dp_percentage');
            $table->text('location')->nullable()->after('engine_specification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // [DIPERBAIKI] Tambahkan variabel '$table'
        Schema::table('vehicles', function (Blueprint $table) {
            // [DIPERBAIKI] Panggil method dropColumn dari '$table'
            $table->dropColumn(['description', 'dp_percentage', 'engine_specification', 'location']);
        });
    }
};
