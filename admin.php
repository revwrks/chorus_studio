<?php
/**
 * Chorus Studio - Admin Entry Point
 * Architecture: Clean MVC (Delegates to App\Controllers\AdminController)
 */
require_once __DIR__ . '/includes/auth.php';

use App\Controllers\AdminController;

$controller = new AdminController();
$controller->handle();
