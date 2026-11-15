<?php
/**
 * Chorus Studio - Murid Entry Point
 * Architecture: Clean MVC (Delegates to App\Controllers\MuridController)
 */
require_once __DIR__ . '/includes/auth.php';

use App\Controllers\MuridController;

$controller = new MuridController();
$controller->handle();
