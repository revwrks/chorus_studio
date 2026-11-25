-- Performance indexes for Chorus Studio (MySQL 8 / MariaDB compatible)
-- Run once against an initialized database.

CREATE INDEX `idx_pertemuan_tanggal` ON `pertemuan` (`tanggal`);
CREATE INDEX `idx_pertemuan_murid_tanggal` ON `pertemuan` (`id_murid`, `tanggal`);
CREATE INDEX `idx_pertemuan_coach_tanggal` ON `pertemuan` (`coach_id`, `tanggal`);
CREATE INDEX `idx_murid_status` ON `murid` (`status`);
CREATE INDEX `idx_admin_role` ON `admin` (`role`);
CREATE INDEX `idx_refleksi_tahun_bulan` ON `refleksi` (`tahun`, `bulan`);
