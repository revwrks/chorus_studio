-- =====================================================================
-- CHORUS STUDIO - UPDATE DATABASE UNTUK FITUR:
-- Activity Log (Audit Trail Capped at 250 Items)
-- =====================================================================
-- PANDUAN IMPORT KE INFINITYFREE:
-- 1. Buka cPanel akun InfinityFree Anda.
-- 2. Buka menu "phpMyAdmin", lalu klik database Chorus Studio Anda
--    (misal: if0_41396992_chorus_studio).
-- 3. Klik tab "SQL" di bagian atas.
-- 4. Copy-paste isi query di bawah ini, lalu klik tombol "Go / Kirim".
-- =====================================================================

CREATE TABLE IF NOT EXISTS `activity_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `user_nama` VARCHAR(150) DEFAULT NULL,
    `user_role` VARCHAR(50) NOT NULL DEFAULT 'system',
    `action` VARCHAR(50) NOT NULL,
    `tipe` ENUM('info', 'success', 'warning', 'danger') NOT NULL DEFAULT 'info',
    `deskripsi` TEXT NOT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_created_at` (`created_at`),
    KEY `idx_action` (`action`),
    KEY `idx_user_id` (`user_id`),
    CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`) REFERENCES `admin` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
