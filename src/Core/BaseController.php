<?php
namespace App\Core;

abstract class BaseController
{
    protected ?\PDO $pdo = null;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    abstract public function handle(): void;

    protected function render(string $template, array $data = [], bool $withLayout = true): void
    {
        View::render($template, $data, $withLayout);
    }

    protected function renderPartial(string $template, array $data = []): void
    {
        View::renderPartial($template, $data);
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function isPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
    }

    protected function isAjax(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false)
            || isset($_POST['is_ajax']);
    }

    protected function verifyCsrf(): void
    {
        Auth::verifyCsrf();
    }

    protected function setFlash(string $type, string $message): void
    {
        Auth::setFlash($type, $message);
    }

    protected function requireLogin(): void
    {
        Auth::requireLogin();
    }

    protected function requireRole(string|array $roles): void
    {
        Auth::requireRole($roles);
    }

    protected function currentUserId(): ?int
    {
        return Auth::currentUserId();
    }

    protected function currentUserRole(): string
    {
        return Auth::currentUserRole();
    }

    protected function isOwner(): bool
    {
        return Auth::isOwner();
    }

    protected function isAdminOrOwner(): bool
    {
        return Auth::isAdminOrOwner();
    }
}
