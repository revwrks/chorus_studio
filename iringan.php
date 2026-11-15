<?php
/**
 * Chorus Studio - Iringan Entry Point
 * Architecture: Clean MVC (Delegates to App\Controllers\IringanController)
 */
require_once __DIR__ . '/includes/auth.php';

use App\Controllers\IringanController;

$controller = new IringanController();
$controller->handle();
