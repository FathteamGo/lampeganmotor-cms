# Bug Analysis: Status Kendaraan Available Padahal Sudah Terjual

## 📋 Ringkasan Bug
19 kendaraan di file `notes/nopol.md` statusnya `available` padahal sudah ada di record penjualan (sale).

---

## 🔍 Penyebab Utama

### 1. Race Condition di syncVehicleStatus() - Penyebab Utama ⚠️

`Sale::syncVehicleStatus()` dipanggil di model events (`created`, `updated`, `deleted`), tapi ada **logika yang salah**:

```php
// Sale.php:164
$newStatus = $activeSalesCount === 1 ? 'sold' : 'available';
```

**Masalah:**
- Jika 2 atau lebih sale aktif (misal: `proses` + `kirim`) → status jadi `available` (salah!)
- Perlu minimal 1 sale aktif → `sold`

**Berikut skenario yang menyebabkan bug:**
1. Sales input `proses` → `syncVehicleStatus` dipanggil → status jadi `sold` ✓
2. Admin update status dari `proses` ke `kirim` → `syncVehicleStatus` dipanggil
3.Karena `updated` hanya trigger jika `status` berubah (`isDirty('status')`),
   dan perubahan dari `proses` ke `kirim` adalah perubahan `status` → trigger OK
4. **Tapi**: Jika ada duplicate sale yang dibuat manual (misal dari seeding, duplicate entry, atau bug lain),
   2 active sales → `activeSalesCount === 1` → `false` → status jadi `available` ✗

### 2. Multiple Active Sales Tanpa Deteksi

Tidak ada guard yang mencegah 2 sale dengan status `proses`/`kirim`/`selesai`
untuk vehicle yang sama. Validation di `SaleForm.php` hanya di `afterStateUpdated`,
tapi tidak ada check saat pembuatan sale baru.

### 3. Model Event `updated` Hanya Trap Perubahan `status`

`Sale.php:131-134`:
```php
static::updated(function ($sale) {
    if ($sale->isDirty('status')) {
        self::syncVehicleStatus($sale->vehicle_id);
    }
});
```

Jika `updated` tidak mengubah `status`, sync tidak terjadi. Ini OK secara desain
tapi bisa jadi masalah jika ada update langsung ke DB atau queue job.

### 4. Kemungkinan Penyebab Lain

- **Update langsung via SQL/Database seeding** - tidak trigger model events
- **Mass assignment dari API/Seeder** yang bypass model events
- **Cache inconsistency** - status tersimpan berbeda dari yang diharapkan

---

## ✅ Rencana Perbaikan

### Perbaikan 1: Perbaiki Logika syncVehicleStatus() [Priority: HIGH]

```php
// Sale.php - Ganti logika syncVehicleStatus()
public static function syncVehicleStatus($vehicleId)
{
    try {
        $vehicle = Vehicle::find($vehicleId);
        if (!$vehicle) return;

        // Hitung active sales
        $activeSalesCount = Sale::where('vehicle_id', $vehicleId)
            ->where('status', '!=', 'cancel')
            ->count();

        // Jika ada minimal 1 sale aktif → sold
        // Jika 0 atau semua cancel → available
        $newStatus = $activeSalesCount >= 1 ? 'sold' : 'available';

        if ($vehicle->status !== $newStatus) {
            $vehicle->update(['status' => $newStatus]);
        }
    } catch (\Exception $e) {
        Log::error("Failed to sync vehicle status: {$e->getMessage()}");
    }
}
```

### Perbaikan 2: Tambahkan Unique Constraint di Level Form [Priority: HIGH]

Di `CreateSale.php` - tambahkan validasi di `mutateFormDataBeforeCreate`:

```php
protected function mutateFormDataBeforeCreate(array $data): array
{
    // ... existing code ...

    // Validasi: kendaraan tidak boleh punya active sale
    $hasActiveSale = Sale::where('vehicle_id', $data['vehicle_id'])
        ->whereIn('status', ['proses', 'kirim', 'selesai'])
        ->exists();

    if ($hasActiveSale) {
        Notification::make()
            ->title('Error!')
            ->body('Kendaraan ini sudah ada penjualan aktif.')
            ->danger()
            ->send();

        $this->halt();
    }

    // ... rest of code ...
}
```

### Perbaikan 3: Artisan Command untuk Sync Massal [Priority: MEDIUM]

Sudah ada `SyncVehicleStatusCommand` tapi perlu augment:

```bash
php artisan vehicles:sync-status --force
```

Command ini akan sync semua vehicle berdasarkan sales records.

### Perbaikan 4: Trigger Sync di Semua Kasus Perubahan Sale [Priority: HIGH]

Di `Sale.php`, ubah `updated` event untuk selalu sync:

```php
static::updated(function ($sale) {
    // Selalu sync, bukan hanya saat status berubah
    self::syncVehicleStatus($sale->vehicle_id);
});
```

### Perbaikan 5: Tambah Monitoring/Alert [Priority: LOW]

Tambahkan daily/weekly job untuk mendeteksi inconsistency
antara vehicle.status dan sales records.

---

## 🛠️ Langkah Pemulihan (Immediate Fix)

1. **Jalankan script SQL** di `notes/nopol.md` untuk update status 19 kendaraan
2. **Jalankan** `php artisan vehicles:sync-status --force` untuk sync semua vehicle
3. **Review** apakah ada duplicate sale records di database

---

## 📝 Files yang Berubah

- `app/Models/Sale.php` - syncVehicleStatus() logic
- `app/Filament/Resources/Sales/Pages/CreateSale.php` - validasi duplicate
- `app/Filament/Resources/Sales/Pages/EditSale.php` - guard untuk multiple sales

---

## 🔒 Prevention

1. Unique constraint di database: `vehicle_id + status` kombinasi
2. Full-text search/duplicate detection saat input sale baru
3. Periodic sync job (schedule: weekly)
4. Logging untuk setiap perubahan status vehicle