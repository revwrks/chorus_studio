<?php
/**
 * Chorus Studio - Dashboard Entry Point
 * Architecture: Clean MVC (Delegates to App\Controllers\DashboardController)
 */
require_once __DIR__ . '/includes/auth.php';

use App\Controllers\DashboardController;

$controller = new DashboardController();
$controller->handle();
