# 📋 Requirements: Bug Laba Kotor Dashboard Tidak Akurat

**Tanggal Laporan:** 2026-05-17  
**Reporter:** Owner  
**Severity:** High — data finansial salah tampil  
**File Utama:** `app/Filament/Widgets/DashboardStats.php`, `app/Models/Sale.php`

---

## 🔍 Gejala (Symptom)

| Sumber | Nilai |
|--------|-------|
| Dashboard "Laba Penjualan Bulan Ini" | **Rp 76.870.000** |
| Hitungan manual (kalkulator owner) | **~Rp 55.713.xxx** |
| Selisih | **~Rp 21.157.xxx** |

---

## 🐛 Root Cause Analysis

### Bug #1 — Formula Kredit Inkonsisten antara Bulan Ini vs Tahun Ini

**File:** `DashboardStats.php` — baris 69 vs 106

```php
// ❌ BULAN INI (baris 69) — SALAH: tanda + pada dpReal
$labaPenjualanBulanIni += ($otr - $dpPo + $dpReal - $purchasePrice);
//                                       ^^^ PLUS (salah!)

// ✅ TAHUN INI (baris 106) — BENAR: tanda - pada dpReal
$labaPenjualanTahunIni += ($otr - $dpPo - $dpReal - $purchasePrice);
//                                       ^^^ MINUS (benar)
```

**Dampak:** Untuk setiap transaksi kredit bulan ini, `dp_real` **dijumlahkan** alih-alih dikurangkan. Jika total `dp_real` kredit bulan ini misalnya Rp 10.578.500, maka laba akan **lebih besar 2× dp_real** = selisih ~Rp 21 juta.

---

### Bug #2 — `getLabaBersihAttribute()` Tidak Konsisten dengan Widget

**File:** `Sale.php` — baris 80–118

Model `Sale` punya `laba_bersih` attribute yang **mengurangi cmo_fee + direct_commission**, tapi `DashboardStats.php` **tidak mengurangi biaya-biaya tersebut** dari `$labaPenjualanBulanIni`.

```php
// Model Sale.php (baris 116) — mengurangi biaya:
$laba -= ($cmo + $komisi);

// DashboardStats.php — TIDAK mengurangi biaya apapun!
// Hanya: OTR - DP_PO ± DP_REAL - purchase_price
```

**Dampak:** Laba di dashboard lebih besar dari laba bersih sesungguhnya karena biaya komisi CMO & direct commission tidak dihitung.

> ⚠️ **Perlu konfirmasi owner:** Apakah "Laba Penjualan" di dashboard dimaksudkan sebagai **laba kotor** (belum dikurangi komisi) atau **laba bersih** (sudah dikurangi cmo_fee + direct_commission)?

---

### Bug #3 — `tukartambah` Tidak Ditangani di `getLabaBersihAttribute()`

**File:** `Sale.php` — baris 92–113

`DashboardStats.php` memiliki `case 'tukartambah'`, tapi `getLabaBersihAttribute()` di model **tidak punya** case tersebut, sehingga laba tukar tambah selalu 0 di report P&L.

```php
// DashboardStats.php — ADA case tukartambah:
case 'tukartambah':
    $labaPenjualanBulanIni += ($otr - $purchasePrice);
    break;

// Sale.php getLabaBersihAttribute() — TIDAK ADA case tukartambah:
// default: $laba tetap 0
```

---

### Bug #4 — `ProfitAndLossReport.php` Menggunakan Formula Berbeda (totalSales = sum(sale_price))

**File:** `ProfitAndLossReport.php` — baris 99–102

```php
// P&L Report: pakai sum(sale_price) bukan laba
$this->totalSales = (float) Sale::query()
    ->where('status', '!=', 'CANCEL')
    ->whereBetween('sale_date', $range)
    ->sum('sale_price');  // ← INI ADALAH OTR TOTAL, BUKAN LABA!
```

Laporan P&L menjumlahkan **harga jual OTR** bukan **laba kotor**. Ini menyebabkan angka P&L jauh lebih besar dari kenyataan. Harga beli kendaraan tidak dikurangkan sama sekali.

---

### Bug #5 — Status Cancel Case-Sensitive

**File:** `ProfitAndLossReport.php` baris 100 vs `Sale.php` baris 50

```php
// DashboardStats.php pakai scopeValid → status != 'cancel' (lowercase)
Sale::valid()  // → status != 'cancel'

// ProfitAndLossReport.php langsung query → status != 'CANCEL' (UPPERCASE)
->where('status', '!=', 'CANCEL')
```

Jika database menyimpan status `'cancel'` (lowercase), maka filter di P&L Report **tidak akan menyaring transaksi cancel** dengan benar tergantung collation database.

---

## 📐 Formula Laba Kotor yang Benar (Referensi)

| Payment Method | Formula Laba Kotor |
|----------------|-------------------|
| `cash` | `OTR - harga_beli` |
| `cash_tempo` | `OTR - harga_beli` |
| `credit` | `OTR - dp_po - dp_real - harga_beli` |
| `dana_tunai` | `OTR - dp_po - payment_to_customer` |
| `tukartambah` | `OTR - harga_beli` |

**Laba Bersih** = Laba Kotor − cmo_fee − direct_commission

---

## 📂 File yang Terdampak

| File | Bug # |
|------|-------|
| `app/Filament/Widgets/DashboardStats.php` | #1, #2, #3 |
| `app/Models/Sale.php` | #2, #3 |
| `app/Filament/Pages/ProfitAndLossReport.php` | #4, #5 |

---

## ✅ Kriteria Selesai (Acceptance Criteria)

1. Nilai "Laba Penjualan Bulan Ini" di dashboard harus sama dengan hasil hitungan manual owner
2. Formula kredit untuk bulan ini dan tahun ini harus identik
3. Semua payment method (termasuk `tukartambah`) harus terhitung konsisten di semua lokasi
4. Laporan P&L harus menampilkan **laba kotor** (bukan OTR mentah)
5. Filter status cancel harus konsisten (lowercase `'cancel'`) di semua query
