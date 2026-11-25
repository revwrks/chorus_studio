<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}
require_once __DIR__ . '/../includes/db.php';

try {
    echo "1. Updating admin table role enum...\n";
    // Check current column definition
    $stmt = $pdo->query("SHOW COLUMNS FROM admin LIKE 'role'");
    $col = $stmt->fetch();
    if ($col && strpos($col['Type'], "'orang_tua'") === false) {
        $pdo->exec("ALTER TABLE admin MODIFY COLUMN role ENUM('owner','admin','coach','orang_tua') NOT NULL DEFAULT 'admin'");
        echo "   Role enum updated to include 'orang_tua'.\n";
    } else {
        echo "   Role enum already includes 'orang_tua'.\n";
    }

    echo "2. Creating orang_tua_murid table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS orang_tua_murid (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_orang_tua INT NOT NULL,
        id_murid INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unik_ortu_murid (id_orang_tua, id_murid),
        FOREIGN KEY (id_orang_tua) REFERENCES admin(id) ON DELETE CASCADE,
        FOREIGN KEY (id_murid) REFERENCES murid(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "   Table orang_tua_murid ready.\n";

    echo "3. Creating iringan_murid table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS iringan_murid (
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
        FOREIGN KEY (id_murid) REFERENCES murid(id) ON DELETE CASCADE,
        FOREIGN KEY (id_lagu) REFERENCES lagu(id_lagu) ON DELETE SET NULL,
        FOREIGN KEY (created_by) REFERENCES admin(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "   Table iringan_murid ready.\n";

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "MIGRATION ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
