<?php
namespace App\Core;

class View
{
    private static string $viewsPath = '';

    public static function setViewsPath(string $path): void
    {
        self::$viewsPath = rtrim($path, '/\\');
    }

    public static function getViewsPath(): string
    {
        if (empty(self::$viewsPath)) {
            self::$viewsPath = dirname(__DIR__, 2) . '/views';
        }
        return self::$viewsPath;
    }

    public static function render(string $template, array $data = [], bool $withLayout = true): void
    {
        $baseDir = dirname(__DIR__, 2);
        $viewFile = self::getViewsPath() . '/' . str_replace('.', '/', $template) . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View template not found: {$template} ({$viewFile})");
        }

        // Extract variables into local scope for the view template
        extract($data, EXTR_SKIP);

        if ($withLayout) {
            require $baseDir . '/includes/header.php';
        }

        require $viewFile;

        if ($withLayout) {
            require $baseDir . '/includes/footer.php';
        }
    }

    public static function renderPartial(string $template, array $data = []): void
    {
        self::render($template, $data, false);
    }

    public static function e(?string $val): string
    {
        return htmlspecialchars((string)($val ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
