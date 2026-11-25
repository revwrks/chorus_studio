<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}
/**
 * End-to-End Integration Test:
 * - Murid Status (Aktif / Non-Aktif)
 * - Rencana Pertemuan Page (rencana_pertemuan.php)
 * - Dashboard "Pertemuan / Target" Card & Interactive Modal
 * - Parent Dashboard "Pertemuan / Target" Non-clickable View
 */
require_once __DIR__ . '/../includes/auth.php';

echo "=======================================================\n";
echo "E2E INTEGRATION TEST: RENCANA PERTEMUAN & STATUS MURID\n";
echo "=======================================================\n\n";

function assert_test(bool $condition, string $message): void {
    if ($condition) {
        echo "  [PASS] $message\n";
    } else {
        echo "  [FAIL] $message\n";
        exit(1);
    }
}

$baseUrl = 'http://localhost/chorus_studio';
$adminCookie = tempnam(sys_get_temp_dir(), 'cs_admin_');
$coachCookie = tempnam(sys_get_temp_dir(), 'cs_coach_');
$parentCookie = tempnam(sys_get_temp_dir(), 'cs_parent_');

function curl_request(string $url, string $method = 'GET', ?array $postFields = null, ?string $cookieFile = null): array {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($postFields !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
        }
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    curl_close($ch);

    $location = null;
    if (preg_match('/^Location:\s*(.*)$/mi', $headers, $matches)) {
        $location = trim($matches[1]);
    }

    return [
        'code' => $httpCode,
        'headers' => $headers,
        'body' => $body,
        'location' => $location,
    ];
}

$muridService = new MuridService();
$rencanaService = new RencanaPertemuanService();

// 1. Create two test students: one Aktif, one Non-Aktif
echo "--- 1. Testing Murid Status & Filtering in Rencana Pertemuan ---\n";

$res1 = $muridService->create('E2E Murid Aktif', 'aktif_ig', null, 'aktif');
assert_test($res1['success'] === true, 'Created active student');
$idAktif = $res1['id'];

$res2 = $muridService->create('E2E Murid NonAktif', 'nonaktif_ig', null, 'non_aktif');
assert_test($res2['success'] === true, 'Created non-active student');
$idNonAktif = $res2['id'];

$curY = (int)date('Y');
$curM = (int)date('n');

$plans = $rencanaService->getListForPeriod($curY, $curM);
$mIdsInPlan = array_column($plans, 'murid_id');
assert_test(in_array($idAktif, $mIdsInPlan), 'Active student is listed in Rencana Pertemuan period list');
assert_test(!in_array($idNonAktif, $mIdsInPlan), 'Non-active student is NOT listed in Rencana Pertemuan period list');

// 2. HTTP Login as Admin & Access rencana_pertemuan.php
echo "\n--- 2. Testing HTTP Flow: Admin on rencana_pertemuan.php ---\n";

// Login as admin
$loginPage = curl_request("$baseUrl/login.php", 'GET', null, $adminCookie);
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $loginPage['body'], $loginCsrfMatch);
$loginCsrf = $loginCsrfMatch[1] ?? '';

$loginResp = curl_request("$baseUrl/login.php", 'POST', [
    'csrf_token' => $loginCsrf,
    'username' => 'admin',
    'password' => 'admin123',
], $adminCookie);
assert_test($loginResp['code'] === 302, 'Admin logged in successfully');

// GET rencana_pertemuan.php
$pageResp = curl_request("$baseUrl/rencana_pertemuan.php?tahun=$curY&bulan=$curM", 'GET', null, $adminCookie);
assert_test($pageResp['code'] === 200, 'rencana_pertemuan.php returned HTTP 200');
assert_test(strpos($pageResp['body'], 'Rencana Pertemuan') !== false, 'rencana_pertemuan.php contains page title');
assert_test(strpos($pageResp['body'], 'Bulan Ini') !== false, 'rencana_pertemuan.php has quick period pill "Bulan Ini"');
assert_test(strpos($pageResp['body'], 'Bulan Depan') !== false, 'rencana_pertemuan.php has quick period pill "Bulan Depan"');
assert_test(strpos($pageResp['body'], 'E2E Murid Aktif') !== false, 'rencana_pertemuan.php table shows active student');
assert_test(strpos($pageResp['body'], 'E2E Murid NonAktif') === false, 'rencana_pertemuan.php table excludes non-active student');

// Extract CSRF token from page
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $pageResp['body'], $csrfMatch);
$csrfToken = $csrfMatch[1] ?? '';
assert_test(!empty($csrfToken), 'Extracted CSRF token from page');
// Check pagination and clean layout assertions on rencana_pertemuan.php
assert_test(strpos($pageResp['body'], 'data-pagination-table="#tableRencana"') !== false, 'rencana_pertemuan.php contains client-side pagination container');
assert_test(strpos($pageResp['body'], 'd-none d-sm-table-cell') !== false, 'rencana_pertemuan.php has mobile-hidden status column');
assert_test(strpos($pageResp['body'], 'Coach Pembina</th>') !== false, 'rencana_pertemuan.php contains Coach Pembina column for admin');
assert_test(strpos($pageResp['body'], '<th>Catatan Rencana</th>') !== false, 'rencana_pertemuan.php contains Catatan Rencana column');
assert_test(strpos($pageResp['body'], 'href="rencana_pertemuan.php"') !== false, 'Admin sidebar contains link to Rencana Pertemuan');

// 3. Save target via AJAX single save
echo "\n--- 3. Testing AJAX Target Save & Stepper Updates ---\n";
$saveResp = curl_request("$baseUrl/rencana_pertemuan.php", 'POST', [
    'action' => 'save_single',
    'id_murid' => $idAktif,
    'tahun' => $curY,
    'bulan' => $curM,
    'target_pertemuan' => 5,
    'catatan' => 'Latihan lagu festival',
    'is_ajax' => '1',
    'csrf_token' => $csrfToken,
], $adminCookie);

assert_test($saveResp['code'] === 200, 'AJAX save returned HTTP 200');
$saveJson = json_decode($saveResp['body'], true);
assert_test($saveJson['success'] === true, 'AJAX save returned success: true');

$savedTarget = $rencanaService->getMonthlyTargetForMurid($idAktif, $curY, $curM);
assert_test($savedTarget === 5, 'Target in database correctly set to 5');

// 4. Batch target update via POST
echo "\n--- 4. Testing Batch Target Save ---\n";
$batchResp = curl_request("$baseUrl/rencana_pertemuan.php", 'POST', [
    'action' => 'save_batch',
    'tahun' => $curY,
    'bulan' => $curM,
    'targets' => [$idAktif => 6],
    'catatans' => [$idAktif => 'Batch target 6 sesi'],
    'csrf_token' => $csrfToken,
], $adminCookie);

assert_test($batchResp['code'] === 302, 'Batch save redirected successfully');
$batchTarget = $rencanaService->getMonthlyTargetForMurid($idAktif, $curY, $curM);
assert_test($batchTarget === 6, 'Batch save updated target to 6');

// 5. Test Staff Dashboard Card & Modal on index.php
echo "\n--- 5. Testing Staff Dashboard: Pertemuan / Target & Modal ---\n";
$dashResp = curl_request("$baseUrl/index.php?tahun=$curY&bulan=$curM", 'GET', null, $adminCookie);
assert_test($dashResp['code'] === 200, 'index.php returned HTTP 200');
assert_test(strpos($dashResp['body'], 'Pertemuan / Target') !== false, 'Dashboard has "Pertemuan / Target" KPI card');
assert_test(strpos($dashResp['body'], 'modalPertemuanTargetDetail') !== false, 'Dashboard has modalPertemuanTargetDetail trigger and modal');
assert_test(strpos($dashResp['body'], 'modal-search-input') !== false, 'Dashboard modal has client-side search input');
assert_test(strpos($dashResp['body'], 'E2E Murid Aktif') !== false, 'Modal contains active student with plan');
assert_test(strpos($dashResp['body'], 'Atur Rencana Pertemuan') !== false, 'Admin modal displays "Atur Rencana Pertemuan" button');

// 6. Test Security: Coach and Orang Tua role boundaries
echo "\n--- 6. Testing Role Security Boundaries ---\n";
$adminService = new AdminService();

// Create and test Coach user
$coachUsername = 'test_coach_' . time();
$coachCreate = $adminService->create($coachUsername, 'Coach Test E2E', 'password123', 'coach');
assert_test($coachCreate['success'] === true, 'Created coach user');
$coachId = $coachCreate['id'];

// Login as coach
$coachLoginPage = curl_request("$baseUrl/login.php", 'GET', null, $coachCookie);
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $coachLoginPage['body'], $cCsrfMatch);
$cCsrf = $cCsrfMatch[1] ?? '';

$coachLogin = curl_request("$baseUrl/login.php", 'POST', [
    'csrf_token' => $cCsrf,
    'username' => $coachUsername,
    'password' => 'password123',
], $coachCookie);
assert_test($coachLogin['code'] === 302, 'Coach logged in successfully');

// Coach access to rencana_pertemuan.php -> MUST be allowed (200)
$coachAccess = curl_request("$baseUrl/rencana_pertemuan.php", 'GET', null, $coachCookie);
assert_test($coachAccess['code'] === 200, 'Coach can access rencana_pertemuan.php (HTTP 200)');
assert_test(strpos($coachAccess['body'], 'Coach Pembina</th>') === false, 'Coach view hides Coach Pembina column');
assert_test(strpos($coachAccess['body'], '<th>Catatan Rencana</th>') !== false, 'Coach view displays Catatan Rencana column');

// Coach dashboard: check sidebar DOES have rencana_pertemuan.php and modal button
$coachDash = curl_request("$baseUrl/index.php?tahun=$curY&bulan=$curM", 'GET', null, $coachCookie);
assert_test($coachDash['code'] === 200, 'Coach dashboard returned HTTP 200');
assert_test(strpos($coachDash['body'], 'href="rencana_pertemuan.php"') !== false, 'Coach sidebar contains link to Rencana Pertemuan');
assert_test(strpos($coachDash['body'], 'Atur Rencana Pertemuan') !== false, 'Coach modal contains "Atur Rencana Pertemuan" button');

// Create Orang Tua user
$parentUsername = 'test_ortu_' . time();
$parentCreate = $adminService->create($parentUsername, 'Orang Tua Test', 'password123', 'orang_tua', [$idAktif]);
assert_test($parentCreate['success'] === true, 'Created parent user');
$parentId = $parentCreate['id'];

// Login as parent
$parentLoginPage = curl_request("$baseUrl/login.php", 'GET', null, $parentCookie);
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $parentLoginPage['body'], $pCsrfMatch);
$pCsrf = $pCsrfMatch[1] ?? '';

$parentLogin = curl_request("$baseUrl/login.php", 'POST', [
    'csrf_token' => $pCsrf,
    'username' => $parentUsername,
    'password' => 'password123',
], $parentCookie);
assert_test($parentLogin['code'] === 302, 'Parent logged in successfully');

// Parent tries to access rencana_pertemuan.php -> must be blocked
$blockedResp = curl_request("$baseUrl/rencana_pertemuan.php", 'GET', null, $parentCookie);
assert_test($blockedResp['code'] === 302 && strpos($blockedResp['location'], '403') !== false, 'Parent access to rencana_pertemuan.php is blocked (403)');

// Parent accessing dashboard: must see "Pertemuan / Target", but NO modal button trigger
$parentDash = curl_request("$baseUrl/index.php?tahun=$curY&bulan=$curM", 'GET', null, $parentCookie);
assert_test($parentDash['code'] === 200, 'Parent dashboard returned HTTP 200');
assert_test(strpos($parentDash['body'], 'Pertemuan / Target') !== false, 'Parent dashboard displays "Pertemuan / Target"');
assert_test(strpos($parentDash['body'], 'data-bs-target="#modalPertemuanTargetDetail"') === false, 'Parent dashboard does NOT have modal trigger for staff breakdown');

// 7. Cleanup test data
echo "\n--- 7. Cleanup ---\n";
$muridService->delete($idAktif);
$muridService->delete($idNonAktif);
$adminService->delete($parentId, 1);
$adminService->delete($coachId, 1);

@unlink($adminCookie);
@unlink($coachCookie);
@unlink($parentCookie);

echo "\n=======================================================\n";
echo ">>> ALL E2E INTEGRATION TESTS PASSED (100% GREEN) <<<\n";
echo "=======================================================\n";
