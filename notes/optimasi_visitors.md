# 🚀 Panduan Optimasi & Pembersihan Tabel Visitors (Tahun 2025)

Tabel `visitors` pada database aplikasi Anda telah membengkak (bloated). Penghapusan jutaan baris data secara langsung menggunakan query tunggal (`DELETE FROM ...`) **sangat berbahaya** di production karena:
1. **Database Lock**: Mengunci tabel dalam waktu lama, sehingga website tidak bisa diakses (downtime).
2. **Transaction Log Bloat**: Membebani Undo Log / Redo Log MySQL, yang bisa mengakibatkan crash atau server kehabisan memori.
3. **OS Storage Fragmentation**: Pada engine InnoDB, perintah `DELETE` **tidak mengembalikan ruang disk ke OS** melainkan hanya menandainya sebagai "kosong" untuk ditulis ulang. File fisik database (`.ibd`) tetap berukuran besar.

Berikut adalah dua metode aman dan efisien untuk **memutihkan (membersihkan) data visitor tahun 2025** sekaligus mengoptimalkan ruang penyimpanan disk secara maksimal.

---

## 🛠️ Metode 1: Menggunakan Laravel Artisan Command (Sangat Direkomendasikan)

Kami telah membuat Artisan Command premium khusus yang aman, memiliki indikator progress, fitur throttling (jeda tidur antar chunk), dan backup otomatis ke tabel arsip.

### 1. Jalankan Simulasi (Dry Run)
Sebelum mengeksekusi penghapusan nyata, Anda dapat melihat statistik jumlah data tahun 2025 beserta estimasi ukuran penyimpanan tabel saat ini:
```bash
php artisan visitors:optimize --year=2025 --dry-run
```

### 2. Eksekusi Bersih (Penghapusan Langsung + Optimasi Space)
Untuk langsung menghapus data secara bertahap (batch size 5,000 baris dengan jeda 50ms antar batch agar server tetap stabil) kemudian mengklaim kembali ruang disk OS:
```bash
php artisan visitors:optimize --year=2025
```

### 3. Eksekusi dengan Backup Otomatis (Archive)
Jika Anda masih memerlukan data tahun 2025 untuk keperluan audit atau pelaporan statistik di masa depan, gunakan flag `--archive`. Command akan otomatis membuat tabel `visitors_archive_2025` dan menyalin seluruh data ke sana sebelum menghapusnya secara bertahap dari tabel utama `visitors`:
```bash
php artisan visitors:optimize --year=2025 --archive
```

### Parameter Tambahan yang Dapat Disesuaikan:
* `--chunk=10000`: Mengubah jumlah baris per batch penghapusan (default: `5000`).
* `--sleep=100`: Menambah jeda istirahat antar batch dalam milidetik (default: `50ms`) untuk mengurangi beban I/O CPU pada database yang sangat sibuk.

---

## 🛢️ Metode 2: Menggunakan Raw SQL (Eksekusi Langsung di phpMyAdmin / DBeaver)

Jika Anda ingin menjalankan pembersihan langsung melalui SQL Client tanpa melalui PHP, gunakan script terstruktur di bawah ini.

### Langkah Awal: Backup / Salin Data ke Tabel Arsip (Opsional)
Jalankan query ini terlebih dahulu jika ingin mengamankan data tahun 2025 ke tabel khusus:

```sql
-- 1. Buat tabel backup dengan struktur yang identik
CREATE TABLE IF NOT EXISTS `visitors_archive_2025` LIKE `visitors`;

-- 2. Salin data tahun 2025 secara efisien
INSERT IGNORE INTO `visitors_archive_2025`
SELECT * FROM `visitors`
WHERE `visited_at` BETWEEN '2025-01-01 00:00:00' AND '2025-12-31 23:59:59';
```

### Langkah Kedua: Hapus Data Secara Bertahap (Safe Chunked Delete)
Gunakan MySQL Stored Procedure agar penghapusan dilakukan secara berkala (5.000 baris per iterasi) dengan jeda waktu (`DO SLEEP`), sehingga website Anda **tidak mengalami lag atau lock-up**:

```sql
DELIMITER $$

DROP PROCEDURE IF EXISTS PurgeVisitors2025$$

CREATE PROCEDURE PurgeVisitors2025()
BEGIN
    DECLARE deleted_rows INT DEFAULT 1;
    DECLARE total_deleted INT DEFAULT 0;
    
    -- Mulai perulangan penghapusan
    WHILE deleted_rows > 0 DO
        -- Hapus dalam batch kecil menggunakan LIMIT
        DELETE FROM `visitors`
        WHERE `visited_at` BETWEEN '2025-01-01 00:00:00' AND '2025-12-31 23:59:59'
        LIMIT 5000;
        
        SET deleted_rows = ROW_COUNT();
        SET total_deleted = total_deleted + deleted_rows;
        
        -- Jeda 50ms (0.05 detik) untuk memberi nafas bagi CPU database & query website lain
        IF deleted_rows > 0 THEN
            DO SLEEP(0.05);
        END IF;
    END WHILE;
    
    -- Tampilkan ringkasan hasil
    SELECT CONCAT('Sukses! Total baris data 2025 yang berhasil dihapus: ', FORMAT(total_deleted, 0)) AS Status;
END$$

DELIMITER ;

-- Jalankan procedure pembersihan
CALL PurgeVisitors2025();

-- Bersihkan procedure dari database setelah selesai digunakan
DROP PROCEDURE IF EXISTS PurgeVisitors2025;
```

---

## ⚡ Langkah Terakhir: Rekonstruksi & Klaim Ruang Penyimpanan Disk (Disk Reclaim)

Setelah baris data berhasil dihapus dengan **Metode 1** atau **Metode 2**, harddisk Anda **belum akan bertambah lega** karena fragmentasi file InnoDB. 

Untuk memaksa MySQL membangun ulang file tabel, mengklaim space kosong dari OS, serta memperbarui index statistik query planner, jalankan kedua perintah SQL ini:

```sql
-- 1. Mengurangi ukuran file fisik .ibd dan merebut kembali space disk dari OS
OPTIMIZE TABLE `visitors`;

-- 2. Memperbarui statistik indeks untuk kinerja query yang optimal di masa mendatang
ANALYZE TABLE `visitors`;
```

> [!WARNING]
> **Penting untuk Diperhatikan:**
> Proses `OPTIMIZE TABLE` akan melakukan Pembuatan Ulang Tabel (*Table Rebuild*). Pada MySQL 5.6+ (Engine InnoDB), proses ini berjalan secara **Online DDL** (non-blocking), namun tetap memakan resource I/O Storage yang tinggi. 
>
> **Disarankan untuk mengeksekusi optimasi ini pada jam-jam sepi pengunjung (misal: pukul 01:00 - 04:00 dini hari)** demi kenyamanan operasional aplikasi.

---

## 📊 Cara Memantau Ukuran Tabel Sebelum & Sesudah
Anda bisa mengecek ukuran penyimpanan fisik tabel `visitors` di database secara real-time dengan query berikut:

```sql
SELECT 
    TABLE_NAME AS `Tabel`,
    ROUND(DATA_LENGTH / 1024 / 1024, 2) AS `Ukuran Data (MB)`,
    ROUND(INDEX_LENGTH / 1024 / 1024, 2) AS `Ukuran Indeks (MB)`,
    ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS `Total Ukuran Fisik (MB)`,
    ROUND(DATA_FREE / 1024 / 1024, 2) AS `Ruang Kosong/Terfragmentasi (MB)`
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'visitors';
```
Setelah menjalankan `OPTIMIZE TABLE`, kolom `Ruang Kosong/Terfragmentasi (MB)` akan berkurang drastis mendekati `0` dan `Total Ukuran Fisik (MB)` akan menyusut sesuai data aktual yang tersisa.
