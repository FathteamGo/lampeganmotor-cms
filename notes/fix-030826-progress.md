# Progres Perbaikan Buyback — fix-030826

## Status Tahap
- [ ] Tahap 1 — Sentralisasi definisi "penjualan aktif"
- [ ] Tahap 2 — Izinkan buyback
- [ ] Tahap 3 — Guard create/edit sale
- [ ] Tahap 4 — Buka kunci status manual
- [ ] Tahap 5 — Simpan `payment_to_customer`
- [ ] Tahap 6 — Perbaiki perhitungan laba motor buyback
- [ ] Tahap 7 — Jangan timpa data master customer dengan `null`
- [ ] Tahap 8 — `syncVehicleStatus` kembalikan status
- [ ] Tahap 9 — Keputusan soft delete `Vehicle`

## Hasil Verifikasi Database (Tahap 0)
BLOKIR: Database tidak bisa diakses (No such file or directory / connection error). Lanjut ke Tahap 1-7.

## Hasil Pengujian
(Menunggu)

## Perubahan Angka Laporan (Tahap 6)
(Menunggu)

## Blokir & Keputusan
Database error mencegah tahap 0 berjalan. Tahap 1-7 tidak bergantung pada database, dikerjakan.