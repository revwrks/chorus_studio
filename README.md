# Chorus Studio 🎵

> **Vocal Coaching Academy & Performance Management System**  
> Modern, production-grade management portal for music & vocal academies built with **Clean OOP MVC Architecture**, **Vanilla ES6+**, and **Acoustic Luxury 2.0 Design System**.

---

![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Database](https://img.shields.io/badge/MySQL-MariaDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Architecture](https://img.shields.io/badge/Architecture-OOP%20MVC%20%2B%20Services-23483b?style=for-the-badge)
![Tests](https://img.shields.io/badge/Automated%20Tests-15%2F15%20Passing-2ea44f?style=for-the-badge)
![Zero Dependencies](https://img.shields.io/badge/Dependencies-Zero%20External%20Bloat-c5a059?style=for-the-badge)

---

## 📌 Sekilas Project

**Chorus Studio** adalah aplikasi web operasional terpadu yang dirancang khusus untuk studio musik dan akademi vokal profesional. Sistem ini memodernisasi tata kelola studio vokal—menggantikan spreadsheet terpisah dan koordinasi manual—dengan platform web yang elegan, cepat, dan responsif.

Sistem mencakup pencatatan progres murid, pemantauan target sesi bulanan, evaluasi refleksi pedagogis berkala, katalog repertoar lagu dan iringan audio, portal akses mandiri untuk orang tua murid, hingga generator laporan cetak PDF dengan tipografi modern.

---

## 🔑 Demo Login Accounts

Untuk mempermudah eksplorasi lokal, sistem telah dilengkapi dengan data fiktif siap pakai (`setup/demo_seeder.sql`):

| Role | Username | Password | Deskripsi Hak Akses |
| :--- | :--- | :--- | :--- |
| **Owner / Principal** | `admin` | `admin123` | Akses penuh: Analitik studio, manajemen coach & user, audit trail, laporan studio. |
| **Coach (Pengajar)** | `coach_reva` | `coach123` | Akses pengajaran: Catatan pertemuan murid, evaluasi target, refleksi vokal bulanan. |
| **Orang Tua Murid** | `ortu_aria` | `ortu123` | Portal wali murid: Memantau progres latihan anak, riwayat pertemuan, & unduh laporan vokal. |

---

## 🏛️ Arsitektur & Prinsip Rekayasa (Engineering Highlights)

Aplikasi dibangun dari nol mengedepankan **high performance**, **zero framework bloat**, dan kemudahan deployment di berbagai lingkungan hosting:

```
[ HTTP Request ]
       │
       ▼
[ Thin Entry Point ] (e.g. murid.php, index.php)
       │
       ├──► [ PSR-4 Autoloader ] (App\Core, App\Controllers, App\Services)
       ▼
[ Controller Layer ] (Extends BaseController: CSRF, Auth, Validation, JSON)
       │
       ├──► [ Service Layer ] (Domain Business Logic & Complex Workflows)
       │           │
       │           └──► [ Database PDO ] (Prepared Statements, Transactional)
       │
       └──► [ View Engine ] (Modular Layout Injection & XSS Escaping)
                   │
                   ▼
           [ Rendered HTML ]
```

### 1. Clean OOP Model-View-Controller (MVC)
- **Core Infrastructure (`src/Core/`)**: Mengimplementasikan `Autoloader` standar PSR-4, abstract `BaseController` untuk menangani routing, session flash, CSRF protection, dan role validation, serta `View` template engine yang menginjeksi layout secara modular.
- **Service Layer (`src/Services/`)**: Logika bisnis dipisahkan total dari controller ke dalam 8 dedicated domain services (`MuridService`, `PertemuanService`, `RencanaPertemuanService`, dll) untuk testability tinggi dan *Single Responsibility Principle*.
- **Ultra-Thin Entry Points**: File root (`index.php`, `murid.php`, dll) hanya berfungsi sebagai *delegation dispatchers* (3–7 baris kode), menjamin kompatibilitas 100% pada server Apache konvensional tanpa rewrite engine yang kompleks.

### 2. Client-Side Canvas Image Compression
- Formulir upload foto murid dilengkapi engine kompresi otomatis berbasis **HTML5 Canvas API**.
- Foto resolusi tinggi (3MB–10MB dari kamera ponsel) dikompresi langsung di browser pengguna menjadi Web-optimized JPEG berukuran **~150KB** sebelum dikirim via AJAX multipart.
- Menghemat konsumsi bandwidth server, mencegah HTTP timeout, dan mengoptimalkan penyimpanan media.

### 3. Target & Realisasi Multi-Filter Dock
- Halaman perencanaan pertemuan dilengkapi bilik filter dinamis (*Status Capaian*, *Realisasi Pertemuan*, *Coach Pembina*, dan *Catatan Rencana*).
- Filter berjalan instan di sisi klien dengan engine pencarian teks real-time dan badge counter DOM otomatis tanpa reload halaman.

### 4. Custom Vector PDF Engine (Poppins Typography)
- Mengintegrasikan engine **FPDF** yang dimodifikasi khusus untuk mendukung embedding font Google Fonts **Poppins (Medium & SemiBold)**.
- Menghasilkan laporan progres vokal berkualitas tinggi siap cetak, baik per murid maupun ekspor massal (*Bulk PDF generation*).

### 5. Self-Pruning Rolling Activity Log
- Sistem audit trail mencatat seluruh aktivitas sensitif studio (tambah murid, ubah target, login, hapus data).
- Dilengkapi mekanisme *rolling window* otomatis (dibatasi 250 baris terbaru) untuk mencegah pembengkakan ukuran tabel pada database produksi jangka panjang.

---

## 🎨 Visual Design: Acoustic Luxury 2.0

Antarmuka dirancang khusus untuk memancarkan aura musikalitas klasik berpadu dengan kemewahan modern (*Acoustic Luxury*):
- **Forest Pine** (`#23483b`): Warna primer menenangkan yang merepresentasikan ruang studio berperedam kayu akustik.
- **Champagne Gold** (`#c5a059`): Aksen metalik elegan untuk tombol aksi, badge capaian, dan highlight interaktif.
- **Warm Cream & Ivory** (`#fcfaf7`): Latar belakang lembut yang ramah di mata dan nyaman digunakan dalam durasi kerja panjang.
- **Mobile Navigation Dock**: Drawer navigasi adaptif dengan dukungan gestur sentuh untuk smartphone dan tablet.

---

## 🚀 Panduan Menjalankan Secara Lokal (1-Minute Quickstart)

### 1. Clone Repositori
```bash
git clone https://github.com/revwrks/chorus_studio.git
cd chorus_studio
```

### 2. Setup Database & Demo Data
1. Buka MySQL / MariaDB (misal via phpMyAdmin atau CLI), lalu import skema dan data demo:
   ```bash
   mysql -u root -p < setup/demo_seeder.sql
   ```
   *(File ini otomatis membuat database `chorus_studio`, seluruh struktur tabel, dan data fiktif siap pakai).*

### 3. Konfigurasi Environment
Salin file template konfigurasi menjadi `config.php`:
```bash
cp config.example.php config.php
```
*Secara default, `config.php` telah terkonfigurasi untuk MySQL lokal (`localhost`, database: `chorus_studio`, user: `root`, password: kosong).*

### 4. Buka di Browser
Arahkan browser Anda ke:
```text
http://localhost/chorus_studio/login.php
```
Gunakan akun demo:
- **Username:** `admin` | **Password:** `admin123`

---

## 🧪 Automated Testing Suite

Aplikasi diverifikasi melalui **15 automated test suites** independen tanpa memerlukan PHPUnit atau library pihak ketiga:

```bash
# Menjalankan test regression hak akses & CRUD Coach
php tests/regression_test.php

# Menjalankan test flow rencana pertemuan & capaian target
php tests/test_rencana_pertemuan_flow.php

# Menjalankan test live search, AJAX forms, & image compression contract
php tests/test_features_1_2_3.php

# Menjalankan test portal akses orang tua
php tests/test_orang_tua_feature.php
php tests/test_http_parent_flow.php

# Menjalankan test filter dinamis & visual zoom
php tests/test_filter_rencana_pertemuan.php
php tests/test_filter_pertemuan_and_coach_dashboard.php
php tests/test_filtering_and_photo_zoom.php

# Menjalankan test audit trail activity log
php tests/test_activity_log_flow.php
```

---

## 📂 Struktur Direktori Proyek

```text
chorus_studio/
├── config.example.php      # Template konfigurasi environment database aman
├── config.php              # Konfigurasi aktif server (diabaikan oleh Git)
├── src/                    # Source Code OOP MVC
│   ├── Core/               # Autoloader, BaseController, View Engine, Database PDO
│   ├── Controllers/        # 12 Page & API Controllers
│   └── Services/           # 8 Domain Business Services (Separation of Concerns)
├── views/                  # View Template Modular
│   ├── dashboard/          # Tampilan dashboard staff & portal wali murid
│   ├── rencana_pertemuan/  # Manajemen target & filter dock interaktif
│   ├── murid/              # Katalog murid, profil, & modal canvas upload
│   ├── pertemuan/          # Log catatan sesi vokal & riwayat
│   ├── iringan/            # Manajemen audio backing track
│   ├── lagu/               # Repertoar lagu & tingkat kesulitan
│   ├── refleksi/           # Evaluasi bulanan murid
│   ├── admin/              # Tata kelola akun staf & role RBAC
│   ├── activity_log/       # Tampilan audit trail aktivitas studio
│   ├── auth/               # Halaman login
│   ├── profil/             # Form edit profil mandiri
│   └── laporan/            # Preview & unduh laporan evaluasi PDF
├── assets/                 # Aset statis (Logo, brand graphics, placeholder)
├── includes/               # Bootstrap global, auth bridge, dan layout header/footer
├── setup/                  # Skrip database (demo_seeder.sql, skema DDL)
├── style/                  # Styling tema Acoustic Luxury 2.0 (main.css)
├── tests/                  # 15 Automated Test Suites
└── [index.php, ...]        # Thin Entry Points (mendelegasikan request ke Controller)
```

---

## 👤 Author & Credits

**Chorus Studio** dirancang dan dikembangkan dengan penuh perhatian terhadap arsitektur perangkat lunak yang bersih, skalabel, dan pengalaman pengguna yang bernilai tinggi.
