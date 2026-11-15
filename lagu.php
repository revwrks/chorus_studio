<?php
/**
 * Chorus Studio - Lagu Entry Point
 * Architecture: Clean MVC (Delegates to App\Controllers\LaguController)
 */
require_once __DIR__ . '/includes/auth.php';

use App\Controllers\LaguController;

$controller = new LaguController();
$controller->handle();
