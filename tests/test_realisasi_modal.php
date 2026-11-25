<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}
/**
 * Test Suite: Realisasi Detail Modal Verification
 * Tests getRealisasiDates method, AJAX endpoints, and UI integration on both rencana_pertemuan.php and index.php.
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
echo "TEST SUITE: REALISASI PERTEMUAN DETAIL MODAL\n";
echo "=======================================================\n\n";

$rencanaService = new RencanaPertemuanService($pdo);
$muridService = new MuridService($pdo);

// 1. Setup a test student and 2 sample meetings
echo "--- 1. Setting Up Test Data ---\n";
$testMuridName = 'Murid Test Realisasi ' . time();
$createRes = $muridService->create($testMuridName, 'test_ig');
assert_test($createRes['success'] === true, "Created test student: {$testMuridName}");
$testMuridId = (int)$createRes['id'];

$curY = (int)date('Y');
$curM = (int)date('n');
$tgl1 = sprintf('%04d-%02d-05', $curY, $curM);
$tgl2 = sprintf('%04d-%02d-12', $curY, $curM);

// Create admin/coach for testing
$adminUser = $pdo->query("SELECT id, nama, username FROM admin WHERE role IN ('owner', 'admin') LIMIT 1")->fetch();
$coachId = (int)$adminUser['id'];

// Insert meetings
$stmtP = $pdo->prepare("INSERT INTO pertemuan (id_murid, id_lagu, coach_id, tanggal, catatan) VALUES (?, NULL, ?, ?, ?)");
$stmtP->execute([$testMuridId, $coachId, $tgl1, 'Latihan vokal tangga nada dasar']);
$meetingId1 = (int)$pdo->lastInsertId();

$stmtP->execute([$testMuridId, $coachId, $tgl2, 'Latihan resonansi dan artikulasi']);
$meetingId2 = (int)$pdo->lastInsertId();
assert_test($meetingId1 > 0 && $meetingId2 > 0, "Inserted 2 test meetings for student on dates $tgl1 and $tgl2");

// 2. Test RencanaPertemuanService::getRealisasiDates
echo "\n--- 2. Testing RencanaPertemuanService::getRealisasiDates ---\n";
$dates = $rencanaService->getRealisasiDates($testMuridId, $curY, $curM);
assert_test(count($dates) === 2, "getRealisasiDates returned 2 meetings");
assert_test(!empty($dates[0]['tanggal_formatted']), "First meeting has tanggal_formatted: " . ($dates[0]['tanggal_formatted'] ?? ''));
assert_test(!empty($dates[0]['tanggal_short']), "First meeting has tanggal_short: " . ($dates[0]['tanggal_short'] ?? ''));
assert_test($dates[0]['catatan'] === 'Latihan vokal tangga nada dasar', "First meeting catatan matches");
assert_test(!empty($dates[0]['coach_nama']), "First meeting has coach_nama: " . ($dates[0]['coach_nama'] ?? ''));

// Test empty month
$datesEmpty = $rencanaService->getRealisasiDates($testMuridId, $curY - 5, 1);
assert_test(count($datesEmpty) === 0, "getRealisasiDates returns empty array for month with no sessions");

// 3. Test HTTP AJAX Endpoint on rencana_pertemuan.php
echo "\n--- 3. Testing HTTP AJAX Endpoint on rencana_pertemuan.php ---\n";

function login_and_fetch_json(string $username, string $password, string $fetchUrl): array {
    $cookieFile = tempnam(sys_get_temp_dir(), 'chorus_cookie_');
    
    // 1. GET login for CSRF token
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
    $resBody = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    @unlink($cookieFile);
    return ['code' => $httpCode, 'body' => $resBody];
}

// Fetch AJAX
$ajaxUrl = "rencana_pertemuan.php?action=get_realisasi_detail&murid_id={$testMuridId}&tahun={$curY}&bulan={$curM}";
$res = login_and_fetch_json('admin', 'admin123', $ajaxUrl);
assert_test($res['code'] === 200, "AJAX endpoint returned HTTP 200");

$json = json_decode($res['body'], true);
assert_test(is_array($json), "Response is valid JSON");
assert_test(($json['success'] ?? false) === true, "JSON success is true");
assert_test(($json['murid']['id'] ?? 0) === $testMuridId, "JSON murid id matches");
assert_test(($json['murid']['nama'] ?? '') === $testMuridName, "JSON murid nama matches");
assert_test(($json['total'] ?? 0) === 2, "JSON total is 2");
assert_test(count($json['pertemuan'] ?? []) === 2, "JSON pertemuan array has 2 items");

// 4. Test HTTP AJAX Endpoint on index.php
echo "\n--- 4. Testing HTTP AJAX Endpoint on index.php ---\n";
$ajaxUrlIndex = "index.php?action=get_realisasi_detail&murid_id={$testMuridId}&tahun={$curY}&bulan={$curM}";
$resIndex = login_and_fetch_json('admin', 'admin123', $ajaxUrlIndex);
assert_test($resIndex['code'] === 200, "index.php AJAX endpoint returned HTTP 200");
$jsonIndex = json_decode($resIndex['body'], true);
assert_test(is_array($jsonIndex) && ($jsonIndex['success'] ?? false) === true, "index.php JSON success is true");
assert_test(($jsonIndex['total'] ?? 0) === 2, "index.php JSON total is 2");

// 5. Test UI Elements on rencana_pertemuan.php
echo "\n--- 5. Testing UI Elements on rencana_pertemuan.php ---\n";
$pageRes = login_and_fetch_json('admin', 'admin123', "rencana_pertemuan.php?tahun={$curY}&bulan={$curM}");
assert_test(strpos($pageRes['body'], 'btn-realisasi-detail') !== false, "rencana_pertemuan.php contains btn-realisasi-detail buttons");
assert_test(strpos($pageRes['body'], 'data-bs-target="#modalDetailRealisasi"') !== false, "rencana_pertemuan.php buttons target modalDetailRealisasi");
assert_test(strpos($pageRes['body'], 'id="modalDetailRealisasi"') !== false, "rencana_pertemuan.php includes modalDetailRealisasi modal");

// 6. Test UI Elements on index.php
echo "\n--- 6. Testing UI Elements on index.php ---\n";
$dashRes = login_and_fetch_json('admin', 'admin123', "index.php?tahun={$curY}&bulan={$curM}");
assert_test(strpos($dashRes['body'], 'id="modalDetailRealisasi"') !== false, "index.php includes modalDetailRealisasi modal");
assert_test(strpos($dashRes['body'], 'btn-realisasi-detail') !== false, "index.php includes btn-realisasi-detail inside modal breakdown");

// 7. Cleanup
echo "\n--- 7. Cleaning Up Test Data ---\n";
$pdo->prepare("DELETE FROM pertemuan WHERE id IN (?, ?)")->execute([$meetingId1, $meetingId2]);
$muridService->delete($testMuridId);
echo "  [PASS] Test data cleaned up successfully\n";

echo "\n=======================================================\n";
echo ">>> ALL REALISASI DETAIL MODAL TESTS PASSED (100%) <<<\n";
echo "=======================================================\n";
