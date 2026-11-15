<?php
namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $configFile = dirname(__DIR__, 2) . '/config.php';
            $config = file_exists($configFile) ? require $configFile : [];

            $isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'], true) 
                    || in_array(explode(':', $_SERVER['HTTP_HOST'] ?? '')[0], ['localhost', '127.0.0.1'], true)
                    || (php_sapi_name() === 'cli' && empty($_SERVER['HTTP_HOST']));

            if ($isLocal) {
                // Local defaults
                $host = getenv('DB_HOST') ?: ($config['db_host'] ?? 'localhost');
                $db   = getenv('DB_NAME') ?: ($config['db_name'] ?? 'chorus_studio');
                $user = getenv('DB_USER') ?: ($config['db_user'] ?? 'root');
                $pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : ($config['db_pass'] ?? '');
            } else {
                // Hosting configuration from config.php or environment
                $host = getenv('DB_HOST') ?: ($config['db_host'] ?? '');
                $db   = getenv('DB_NAME') ?: ($config['db_name'] ?? '');
                $user = getenv('DB_USER') ?: ($config['db_user'] ?? '');
                $pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : ($config['db_pass'] ?? '');
            }

            $charset = 'utf8mb4';
            $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                error_log("Database Connection Error: " . $e->getMessage());
                die('Terjadi kesalahan pada sistem database: ' . ($isLocal ? $e->getMessage() : 'Gagal terhubung ke database hosting. Pastikan host, nama database, username, dan password di Database configuration sudah diisi dengan benar.'));
            }
        }

        return self::$instance;
    }
}

if (!class_exists('Database', false)) {
    class_alias(Database::class, 'Database');
}
