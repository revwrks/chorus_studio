<?php
/**
 * Chorus Studio - Pertemuan Entry Point
 * Architecture: Clean MVC (Delegates to App\Controllers\PertemuanController)
 */
require_once __DIR__ . '/includes/auth.php';

use App\Controllers\PertemuanController;

$controller = new PertemuanController();
$controller->handle();
