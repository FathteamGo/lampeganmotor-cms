<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Drop foreign key dulu
            $table->dropForeign('sales_vehicle_id_foreign');

            // Drop unique index
            $table->dropUnique('sales_vehicle_id_status_unique');

            // Tambahkan kembali foreign key tanpa unique index
            $table->foreign('vehicle_id', 'sales_vehicle_id_foreign')
                  ->references('id')
                  ->on('vehicles')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Drop FK sementara
            $table->dropForeign('sales_vehicle_id_foreign');

            // Tambahkan kembali unique index
            $table->unique(['vehicle_id', 'status'], 'sales_vehicle_id_status_unique');

            // Tambahkan FK kembali
            $table->foreign('vehicle_id', 'sales_vehicle_id_foreign')
                  ->references('id')
                  ->on('vehicles')
                  ->cascadeOnDelete();
        });
    }
};
