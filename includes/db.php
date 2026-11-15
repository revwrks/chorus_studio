<?php
/**
 * Database connection bootstrap
 * Reuses singleton Database PDO instance for InfinityFree performance.
 */
require_once __DIR__ . '/classes/Database.php';
$pdo = Database::getConnection();