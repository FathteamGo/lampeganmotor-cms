<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// use DB;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah opsi baru ke enum
        DB::statement("ALTER TABLE sales MODIFY COLUMN order_source ENUM('fb', 'ig', 'tiktok', 'walk_in', 'olx') NULL;");
    }

    public function down(): void
    {
        // Balik ke versi lama kalau rollback
        DB::statement("ALTER TABLE sales MODIFY COLUMN order_source ENUM('fb', 'ig', 'tiktok', 'walk_in') NULL;");
    }
};
