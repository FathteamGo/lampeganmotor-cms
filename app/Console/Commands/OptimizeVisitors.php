<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OptimizeVisitors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'visitors:optimize 
                            {--year=2025 : Tahun data visitor yang akan dibersihkan (default: 2025)} 
                            {--chunk=5000 : Ukuran chunk per batch penghapusan (default: 5000)}
                            {--archive : Salin/backup data tahun target ke tabel archive sebelum dihapus}
                            {--sleep=50 : Waktu istirahat (milliseconds) antar chunk untuk mencegah database lock (default: 50)}
                            {--dry-run : Lakukan simulasi hitungan data dan estimasi size tanpa melakukan perubahan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimasi tabel visitors dengan membersihkan data tahun lalu secara berkala dan melakukan rekonstruksi tabel (OPTIMIZE TABLE) untuk mengklaim kembali ruang penyimpanan disk.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $year = (int) $this->option('year');
        $chunkSize = (int) $this->option('chunk');
        $sleepMs = (int) $this->option('sleep');
        $archive = $this->option('archive');
        $dryRun = $this->option('dry-run');

        if ($year < 2000 || $year > date('Y')) {
            $this->error("❌ Tahun '{$year}' tidak valid.");
            return Command::FAILURE;
        }

        $startDate = "{$year}-01-01 00:00:00";
        $endDate = "{$year}-12-31 23:59:59";

        $this->components->info("🚀 Memulai Proses Optimasi & Pembersihan Tabel Visitors");
        $this->components->twoColumnDetail("Tahun Target", (string) $year);
        $this->components->twoColumnDetail("Rentang Waktu", "{$startDate} s/d {$endDate}");
        $this->components->twoColumnDetail("Ukuran Chunk", "{$chunkSize} baris per batch");
        $this->components->twoColumnDetail("Mode", $dryRun ? '<fg=yellow;options=bold>SIMULASI (DRY-RUN)</>' : '<fg=red;options=bold>EKSEKUSI NYATA</>');

        // 1. Cek total records yang ada di tahun target
        $totalTargetRows = DB::table('visitors')
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->count();

        $totalAllRows = DB::table('visitors')->count();

        $this->components->twoColumnDetail("Total Baris di Tabel (Saat Ini)", number_format($totalAllRows));
        $this->components->twoColumnDetail("Jumlah Baris Tahun {$year}", "<fg=" . ($totalTargetRows > 0 ? "yellow" : "green") . ";options=bold>" . number_format($totalTargetRows) . "</>");

        if ($totalTargetRows === 0) {
            $this->info("✨ Tidak ditemukan data visitor untuk tahun {$year}. Tidak ada yang perlu dibersihkan!");
            return Command::SUCCESS;
        }

        // Ambil ukuran tabel saat ini dari Database
        $sizeBefore = $this->getTableSize();
        $this->components->twoColumnDetail("Ukuran Fisik Tabel Sebelum", sprintf("%.2f MB (Data: %.2f MB, Index: %.2f MB)", $sizeBefore['total_mb'], $sizeBefore['data_mb'], $sizeBefore['index_mb']));
        $this->components->twoColumnDetail("Ruang Kosong Terfragmentasi (Free Space)", sprintf("%.2f MB", $sizeBefore['free_mb']));

        if ($dryRun) {
            $this->newLine();
            $this->info("💡 [SIMULASI] Jika dieksekusi, sebanyak " . number_format($totalTargetRows) . " baris data tahun {$year} akan dihapus.");
            if ($archive) {
                $this->info("💡 [SIMULASI] Data akan disalin ke tabel archive: 'visitors_archive_{$year}' sebelum dihapus.");
            }
            $this->info("✅ Simulasi dry-run selesai. Tidak ada data yang diubah.");
            return Command::SUCCESS;
        }

        // Konfirmasi jika tidak ada flag dry-run dan bukan running di CI/CD
        if (!$this->confirm("Apakah Anda yakin ingin memproses pembersihan " . number_format($totalTargetRows) . " baris data? Pastikan Anda sudah membackup database!", true)) {
            $this->warn("⚠️ Proses dibatalkan oleh pengguna.");
            return Command::SUCCESS;
        }

        // 2. Proses Archiving (jika diinginkan)
        $archiveTable = "visitors_archive_{$year}";
        if ($archive) {
            $this->info("📦 Menyiapkan Tabel Archive '{$archiveTable}'...");
            
            // Buat tabel archive jika belum ada
            if (!Schema::hasTable($archiveTable)) {
                DB::statement("CREATE TABLE `{$archiveTable}` LIKE visitors");
                $this->info("✅ Tabel '{$archiveTable}' berhasil dibuat.");
            } else {
                $this->warn("⚠️ Tabel '{$archiveTable}' sudah ada. Data baru akan ditambahkan.");
            }

            $this->info("📥 Menyalin data tahun {$year} ke tabel archive...");
            
            // Proses salin secara bertahap menggunakan chunking ID agar tidak membebani memori dan mengunci tabel
            $this->archiveDataInChunks($archiveTable, $startDate, $endDate, $chunkSize);
            
            $archiveCount = DB::table($archiveTable)->count();
            $this->info("✅ Berhasil menyalin data. Total baris di tabel '{$archiveTable}': " . number_format($archiveCount));
        }

        // 3. Proses Deletion dengan Throttling Batching
        $this->info("🔥 Memulai penghapusan data tahun {$year} secara berkala...");
        
        $progressBar = $this->output->createProgressBar($totalTargetRows);
        $progressBar->start();

        $deletedTotal = 0;

        while (true) {
            // Karena kita melakukan limit delete secara berulang, query ini sangat ringan dan tidak mengunci tabel secara luas
            $deleted = DB::table('visitors')
                ->whereBetween('visited_at', [$startDate, $endDate])
                ->limit($chunkSize)
                ->delete();

            if ($deleted === 0) {
                break;
            }

            $deletedTotal += $deleted;
            $progressBar->advance($deleted);

            // Jeda sejenak untuk memberi waktu database melayani request user/aplikasi lain
            if ($sleepMs > 0) {
                usleep($sleepMs * 1000);
            }
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info("✅ Berhasil menghapus total " . number_format($deletedTotal) . " data visitor tahun {$year}.");

        // 4. Proses Rekonstruksi & Optimasi Tabel
        $this->info("🔄 Melakukan rekonstruksi tabel untuk merapikan fragmentasi disk (OPTIMIZE TABLE)...");
        $this->info("⚠️  Harap tunggu, proses ini dapat memakan waktu beberapa saat tergantung spesifikasi storage.");
        
        $startTime = microtime(true);
        // Jalankan OPTIMIZE TABLE
        DB::statement("OPTIMIZE TABLE visitors");
        // Jalankan ANALYZE TABLE untuk memperbarui index statistics
        DB::statement("ANALYZE TABLE visitors");
        $duration = round(microtime(true) - $startTime, 2);

        $this->info("✅ Optimasi tabel selesai dalam {$duration} detik!");

        // Ambil ukuran tabel setelah optimasi
        $sizeAfter = $this->getTableSize();
        $savedSpace = max(0, $sizeBefore['total_mb'] - $sizeAfter['total_mb']);

        $this->newLine();
        $this->info("📊 Ringkasan Hasil Optimasi:");
        $this->components->twoColumnDetail("Total Baris Dihapus", number_format($deletedTotal));
        $this->components->twoColumnDetail("Ukuran Fisik Tabel Sebelum", sprintf("%.2f MB", $sizeBefore['total_mb']));
        $this->components->twoColumnDetail("Ukuran Fisik Tabel Sesudah", sprintf("%.2f MB", $sizeAfter['total_mb']));
        $this->components->twoColumnDetail("Ruang Disk yang Berhasil Diklaim Kembali (Saved)", "<fg=green;options=bold>" . sprintf("%.2f MB", $savedSpace) . "</>");
        $this->components->twoColumnDetail("Sisa Fragmentasi (Free Space)", sprintf("%.2f MB", $sizeAfter['free_mb']));

        if ($archive) {
            $this->info("📂 Catatan: Backup data historis 2025 aman disimpan di tabel database '[{$archiveTable}](file:///d:/01_WEB/01_Projects/lampeganmotor-cms/database/migrations)'.");
        }

        return Command::SUCCESS;
    }

    /**
     * Mengambil ukuran real tabel visitors dari information_schema
     */
    private function getTableSize(): array
    {
        try {
            $result = DB::select("
                SELECT 
                    ROUND(DATA_LENGTH / 1024 / 1024, 2) AS data_mb,
                    ROUND(INDEX_LENGTH / 1024 / 1024, 2) AS index_mb,
                    ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS total_mb,
                    ROUND(DATA_FREE / 1024 / 1024, 2) AS free_mb
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'visitors'
            ");

            if (!empty($result)) {
                return (array) $result[0];
            }
        } catch (\Exception $e) {
            // Fallback jika permission atau query error
        }

        return [
            'data_mb' => 0.00,
            'index_mb' => 0.00,
            'total_mb' => 0.00,
            'free_mb' => 0.00,
        ];
    }

    /**
     * Menyalin data dari visitors ke tabel archive secara chunked agar tidak membebani DB
     */
    private function archiveDataInChunks(string $archiveTable, string $startDate, string $endDate, int $chunkSize): void
    {
        // Temukan ID minimum dan maksimum untuk range pencarian cepat
        $bounds = DB::table('visitors')
            ->select(DB::raw('MIN(id) as min_id, MAX(id) as max_id'))
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->first();

        if (!$bounds || is_null($bounds->min_id)) {
            return;
        }

        $minId = $bounds->min_id;
        $maxId = $bounds->max_id;

        $currentId = $minId;
        
        // Progress bar untuk archiver
        $totalRowsToArchive = DB::table('visitors')
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->count();
            
        $archiveProgress = $this->output->createProgressBar($totalRowsToArchive);
        $archiveProgress->start();

        while ($currentId <= $maxId) {
            $nextId = $currentId + $chunkSize;

            // Pindahkan data dalam range ID
            $inserted = DB::insert("
                INSERT IGNORE INTO `{$archiveTable}` (id, ip_address, user_agent, url, visited_at)
                SELECT id, ip_address, user_agent, url, visited_at
                FROM `visitors`
                WHERE id >= ? AND id < ? 
                  AND visited_at BETWEEN ? AND ?
            ", [$currentId, $nextId, $startDate, $endDate]);

            $archiveProgress->advance($inserted);
            $currentId = $nextId;
        }

        $archiveProgress->finish();
        $this->newLine(2);
    }
}
