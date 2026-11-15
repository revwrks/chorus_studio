-- =====================================================================
-- CHORUS STUDIO - UPDATE STRUKTUR DATABASE UNTUK INFINITYFREE
-- Fitur: Akses Orang Tua (Parent Portal) & Manajemen Lagu Iringan
-- =====================================================================
-- PETUNJUK IMPORT KE INFINITYFREE:
-- 1. Buka cPanel InfinityFree -> klik phpMyAdmin.
-- 2. Pilih database Anda di kolom sebelah kiri (misal: epiz_xxxxx_chorus_studio).
-- 3. Klik tab "Import" di bagian atas.
-- 4. Pilih file "update_struktur_infinityfree.sql" ini, lalu klik "Go / Kirim".
-- =====================================================================

-- 1. Perbarui kolom role pada tabel admin untuk menambahkan role 'orang_tua'
ALTER TABLE admin MODIFY COLUMN role ENUM('owner','admin','coach','orang_tua') NOT NULL DEFAULT 'admin';

-- 2. Buat tabel relasi orang_tua_murid (menghubungkan akun orang tua dengan anak)
CREATE TABLE IF NOT EXISTS orang_tua_murid (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_orang_tua INT NOT NULL,
    id_murid INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unik_ortu_murid (id_orang_tua, id_murid),
    CONSTRAINT fk_ortu FOREIGN KEY (id_orang_tua) REFERENCES admin(id) ON DELETE CASCADE,
    CONSTRAINT fk_murid FOREIGN KEY (id_murid) REFERENCES murid(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Buat tabel iringan_murid (menyimpan link Google Drive lagu iringan latihan & lomba)
CREATE TABLE IF NOT EXISTS iringan_murid (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_murid INT NOT NULL,
    id_lagu INT DEFAULT NULL,
    judul_iringan VARCHAR(255) NOT NULL,
    kategori ENUM('latihan','lomba','lainnya') NOT NULL DEFAULT 'latihan',
    gdrive_link VARCHAR(500) NOT NULL,
    catatan VARCHAR(255) DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_iringan_murid FOREIGN KEY (id_murid) REFERENCES murid(id) ON DELETE CASCADE,
    CONSTRAINT fk_iringan_lagu FOREIGN KEY (id_lagu) REFERENCES lagu(id_lagu) ON DELETE SET NULL,
    CONSTRAINT fk_iringan_admin FOREIGN KEY (created_by) REFERENCES admin(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tambahkan kolom status pada tabel murid (aktif / non_aktif)
ALTER TABLE murid ADD COLUMN IF NOT EXISTS status ENUM('aktif', 'non_aktif') NOT NULL DEFAULT 'aktif' AFTER foto;

-- 5. Buat tabel rencana_pertemuan (target sesi latihan murid per bulan)
CREATE TABLE IF NOT EXISTS rencana_pertemuan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_murid INT NOT NULL,
    coach_id INT DEFAULT NULL,
    tahun INT NOT NULL,
    bulan INT NOT NULL,
    target_pertemuan INT NOT NULL DEFAULT 4,
    catatan VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unik_murid_periode (id_murid, tahun, bulan),
    KEY idx_rencana_periode (tahun, bulan),
    KEY idx_rencana_coach (coach_id),
    CONSTRAINT fk_rencana_murid FOREIGN KEY (id_murid) REFERENCES murid(id) ON DELETE CASCADE,
    CONSTRAINT fk_rencana_coach FOREIGN KEY (coach_id) REFERENCES admin(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

