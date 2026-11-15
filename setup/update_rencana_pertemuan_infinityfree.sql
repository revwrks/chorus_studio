-- =====================================================================
-- CHORUS STUDIO - UPDATE DATABASE UNTUK FITUR:
-- 1. Status Murid (Aktif / Non-Aktif)
-- 2. Rencana Pertemuan (Target Sesi Murid per Bulan)
-- =====================================================================
-- PANDUAN IMPORT KE INFINITYFREE:
-- 1. Buka cPanel akun InfinityFree Anda.
-- 2. Buka menu "phpMyAdmin", lalu klik database Chorus Studio Anda
--    (misal: if0_41396992_chorus_studio).
-- 3. Klik tab "SQL" atau tab "Import" di bagian atas.
-- 4. Copy-paste isi query di bawah ini, lalu klik tombol "Go / Kirim".
-- =====================================================================

-- 1. Tambahkan kolom 'status' pada tabel murid jika belum ada
ALTER TABLE `murid` 
ADD COLUMN `status` ENUM('aktif', 'non_aktif') NOT NULL DEFAULT 'aktif' AFTER `foto`;

-- 2. Pastikan seluruh data murid yang ada saat ini berstatus 'aktif'
UPDATE `murid` 
SET `status` = 'aktif' 
WHERE `status` IS NULL OR `status` = '';

-- 3. Buat tabel 'rencana_pertemuan' untuk menyimpan target sesi latihan bulanan
CREATE TABLE IF NOT EXISTS `rencana_pertemuan` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_murid` INT NOT NULL,
    `coach_id` INT DEFAULT NULL,
    `tahun` INT NOT NULL,
    `bulan` INT NOT NULL,
    `target_pertemuan` INT NOT NULL DEFAULT 4,
    `catatan` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unik_murid_periode` (`id_murid`, `tahun`, `bulan`),
    KEY `idx_rencana_periode` (`tahun`, `bulan`),
    KEY `idx_rencana_coach` (`coach_id`),
    CONSTRAINT `fk_rencana_murid` FOREIGN KEY (`id_murid`) REFERENCES `murid` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rencana_coach` FOREIGN KEY (`coach_id`) REFERENCES `admin` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
