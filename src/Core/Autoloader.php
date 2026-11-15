<?php
namespace App\Core;

class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register([self::class, 'autoload']);
    }

    public static function autoload(string $class): void
    {
        $baseDir = dirname(__DIR__, 2);

        // 1. PSR-4 for App\ namespace
        if (str_starts_with($class, 'App\\')) {
            $relativeClass = substr($class, 4); // Strip "App\"
            $file = $baseDir . '/src/' . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }

        // 2. Unqualified class names
        $lookupDirs = [
            $baseDir . '/src/Core/' . $class . '.php',
            $baseDir . '/src/Services/' . $class . '.php',
            $baseDir . '/src/Controllers/' . $class . '.php',
            $baseDir . '/includes/classes/' . $class . '.php',
        ];

        foreach ($lookupDirs as $file) {
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
}
