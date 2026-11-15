<?php
/**
 * Chorus Studio - Generate Bulk PDF Entry Point
 * Architecture: Clean MVC (Delegates to App\Controllers\ReportController)
 */
require_once __DIR__ . '/includes/auth.php';

use App\Controllers\ReportController;

$controller = new ReportController();
$controller->generateBulk();
