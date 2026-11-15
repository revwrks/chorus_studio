# Chorus Studio 🎵

> Sistem Manajemen & Pelaporan Studio Vokal Berbasis Web dengan Desain **Acoustic Luxury 2.0**.

Chorus Studio adalah aplikasi web komprehensif untuk tata kelola operasional studio musik/vokal. Menggabungkan pencatatan progres murid, jadwal pertemuan, evaluasi refleksi berkala, pustaka lagu iringan, dan portal orang tua ke dalam satu antarmuka yang elegan, cepat, dan responsif.

---

## ✨ Fitur Utama

- **Role-Based Access Control (RBAC)**:
  - **Owner**: Akses penuh ke seluruh data, analitik studio, manajemen user, dan log aktivitas studio.
  - **Admin**: Tata kelola murid, lagu iringan, jadwal pertemuan, dan target rencana pertemuan.
  - **Coach**: Akses pengajaran murid, input laporan pertemuan, catatan refleksi, dan evaluasi target.
  - **Orang Tua**: Portal khusus untuk memantau progres vokal anak, riwayat pertemuan, lagu iringan, dan laporan berkala.

- **Rencana Pertemuan & Target Capaian**:
  - Target bulanan per murid dengan progress bar capaian real-time.
  - Multi-filter dock: filter status capaian (tercapai, sedang berjalan, belum mulai), realisasi sesi, coach pembina, dan catatan rencana.
  - Modal breakdown detail realisasi pertemuan langsung dari tabel rencana.

- **Pustaka Lagu & Iringan Audio**:
  - Manajemen katalog repertoar lagu dengan tingkat kesulitan (mudah, sedang, sulit, festival).
  - Tautan audio/video terintegrasi dengan Google Drive dan YouTube.

- **Optimasi Unggah Foto Murid**:
  - Kompresi sisi klien otomatis berbasis HTML5 Canvas (mengubah file foto kamera resolusi tinggi menjadi ~150KB tanpa penurunan kualitas visual) sebelum dikirim ke server.

- **Ekspor Laporan PDF Berkualitas Tinggi**:
  - Cetak laporan evaluasi murid individu maupun batch (bulk PDF) menggunakan engine FPDF kustom dengan tipografi modern **Poppins**.

- **Activity Log & Audit Trail**:
  - Pencatatan otomatis untuk setiap aksi penting di studio dengan mekanisme rolling auto-cleanup maksimal 250 log terbaru untuk menjaga efisiensi database.

---

## 🛠️ Tech Stack

- **Backend**: PHP 8.x (Native, OOP Architecture, PDO Database Driver)
- **Database**: MySQL / MariaDB (Prepared Statements & Transactional Integrity)
- **Frontend**: HTML5, Vanilla JavaScript (AJAX Form, Live Client Search, Modal Engines), CSS3 Modern Variables (Acoustic Luxury Palette: Forest Pine `#23483b` & Champagne Gold `#c5a059`)
- **PDF Engine**: FPDF dengan dukungan font kustom Poppins (Medium & Semi-Bold)
- **Testing**: Native Automated Test Suites (15 test suites end-to-end tanpa dependency eksternal)

---

## 🚀 Panduan Instalasi Lokal

### 1. Prasyarat
- Web Server (Apache via XAMPP, Laragon, atau Valet)
- PHP >= 8.0 dengan ekstensi `pdo_mysql`, `gd`, `fileinfo`
- MySQL / MariaDB

### 2. Setup Database
1. Buat database baru bernama `chorus_studio` di MySQL:
   ```sql
   CREATE DATABASE chorus_studio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. Import skema dan data awal dari direktori `setup/`:
   - Import `setup/setup.sql` atau gunakan `setup/exported_updated_data.sql` untuk basis data lengkap.

### 3. Konfigurasi
Koneksi database telah dikonfigurasi secara otomatis di `includes/classes/Database.php`.
- Secara default, pada lingkungan lokal (`localhost` / `127.0.0.1`), sistem menggunakan:
  - Host: `localhost`
  - Database: `chorus_studio`
  - User: `root`
  - Password: `''` (kosong)
- Anda juga dapat menggunakan Environment Variables:
  - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`

### 4. Menjalankan Aplikasi
1. Tempatkan repositori ini pada direktori root web server (misal: `htdocs/chorus_studio`).
2. Pastikan direktori `assets/uploads/` memiliki izin tulis (writable).
3. Buka browser dan arahkan ke:
   ```
   http://localhost/chorus_studio/login.php
   ```

---

## 🧪 Menjalankan Automated Tests

Seluruh fitur inti dilengkapi test suite otomatis tanpa memerlukan dependency eksternal:

```bash
# Menjalankan test filter rencana pertemuan
php tests/test_filter_rencana_pertemuan.php

# Menjalankan test regression role coach & CRUD
php tests/regression_test.php

# Menjalankan test rencana pertemuan & target
php tests/test_rencana_pertemuan_flow.php

# Menjalankan test portal orang tua
php tests/test_orang_tua_feature.php
php tests/test_http_parent_flow.php

# Menjalankan test activity log
php tests/test_activity_log_flow.php
```

---

## 📂 Struktur Direktori & Arsitektur MVC (OOP)

Aplikasi dibangun dengan arsitektur **Clean MVC (Model-View-Controller)** berbasis Object-Oriented Programming (OOP) yang terstruktur rapi:

```
chorus_studio/
├── src/                    # Source Code Inti Berorientasi Objek (OOP)
│   ├── Core/               # Infrastructure & Base Classes (Database, Auth, BaseController, View, Autoloader)
│   ├── Controllers/        # Page Controllers (Dashboard, Murid, Lagu, Pertemuan, Rencana, Auth, dll.)
│   └── Services/           # Domain Services (MuridService, PertemuanService, ActivityLogService, dll.)
├── views/                  # Template Markup & Tampilan Antarmuka Bersih
│   ├── dashboard/          # View Dashboard Staff & Portal Orang Tua
│   ├── murid/              # View Manajemen Murid & Modals
│   ├── rencana_pertemuan/  # View Target Rencana & Filter Dock
│   ├── pertemuan/          # View Log Sesi Pertemuan & Filter
│   ├── iringan/            # View Katalog Audio Iringan
│   ├── lagu/               # View Master Repertoar Lagu
│   ├── refleksi/           # View Catatan Evaluasi & Filter
│   ├── admin/              # View Manajemen Akun & Hak Akses
│   ├── profil/             # View Pengaturan Profil Mandiri
│   ├── activity_log/       # View Audit Trail Sistem
│   ├── auth/               # View Form Login
│   └── laporan/            # View Unduh Laporan Individual & Bulk ZIP
├── assets/                 # Brand assets, logo, icon, dan folder uploads
│   ├── img/                # Logo & visual brand identity
│   ├── tmp/                # File temporer sesi ekspor PDF
│   └── uploads/            # Foto profil murid (git-ignored)
├── includes/               # Bootstrap global, auth bridge, dan layout header/footer
├── setup/                  # Skema database & script migrasi
├── style/                  # Styling tema Acoustic Luxury 2.0 (main.css)
├── tests/                  # 15 Automated Test Suites
├── [index.php, ...]        # Thin Entry Points (mendelegasikan request langsung ke Controller)
```


---

## 📄 Lisensi

Chorus Studio &copy; Hak Cipta Dilindungi. Dikembangkan untuk efisiensi dan tata kelola profesional studio vokal.
