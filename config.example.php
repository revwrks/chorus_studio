<?php
/**
 * Chorus Studio - Configuration Template (Example)
 * 
 * Petunjuk:
 * 1. Salin file ini menjadi 'config.php'
 * 2. Masukkan kredensial database lokal & hosting Anda pada file 'config.php'
 * 3. File 'config.php' sudah masuk ke .gitignore sehingga TIDAK AKAN PERNAH ter-upload ke Git/GitHub.
 */

$isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'], true) 
        || in_array(explode(':', $_SERVER['HTTP_HOST'] ?? '')[0], ['localhost', '127.0.0.1'], true)
        || (php_sapi_name() === 'cli' && empty($_SERVER['HTTP_HOST']));

if ($isLocal) {
    return [
        'db_host' => 'localhost',
        'db_name' => 'chorus_studio',
        'db_user' => 'root',
        'db_pass' => '',
    ];
}

// Konfigurasi Server Produksi / Hosting (InfinityFree)
return [
    'db_host' => 'your_hosting_mysql_host',
    'db_name' => 'your_hosting_db_name',
    'db_user' => 'your_hosting_db_user',
    'db_pass' => 'your_hosting_db_password',
];
