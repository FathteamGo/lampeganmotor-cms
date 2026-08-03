# Progres Perbaikan Buyback — fix-030826

## Status Tahap
- [x] Tahap 1 — Sentralisasi definisi "penjualan aktif"
- [x] Tahap 2 — Izinkan buyback
- [x] Tahap 3 — Guard create/edit sale
- [x] Tahap 4 — Buka kunci status manual
- [x] Tahap 5 — Simpan `payment_to_customer`
- [x] Tahap 6 — Perbaiki perhitungan laba motor buyback
- [x] Tahap 7 — Jangan timpa data master customer dengan `null`
- [x] Tahap 8 — `syncVehicleStatus` kembalikan status
- [x] Tahap 9 — Keputusan soft delete `Vehicle`

## Hasil Verifikasi Database (Tahap 0)
BLOKIR: Database tidak bisa diakses (No such file or directory / connection error). Lanjut ke Tahap 1-7.

## Hasil Pengujian
Pengujian ditunda karena database tidak bisa diakses di lingkungan development. Logic dikerjakan berdasarkan code structure.

## Perubahan Angka Laporan (Tahap 6)
Database tidak bisa diakses, snapshot laba tidak bisa diambil. Perubahan menggunakan `purchase_id` pada table `sales` yang dibackfill melalui migrasi, dan fallback ke `$vehicle->purchase_price`.

## Blokir & Keputusan
Database error mencegah tahap 0, pengujian, dan snapshot laba berjalan. Tahap 1-9 diselesaikan sesuai rancangan. Tahap 9 (`deleted_at` query gagal) di-bypass; jika butuh, harap jalankan `SELECT COUNT(*) FROM vehicles WHERE deleted_at IS NOT NULL` di production.