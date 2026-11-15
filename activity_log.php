<?php
/**
 * Chorus Studio - Activity Log Entry Point
 * Architecture: Clean MVC (Delegates to App\Controllers\ActivityLogController)
 */
require_once __DIR__ . '/includes/auth.php';

use App\Controllers\ActivityLogController;

$controller = new ActivityLogController();
$controller->handle();
