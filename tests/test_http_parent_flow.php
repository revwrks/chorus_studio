<?php
/**
 * End-to-End HTTP Test for Orang Tua Feature
 * Exercises http://localhost/chorus_studio
 */

$baseUrl = 'http://localhost/chorus_studio';
$adminCookie = sys_get_temp_dir() . '/cookie_admin_' . uniqid() . '.txt';
$parentCookie = sys_get_temp_dir() . '/cookie_parent_' . uniqid() . '.txt';

require_once __DIR__ . '/../includes/db.php';

function curl_request($url, $method = 'GET', $data = null, $cookieFile = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? http_build_query($data) : $data);
    }
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($res, 0, $headerSize);
    $body = substr($res, $headerSize);
    curl_close($ch);

    $location = null;
    if (preg_match('/^Location:\s*(.+)$/mi', $header, $m)) {
        $location = trim($m[1]);
    }

    return ['code' => $code, 'location' => $location, 'header' => $header, 'body' => $body];
}

function get_csrf_token($html) {
    if (preg_match('/name="csrf_token"\s+value="([^"]+)"/', $html, $m)) {
        return $m[1];
    }
    return '';
}

echo "=== E2E HTTP INTEGRATION TEST: FITUR ORANG TUA ===\n\n";

// 1. Create a permanent test student if none exists, or use existing
$testKidName = 'Siti Ananda E2E';
$stmtKid = $pdo->prepare("SELECT id FROM murid WHERE nama = ?");
$stmtKid->execute([$testKidName]);
$kidId = $stmtKid->fetchColumn();
if (!$kidId) {
    $pdo->prepare("INSERT INTO murid (nama, instagram) VALUES (?, '@sitiananda')")->execute([$testKidName]);
    $kidId = (int)$pdo->lastInsertId();
}

// 2. Setup known admin password for testing
$adminPass = 'admin123';
$hash = password_hash($adminPass, PASSWORD_BCRYPT);
$pdo->prepare("UPDATE admin SET password = ? WHERE username = 'admin'")->execute([$hash]);

// 3. Admin Login
echo "1. Logging in as Admin...\n";
$loginPage = curl_request("$baseUrl/login.php", 'GET', null, $adminCookie);
$csrf = get_csrf_token($loginPage['body']);
$loginResp = curl_request("$baseUrl/login.php", 'POST', [
    'csrf_token' => $csrf,
    'username' => 'admin',
    'password' => $adminPass
], $adminCookie);

echo "   Admin login response: Code {$loginResp['code']}, Redirect to: " . ($loginResp['location'] ?? 'none') . "\n";
if ($loginResp['location'] !== 'index.php') {
    die("FATAL: Admin login failed\n");
}

// 4. Admin creates Orang Tua account linked to $kidId
echo "2. Admin creating Orang Tua account via admin.php...\n";
$adminPage = curl_request("$baseUrl/admin.php", 'GET', null, $adminCookie);
$csrfAdmin = get_csrf_token($adminPage['body']);
$ortuUsername = 'ortu_e2e_' . time();
$ortuPass = 'ortu12345';

$createOrtuResp = curl_request("$baseUrl/admin.php", 'POST', [
    'csrf_token' => $csrfAdmin,
    'action' => 'create',
    'username' => $ortuUsername,
    'nama' => 'Ibu Siti E2E',
    'role' => 'orang_tua',
    'password' => $ortuPass,
    'murid_ids' => [$kidId]
], $adminCookie);

echo "   Create Orang Tua response: Code {$createOrtuResp['code']}, Redirect: {$createOrtuResp['location']}\n";

// Verify Orang Tua in DB
$stmtOrtu = $pdo->prepare("SELECT id, role FROM admin WHERE username = ?");
$stmtOrtu->execute([$ortuUsername]);
$ortuDb = $stmtOrtu->fetch();
$ortuId = (int)($ortuDb['id'] ?? 0);
echo "   Orang Tua in DB: ID $ortuId, Role: {$ortuDb['role']}\n";

$checkLink = $pdo->prepare("SELECT id_murid FROM orang_tua_murid WHERE id_orang_tua = ?");
$checkLink->execute([$ortuId]);
$linkedKid = (int)$checkLink->fetchColumn();
echo "   Linked Murid ID: $linkedKid (Expected: $kidId)\n";

// 5. Admin adds an iringan for the student
echo "3. Admin adding Lagu Iringan for $testKidName...\n";
$iringanPage = curl_request("$baseUrl/iringan.php", 'GET', null, $adminCookie);
$csrfIringan = get_csrf_token($iringanPage['body']);

$addIringanResp = curl_request("$baseUrl/iringan.php", 'POST', [
    'csrf_token' => $csrfIringan,
    'action' => 'create',
    'id_murid' => $kidId,
    'judul_iringan' => 'Simfoni E2E Test',
    'kategori' => 'lomba',
    'gdrive_link' => 'https://drive.google.com/file/d/test-e2e-drive/view',
    'catatan' => 'Lagu iringan final lomba'
], $adminCookie);
echo "   Add Iringan response: Code {$addIringanResp['code']}, Redirect: {$addIringanResp['location']}\n";

// 6. Test Orang Tua Login
echo "4. Logging in as Orang Tua ($ortuUsername)...\n";
$loginPageOrtu = curl_request("$baseUrl/login.php", 'GET', null, $parentCookie);
$csrfOrtu = get_csrf_token($loginPageOrtu['body']);

$loginRespOrtu = curl_request("$baseUrl/login.php", 'POST', [
    'csrf_token' => $csrfOrtu,
    'username' => $ortuUsername,
    'password' => $ortuPass
], $parentCookie);
echo "   Orang Tua login response: Code {$loginRespOrtu['code']}, Redirect: {$loginRespOrtu['location']}\n";

// 7. Orang Tua accesses Dashboard
echo "5. Orang Tua accessing Dashboard (index.php)...\n";
$dashboardResp = curl_request("$baseUrl/index.php", 'GET', null, $parentCookie);
$hasDashboardTitle = strpos($dashboardResp['body'], 'Dashboard Orang Tua') !== false;
$hasChildName = strpos($dashboardResp['body'], $testKidName) !== false;
$hasKPI = strpos($dashboardResp['body'], 'Pertemuan Bulan Ini') !== false;
$hasNoWarnings = (strpos($dashboardResp['body'], 'Warning:') === false && strpos($dashboardResp['body'], 'Notice:') === false);
$hasNoLeakedCode = (strpos($dashboardResp['body'], 'pertemuanService') === false);

echo "   Dashboard contains 'Dashboard Orang Tua': " . ($hasDashboardTitle ? "YES" : "NO") . "\n";
echo "   Dashboard contains child name '$testKidName': " . ($hasChildName ? "YES" : "NO") . "\n";
echo "   Dashboard contains KPI 'Pertemuan Bulan Ini': " . ($hasKPI ? "YES" : "NO") . "\n";
echo "   Dashboard clean (No PHP Warnings / Notices): " . ($hasNoWarnings ? "YES" : "NO") . "\n";
echo "   Dashboard clean (No raw PHP leaked code): " . ($hasNoLeakedCode ? "YES" : "NO") . "\n";

// 8. Orang Tua accesses Lagu Iringan
echo "6. Orang Tua accessing Lagu Iringan (iringan.php)...\n";
$iringanOrtuResp = curl_request("$baseUrl/iringan.php", 'GET', null, $parentCookie);
$hasIringanTitle = strpos($iringanOrtuResp['body'], 'Lagu Iringan Latihan & Lomba') !== false;
$hasTrackTitle = strpos($iringanOrtuResp['body'], 'Simfoni E2E Test') !== false;
$hasGDriveBtn = strpos($iringanOrtuResp['body'], 'Buka Google Drive') !== false;
echo "   Iringan page has header: " . ($hasIringanTitle ? "YES" : "NO") . "\n";
echo "   Iringan page has track 'Simfoni E2E Test': " . ($hasTrackTitle ? "YES" : "NO") . "\n";
echo "   Iringan page has 'Buka Google Drive' button: " . ($hasGDriveBtn ? "YES" : "NO") . "\n";

// 9. Orang Tua attempts unauthorized access to admin.php and murid.php
echo "7. Testing Security Boundaries (Orang Tua trying to access admin.php & murid.php)...\n";
$unauthAdmin = curl_request("$baseUrl/admin.php", 'GET', null, $parentCookie);
$unauthMurid = curl_request("$baseUrl/murid.php", 'GET', null, $parentCookie);

$adminBlocked = ($unauthAdmin['code'] === 403 || strpos($unauthAdmin['location'], 'error.php?code=403') !== false);
$muridBlocked = ($unauthMurid['code'] === 403 || strpos($unauthMurid['location'], 'error.php?code=403') !== false);
echo "   Access to admin.php blocked (403): " . ($adminBlocked ? "YES" : "NO") . " (Code: {$unauthAdmin['code']}, Location: {$unauthAdmin['location']})\n";
echo "   Access to murid.php blocked (403): " . ($muridBlocked ? "YES" : "NO") . " (Code: {$unauthMurid['code']}, Location: {$unauthMurid['location']})\n";

// 10. Clean up
echo "8. Cleaning up test data...\n";
$pdo->prepare("DELETE FROM iringan_murid WHERE id_murid = ?")->execute([$kidId]);
$pdo->prepare("DELETE FROM admin WHERE id = ?")->execute([$ortuId]);
$pdo->prepare("DELETE FROM murid WHERE id = ?")->execute([$kidId]);
@unlink($adminCookie);
@unlink($parentCookie);

$allPassed = $hasDashboardTitle && $hasChildName && $hasKPI && $hasNoWarnings && $hasNoLeakedCode && $hasIringanTitle && $hasTrackTitle && $hasGDriveBtn && $adminBlocked && $muridBlocked;

if ($allPassed) {
    echo "\n>>> E2E RESULT: ALL PASS (100% OPERATIONAL) <<<\n";
    exit(0);
} else {
    echo "\n>>> E2E RESULT: FAILED CHECKS <<<\n";
    exit(1);
}
