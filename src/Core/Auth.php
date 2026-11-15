<?php
namespace App\Core;

/**
 * Auth & Session Management Class
 * Handles authentication, authorization, CSRF, security headers, parent context, and flash messages.
 */
class Auth
{
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                    || (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && $_SERVER['HTTP_FRONT_END_HTTPS'] !== 'off')
                    || (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }

        // Security headers
        if (!headers_sent()) {
            header("X-Content-Type-Options: nosniff");
            header("X-Frame-Options: SAMEORIGIN");
            header("X-XSS-Protection: 1; mode=block");
            header("Referrer-Policy: strict-origin-when-cross-origin");
            header("Content-Security-Policy: default-src 'self'; connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://errors.infinityfree.net; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src 'self' data: https://cdnjs.cloudflare.com https://fonts.gstatic.com; img-src 'self' data: blob: https:; frame-ancestors 'none';");
        }
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function verifyCsrf(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
                $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
                       || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false)
                       || isset($_POST['is_ajax']);
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Sesi login telah kedaluwarsa atau token keamanan tidak valid. Silakan muat ulang halaman.'
                    ]);
                    exit;
                }
                http_response_code(403);
                die('CSRF validation failed. Invalid or missing token.');
            }
        }
    }

    public static function setFlash(string $type, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash_message'] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    public static function getFlash(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!empty($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            return $flash;
        }
        return null;
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['admin_id']);
    }

    public static function currentUserId(): ?int
    {
        return isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : null;
    }

    public static function currentUserRole(): string
    {
        return $_SESSION['admin_role'] ?? 'admin';
    }

    public static function currentUserName(): string
    {
        return $_SESSION['admin_username'] ?? '';
    }

    public static function currentUserDisplayName(): string
    {
        return $_SESSION['admin_nama'] ?? $_SESSION['admin_username'] ?? 'User';
    }

    public static function isOwner(): bool
    {
        return self::currentUserRole() === 'owner';
    }

    public static function isAdminOrOwner(): bool
    {
        $role = self::currentUserRole();
        return $role === 'admin' || $role === 'owner';
    }

    public static function isCoach(): bool
    {
        return self::currentUserRole() === 'coach';
    }

    public static function isOrangTua(): bool
    {
        return self::currentUserRole() === 'orang_tua';
    }

    public static function isStaff(): bool
    {
        return in_array(self::currentUserRole(), ['owner', 'admin', 'coach'], true);
    }

    public static function getOrangTuaAnakList(?int $userId = null): array
    {
        $userId = $userId ?? self::currentUserId();
        if (!$userId) {
            return [];
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT m.id, m.nama, m.instagram, m.foto 
                               FROM orang_tua_murid otm
                               JOIN murid m ON otm.id_murid = m.id
                               WHERE otm.id_orang_tua = ?
                               ORDER BY m.nama ASC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function getActiveMuridId(): ?int
    {
        if (!self::isOrangTua()) {
            return null;
        }
        $children = self::getOrangTuaAnakList();
        if (empty($children)) {
            return null;
        }

        $childIds = array_map('intval', array_column($children, 'id'));

        // If query string explicitly requests a child belonging to this parent:
        if (isset($_GET['anak_id']) && in_array((int)$_GET['anak_id'], $childIds, true)) {
            $_SESSION['active_murid_id'] = (int)$_GET['anak_id'];
        }

        if (empty($_SESSION['active_murid_id']) || !in_array((int)$_SESSION['active_murid_id'], $childIds, true)) {
            $_SESSION['active_murid_id'] = (int)$childIds[0];
        }

        return (int)$_SESSION['active_murid_id'];
    }

    public static function getActiveMuridData(): ?array
    {
        $activeId = self::getActiveMuridId();
        if (!$activeId) {
            return null;
        }
        $children = self::getOrangTuaAnakList();
        foreach ($children as $c) {
            if ((int)$c['id'] === $activeId) {
                return $c;
            }
        }
        return null;
    }

    public static function baseRedirectUrl(string $path): string
    {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $prefix = (strpos($script, '/includes/') !== false) ? '../' : '';
        return $prefix . ltrim($path, '/');
    }

    public static function requireLogin(): void
    {
        if (!self::isLoggedIn()) {
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
                   || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false)
                   || isset($_POST['is_ajax']);
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Sesi Anda telah berakhir. Silakan muat ulang dan login kembali.']);
                exit;
            }
            header('Location: ' . self::baseRedirectUrl('login.php'));
            exit;
        }
    }

    public static function requireRole($roles): void
    {
        self::requireLogin();
        $roles = is_array($roles) ? $roles : [$roles];
        if (!in_array(self::currentUserRole(), $roles, true)) {
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
                   || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false)
                   || isset($_POST['is_ajax']);
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Akses ditolak. Anda tidak memiliki izin untuk tindakan ini.']);
                exit;
            }
            http_response_code(403);
            header('Location: ' . self::baseRedirectUrl('error.php?code=403'));
            exit;
        }
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            setcookie(session_name(), '', time() - 42000, '/');
        }
        session_destroy();
    }
}

if (!class_exists('Auth', false)) {
    class_alias(Auth::class, 'Auth');
}
