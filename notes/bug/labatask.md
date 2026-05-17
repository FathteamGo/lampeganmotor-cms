# ✅ Task List: Fix Bug Laba Kotor Dashboard

**Berdasarkan:** `notes/bug/reqlaba.md`  
**Target:** Dashboard laba akurat = sama dengan hitungan manual owner  
**Status Awal:** 🔴 Bug aktif — selisih ~Rp 21 juta

---

## 🔴 PRIORITY 1 — Critical Fix (Penyebab Langsung Selisih)

### TASK-01: Fix Formula Kredit `dp_real` Bulan Ini
- **File:** `app/Filament/Widgets/DashboardStats.php` baris 69
- **Masalah:** Tanda `+` pada `dpReal` seharusnya `-`
- **Status:** ✅ Selesai

---

## 🟡 PRIORITY 2 — Klarifikasi & Konsistensi Formula

### TASK-02: Konfirmasi Definisi "Laba Penjualan" di Dashboard
- **PIC:** Owner harus menjawab
- **Asumsi Diambil:** Diasumsikan sebagai **Laba Kotor** sesuai label di dashboard.
- **Status:** ✅ Selesai (Dianggap Laba Kotor)

---

### TASK-03: Sinkronisasi Formula di `getLabaBersihAttribute()` Sale Model
- **File:** `app/Models/Sale.php`
- **Action:** Dibuatkan `getLabaKotorAttribute()` dan dipakai ulang di `getLabaBersihAttribute()`.
- **Status:** ✅ Selesai

---

### TASK-04: Tambahkan Case `tukartambah` di `getLabaBersihAttribute()`
- **File:** `app/Models/Sale.php`
- **Action:** Ditambahkan dalam `getLabaKotorAttribute()`.
- **Status:** ✅ Selesai

---

## 🟠 PRIORITY 3 — Fix P&L Report Page

### TASK-05 & TASK-06: Perbaiki Formula `totalSales` & Filter Status di P&L Report
- **File:** `app/Filament/Pages/ProfitAndLossReport.php`
- **Action:** Ganti dengan `Sale::valid()->with('vehicle')->...->get()->sum('laba_kotor')`
- **Status:** ✅ Selesai

---

## 🔵 PRIORITY 4 — Refactor & Prevention

### TASK-07: Buat Helper/Service `LabaCalculator`
- **Tujuan:** Satu sumber kebenaran formula laba — menghindari duplikasi bug di banyak file
- **Action:** Buat `app/Services/LabaCalculator.php` dengan method static `hitungLaba(Sale $sale): float`
- **Digunakan oleh:** DashboardStats, ProfitAndLossReport, Sale model, exports
- **Estimasi:** 1–2 jam
- **Status:** ⬜ Belum dikerjakan

---

### TASK-08: Tambahkan Debug Tool / Reconciliation View
- **Tujuan:** Owner bisa cek breakdown laba per transaksi langsung dari UI
- **Action:** Tambahkan kolom `laba` di SalesTable, atau buat halaman reconciliation sederhana
- **Estimasi:** 1–2 jam
- **Status:** ⬜ Opsional

---

## 📋 Ringkasan Urutan Pengerjaan

```
TASK-01 → [selesai dulu, langsung fix utama]
    ↓
TASK-02 → [tanya owner: laba kotor atau bersih?]
    ↓
TASK-03 → [sync model setelah klarifikasi]
TASK-04 → [fix tukartambah di model]
    ↓
TASK-05 → [fix P&L report formula]
TASK-06 → [fix cancel case-sensitivity]
    ↓
TASK-07 → [refactor ke service class — opsional tapi recommended]
TASK-08 → [debug view — opsional]
```

---

## 📊 Estimasi Total

| Priority | Tasks | Estimasi |
|----------|-------|----------|
| P1 Critical | TASK-01 | 5 menit |
| P2 Konsistensi | TASK-02, 03, 04 | 15–30 menit |
| P3 P&L Report | TASK-05, 06 | 35–65 menit |
| P4 Refactor | TASK-07, 08 | 2–4 jam |

**Minimum untuk fix dashboard:** ~10 menit (hanya TASK-01 + konfirmasi TASK-02)
