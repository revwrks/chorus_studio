<?php
/**
 * Chorus Studio - Profil Pengguna Entry Point
 * Architecture: Clean MVC (Delegates to App\Controllers\ProfilController)
 */
require_once __DIR__ . '/includes/auth.php';

use App\Controllers\ProfilController;

$controller = new ProfilController();
$controller->handle();
