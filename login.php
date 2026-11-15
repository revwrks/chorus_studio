<?php
/**
 * Chorus Studio - Login Entry Point
 * Architecture: Clean MVC (Delegates to App\Controllers\AuthController)
 */
require_once __DIR__ . '/includes/auth.php';

use App\Controllers\AuthController;

$controller = new AuthController();
$controller->login();
