<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambahkan 'cancel' ke enum tanpa hapus data
        DB::statement("ALTER TABLE sales MODIFY COLUMN status ENUM('proses', 'kirim', 'selesai', 'cancel') DEFAULT 'proses'");
    }

    public function down(): void
    {
        // Kembalikan ke versi sebelumnya (opsional)
        DB::statement("ALTER TABLE sales MODIFY COLUMN status ENUM('proses', 'kirim', 'selesai') DEFAULT 'proses'");
    }
};
