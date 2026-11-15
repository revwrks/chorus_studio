<?php
require_once __DIR__ . '/../includes/db.php';

try {
    echo "1. Checking column 'status' on murid table...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM murid LIKE 'status'");
    $col = $stmt->fetch();
    if (!$col) {
        $pdo->exec("ALTER TABLE murid ADD COLUMN status ENUM('aktif', 'non_aktif') NOT NULL DEFAULT 'aktif' AFTER foto");
        echo "   Column 'status' added to table murid (default 'aktif').\n";
    } else {
        echo "   Column 'status' already exists on table murid.\n";
    }

    // Ensure all existing murid have status 'aktif' if empty/null
    $pdo->exec("UPDATE murid SET status = 'aktif' WHERE status IS NULL OR status = ''");

    echo "2. Checking table 'rencana_pertemuan'...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS rencana_pertemuan (
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
        FOREIGN KEY (id_murid) REFERENCES murid(id) ON DELETE CASCADE,
        FOREIGN KEY (coach_id) REFERENCES admin(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "   Table 'rencana_pertemuan' is ready.\n";

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "MIGRATION ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
