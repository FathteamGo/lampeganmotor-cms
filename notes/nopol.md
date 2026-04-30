1. D 4938 VFI
2. D 4685 VFM
3. D 4629 VDF
4. D 3350 VFD
5. D 6167 ADH
6. D 3957 VEF
7. Z 4989 DB
8. D 4128 VFT
9. D 3868 ACG
10. D 6513 LK
11. D 3648 VEO
12. D 2728 ADX
13. D 6624 ADQ
14. D 2731 ADX
15. D 4808 AES
16. D 5267 AEN
17. D 3378 ADT
18. D 4392 ADP
19. T 3914 XB

---
## SCRIPT UPDATE STATUS -> SOLD

UPDATE vehicles
SET status = 'sold', updated_at = NOW()
WHERE license_plate IN (
    'D 4938 VFI','D 4685 VFM','D 4629 VDF','D 3350 VFD','D 6167 ADH',
    'D 3957 VEF','Z 4989 DB','D 4128 VFT','D 3868 ACG','D 6513 LK',
    'D 3648 VEO','D 2728 ADX','D 6624 ADQ','D 2731 ADX','D 4808 AES',
    'D 5267 AEN','D 3378 ADT','D 4392 ADP','T 3914 XB'
)
AND status != 'sold';

---
## VERIFIKASI SETELAH UPDATE

-- Cek apakah ada sale yang masih aktif untuk kendaraan tersebut
SELECT v.license_plate, v.status AS vehicle_status, s.id AS sale_id, s.status AS sale_status
FROM vehicles v
LEFT JOIN sales s ON s.vehicle_id = v.id AND s.status != 'cancel'
WHERE v.license_plate IN (
    'D 4938 VFI','D 4685 VFM','D 4629 VDF','D 3350 VFD','D 6167 ADH',
    'D 3957 VEF','Z 4989 DB','D 4128 VFT','D 3868 ACG','D 6513 LK',
    'D 3648 VEO','D 2728 ADX','D 6624 ADQ','D 2731 ADX','D 4808 AES',
    'D 5267 AEN','D 3378 ADT','D 4392 ADP','T 3914 XB'
);

-- Cek apakah status kendaraan sudah berubah
SELECT license_plate, status FROM vehicles
WHERE license_plate IN (
    'D 4938 VFI','D 4685 VFM','D 4629 VDF','D 3350 VFD','D 6167 ADH',
    'D 3957 VEF','Z 4989 DB','D 4128 VFT','D 3868 ACG','D 6513 LK',
    'D 3648 VEO','D 2728 ADX','D 6624 ADQ','D 2731 ADX','D 4808 AES',
    'D 5267 AEN','D 3378 ADT','D 4392 ADP','T 3914 XB'
);

---
## PHP ARTISAN COMMAND (Laravel)

// Jalankan via terminal:
php artisan vehicles:fix-sold-status

// Atau bisa juga gunakan command sync yang sudah ada:
php artisan vehicles:sync-status --force

---
## LARAVEL FIX SCRIPT (Tempatkan di app/Console/Commands/FixSoldStatusCommand.php)

<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Models\Vehicle;
use Illuminate\Console\Command;

class FixSoldStatusCommand extends Command
{
    protected \$signature = 'vehicles:fix-sold-status';
    protected \$description = 'Update status kendaraan yang sudah terjual tapi masih available';

    public function handle(): int
    {
        \$licensePlates = [
            'D 4938 VFI','D 4685 VFM','D 4629 VDF','D 3350 VFD','D 6167 ADH',
            'D 3957 VEF','Z 4989 DB','D 4128 VFT','D 3868 ACG','D 6513 LK',
            'D 3648 VEO','D 2728 ADX','D 6624 ADQ','D 2731 ADX','D 4808 AES',
            'D 5267 AEN','D 3378 ADT','D 4392 ADP','T 3914 XB'
        ];

        \$vehicles = Vehicle::whereIn('license_plate', \$licensePlates)
            ->where('status', '!=', 'sold')
            ->get();

        if (\$vehicles->isEmpty()) {
            \$this->info('Semua kendaraan sudah berstatus sold.');
            return Command::SUCCESS;
        }

        foreach (\$vehicles as \$vehicle) {
            \$this->info("Updating: {\$vehicle->license_plate}");
            \$vehicle->update(['status' => 'sold']);
        }

        \$this->info("Berhasil update {\$vehicles->count()} kendaraan menjadi sold.");

        return Command::SUCCESS;
    }
}

// Atau gunakan approach sync yang lebih robust:
// php artisan vehicles:sync-status --force