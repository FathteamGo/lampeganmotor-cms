# Prompt Implementasi — Perbaikan Siklus Jual-Beli Motor (Buyback)

> **Cara pakai:** salin seluruh isi file ini sebagai prompt untuk AI agent, atau jalankan
> `claude "kerjakan notes/fix-030826-prompt.md"` dari root project.
>
> **Dokumen sumber:** `notes/fix-030826.md` (analisa lengkap, WAJIB dibaca sebelum menulis kode)

---

## PERAN & TUJUAN

Kamu adalah senior Laravel/Filament engineer yang mengerjakan CMS showroom motor
(`lampeganmotor-cms`). Tugasmu: **memperbaiki seluruh bug pada alur penjualan & buyback
sampai tuntas dan siap produksi** — bukan sekadar menambal gejala.

Ada 13 bug terdokumentasi (BUG-1 s/d BUG-13) di `notes/fix-030826.md`. Selesaikan
**semuanya**, dalam urutan tahap yang sudah ditentukan, dengan pengujian di setiap tahap.

Stack: Laravel 12, PHP 8.2, Filament 4.0.3, Livewire 3, MySQL/MariaDB.

---

## KONTEKS MASALAH (ringkas)

Satu unit motor bisa berputar berkali-kali:

```
Beli dari supplier → Purchase #1 → status: available
Jual ke customer   → Sale #1 (proses → kirim → selesai) → status: sold
Customer jual balik → Purchase #2 (buyback) → HARUSNYA kembali available   ← GAGAL
Jual lagi          → Sale #2                                               ← ERROR
```

**Akar masalah:** sale berstatus `selesai` diperlakukan sebagai "penjualan aktif yang
mengunci motor". Padahal `selesai` artinya transaksi tuntas dan motor sah pindah ke
customer — itu **prasyarat** buyback, bukan penghalang. Kesalahan ini ada di 6 lokasi
yang saling menutup jalan, sehingga motor terkunci permanen tanpa workaround.

### Prinsip yang harus dipegang di seluruh perubahan

> **Yang menentukan boleh-tidaknya motor dijual adalah `vehicles.status`, BUKAN riwayat tabel `sales`.**
>
> **Penjualan yang mengunci motor = `status IN ('proses','kirim')`.
> Status `selesai` adalah riwayat, bukan pengunci.**

Konsekuensi: motor yang masih di tangan customer aman karena `vehicles.status = 'sold'`,
bukan karena riwayat sale-nya dicek berulang. Buyback cukup mengubah status ke
`available`, dan seluruh alur penjualan otomatis terbuka lagi.

---

## LANGKAH 0 — WAJIB SEBELUM MENULIS KODE

1. Baca `notes/fix-030826.md` sampai habis. Semua nomor bug di prompt ini merujuk ke sana.
2. Baca file-file yang akan diubah — **jangan percaya nomor baris di dokumen** (bisa
   bergeser). Cari berdasarkan isi kode, bukan posisi baris.
3. Buat branch kerja:
   ```bash
   git checkout -b fix/buyback-sale-cycle
   ```
4. Buat file progres `notes/fix-030826-progress.md` memakai template di bagian
   [PELAPORAN PROGRES](#pelaporan-progres). **Update file ini setiap kali satu tahap selesai.**
5. Jalankan verifikasi database (bagian [VERIFIKASI DATABASE](#verifikasi-database-tahap-0)).
   Kalau DB tidak bisa diakses, catat di file progres sebagai **BLOKIR** dan lanjutkan
   tahap 1-7 (tidak bergantung pada hasil verifikasi), lalu laporkan di akhir.

---

## VERIFIKASI DATABASE (TAHAP 0)

Jalankan ini dulu — hasilnya menentukan apakah ada penyebab error **tambahan** di sisi DB
yang akan tetap muncul walaupun kode sudah benar.

```bash
php artisan tinker --execute="
  print_r(DB::select('SHOW INDEX FROM sales'));
  print_r(DB::select(\"SHOW COLUMNS FROM sales WHERE Field IN ('status','order_source','result')\"));
  print_r(DB::select('SELECT VERSION() as v'));
  print_r(DB::table('migrations')->where('migration','like','%sales%')->orderByDesc('id')->limit(10)->get());
"
```

Yang dicari:

| Cek | Kondisi bermasalah | Tindakan |
|---|---|---|
| Index `sales_vehicle_id_status_unique` / `sales_vehicle_id_unique` masih ada | **BUG-12** — penyebab langsung SQLSTATE 23000 | Jalankan `php artisan migrate --force` (migrasi `2025_11_07_061048`). Kalau tetap ada, buat migrasi baru untuk drop index-nya. |
| Enum `status` tidak memuat `'cancel'`, atau `order_source` tidak memuat `'olx'` | **BUG-11** — migrasi enum gagal | Buat migrasi perbaikan. Kalau engine-nya PostgreSQL, tulis ulang dengan sintaks portabel. |
| `VERSION()` menunjukkan PostgreSQL | **BUG-11** kritis | Laporkan ke user sebelum lanjut — 3 migrasi enum memakai sintaks MySQL-only. |

Ambil juga log produksi terbaru untuk konfirmasi error aslinya:
```bash
tail -n 500 storage/logs/laravel.log | grep -B 5 -A 20 "ERROR"
```

Catat semua hasil di file progres.

---

## TAHAP IMPLEMENTASI

Kerjakan **berurutan**. Jangan lompat — memperbaiki sebagian hanya memindahkan letak error.
Commit terpisah per tahap dengan pesan yang jelas.

---

### TAHAP 1 — Sentralisasi definisi "penjualan aktif" [fondasi]

**File:** `app/Models/Vehicle.php`

Tambahkan satu sumber kebenaran supaya kondisi yang sama tidak disalin ke 6 tempat:

```php
/** Status sale yang benar-benar mengunci motor (tidak termasuk 'selesai') */
public const LOCKING_SALE_STATUSES = ['proses', 'kirim'];

/** Sale yang sedang berjalan untuk motor ini. $exceptSaleId untuk mode edit. */
public function runningSale(?int $exceptSaleId = null): ?Sale
{
    return $this->sales()
        ->whereIn('status', self::LOCKING_SALE_STATUSES)
        ->when($exceptSaleId, fn ($q) => $q->where('id', '!=', $exceptSaleId))
        ->latest('id')
        ->first();
}

public function isSellable(): bool
{
    return $this->status === 'available' && $this->runningSale() === null;
}
```

Relasi `activeSale()` yang sudah ada (`app/Models/Vehicle.php`) sudah memakai definisi yang
benar tapi tidak pernah dipakai. Setelah tahap ini, **pertimbangkan menghapusnya** kalau
sudah tergantikan `runningSale()` — jangan tinggalkan dua helper yang tumpang tindih.

**Selesai bila:** helper ada, `php artisan tinker` bisa memanggil
`Vehicle::find(1)->isSellable()` tanpa error.

---

### TAHAP 2 — Izinkan buyback [BUG-1] 🔴 root cause

**File:** `app/Filament/Resources/Purchases/Pages/CreatePurchase.php`

Cari blok `$hasActiveSale` di dalam cabang `else` (kendaraan sudah ada / buyback):

```php
// SEBELUM
$hasActiveSale = \App\Models\Sale::where('vehicle_id', $vehicle->id)
    ->where('status', '!=', 'cancel')      // ← 'selesai' ikut terhitung
    ->exists();
```

Ganti menjadi:

```php
// SESUDAH — hanya sale yang MASIH BERJALAN yang memblokir buyback.
// Sale 'selesai' justru syarat sah buyback (motor sudah di tangan customer).
$runningSale = $vehicle->runningSale();
```

Lalu ganti `if (!$hasActiveSale)` menjadi `if (! $runningSale)`.

**Tambahan wajib:** saat ini kalau buyback ditolak, kode hanya menulis `Log::info` dan
purchase tetap tersimpan diam-diam — operator mengira sudah beres. Ubah jadi penolakan
eksplisit:

```php
if ($runningSale) {
    throw ValidationException::withMessages([
        'vin' => "Motor ini masih dalam penjualan berjalan atas nama "
               . ($runningSale->customer?->name ?? 'customer') . " (status: {$runningSale->status}). "
               . "Selesaikan atau batalkan penjualan tersebut sebelum melakukan pembelian kembali.",
    ]);
}
```

**Bersih-bersih sekalian:** file ini penuh `Log::info` debugging (LOG 1 s/d LOG 10) yang
membuang seluruh isi form ke log — termasuk data customer. **Hapus semua log debugging
tersebut**, sisakan hanya `Log::error` di blok catch dan satu `Log::info` untuk peristiwa
buyback yang berarti. Ini syarat production-ready.

**Selesai bila:** buyback pada motor dengan sale `selesai` berhasil mengubah status ke
`available`; buyback pada motor dengan sale `proses`/`kirim` ditolak dengan pesan jelas.

---

### TAHAP 3 — Guard create/edit sale [BUG-3, BUG-5, BUG-6] 🔴 satu paket

Ketiga file di bawah **harus diubah bersamaan**. Mengubah sebagian akan memindahkan error.

#### 3a. `app/Filament/Resources/Sales/Pages/CreateSale.php`

Ganti blok validasi `$hasActiveSale`:

```php
if (!empty($data['vehicle_id'])) {
    $vehicle = \App\Models\Vehicle::find($data['vehicle_id']);

    if (! $vehicle) {
        Notification::make()->title('Error!')
            ->body('Kendaraan tidak ditemukan.')->danger()->send();
        $this->halt();
    }

    $running = $vehicle->runningSale();
    if ($running) {
        Notification::make()
            ->title('Motor sedang dalam transaksi')
            ->body("Motor ini masih terikat penjualan berjalan atas nama "
                 . ($running->customer?->name ?? 'customer')
                 . " (status: {$running->status}).")
            ->danger()->send();
        $this->halt();
    }

    if ($vehicle->status !== 'available') {
        Notification::make()
            ->title('Motor belum tersedia')
            ->body("Status motor saat ini: {$vehicle->status}. "
                 . "Untuk motor buyback, input data Pembelian terlebih dahulu.")
            ->danger()->send();
        $this->halt();
    }
}
```

#### 3b. `app/Filament/Resources/Sales/Pages/EditSale.php`

Ganti blok `$existingActive`:

```php
if ($newStatus && in_array($newStatus, ['proses', 'kirim', 'selesai'])) {
    $running = \App\Models\Vehicle::find($this->record->vehicle_id)
        ?->runningSale(exceptSaleId: $this->record->id);

    if ($running) {
        throw ValidationException::withMessages([
            'status' => "Motor ini sedang dalam penjualan berjalan atas nama "
                      . ($running->customer?->name ?? 'customer') . ".",
        ]);
    }
}
```

⚠️ Perhatikan: **`->customer?->name`**, bukan `->customer_name`. Model `Sale` tidak punya
kolom/accessor `customer_name` — pesan lama selalu keluar sebagai `"...customer: ."` [BUG-6].
Cari dan perbaiki **semua** kemunculan `customer_name` pada objek `Sale` di seluruh project:

```bash
grep -rn 'customer_name' app/Filament app/Models app/Exports
```

Hati-hati membedakannya dari `$data['customer_name']` (key form) yang memang benar.

Hapus juga `session()->flash(...)` yang mendahului `throw` — flash tersebut tidak pernah
tampil karena exception langsung menghentikan request, jadi hanya menyesatkan.

#### 3c. `app/Filament/Resources/Sales/Schemas/SaleForm.php`

**Hapus** `afterStateUpdated` pada `Select::make('vehicle_id')` yang melempar
`ValidationException`. Dua hal salah di sana [BUG-5]:
- field tidak `->live()`, jadi callback-nya tidak pernah jalan saat create (dead code);
- key error bag `'vehicle_id'` salah — Filament menyimpan state di bawah prefix `data.`,
  jadi pesan tidak pernah tampil di sebelah field.

Ganti dengan validation rule yang berjalan saat submit:

```php
Select::make('vehicle_id')
    ->label('Motor')
    ->options(/* biarkan seperti sekarang: where('status', 'available') */)
    ->required()
    ->searchable()
    ->live()
    ->rule(function ($record) {
        return function (string $attribute, $value, \Closure $fail) use ($record) {
            $vehicle = \App\Models\Vehicle::find($value);
            if (! $vehicle) {
                $fail('Kendaraan tidak ditemukan.');
                return;
            }
            $running = $vehicle->runningSale(exceptSaleId: $record?->id);
            if ($running) {
                $fail("Motor ini masih terikat penjualan berjalan atas nama "
                    . ($running->customer?->name ?? 'customer') . ".");
            }
        };
    }),
```

**JANGAN longgarkan filter `where('status','available')` pada `->options()`.** Filter itu
sudah benar. Kalau dilonggarkan, motor yang masih di tangan customer bisa terjual dua kali.
Motor buyback akan muncul dengan sendirinya setelah TAHAP 2 memperbaiki statusnya.

Periksa juga `afterStateUpdated` pada `Select::make('status')` di file yang sama — blok
`$existing` di dalamnya punya masalah yang sama (key error bag salah + `customer_name`).
Perbaiki atau pindahkan ke validation rule dengan pola yang sama.

**Selesai bila:** seluruh checklist skenario utama & proteksi di
[PENGUJIAN](#pengujian-wajib-lulus-semua) untuk motor buyback lulus.

---

### TAHAP 4 — Buka kunci status manual [BUG-4]

**File:** `app/Filament/Resources/Vehicles/Schemas/VehicleForm.php`

Pada `Select::make('status')`, ganti `disableOptionWhen`:

```php
->disableOptionWhen(fn ($value, $record) =>
    $value === 'available' &&
    $record &&
    \App\Models\Sale::where('vehicle_id', $record->id)
        ->whereIn('status', \App\Models\Vehicle::LOCKING_SALE_STATUSES)
        ->exists()
)
```

Admin dapat pintu darurat yang sah, tapi tetap terlindungi dari mengubah status motor yang
sedang dalam pengiriman.

**Selesai bila:** motor dengan sale `selesai` bisa di-set `available` manual; motor dengan
sale `proses`/`kirim` opsinya tetap ter-disable.

---

### TAHAP 5 — Simpan `payment_to_customer` [BUG-7]

Field ini ada di form Dana Tunai dan dipakai menghitung `remaining_payment` (Laba Penjualan),
tapi **tidak punya kolom DB dan tidak fillable** — nilainya dibuang diam-diam oleh Laravel.

1. Buat migrasi:
   ```php
   Schema::table('sales', function (Blueprint $table) {
       $table->decimal('payment_to_customer', 15, 2)->nullable()->after('dp_real');
   });
   ```
   Sertakan `down()` yang benar (`dropColumn`). Gunakan `Schema::hasColumn` guard agar
   idempoten, mengikuti pola migrasi lain di project ini.

2. `app/Models/Sale.php` — tambahkan ke `$fillable` dan `$casts` (`'decimal:2'`).

3. Verifikasi: buat sale Dana Tunai → simpan → buka Edit → nilainya harus muncul kembali,
   dan `remaining_payment` tidak berubah sendiri saat field lain disentuh.

**Selesai bila:** round-trip create → edit → save tidak mengubah nilai Laba Penjualan.

---

### TAHAP 6 — Perbaiki perhitungan laba motor buyback [BUG-8] ⚠️ RISIKO TINGGI

Ini bug paling berbahaya secara finansial: **tidak memunculkan error apa pun**, tapi membuat
laba di laporan lebih tinggi dari kenyataan untuk setiap motor yang pernah dibeli ulang.

Penyebabnya, dua tempat mengambil purchase **pertama** (id terkecil) tanpa `orderBy`:
- `app/Models/Sale.php` → relasi `purchase()`
- `app/Filament/Resources/Sales/Tables/SalesTable.php` → di kolom `total_pembelian` **dan**
  di method `calculateLabaKotor()`

#### 6a. Perbaikan struktural (kerjakan ini — bukan sekadar `orderBy`)

Ikat sale ke purchase secara eksplisit supaya seluruh kelas bug "purchase mana yang dipakai"
hilang permanen:

1. Migrasi:
   ```php
   Schema::table('sales', function (Blueprint $table) {
       $table->foreignId('purchase_id')->nullable()->after('vehicle_id')
             ->constrained('purchases')->nullOnDelete();
   });
   ```

2. Backfill data lama dalam migrasi yang sama — untuk setiap sale, ambil purchase dengan
   `purchase_date <= sale_date` terbaru pada `vehicle_id` yang sama; kalau tidak ada, ambil
   purchase paling awal:
   ```php
   // Proses per-chunk, jangan load semua sale sekaligus
   ```

3. `Sale::$fillable` → tambahkan `'purchase_id'`. Tambahkan relasi `belongsTo(Purchase::class)`.

4. Isi otomatis saat sale dibuat — di `CreateSale::mutateFormDataBeforeCreate()`, set
   `$data['purchase_id']` dari purchase terbaru untuk `vehicle_id` tersebut.

5. Ubah `Sale::getLabaKotorAttribute()` dan `SalesTable::calculateLabaKotor()` agar memakai
   `$sale->purchase_id`. Pertahankan fallback berjenjang yang sudah ada:
   `purchase->grand_total` → `vehicle->purchase_price` → `0`.

6. Relasi lama `hasOne(Purchase::class, 'vehicle_id', 'vehicle_id')` harus diganti
   `belongsTo` via `purchase_id`. Pastikan **semua** pemakai relasi ini ikut diperbarui:
   ```bash
   grep -rn "purchase\b" app/Models/Sale.php app/Filament/Resources/Sales app/Exports
   ```
   Termasuk eager-load `with(['purchase.additionalCosts'])` di summarizer & export.

#### 6b. Wajib sebelum & sesudah

Ambil snapshot laba **sebelum** perubahan sebagai pembanding:
```bash
php artisan tinker --execute="
  \App\Models\Sale::with('vehicle')->get()
    ->each(fn(\$s) => print(\$s->id.'|'.\$s->laba_kotor.'|'.\$s->laba_bersih.PHP_EOL));
" > /tmp/laba-before.txt
```
Jalankan lagi sesudahnya. **Yang boleh berubah hanya sale pada motor yang punya lebih dari
satu purchase.** Kalau ada sale lain yang angkanya bergeser, itu regresi — perbaiki dulu.

Lampirkan ringkasan selisihnya di file progres, karena ini mengubah angka laporan yang
mungkin sudah terlanjur dipakai user.

**Selesai bila:** laba Sale #2 dihitung terhadap Purchase #2, laba Sale #1 tidak berubah,
dan seluruh sale motor non-buyback angkanya identik dengan snapshot sebelum perubahan.

---

### TAHAP 7 — Jangan timpa data master customer dengan `null` [BUG-9]

Form penjualan **tidak punya field NIK**, tapi `CreateSale` dan `EditSale` menulis
`$data['customer_nik'] ?? null` ke tabel customers → **NIK terhapus setiap simpan**. Hal yang
sama terjadi pada `address`, `instagram`, `tiktok` bila dikosongkan. Ini kena persis di
skenario pelanggan berulang.

#### 7a. `CreateSale.php`

```php
$customer = Customer::firstOrNew([
    'name'  => trim($data['customer_name']),
    'phone' => !empty($data['customer_phone']) ? trim($data['customer_phone']) : null,
]);

// Hanya isi field yang benar-benar dikirim & tidak kosong
foreach (['nik', 'address', 'instagram', 'tiktok'] as $field) {
    if (!empty($data["customer_{$field}"])) {
        $customer->{$field} = $data["customer_{$field}"];
    }
}
$customer->save();
```

#### 7b. `EditSale.php`

Ganti `Customer::where('id', ...)->update([...])` menjadi pola `find` → `fill` → `save`.
Query builder `->update()` **melewati mutator** `setInstagramAttribute` /
`setTiktokAttribute`, jadi normalisasi handle IG/TikTok tidak pernah jalan di jalur edit.
Terapkan pola "hanya isi kalau tidak kosong" yang sama.

#### 7c. Tambahkan field NIK ke `SaleForm`

`EditSale::mutateFormDataBeforeFill()` sudah mengisi `customer_nik`, dan
`mutateFormDataBeforeSave()` sudah membacanya — tapi field-nya tidak pernah ada di form.
Tambahkan `TextInput::make('customer_nik')->label('NIK')` ke section "Data Customer" untuk
melengkapi fitur yang setengah jadi ini.

Catatan: kolom `customers.nik` punya unique index. Tambahkan validasi
`Rule::unique('customers','nik')->ignore($customerId)` agar bentrok NIK muncul sebagai pesan
validasi yang rapi, bukan SQLSTATE 23000.

**Selesai bila:** customer lama beli lagi → NIK, alamat, IG, TikTok tetap utuh.

---

### TAHAP 8 — `syncVehicleStatus` kembalikan status [BUG-10] ⚠️ RISIKO TINGGI

**File:** `app/Models/Sale.php`

Saat ini kalau semua sale sebuah motor di-cancel, fungsi ini `return` tanpa mengubah apa pun
— motor tertinggal berstatus `sold` selamanya.

```php
$hasSoldSale = Sale::where('vehicle_id', $vehicleId)
    ->whereIn('status', ['proses', 'kirim', 'selesai'])
    ->exists();

if ($hasSoldSale) {
    $newStatus = 'sold';
} else {
    // Semua sale cancel / tidak ada sale.
    // Jangan ganggu status khusus yang di-set manual operator.
    if (in_array($vehicle->status, ['in_repair', 'hold'])) {
        return;
    }
    $newStatus = 'available';
}
```

⚠️ **Baca `fixbug.md` di root project sebelum mengerjakan tahap ini.** Perubahan serupa dulu
memicu bug "19 motor available padahal sudah terjual". Biang keroknya adalah kondisi
`$activeSalesCount === 1` yang sudah diganti `exists()`, jadi seharusnya aman — tapi:

- kerjakan sebagai **commit terpisah**, paling akhir;
- uji khusus: cancel sale → motor `available`; lalu buat sale baru → motor `sold`;
- uji regresi: motor dengan sale `selesai` **tidak boleh** berubah jadi `available`;
- setelah deploy, jalankan audit:
  ```sql
  SELECT v.id, v.license_plate, v.status, s.status AS sale_status
  FROM vehicles v JOIN sales s ON s.vehicle_id = v.id
  WHERE v.status = 'available' AND s.status IN ('proses','kirim','selesai');
  ```
  Hasilnya harus **kosong** untuk motor yang belum di-buyback.

Kalau setelah pengujian kamu menilai risikonya lebih besar dari manfaatnya, **boleh
tidak diterapkan** — tapi catat alasannya di file progres, jangan diam-diam dilewati.

---

### TAHAP 9 — Keputusan soft delete `Vehicle` [BUG-13]

Kolom `vehicles.deleted_at` ada (migrasi `2025_09_01_151858`) tapi model `Vehicle` tidak
memakai trait `SoftDeletes`. Akibatnya motor yang "dihapus" tetap ikut ter-query di dropdown
dan laporan.

Cek dulu apakah ada data dengan `deleted_at IS NOT NULL`:
```sql
SELECT COUNT(*) FROM vehicles WHERE deleted_at IS NOT NULL;
```

- **Ada datanya** → tambahkan `use SoftDeletes;` ke model, lalu audit semua query `Vehicle`
  yang mungkin berubah hasilnya (dropdown, laporan, statistik dashboard).
- **Kosong** → buat migrasi untuk menghapus kolomnya.

Jangan tebak — putuskan berdasarkan data. Kalau DB tidak bisa diakses, tandai sebagai
**BLOKIR** di file progres dan tanyakan ke user.

---

## STANDAR MUTU (production-ready)

Setiap tahap harus memenuhi ini sebelum dianggap selesai:

1. **Tidak ada `dd()`, `dump()`, `ray()`, atau `Log::info` debugging** yang tersisa.
   `CreatePurchase.php` saat ini membuang seluruh isi form ke log termasuk data customer —
   ini harus bersih (lihat TAHAP 2).
2. **Tidak ada kondisi yang disalin-tempel.** Semua cek "penjualan aktif" harus lewat
   `Vehicle::runningSale()` / `LOCKING_SALE_STATUSES`. Setelah selesai, perintah ini tidak
   boleh menemukan sisa pola lama:
   ```bash
   grep -rn "'proses', 'kirim', 'selesai'" app/
   grep -rn "status', '!=', 'cancel'" app/
   ```
   (kecuali di `Sale::syncVehicleStatus()` dan `Sale::scopeValid()`, yang memang benar
   memakai definisi riwayat)
3. **Semua pesan error informatif dan berbahasa Indonesia**, menyebutkan nama customer &
   status yang bentrok, serta memberi tahu apa yang harus dilakukan user.
4. **Migrasi punya `down()` yang benar** dan idempoten (`Schema::hasColumn` guard).
5. **Tidak ada N+1 query baru.** Cek eager-loading pada tabel & summarizer setelah
   perubahan relasi di TAHAP 6.
6. **Konsisten dengan gaya kode yang ada:** komentar bahasa Indonesia, penamaan mengikuti
   file sekitarnya, struktur Filament v4 (`Schemas/`, `Tables/`, `Pages/`).
7. Jalankan sebelum commit terakhir:
   ```bash
   ./vendor/bin/pint --dirty
   php artisan test
   ```
   Kalau `php artisan test` gagal karena alasan environment (bukan karena perubahanmu),
   catat apa adanya di file progres — **jangan dilaporkan sebagai lulus**.

---

## PENGUJIAN (WAJIB LULUS SEMUA)

### Skenario utama

- [ ] Beli motor baru dari supplier → status `available`, muncul di dropdown Penjualan
- [ ] Jual ke customer (`proses`) → status motor `sold`, hilang dari dropdown
- [ ] Naikkan sale ke `kirim` lalu `selesai` → motor tetap `sold`
- [ ] **Buyback:** buat Purchase baru dengan VIN/no. mesin sama → status motor kembali `available`
- [ ] **Motor buyback muncul kembali** di dropdown form Penjualan
- [ ] **Buat Sale #2** untuk motor tersebut → berhasil, tanpa error
- [ ] Ubah status Sale #2 `proses` → `kirim` → `selesai` → berhasil, tidak ada pesan "sudah dijual"
- [ ] **Laba Sale #2 dihitung terhadap Purchase #2**, bukan Purchase #1
- [ ] Sale #1 (lama) tetap tampil utuh di laporan, laba-nya **tidak berubah**

### Skenario proteksi (harus tetap DITOLAK)

- [ ] Motor dengan sale `proses` berjalan → tidak bisa dibuat sale kedua
- [ ] Motor dengan sale `kirim` berjalan → buyback ditolak dengan pesan jelas
- [ ] Motor status `sold` (belum ada buyback) → tidak muncul di dropdown Penjualan
- [ ] Motor status `sold` (belum ada buyback) → tidak bisa dipaksa lewat manipulasi request

### Skenario data

- [ ] Customer lama beli lagi → NIK, alamat, IG, TikTok **tidak hilang**
- [ ] Sale Dana Tunai → `payment_to_customer` tersimpan & muncul kembali saat diedit
- [ ] Cancel satu-satunya sale sebuah motor → motor kembali `available` (setelah TAHAP 8)
- [ ] Export Excel penjualan → angka laba sama dengan yang tampil di tabel

### Cara menguji

Prioritaskan menjalankan aplikasinya sungguhan (`php artisan serve` / docker-compose) dan
menelusuri alurnya lewat UI, karena bug-bug ini muncul di lapisan Filament — bukan di model.
Kalau UI tidak bisa dijalankan, gunakan `php artisan tinker` untuk mensimulasikan alur
create/update dan catat keterbatasannya di file progres.

Buat juga **feature test** minimal untuk siklus buyback di `tests/Feature/` supaya regresi
ini tidak terulang:
```
tests/Feature/VehicleResellCycleTest.php
```
Minimal mencakup: purchase → sale selesai → buyback → sale kedua berhasil, dan sale kedua
ditolak saat sale pertama masih `proses`.

---

## PELAPORAN PROGRES

Buat `notes/fix-030826-progress.md` di awal, **update setiap kali satu tahap selesai**
(jangan ditumpuk di akhir). Format:

```markdown
# Progres Perbaikan Buyback — fix-030826

**Branch:** fix/buyback-sale-cycle
**Mulai:** <tanggal>
**Update terakhir:** <tanggal jam>

## Status Tahap

| Tahap | Judul | Bug | Status | Commit | Catatan |
|---|---|---|---|---|---|
| 0 | Verifikasi database | 11, 12 | ⬜ Belum / 🔄 Jalan / ✅ Selesai / 🚫 Blokir | — | |
| 1 | Helper runningSale() | — | ⬜ | | |
| 2 | Izinkan buyback | 1 | ⬜ | | |
| 3 | Guard create/edit sale | 3, 5, 6 | ⬜ | | |
| 4 | Buka kunci status manual | 4 | ⬜ | | |
| 5 | Kolom payment_to_customer | 7 | ⬜ | | |
| 6 | Perhitungan laba buyback | 8 | ⬜ | | |
| 7 | Data master customer | 9 | ⬜ | | |
| 8 | syncVehicleStatus | 10 | ⬜ | | |
| 9 | Soft delete Vehicle | 13 | ⬜ | | |

## Hasil Verifikasi Database (Tahap 0)
<isi hasil query di sini>

## Hasil Pengujian
<checklist pengujian dengan status aktual — tulis apa adanya, termasuk yang gagal>

## Perubahan Angka Laporan (Tahap 6)
<ringkasan selisih laba sebelum vs sesudah>

## Blokir & Keputusan
<hal yang tidak bisa diselesaikan + alasannya, atau keputusan yang butuh konfirmasi user>
```

---

## ATURAN KERJA

- **Jangan lompat tahap.** Urutannya sengaja: tahap 1-4 saja sudah menuntaskan kedua keluhan
  user; tahap 5-9 perbaikan integritas data yang menyertai.
- **Commit per tahap**, pesan jelas, contoh:
  `fix(purchase): izinkan buyback motor dengan sale selesai [BUG-1]`
- **Jangan push, jangan buat PR, jangan merge ke `main`** tanpa diminta user.
- **Jangan jalankan `migrate:fresh`, `db:wipe`, atau perintah destruktif apa pun** pada
  database yang berisi data. Kalau butuh data uji, buat di database terpisah.
- **Backup database sebelum menjalankan migrasi TAHAP 6** (ada backfill data).
- **Laporkan apa adanya.** Kalau ada tahap yang gagal atau tidak bisa diuji, tulis
  terus terang di file progres beserta alasannya. Jangan tandai ✅ untuk sesuatu yang
  belum benar-benar diverifikasi.
- Kalau menemukan bug **baru** di luar 13 yang terdokumentasi, catat di file progres di
  bagian terpisah — jangan langsung diperbaiki kalau di luar cakupan, kecuali memblokir.
- Kalau ada keputusan yang butuh masukan user (misalnya soft delete di TAHAP 9, atau
  apakah TAHAP 8 layak diterapkan), **tanyakan** — jangan menebak.

## KRITERIA SELESAI

Pekerjaan dianggap tuntas bila:

1. Tahap 0-9 berstatus ✅ atau punya alasan tertulis kenapa tidak (🚫);
2. Seluruh checklist [PENGUJIAN](#pengujian-wajib-lulus-semua) lulus dan tercatat hasil aktualnya;
3. `grep` pada [STANDAR MUTU](#standar-mutu-production-ready) poin 2 tidak menemukan sisa pola lama;
4. `./vendor/bin/pint --dirty` bersih dan `php artisan test` dilaporkan apa adanya;
5. `notes/fix-030826-progress.md` terisi lengkap termasuk perubahan angka laporan dari TAHAP 6;
6. Feature test siklus buyback ada dan lulus;
7. Ringkasan akhir disampaikan ke user: apa yang diperbaiki, apa yang berubah di angka
   laporan, apa yang perlu dijalankan saat deploy (migrasi, audit SQL), dan apa yang masih terbuka.
