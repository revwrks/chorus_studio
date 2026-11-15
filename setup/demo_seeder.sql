-- =====================================================================
-- CHORUS STUDIO - DEMO SEEDER (SHOWCASE READY)
-- Fictional dataset for vocal coaching academy portfolio demonstration
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `chorus_studio` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `chorus_studio`;

-- 1. Table structure: admin (Users & RBAC)
CREATE TABLE IF NOT EXISTS `admin` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `nama` VARCHAR(150) DEFAULT NULL,
    `role` ENUM('owner','admin','coach','orang_tua') NOT NULL DEFAULT 'admin',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Table structure: murid
CREATE TABLE IF NOT EXISTS `murid` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(200) NOT NULL,
    `instagram` VARCHAR(150) DEFAULT NULL,
    `foto` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('aktif', 'non_aktif') NOT NULL DEFAULT 'aktif',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Table structure: lagu
CREATE TABLE IF NOT EXISTS `lagu` (
    `id_lagu` INT AUTO_INCREMENT PRIMARY KEY,
    `judul` VARCHAR(255) NOT NULL,
    `youtube_link` VARCHAR(255) DEFAULT NULL,
    `iringan_link` VARCHAR(255) DEFAULT NULL,
    `tingkat` ENUM('mudah','sedang','sulit','festival') DEFAULT 'mudah'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Table structure: pertemuan
CREATE TABLE IF NOT EXISTS `pertemuan` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_murid` INT NOT NULL,
    `id_lagu` INT DEFAULT NULL,
    `coach_id` INT DEFAULT NULL,
    `tanggal` DATE NOT NULL,
    `catatan` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`id_murid`) REFERENCES `murid`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`id_lagu`) REFERENCES `lagu`(`id_lagu`) ON DELETE SET NULL,
    FOREIGN KEY (`coach_id`) REFERENCES `admin`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Table structure: refleksi
CREATE TABLE IF NOT EXISTS `refleksi` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_murid` INT NOT NULL,
    `tahun` INT NOT NULL,
    `bulan` INT NOT NULL,
    `refleksi` TEXT,
    `rencana_perbaikan` TEXT,
    `saran` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unik_murid_bulan` (`id_murid`,`tahun`,`bulan`),
    FOREIGN KEY (`id_murid`) REFERENCES `murid`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Table structure: orang_tua_murid
CREATE TABLE IF NOT EXISTS `orang_tua_murid` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_orang_tua` INT NOT NULL,
    `id_murid` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unik_ortu_murid` (`id_orang_tua`, `id_murid`),
    FOREIGN KEY (`id_orang_tua`) REFERENCES `admin`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`id_murid`) REFERENCES `murid`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Table structure: iringan_murid
CREATE TABLE IF NOT EXISTS `iringan_murid` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_murid` INT NOT NULL,
    `id_lagu` INT DEFAULT NULL,
    `judul_iringan` VARCHAR(255) NOT NULL,
    `kategori` ENUM('latihan','lomba','lainnya') NOT NULL DEFAULT 'latihan',
    `gdrive_link` VARCHAR(500) NOT NULL,
    `catatan` VARCHAR(255) DEFAULT NULL,
    `created_by` INT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`id_murid`) REFERENCES `murid`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`id_lagu`) REFERENCES `lagu`(`id_lagu`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `admin`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Table structure: rencana_pertemuan
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
    FOREIGN KEY (`id_murid`) REFERENCES `murid`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`coach_id`) REFERENCES `admin`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Table structure: activity_log
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
    FOREIGN KEY (`user_id`) REFERENCES `admin`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- POPULATE DEMO DATA (FICTIONAL)
-- =====================================================================

-- Users (Passwords: admin123, coach123, ortu123)
INSERT INTO `admin` (`id`, `username`, `password`, `nama`, `role`) VALUES
(1, 'admin', '$2y$10$5bT1hk51a.enp6LWTwGE2OjXnBY.C9do7Ed7bn6ABTC/Kxwcgx2TG', 'Studio Principal (Owner)', 'owner'),
(2, 'coach_reva', '$2y$10$P/8vmCCxjGFHCpnPdzVnb.pkGXro0ZuXcwz3ZVzSBsHwF/MDUPnni', 'Coach Reva Octaviani', 'coach'),
(3, 'ortu_aria', '$2y$10$Goo0GfbwYOg2hzh1alvQousd5SHrtCgV3QETTwr7r5aCp7VRGxe0u', 'Bapak Handoko (Wali Aria)', 'orang_tua')
ON DUPLICATE KEY UPDATE `nama` = VALUES(`nama`);

-- Fictional Students
INSERT INTO `murid` (`id`, `nama`, `instagram`, `status`) VALUES
(1, 'Aria Pratama', 'ariapratama_sing', 'aktif'),
(2, 'Clarissa Bella', 'clarissabellamusic', 'aktif'),
(3, 'Devon Mahendra', 'devon_vocal', 'aktif'),
(4, 'Evelyn Wijaya', 'evelyn.singing', 'aktif'),
(5, 'Fabian Nathan', 'fabian_nathan', 'aktif'),
(6, 'Giselle Angelica', 'giselle_vocalist', 'non_aktif')
ON DUPLICATE KEY UPDATE `nama` = VALUES(`nama`);

-- Connect Parent to Aria Pratama
INSERT INTO `orang_tua_murid` (`id_orang_tua`, `id_murid`) VALUES
(3, 1)
ON DUPLICATE KEY UPDATE `id_murid` = VALUES(`id_murid`);

-- Song Repertoire
INSERT INTO `lagu` (`id_lagu`, `judul`, `youtube_link`, `iringan_link`, `tingkat`) VALUES
(1, 'Never Enough - Loren Allred', 'https://youtu.be/fKEMA242JXA', 'https://drive.google.com/drive/folders/demo', 'sulit'),
(2, 'Fly Me to the Moon - Frank Sinatra', 'https://youtu.be/5hxib86Un60', 'https://drive.google.com/drive/folders/demo', 'sedang'),
(3, 'Can\'t Help Falling in Love - Elvis Presley', 'https://youtu.be/vGJTaP6anOU', 'https://drive.google.com/drive/folders/demo', 'mudah'),
(4, 'Bohemian Rhapsody - Queen', 'https://youtu.be/fJ9rUzIMcZQ', 'https://drive.google.com/drive/folders/demo', 'festival'),
(5, 'Dan... - Sheila on 7', 'https://youtu.be/cK4p_N2qN50', 'https://drive.google.com/drive/folders/demo', 'mudah')
ON DUPLICATE KEY UPDATE `judul` = VALUES(`judul`);

-- Sample Backing Track
INSERT INTO `iringan_murid` (`id_murid`, `id_lagu`, `judul_iringan`, `kategori`, `gdrive_link`, `catatan`, `created_by`) VALUES
(1, 1, 'Never Enough - Piano Backing Track (Key +1)', 'latihan', 'https://drive.google.com/drive/folders/demo', 'Transpose naik 1 semitone untuk range vokal Aria', 2),
(2, 2, 'Fly Me to the Moon - Acoustic Jazz Trio (Key Normal)', 'lomba', 'https://drive.google.com/drive/folders/demo', 'Versi aransemen jazz swing untuk persiapan festival', 2)
ON DUPLICATE KEY UPDATE `judul_iringan` = VALUES(`judul_iringan`);

-- Sample Sessions (Pertemuan)
INSERT INTO `pertemuan` (`id_murid`, `id_lagu`, `coach_id`, `tanggal`, `catatan`) VALUES
(1, 1, 2, '2026-11-03', 'Eksplorasi phrasing verse 1. Fokus pernapasan diafragma dan penempatan resonansi rongga hidung (mask resonance).'),
(1, 1, 2, '2026-11-07', 'Pemanasan arpeggio legato. Mengatasi transisi register chest ke mixed voice di nada F#4. Progres artikulasi sangat bagus.'),
(1, 1, 2, '2026-11-10', 'Simulasi bernyanyi dengan backing track. Pitch sudah terkontrol stabil pada dinamika forte di bagian chorus.'),
(2, 2, 2, '2026-11-04', 'Latihan swing feel dan syncopation. Intonasi nada stabil, vibrato natural pada akhir phrase.'),
(2, 2, 2, '2026-11-08', 'Latihan dinamika mezza voce untuk interpretasi jazz yang lebih santai.'),
(3, 3, 2, '2026-11-05', 'Fokus tone balance dan relaksasi rahang saat menyanyikan nada rendah.');

-- Monthly Plan & Targets (Rencana Pertemuan - Tahun 2026 Bulan 11)
INSERT INTO `rencana_pertemuan` (`id_murid`, `coach_id`, `tahun`, `bulan`, `target_pertemuan`, `catatan`) VALUES
(1, 2, 2026, 11, 4, 'Persiapan seleksi vokal kompetisi regional'),
(2, 2, 2026, 11, 4, 'Pendalaman teknik vokal genre jazz'),
(3, 2, 2026, 11, 4, 'Pematangan pernapasan diafragma dan artikulasi'),
(4, 2, 2026, 11, 2, 'Latihan pengenalan dasar vokal pop'),
(5, 2, 2026, 11, 4, 'Program intensif persiapan konser studio akhir tahun')
ON DUPLICATE KEY UPDATE `target_pertemuan` = VALUES(`target_pertemuan`);

-- Sample Monthly Reflection
INSERT INTO `refleksi` (`id_murid`, `tahun`, `bulan`, `refleksi`, `rencana_perbaikan`, `saran`) VALUES
(1, 2026, 11, 
'Aria menunjukkan kemajuan signifikan dalam penguasaan teknik mixed voice dan kontrol dinamika nada tinggi pada lagu Never Enough.',
'Fokus pada konsistensi penopang napas (breath support) agar nada tinggi forte tidak tegang pada area tenggorokan.',
'Latihan vocal fry dan lip trill rutin selama 10 menit setiap pagi untuk relaksasi pita suara.')
ON DUPLICATE KEY UPDATE `refleksi` = VALUES(`refleksi`);

-- Sample Activity Logs
INSERT INTO `activity_log` (`user_id`, `user_nama`, `user_role`, `action`, `tipe`, `deskripsi`) VALUES
(1, 'Studio Principal (Owner)', 'owner', 'SYSTEM_INITIALIZE', 'info', 'Inisialisasi database Chorus Studio demo showcase.'),
(2, 'Coach Reva Octaviani', 'coach', 'TAMBAH_PERTEMUAN', 'success', 'Menambahkan catatan pertemuan untuk murid Aria Pratama.'),
(2, 'Coach Reva Octaviani', 'coach', 'UPDATE_RENCANA', 'info', 'Mengatur target pertemuan periode November 2026 untuk 5 murid aktif.');
