<?php
/**
 * Chorus Studio - Refleksi Entry Point
 * Architecture: Clean MVC (Delegates to App\Controllers\RefleksiController)
 */
require_once __DIR__ . '/includes/auth.php';

use App\Controllers\RefleksiController;

$controller = new RefleksiController();
$controller->handle();
