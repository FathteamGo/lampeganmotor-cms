# Progres Perbaikan Buyback — fix-030826

**Branch:** `fix/buyback-sale-cycle`
**Update terakhir:** 3 Agustus 2026
**Belum di-push, belum di-merge ke `main`.**

---

## Status Tahap

| Tahap | Judul | Bug | Status | Commit |
|---|---|---|---|---|
| 0 | Verifikasi database | 11, 12 | ✅ Selesai | — |
| 1 | Helper `runningSale()` | — | ✅ Selesai | `2147aa2`, `fd6036e`, `282b4a5` |
| 2 | Izinkan buyback | 1 | ✅ Selesai | `132b7b8`, `02c41e9` |
| 3 | Guard create/edit sale | 3, 5, 6 | ✅ Selesai | `0c77c19`, `282b4a5` |
| 4 | Buka kunci status manual | 4 | ✅ Selesai | `cf3725c` |
| 5 | Kolom `payment_to_customer` | 7 | ✅ Selesai | `7c16bcf` |
| 6 | Perhitungan laba buyback | 8 | ⚠️ Kode selesai + ada test; **snapshot perlu diulang di data produksi** | `4aae64f`, `a7121f8`, `282b4a5` |
| 7 | Data master customer | 9 | ✅ Selesai | `8595ed1`, `282b4a5` |
| 8 | `syncVehicleStatus` | 10 | ✅ Selesai (ada test) | `98d0dd4` |
| 9 | Soft delete `Vehicle` | 13 | ⚠️ Kolom `deleted_at` dihapus; **cek isinya 0 dulu sebelum migrate** | `2321107` |
| — | Feature test siklus buyback | — | ✅ Selesai (10/10 lulus) | `d09ec5d` |

---

## Hasil Verifikasi Database (Tahap 0)

Dijalankan terhadap database produksi. **Tidak ada penyebab tambahan di sisi DB.**

- Index `sales_vehicle_id_status_unique` / `sales_vehicle_id_unique` **tidak ada** → BUG-12 tidak terjadi.
- Enum `status` sudah memuat `'cancel'`, `order_source` sudah memuat `'olx'` → BUG-11 tidak terjadi.
- Engine: **MySQL 8.0.46** (bukan PostgreSQL), jadi migrasi enum bersintaks MySQL aman.

Artinya seluruh keluhan user murni berasal dari logika aplikasi, dan sudah ditangani Tahap 1–4.

---

## Hasil Pengujian

```bash
php -d extension=pdo_sqlite -d extension=sqlite3 vendor/bin/phpunit --testsuite=Feature
```

**Hasil suite: 14 test, 13 lulus, 1 gagal (`ExampleTest`, environment — lihat di bawah).**

`VehicleResellCycleTest` membuat schema minimal sendiri di `setUp()`, **bukan**
`RefreshDatabase`. Ini disengaja: sebagian migrasi project memakai sintaks
MySQL-only (`ALTER TABLE ... MODIFY COLUMN ... ENUM`), sehingga dengan
`RefreshDatabase` seluruh 10 test langsung error di SQLite —
`SQLSTATE[HY000]: near "MODIFY": syntax error`. Versi manual-schema ini berjalan
di mana pun tanpa perlu container MySQL.

**Hasil `VehicleResellCycleTest`: 10/10 lulus.**

| Skenario | Status |
|---|---|
| Sale `proses` mengunci motor (`sold`, tidak sellable) | ✅ |
| Sale `selesai` **bukan** penjualan berjalan | ✅ |
| Motor buyback bisa dijual kembali (2 purchase, 2 sale) | ✅ |
| Buyback ditolak saat penjualan masih `kirim` | ✅ |
| Sale siklus 2 memakai Purchase #2, siklus 1 tetap Purchase #1 | ✅ |
| Sale backdate memakai pembelian terbaru, bukan tertua | ✅ |
| Laba kotor dihitung terhadap purchase yang terikat | ✅ |
| Sale dibatalkan → motor kembali `available` | ✅ |
| Status `in_repair`/`hold` tidak ditimpa saat sale dibatalkan | ✅ |
| Sale `selesai` tetap menahan motor di `sold` saat sale lain dibatalkan | ✅ |

Test laba adalah yang paling penting: dengan kode lama, laba siklus 2 keluar
Rp 3.000.000 (12jt − harga beli siklus 1). Sekarang Rp 2.000.000 (12jt − 10jt).

### `PurchaseLifecycleTest` — 3/3 lulus (setelah koreksi)

Dua test sudah **merah sejak `abfdc69`**, sebelum pekerjaan ini dimulai —
diverifikasi dengan menjalankan suite di worktree pada commit tersebut.
Keduanya menguji perilaku yang memang sudah sengaja dibuang:

- `test_purchase_tidak_mengubah_status_kendaraan` (dulu `..._sets_status_available`) —
  menuntut `Purchase::create` mengubah status kendaraan, padahal event
  `Purchase::booted()` sengaja dihapus karena jadi penyebab motor terjual
  berubah kembali jadi `available`. Assertion dibalik agar sesuai desain.
- `test_repurchase_of_sold_vehicle_...` — memakai status sale `'completed'`
  yang tidak ada di enum. Diganti `'selesai'`, dan assertion diperjelas:
  buyback diizinkan (tidak ada penjualan berjalan), tapi perubahan status ke
  `available` adalah tugas halaman `CreatePurchase`, bukan model `Purchase`.

### `ExampleTest` — gagal (environment, di luar cakupan)

`GET /` mengembalikan 500: `no such table: visitors`. Halaman depan mencatat
kunjungan, sementara schema test dibuat manual karena sebagian migrasi memakai
sintaks MySQL yang tidak didukung SQLite. Sudah gagal sejak sebelum pekerjaan
ini dan tidak berhubungan dengan perubahan mana pun di sini.

### Yang belum bisa diuji

Alur UI Filament (klik form Pembelian/Penjualan) belum dijalankan — MySQL lokal
di port 3308 tidak aktif dan aplikasi tidak bisa di-boot dari environment ini.
Logika di baliknya sudah tertutup feature test, tapi **verifikasi UI tetap perlu
dilakukan sebelum merge** (lihat Sebelum Merge di bawah).

---

## Perubahan Angka Laporan (Tahap 6) — MASIH PERLU DIULANG DI DATA PRODUKSI

Snapshot laba sebelum/sesudah dijalankan di container MySQL dan hasilnya **0 diff**.
Migrasi backfill berjalan tanpa error.

⚠️ **Angka 0 diff itu belum tentu kabar baik — perlu dipastikan sumber datanya.**
Kalau snapshot diambil di container kosong atau di database yang belum punya motor
buyback, hasil 0 diff hanya membuktikan migrasinya tidak crash, bukan bahwa
perhitungan labanya sudah benar. Justru kalau perbaikannya bekerja, sale pada motor
dengan lebih dari satu purchase **harus** berubah angkanya.

Cek dulu apakah datanya memang ada:

```sql
SELECT vehicle_id, COUNT(*) FROM purchases GROUP BY vehicle_id HAVING COUNT(*) > 1;
```

- **Kosong** → wajar 0 diff; belum ada motor buyback di data. Tidak ada risiko
  terhadap laporan yang sudah terbit.
- **Ada isinya** → sale pada motor-motor itu seharusnya berubah. Kalau tetap
  0 diff, backfill-nya tidak kena sasaran dan harus ditelusuri.

Ulangi snapshot di database produksi (perintah lengkap di bagian Sebelum Merge).

---

## Perbaikan Setelah Review (commit `a7121f8`–`d09ec5d`)

Review terhadap Tahap 1–9 menemukan satu blocker dan beberapa cacat serius.

### 🔴 Blocker: migrasi `purchase_id` tidak ter-commit

Commit `4aae64f` sudah mengubah `Sale::purchase()` jadi `belongsTo('purchase_id')`
dan `CreateSale` sudah menulis kolom itu, tapi file migrasinya tertinggal sebagai
untracked. Deploy dari git → `Column not found: sales.purchase_id` di **semua**
halaman penjualan. Sudah di-commit di `a7121f8`.

### 🟠 Penolakan buyback tidak terlihat sama sekali

`ValidationException` dilempar dengan key `'vin'`. Filament mengikat pesan error
ke *state path* form yang ber-prefix `data.`
(`vendor/filament/forms/resources/views/components/field-wrapper.blade.php:53`),
jadi pesannya tidak pernah tampil — purchase di-rollback tanpa keterangan apa pun,
lebih membingungkan daripada sebelum diperbaiki. Diganti `'data.vin'`.
Hal yang sama diperbaiki di `EditSale` (`'status'` → `'data.status'`).

### 🟠 Validasi NIK memblokir pelanggan lama

`Rule::unique('customers','nik')->ignore($record?->customer_id)` — di halaman
create `$record` selalu null, jadi tidak ada yang di-ignore. Pelanggan lama yang
beli lagi dan NIK-nya diisi langsung tertolak "NIK sudah digunakan". Ini justru
skenario yang jadi keluhan awal user. Sekarang customer di-resolve dulu dari
nama + telepon lewat `SaleForm::resolveCustomerId()`, dengan kriteria yang sama
persis dengan `firstOrNew()` di `CreateSale`.

### 🟠 `purchase_id` jadi basi saat motor diganti di halaman Edit

`SaleForm` mengizinkan mengubah `vehicle_id` pada sale yang sudah ada, tapi
`EditSale` tidak pernah menghitung ulang `purchase_id` → sale menunjuk purchase
milik motor lain. Sekarang dihitung ulang, dan tidak menghapus nilai lama kalau
lookup gagal (kecuali motornya memang diganti).

### 🟡 Lain-lain

- **13 `Log::info` debugging** dibuang dari `CreatePurchase` — sebelumnya seluruh
  isi form termasuk data customer dibuang ke log setiap penyimpanan.
  `ValidationException` juga tidak lagi dicatat sebagai `Log::error`.
- **Fallback pencarian purchase dibalik** — `orderBy('purchase_date')` menaik
  mengambil pembelian **tertua**, yaitu harga dari siklus yang sudah lewat.
  Sekarang menurun, dengan tie-break `id` untuk purchase bertanggal sama.
  Berlaku di `CreateSale` dan di backfill migrasi.
- **Migrasi `purchase_id` dibuat idempoten** (`Schema::hasColumn`) dan backfill
  memakai `chunkById` + `whereDate`, konsisten dengan migrasi lain.
- Logika penentuan modal disatukan di `Vehicle::purchaseForSaleDate()` —
  sebelumnya query-nya disalin di `CreateSale` dan di migrasi.
- No. telepon bisa dikosongkan lagi di halaman edit (sempat ikut aturan
  "hanya isi kalau tidak kosong" sehingga tidak bisa dihapus).
- Cabang mati `if (!$runningSale)`, konstanta `LOCKING_SALE_STATUSES` yang
  menyela deretan relasi, dan import tak terpakai dirapikan.

### Catatan git

Di tengah pengerjaan, sesuatu men-*detach* HEAD ke `fix/buyback-sale-cycle~2`
sehingga commit `02c41e9` dan `282b4a5` sempat lepas dari branch. Sudah
dipulihkan lewat `git checkout fix/buyback-sale-cycle` + cherry-pick. Riwayat
branch sekarang utuh dan berurutan — pastikan `git log` menampilkan
`a7121f8 → 02c41e9 → 282b4a5 → d09ec5d` sebelum merge.

---

## Gerbang Mutu

| Cek | Hasil |
|---|---|
| `grep -rn "'proses', 'kirim', 'selesai'" app/Filament app/Models` | Hanya `Sale::syncVehicleStatus` — pengecualian yang memang benar (definisi riwayat, bukan pengunci) |
| `Log::` di `CreatePurchase` | 2 (satu info buyback, satu error di catch) — dari 15 |
| `dd()` / `dump()` / `ray()` | Bersih |
| `customer_name` pada objek `Sale` | Bersih — semua sudah `customer?->name` |
| `php -l` seluruh file yang diubah | Lolos |
| `./vendor/bin/pint --dirty` | **Tidak dijalankan.** Repo ini tidak punya `pint.json` dan file yang tidak disentuh pun gagal pint (`app/Models/Sale.php`, `SalesTable.php`, dll). Menjalankannya hanya pada file yang diubah akan membuat diff besar yang justru tidak konsisten dengan kode sekitarnya. |

---

## Tahap 9 — Kolom `deleted_at` dihapus (`2321107`)

Kolom `vehicles.deleted_at` ada, tapi model `Vehicle` tidak memakai trait
`SoftDeletes`. Penelusuran menunjukkan **`SoftDeletes` tidak dipakai di mana pun
di seluruh aplikasi**, jadi kolom itu tidak pernah ditulis oleh kode yang ada.
Diputuskan untuk menghapus kolomnya lewat migrasi
`2026_08_03_145131_drop_deleted_at_from_vehicles_table`.

⚠️ **Wajib dipastikan sebelum migrasi dijalankan di produksi.** Migrasinya
`dropSoftDeletes()` tanpa cek isi — kalau ternyata ada baris dengan `deleted_at`
tidak null, penandanya hilang permanen dan motor-motor itu akan muncul kembali
sebagai data aktif:

```sql
SELECT COUNT(*) FROM vehicles WHERE deleted_at IS NOT NULL;
```

Kalau hasilnya **bukan 0**, jangan jalankan migrasi ini — audit dulu motor mana
saja itu dan apakah memang seharusnya tersembunyi.

### ⚠️ Temuan baru saat menelusuri Tahap 9

`VehiclesTable` dan `EditVehicle` punya `DeleteAction` / `DeleteBulkAction`,
sementara FK `sales.vehicle_id` memakai `cascadeOnDelete`
(migrasi `2025_11_07_061048`). Karena `Vehicle` **tidak** memakai `SoftDeletes`,
menghapus satu motor lewat UI akan **menghapus seluruh riwayat penjualannya
secara permanen, tanpa peringatan** — termasuk data yang sudah masuk laporan
laba. Risikonya lebih besar daripada BUG-13 itu sendiri.

Ini di luar cakupan 13 bug yang didokumentasikan, jadi tidak diubah.
Dengan kolom `deleted_at` dihapus, opsi SoftDeletes tertutup — jadi
rekomendasinya sekarang: cabut `DeleteBulkAction` dari `VehiclesTable` seperti yang sudah
dilakukan di `SalesTable`.

---

## Sebelum Merge

1. **Backup database.** Ada tiga migrasi baru, satu melakukan backfill dan satu
   menghapus kolom:
   `2026_08_03_112830_add_payment_to_customer_to_sales_table`
   `2026_08_03_112913_add_purchase_id_to_sales_table` (backfill)
   `2026_08_03_145131_drop_deleted_at_from_vehicles_table` (**destruktif** —
   pastikan `SELECT COUNT(*) FROM vehicles WHERE deleted_at IS NOT NULL` = 0 dulu)
2. **Ambil snapshot laba sebelum migrasi**, jalankan `php artisan migrate --force`,
   lalu ambil snapshot sesudahnya dan bandingkan (perintah lengkap di bagian
   Tahap 6 di atas). Cek juga apakah memang ada motor dengan >1 purchase — kalau
   ada tapi tidak ada angka yang berubah, backfill-nya tidak kena sasaran.
3. **Uji alur lewat UI** untuk kasus nyata yang dilaporkan user:
   beli → jual sampai `selesai` → buyback → jual lagi. Pastikan motornya muncul
   kembali di dropdown Penjualan dan sale kedua tersimpan tanpa error.
4. **Uji penolakan**: buyback motor yang sale-nya masih `kirim` — pesan merah
   harus muncul **di sebelah field Nomor Rangka**, bukan hilang tanpa jejak.
5. **Uji pelanggan lama**: buat penjualan untuk customer yang sudah ada dan isi
   NIK-nya. Harus tersimpan, dan NIK/alamat/IG/TikTok lama tidak boleh hilang.
6. **Putuskan `DeleteBulkAction` di `VehiclesTable`** — lihat temuan di bagian
   Tahap 9. Menghapus motor lewat UI saat ini ikut menghapus seluruh riwayat
   penjualannya secara permanen.

Setelah semuanya lolos, baru merge ke `main`.
