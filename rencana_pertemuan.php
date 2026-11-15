<?php
/**
 * Chorus Studio - Rencana Pertemuan Entry Point
 * Architecture: Clean MVC (Delegates to App\Controllers\RencanaPertemuanController)
 */
require_once __DIR__ . '/includes/auth.php';

use App\Controllers\RencanaPertemuanController;

$controller = new RencanaPertemuanController();
$controller->handle();
