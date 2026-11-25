<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}
require_once __DIR__ . '/../includes/auth.php';

$pdo = Database::getConnection();

$sql = "CREATE TABLE IF NOT EXISTS `activity_log` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$pdo->exec($sql);
echo "Tabel activity_log berhasil dibuat atau sudah ada!\n";
