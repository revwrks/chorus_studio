<?php
/**
 * Application Bootstrap & Authentication Bridge
 * Registers OOP class autoloader and exposes clean backward-compatible functions.
 */

// Register OOP Class Autoloader (PSR-4 App\ namespace and fallback lookup)
require_once dirname(__DIR__) . '/src/Core/Autoloader.php';
\App\Core\Autoloader::register();


// Initialize session and security headers
Auth::init();

// Singleton Database Connection
$pdo = Database::getConnection();

// --- Backward-compatible Procedural Wrappers ---

function csrf_token(): string
{
    return Auth::csrfToken();
}

function csrf_field(): string
{
    return Auth::csrfField();
}

function verify_csrf(): void
{
    Auth::verifyCsrf();
}

function set_flash(string $type, string $message): void
{
    Auth::setFlash($type, $message);
}

function get_flash(): ?array
{
    return Auth::getFlash();
}

function is_logged_in(): bool
{
    return Auth::isLoggedIn();
}

function current_user_id(): ?int
{
    return Auth::currentUserId();
}

function current_user_role(): string
{
    return Auth::currentUserRole();
}

function is_owner(): bool
{
    return Auth::isOwner();
}

function is_admin_or_owner(): bool
{
    return Auth::isAdminOrOwner();
}

function is_coach(): bool
{
    return Auth::isCoach();
}

function is_orang_tua(): bool
{
    return Auth::isOrangTua();
}

function is_staff(): bool
{
    return Auth::isStaff();
}

function get_orang_tua_anak_list($userId = null): array
{
    return Auth::getOrangTuaAnakList($userId);
}

function get_active_murid_id(): ?int
{
    return Auth::getActiveMuridId();
}

function get_active_murid_data(): ?array
{
    return Auth::getActiveMuridData();
}

function base_redirect_url(string $path): string
{
    return Auth::baseRedirectUrl($path);
}

function require_login(): void
{
    Auth::requireLogin();
}

function require_role($roles): void
{
    Auth::requireRole($roles);
}

function logout(): void
{
    Auth::logout();
}
