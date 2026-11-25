<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}
/**
 * Automated E2E Integration Test: Activity Log
 * Tests role boundaries (Owner & Admin only), 250-item cap, pagination UI, and action logging.
 */
require_once __DIR__ . '/../includes/auth.php';

$pdo = Database::getConnection();

function assert_test(bool $condition, string $message): void {
    if ($condition) {
        echo "  [PASS] $message\n";
    } else {
        echo "  [FAIL] $message\n";
        exit(1);
    }
}

echo "=======================================================\n";
echo "E2E INTEGRATION TEST: ACTIVITY LOG (CAP 250 & RBAC)\n";
echo "=======================================================\n\n";

$service = new ActivityLogService($pdo);
$service->ensureTableExists();

// --- 1. Testing ActivityLogService Logging & 250 Cap ---
echo "--- 1. Testing ActivityLogService Logging & 250 Cap ---\n";
$testAction = 'TEST_AUDIT_' . time();
$ok = ActivityLogService::log($testAction, 'Testing automated log entry', 'info', 1, 'Admin Test', 'admin');
assert_test($ok === true, "ActivityLogService::log returned true");

$logs = $service->getLogs(250);
$found = false;
foreach ($logs as $l) {
    if ($l['action'] === $testAction) {
        $found = true;
        assert_test($l['user_nama'] === 'Admin Test', "Log user_nama recorded correctly");
        assert_test($l['tipe'] === 'info', "Log tipe recorded correctly");
        assert_test(!empty($l['relative_time']), "Log relative_time is populated");
        assert_test(!empty($l['action_badge_class']), "Log action_badge_class is generated");
        break;
    }
}
assert_test($found === true, "Newly recorded log found in getLogs()");

// Test capping at 250
echo "\nTesting 250-item cap enforcement:\n";
// Insert multiple items to test cap
for ($i = 0; $i < 300; $i++) {
    $service->record('BULK_TEST', "Bulk log item #$i", 'info', 1, 'System', 'system');
}
$countInDb = (int)$pdo->query('SELECT COUNT(*) FROM activity_log')->fetchColumn();
assert_test($countInDb <= 250, "Total logs in database is capped at <= 250 (Actual: $countInDb)");

$fetchedLogs = $service->getLogs(250);
assert_test(count($fetchedLogs) <= 250, "getLogs(250) returns at most 250 items (Actual: " . count($fetchedLogs) . ")");

// --- 2. Testing HTTP Flow: Admin & Owner Access ---
echo "\n--- 2. Testing HTTP Flow: Admin & Owner Access ---\n";

function make_session_request(string $path, array $sessionData, array $postData = []): array {
    $ch = curl_init();
    $url = 'http://localhost/chorus_studio/' . ltrim($path, '/');
    
    // Create cookie jar
    $cookieFile = tempnam(sys_get_temp_dir(), 'chorus_cookie_');
    
    // First, hit a helper endpoint or login
    $bootstrap = curl_init();
    curl_setopt($bootstrap, CURLOPT_URL, 'http://localhost/chorus_studio/login.php');
    curl_setopt($bootstrap, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($bootstrap, CURLOPT_COOKIEJAR, $cookieFile);
    curl_exec($bootstrap);
    curl_close($bootstrap);
    
    // Inject session by writing temporary bridge or using login
    // Let's use direct curl with login POST
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    
    if (!empty($postData)) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);
    @unlink($cookieFile);
    
    return ['code' => $httpCode, 'body' => $response, 'redirect' => $redirectUrl];
}

// Log in via curl as admin
function login_and_fetch(string $username, string $password, string $fetchUrl): array {
    $cookieFile = tempnam(sys_get_temp_dir(), 'chorus_cookie_');
    
    // 1. GET login to obtain csrf_token
    $ch = curl_init('http://localhost/chorus_studio/login.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    $loginHtml = curl_exec($ch);
    curl_close($ch);
    
    preg_match('/name="csrf_token" value="([^"]+)"/', $loginHtml, $matches);
    $csrf = $matches[1] ?? '';
    
    // 2. POST login
    $ch = curl_init('http://localhost/chorus_studio/login.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'username' => $username,
        'password' => $password,
        'csrf_token' => $csrf
    ]));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_exec($ch);
    curl_close($ch);
    
    // 3. GET target page
    $ch = curl_init('http://localhost/chorus_studio/' . ltrim($fetchUrl, '/'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    $targetHtml = curl_exec($ch);
    $targetCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);
    
    @unlink($cookieFile);
    return ['code' => $targetCode, 'body' => $targetHtml, 'redirect' => $redirectUrl];
}

// Ensure an admin user exists
$adminUser = $pdo->query("SELECT * FROM admin WHERE role IN ('owner', 'admin') LIMIT 1")->fetch();
assert_test(!empty($adminUser), "Found admin user in database: " . ($adminUser['username'] ?? ''));

// Test accessing activity_log.php as admin (password is admin or standard)
// Let's create a temporary test user for owner and coach to be 100% sure
$testOwnerUser = 'test_owner_' . time();
$testCoachUser = 'test_coach_' . time();
$pass = 'password123';
$hash = password_hash($pass, PASSWORD_DEFAULT);

$pdo->prepare("INSERT INTO admin (username, password, nama, role) VALUES (?, ?, 'Test Owner', 'owner')")->execute([$testOwnerUser, $hash]);
$testOwnerId = (int)$pdo->lastInsertId();

$pdo->prepare("INSERT INTO admin (username, password, nama, role) VALUES (?, ?, 'Test Coach', 'coach')")->execute([$testCoachUser, $hash]);
$testCoachId = (int)$pdo->lastInsertId();

// 2.1 Owner access to activity_log.php
$ownerRes = login_and_fetch($testOwnerUser, $pass, 'activity_log.php');
assert_test($ownerRes['code'] === 200, "Owner can access activity_log.php (HTTP 200)");
assert_test(strpos($ownerRes['body'], 'Activity Log') !== false, "Page contains title 'Activity Log'");
assert_test(strpos($ownerRes['body'], 'data-pagination-table="#tableActivityLog"') !== false, "Page contains universal pagination container");
assert_test(strpos($ownerRes['body'], 'data-live-search="#tableActivityLog"') !== false, "Page contains live search input");
assert_test(strpos($ownerRes['body'], 'Total Log Disimpan') !== false, "Page contains stat card 'Total Log Disimpan'");
assert_test(strpos($ownerRes['body'], 'modalClearLogs') !== false, "Owner view contains 'Bersihkan Log' modal button");
assert_test(strpos($ownerRes['body'], 'activity_log.php') !== false, "Owner sidebar includes link to activity_log.php");

// 2.2 Coach access to activity_log.php MUST BE BLOCKED
echo "\n--- 3. Testing Role Boundaries (Coach Access Blocked) ---\n";
$coachRes = login_and_fetch($testCoachUser, $pass, 'activity_log.php');
assert_test($coachRes['code'] === 302 || $coachRes['code'] === 403, "Coach access to activity_log.php is blocked (HTTP {$coachRes['code']})");
if ($coachRes['code'] === 302) {
    assert_test(strpos($coachRes['redirect'], 'error.php?code=403') !== false, "Coach is redirected to error.php?code=403");
}

// 2.3 Coach sidebar does NOT contain activity_log.php
$coachDashRes = login_and_fetch($testCoachUser, $pass, 'index.php');
assert_test(strpos($coachDashRes['body'], 'href="activity_log.php"') === false, "Coach sidebar does NOT contain link to activity_log.php");

// Cleanup test users
$pdo->prepare("DELETE FROM admin WHERE id IN (?, ?)")->execute([$testOwnerId, $testCoachId]);
$pdo->prepare("DELETE FROM activity_log WHERE action = 'BULK_TEST'")->execute();

// Reseed initial logs to leave the table in rich state
$service->seedInitialLogsIfEmpty();

echo "\n=======================================================\n";
echo ">>> ALL ACTIVITY LOG TESTS PASSED (100% GREEN) <<<\n";
echo "=======================================================\n";
