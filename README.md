# Chorus Studio

Vocal Coaching Academy & Performance Management System. Aplikasi web operasional untuk studio musik dan akademi vokal berbasis **PHP 8+ OOP MVC**, **Vanilla ES6+**, dan sistem desain **Warm Acoustic Clarity**.

---

## Sekilas Project

**Chorus Studio** memusatkan operasional studio vokal dalam satu portal web tanpa ketergantungan framework berat:

- **Pencatatan Sesi & Progres Murid**: Log pertemuan per murid, materi lagu, catatan latihan coach, dan status keaktifan murid (`aktif` / `non_aktif`).
- **Rencana Pertemuan & Target Bulanan**: Penetapan target sesi per bulan, pelacakan realisasi otomatis, serta rincian riwayat sesi via modal AJAX.
- **Katalog Lagu & Iringan Audio**: Manajemen repertoar lagu (tingkat kesulitan, referensi YouTube, tautan iringan) dan daftar lagu iringan Google Drive per murid.
- **Refleksi & Laporan PDF**: Evaluasi berkala per murid dengan ekspor PDF berfont Poppins (tunggal maupun bulk ZIP).
- **Portal Orang Tua**: Tampilan khusus wali murid dengan pengalih profil anak (multi-child switcher), pemantauan target bulanan, riwayat pertemuan, iringan audio, dan unduhan laporan anak terkait.
- **Audit Trail**: Activity log dengan mekanisme rolling window otomatis (maksimal 250 baris terbaru).

---

## Demo Login Accounts

Dataset demo tersedia di [`setup/demo_seeder.sql`](setup/demo_seeder.sql):

| Role | Username | Password | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Owner** | `admin` | `admin123` | Akses penuh: dashboard studio, CRUD seluruh data, manajemen akun (termasuk Owner), bulk PDF, dan activity log. |
| **Coach** | `coach_reva` | `coach123` | Akses pengajaran: murid, lagu, iringan, pertemuan, rencana pertemuan (cakupan coach), refleksi, dan laporan tunggal. |
| **Orang Tua** | `ortu_aria` | `ortu123` | Portal wali murid: dashboard progres anak terhubung, lagu iringan anak, unduh PDF anak sendiri, dan edit profil mandiri. |

---

## Arsitektur Sistem

Aplikasi menggunakan pola **Thin Entry Point -> Controller -> Service -> PDO** dengan autoloader PSR-4 (`App\Core`, `App\Controllers`, `App\Services`):

```text
HTTP Request
  ├── Entry Point (index.php, murid.php, ...)
  ├── App\Core\Autoloader (PSR-4)
  ├── App\Controllers\*Controller (extends BaseController: Auth, CSRF, Validation, JSON)
  ├── App\Services\*Service (Domain logic & PDO transactions)
  └── App\Core\View + views/* (Modular HTML rendering & escaping)
```

- **Core (`src/Core/`)**: `Autoloader`, `Database` (PDO singleton), `Auth` (sesi, RBAC, CSRF, security headers), `BaseController`, dan `View`.
- **Controllers (`src/Controllers/`)**: 12 controller untuk autentikasi, dashboard, CRUD operasional, laporan PDF, manajemen akun, profil, dan activity log.
- **Services (`src/Services/`)**: 8 domain service (`AdminService`, `MuridService`, `LaguService`, `IringanService`, `PertemuanService`, `RencanaPertemuanService`, `RefleksiService`, `ActivityLogService`).
- **Frontend (`assets/js/app.js`)**: Engine terpusat untuk theme toggle, drawer & sidebar collapse, toast notification, kompresi gambar Canvas API (`compressImageFile`), lightbox foto murid, form AJAX (`data-ajax-form`), async search/pagination, dan live table filter.

---

## Design System: Warm Acoustic Clarity

Antarmuka didefinisikan di [`style/main.css`](style/main.css) menggunakan token warna semantik Light & Dark mode yang terverifikasi kontras WCAG AA, tipografi `Plus Jakarta Sans`, dan dukungan `prefers-reduced-motion`:

- **Light Theme**: Canvas Warm Cream (`#F6F4EF`), permukaan putih bersih (`#FFFFFF`), primer Forest Pine (`#1F4B3C`), aksen Brass Gold (`#8C6822` / `#B89143`), dan teks arang gelap (`#13221C`).
- **Dark Theme**: Canvas Deep Spruce (`#0D1512`), permukaan `#141F1B`, primer Mint Pine (`#63B995`), aksen Warm Gold (`#DFBA72`), dan teks terang (`#EDF2EF`).
- **Navigasi Adaptif**: Sidebar collapsible untuk desktop serta slide-out drawer dan bottom navigation dock untuk perangkat mobile.

---

## Security & RBAC Hardening

Langkah pengamanan yang diterapkan pada aplikasi:

1. **Session & HTTP Headers (`App\Core\Auth`)**:
   - Cookie sesi menggunakan `httponly`, `samesite=Lax`, dan flag `secure` otomatis saat HTTPS; `session_regenerate_id(true)` dijalankan saat login berhasil.
   - Header keamanan aktif di setiap respons: `Content-Security-Policy`, `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, dan `Permissions-Policy`.
2. **CSRF Protection & Safe Logout**:
   - Seluruh aksi mutasi `POST` (termasuk login, CRUD, `logout.php`, dan endpoint bulk PDF `init`/`generate`/`zip`/`cleanup`) memverifikasi token CSRF (`hash_equals`).
   - Permintaan `GET` ke `logout.php` tidak menghancurkan sesi; logout hanya diproses melalui `POST` dengan token CSRF valid.
3. **Fail-Closed RBAC & Anti-IDOR**:
   - `Auth::currentUserRole()` mengembalikan string kosong `''` bila sesi role belum terset.
   - `AdminService` mencegah pengguna non-`owner` (termasuk `admin`) membuat, mempromosikan, mengedit, atau menghapus akun `owner`.
   - `RencanaPertemuanController` memaksa `coach_id` memakai ID coach yang sedang login saat role `coach` menyimpan target (`save_single` / `save_batch`).
   - Endpoint `index.php?action=get_realisasi_detail` dan `generate_pdf.php` memverifikasi kepemilikan anak untuk role `orang_tua` (mengembalikan HTTP `403` jika mengakses murid lain).
4. **Rate Limiting & Safe Error Pages**:
   - Pembatasan percobaan login gagal berbasis file di `tmp/ratelimit` (5 kali gagal -> blokir 5 menit).
   - `error.php` hanya menampilkan pesan tetap berdasarkan whitelist kode HTTP (`400`, `401`, `403`, `404`, `405`, `500`) dan mengabaikan parameter teks bebas dari query string.
5. **Proteksi Akses Langsung (`.htaccess`)**:
   - Menolak akses HTTP langsung (`403`) ke direktori internal (`/src/`, `/views/`, `/includes/`, `/setup/`, `/tests/`, `/tmp/`) serta file sensitif (`config.php`, `config.example.php`, `README.md`, `*.sql`, `*.bak`, `*.log`, `*.md`).

---

## Panduan Menjalankan Secara Lokal & Deployment Checklist

### 1. Clone & Konfigurasi
```bash
git clone https://github.com/revwrks/chorus_studio.git
cd chorus_studio
cp config.example.php config.php
```
Sesuaikan host, nama database, user, dan password pada `config.php`.

### 2. Inisialisasi Database & Index Performa
Untuk instalasi demo lengkap:
```bash
mysql -u root -p < setup/demo_seeder.sql
mysql -u root -p chorus_studio < setup/migration_indexes.sql
```
Untuk instalasi kosong tanpa data demo, gunakan `setup/setup.sql` lalu jalankan `setup/migration_indexes.sql`.

### 3. Deployment Checklist (Apache / Shared Hosting)
- Pastikan `AllowOverride All` (atau modul `mod_alias` & `mod_authz_core`) aktif agar aturan proteksi di [`.htaccess`](.htaccess) berjalan (`GET /config.php`, `/src/Core/Database.php`, `/setup/demo_seeder.sql` harus mengembalikan HTTP `403`).
- Pastikan file konfigurasi `config.php` tidak ikut ter-commit ke repositori publik.
- Pastikan direktori `assets/uploads/`, `assets/tmp/`, dan `tmp/ratelimit/` memiliki izin tulis bagi proses PHP/web server.
- Jalankan [`setup/migration_indexes.sql`](setup/migration_indexes.sql) sekali pada database target untuk menambahkan index pada kolom `pertemuan`, `murid`, `admin`, dan `refleksi`.
- Hapus atau ganti password seluruh akun demo sebelum digunakan di lingkungan produksi.

---

## Automated Testing Suite

Terdapat **16 script pengujian** mandiri di direktori `tests/` (dijalankan langsung via PHP CLI tanpa PHPUnit):

```bash
php tests/test_security_hardening.php
php tests/regression_test.php
php tests/test_rencana_pertemuan_service.php
php tests/test_rencana_pertemuan_flow.php
php tests/test_realisasi_modal.php
php tests/test_orang_tua_feature.php
php tests/test_http_parent_flow.php
php tests/test_features_1_2_3.php
php tests/test_filtering_and_photo_zoom.php
php tests/test_filter_rencana_pertemuan.php
php tests/test_filter_pertemuan_and_coach_dashboard.php
php tests/test_revisi_table_and_search_all.php
php tests/test_activity_log_flow.php
php tests/test_latest_updates.php
php tests/test_navbar_contrast.php
php tests/test_verify_exported_sql.php
```

---

## Struktur Direktori

```text
chorus_studio/
├── .htaccess               # Aturan proteksi direktori internal, file sensitif, & caching
├── config.example.php      # Template konfigurasi database
├── config.php              # Konfigurasi aktif server (diabaikan Git)
├── assets/
│   ├── img/                # Logo & aset gambar
│   ├── js/app.js           # JavaScript terpusat (theme, sidebar, AJAX, filter, zoom, toast)
│   └── uploads/            # Foto murid hasil upload
├── includes/
│   ├── auth.php            # Bootstrap autoloader, session init, & helper wrapper
│   ├── db.php              # Koneksi PDO wrapper
│   ├── header.php          # Top navbar & sidebar layout
│   ├── footer.php          # Mobile bottom dock, modal global, & script app.js
│   ├── report_pdf.php      # Generator PDF per murid (FPDF + Poppins)
│   └── lib/                # Library pihak ketiga (FPDF, PclZip)
├── src/
│   ├── Core/               # Autoloader, Auth, BaseController, Database, View
│   ├── Controllers/        # 12 Controllers
│   └── Services/           # 8 Domain Services
├── views/                  # Template tampilan per fitur (dashboard, murid, pertemuan, dll.)
├── style/main.css          # Warm Acoustic Clarity design system (Light & Dark)
├── setup/                  # Skema SQL, demo_seeder.sql, dan skrip migrasi/index
├── tests/                  # 16 Automated test scripts
└── [index.php, ...]        # Thin entry points (termasuk error.php, logout.php, generate_bulk_pdf.php)
```
