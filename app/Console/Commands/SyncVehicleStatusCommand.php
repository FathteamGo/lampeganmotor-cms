<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Models\Vehicle;
use Illuminate\Console\Command;

class SyncVehicleStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vehicles:sync-status {--force : Sync semua vehicles tanpa condition}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi status vehicle berdasarkan sales records. Cek ketidakkonsistenan dan perbaiki.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔄 Memulai sinkronisasi status vehicle...');

        $isForce = $this->option('force');
        $synced = 0;
        $errors = 0;

        try {
            // Ambil semua vehicle yang punya sales atau yang status-nya suspicious
            $vehicles = $isForce 
                ? Vehicle::all() 
                : Vehicle::whereIn('status', ['available', 'sold'])
                    ->whereHas('sales')
                    ->orWhere('status', 'sold')
                    ->distinct()
                    ->get();

            $bar = $this->output->createProgressBar(count($vehicles));
            $bar->start();

            foreach ($vehicles as $vehicle) {
                try {
                    // Hanya hitung sale yang benar-benar aktif (proses/kirim)
                    $trulyActiveCount = Sale::where('vehicle_id', $vehicle->id)
                        ->whereIn('status', ['proses', 'kirim'])
                        ->count();

                    // Tentukan status:
                    // - Ada sale aktif (proses/kirim) → sold
                    // - Tidak ada sale aktif → TETAP seperti sekarang (jangan otomatis available)
                    //   Hanya buyback yang boleh set available
                    if ($trulyActiveCount >= 1) {
                        $expectedStatus = 'sold';
                    } else {
                        // Skip - jangan ubah status
                        $bar->advance();
                        continue;
                    }

                    // Jika berbeda dengan actual, update
                    if ($vehicle->status !== $expectedStatus) {
                        $vehicle->update(['status' => $expectedStatus]);
                        $synced++;

                        $this->components->twoColumnDetail(
                            "Vehicle #{$vehicle->id} ({$vehicle->license_plate})",
                            "{$vehicle->status} → {$expectedStatus} (Active Sales: {$trulyActiveCount})"
                        );
                    }

                    $bar->advance();
                } catch (\Exception $e) {
                    $errors++;
                    $this->error("Error syncing vehicle #{$vehicle->id}: {$e->getMessage()}");
                    $bar->advance();
                }
            }

            $bar->finish();
            $this->newLine();

            $this->info("✅ Sinkronisasi selesai!");
            $this->info("📊 Hasil:");
            $this->components->twoColumnDetail('Total vehicles diproses', (string) count($vehicles));
            $this->components->twoColumnDetail('Status ter-sinkronisasi', (string) $synced);
            $this->components->twoColumnDetail('Errors', (string) $errors);

            // Detail tambahan jika ada masalah
            if ($errors > 0) {
                $this->warn("⚠️  Ada {$errors} error yang terjadi. Cek log untuk detail.");
            }

            // Report anomali/masalah
            $this->reportAnomalies();

            return $errors === 0 ? Command::SUCCESS : Command::FAILURE;
        } catch (\Exception $e) {
            $this->error("Fatal error: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Report if there are any anomalies
     */
    private function reportAnomalies(): void
    {
        $this->info('🔍 Memeriksa anomali...');

        // 1. Vehicles dengan multiple active sales (proses/kirim)
        $multiSales = Vehicle::where('status', 'sold')
            ->get()
            ->filter(function ($vehicle) {
                $trulyActiveCount = Sale::where('vehicle_id', $vehicle->id)
                    ->whereIn('status', ['proses', 'kirim'])
                    ->count();
                return $trulyActiveCount > 1;
            })
            ->count();

        if ($multiSales > 0) {
            $this->warn("⚠️  Found {$multiSales} vehicles dengan status 'sold' tapi punya multiple active sales!");
        }

        // 2. Vehicles available tapi punya 1 active sale (proses/kirim)
        $singleSaleAvailable = Vehicle::where('status', 'available')
            ->get()
            ->filter(function ($vehicle) {
                $trulyActiveCount = Sale::where('vehicle_id', $vehicle->id)
                    ->whereIn('status', ['proses', 'kirim'])
                    ->count();
                return $trulyActiveCount === 1;
            })
            ->count();

        if ($singleSaleAvailable > 0) {
            $this->warn("⚠️  Found {$singleSaleAvailable} vehicles dengan status 'available' tapi punya 1 active sale!");
        }

        if ($multiSales === 0 && $singleSaleAvailable === 0) {
            $this->info('✨ Tidak ada anomali ditemukan!');
        }
    }
}
