<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            //  Hapus foreign key dulu
            $table->dropForeign(['vehicle_id']);
        });

        // Hapus unique lama (karena udah gak dikunci foreign key)
        DB::statement('ALTER TABLE sales DROP INDEX sales_vehicle_id_unique');

        // Tambah index baru: unique kombinasi vehicle_id + status
        Schema::table('sales', function (Blueprint $table) {
            $table->unique(['vehicle_id', 'status']);
        });

        // Tambah lagi foreign key-nya biar tetap ada relasi ke vehicles
        Schema::table('sales', function (Blueprint $table) {
            $table->foreign('vehicle_id')
                  ->references('id')
                  ->on('vehicles')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropUnique(['vehicle_id', 'status']);
        });

        DB::statement('ALTER TABLE sales ADD UNIQUE KEY `sales_vehicle_id_unique` (`vehicle_id`)');

        Schema::table('sales', function (Blueprint $table) {
            $table->foreign('vehicle_id')
                  ->references('id')
                  ->on('vehicles')
                  ->onDelete('cascade');
        });
    }
};

