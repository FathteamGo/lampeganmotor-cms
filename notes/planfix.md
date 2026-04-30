# Plan Perbaikan Bug Status Kendaraan

## Status: IN PROGRESS

---

## Analisis Bug

**Masalah:** Dashboard menampilkan 118 unit, Master Data 123 unit (selisih 5 kendaraan).

### Penyebab:
1. Saat buyback, `CreatePurchase.php` langsung ubah status ke `available` TANPA cek apakah kendaraan punya active sale
2. `Purchase.php` booted event juga mengubah status ke `available` tanpa cek active sale
3. Ini menyebabkan kendaraan yang masih dalam proses penjualan (sale aktif) berubah jadi `available`

---

## Perbaikan yang Telah Dilakukan

| No | File | Perubahan | Status |
|----|------|-----------|--------|
| 1 | `app/Models/Sale.php` | Perbaiki logika `syncVehicleStatus()` -> `>= 1` | DONE |
| 2 | `app/Models/Sale.php` | Trigger sync di `updated` selalu jalan | DONE |
| 3 | `app/Filament/Resources/Sales/Pages/CreateSale.php` | Tambah validasi duplicate active sale | DONE |
| 4 | `app/Filament/Resources/Purchases/Pages/CreatePurchase.php` | Cek active sale sebelum ubah status saat buyback | DONE |
| 5 | `app/Models/Purchase.php` | Cek active sale di booted event | DONE |

---

## Detail Perubahan

### 1. `CreatePurchase.php` (Buyback Logic)
**Sebelum:** Langsung ubah status ke `available` saat VIN/engine sama ditemukan
**Sesudah:** Cek apakah kendaraan punya active sale terlebih dahulu

```php
// Cek apakah kendaraan masih punya active sale
$hasActiveSale = Sale::where('vehicle_id', $vehicle->id)
    ->where('status', '!=', 'cancel')
    ->exists();

// Hanya set available jika tidak ada active sale
if (!$hasActiveSale) {
    $updateData['status'] = 'available';
}
```

### 2. `Purchase.php` (Booted Event)
**Sebelum:** Langsung ubah status ke `available`
**Sesudah:** Cek active sale terlebih dahulu

```php
if ($purchase->vehicle && $purchase->vehicle->status !== 'available') {
    $hasActiveSale = Sale::where('vehicle_id', $purchase->vehicle->id)
        ->where('status', '!=', 'cancel')
        ->exists();

    if (!$hasActiveSale) {
        $purchase->vehicle->update(['status' => 'available']);
    }
}
```

---

## Progress Log

- [2026-04-30] Plan dibuat, bug dianalisis
- [2026-04-30] Sale.php - diperbaiki logika syncVehicleStatus (>= 1)
- [2026-04-30] Sale.php - updated event sekarang selalu trigger sync
- [2026-04-30] CreateSale.php - ditambahkan validasi duplicate active sale
- [2026-04-30] CreatePurchase.php - cek active sale saat buyback
- [2026-04-30] Purchase.php - cek active sale di booted event

---

## Catatan Penting

Setelah deploy fix ini, jalankan command untuk sync ulang:
```bash
php artisan vehicles:sync-status --force
```

Ini untuk memastikan semua kendaraan memiliki status yang benar berdasarkan sales records.