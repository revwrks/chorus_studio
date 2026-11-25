<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}
/**
 * Test Suite: Filter Rencana Pertemuan
 * Verifies the multi-filter toolbar, column mapping, precision attributes, and role awareness.
 */

require_once __DIR__ . '/../includes/auth.php';

echo "=======================================================\n";
echo "TEST SUITE: FILTER RENCANA PERTEMUAN\n";
echo "=======================================================\n\n";

function assert_test(bool $cond, string $msg): void {
    if ($cond) {
        echo "  [PASS] $msg\n";
    } else {
        echo "  [FAIL] $msg\n";
        exit(1);
    }
}

$rencanaHtml = file_exists(__DIR__ . '/../views/rencana_pertemuan/index.php')
    ? file_get_contents(__DIR__ . '/../views/rencana_pertemuan/index.php')
    : file_get_contents(__DIR__ . '/../rencana_pertemuan.php');

// 1. Static Markup Verification
echo "--- 1. Testing Filter Markup in rencana_pertemuan.php ---\n";

assert_test(strpos($rencanaHtml, 'class="dropdown filter-dropdown-container" data-table-target="#tableRencana"') !== false, 
    'rencana_pertemuan.php has filter-dropdown-container targeting #tableRencana');
assert_test(strpos($rencanaHtml, 'btn-filter-trigger') !== false, 
    'rencana_pertemuan.php has btn-filter-trigger button');
assert_test(strpos($rencanaHtml, 'filter-count-badge') !== false, 
    'rencana_pertemuan.php has filter-count-badge');
assert_test(strpos($rencanaHtml, 'btn-reset-filters') !== false, 
    'rencana_pertemuan.php has btn-reset-filters button');

// Filter 1: Status Capaian
assert_test(strpos($rencanaHtml, 'data-table-target="#tableRencana" data-live-filter-col="5"') !== false, 
    'rencana_pertemuan.php has Status Capaian filter on col 5');
assert_test(strpos($rencanaHtml, 'value="tercapai">Target Tercapai') !== false, 
    'Status Capaian filter contains option Target Tercapai');
assert_test(strpos($rencanaHtml, 'value="dalam-proses">Sedang Berjalan') !== false, 
    'Status Capaian filter contains option Sedang Berjalan');
assert_test(strpos($rencanaHtml, 'value="belum-mulai">Belum Ada Sesi') !== false, 
    'Status Capaian filter contains option Belum Ada Sesi');

// Filter 2: Realisasi Sesi
assert_test(strpos($rencanaHtml, 'data-table-target="#tableRencana" data-live-filter-col="4"') !== false, 
    'rencana_pertemuan.php has Realisasi Sesi filter on col 4');
assert_test(strpos($rencanaHtml, 'value=":has-value">Sudah Ada Sesi') !== false, 
    'Realisasi filter has :has-value option for sessions > 0');
assert_test(strpos($rencanaHtml, 'value=":empty">Belum Ada Sesi') !== false, 
    'Realisasi filter has :empty option for 0 sessions');

// Filter 3 & 4: Precision Cell Attributes in Table
assert_test(strpos($rencanaHtml, 'data-has-val="<?= $realisasiVal > 0 ? \'1\' : \'0\' ?>"') !== false, 
    'Realisasi cell sets data-has-val attribute (1 or 0)');
assert_test(strpos($rencanaHtml, 'data-filter-val="<?= $capaianStatus ?>"') !== false, 
    'Capaian cell sets data-filter-val attribute');
assert_test(strpos($rencanaHtml, 'data-filter-val="<?= !empty($s[\'coach_nama\'])') !== false, 
    'Coach cell sets data-filter-val attribute');
assert_test(strpos($rencanaHtml, 'data-has-val="<?= (!empty($s[\'catatan\'])') !== false, 
    'Catatan cell sets data-has-val attribute');

// 2. HTTP Verification with Admin and Coach Roles
echo "\n--- 2. Testing HTTP Flow: Admin & Coach Views ---\n";

$baseUrl = 'http://localhost/chorus_studio';
$adminCookie = tempnam(sys_get_temp_dir(), 'test_adm_');
$coachCookie = tempnam(sys_get_temp_dir(), 'test_cch_');

function curl_get(string $url, string $cookie): string {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res ?: '';
}

function curl_post(string $url, array $data, string $cookie): string {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res ?: '';
}

// Login as admin
$loginPage = curl_get("$baseUrl/login.php", $adminCookie);
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $loginPage, $m);
$csrf = $m[1] ?? '';
curl_post("$baseUrl/login.php", ['csrf_token' => $csrf, 'username' => 'admin', 'password' => 'admin123'], $adminCookie);

$adminRencana = curl_get("$baseUrl/rencana_pertemuan.php", $adminCookie);
assert_test(strpos($adminRencana, 'Filter Data') !== false, 'Admin view renders Filter Data dropdown');
assert_test(strpos($adminRencana, 'Status Capaian Target</label>') !== false, 'Admin view renders Status Capaian Target filter');
assert_test(strpos($adminRencana, 'Realisasi Sesi</label>') !== false, 'Admin view renders Realisasi Sesi filter');
assert_test(strpos($adminRencana, 'Coach Pembina</label>') !== false, 'Admin view renders Coach Pembina filter');
assert_test(strpos($adminRencana, 'data-live-filter-col="6"') !== false, 'Admin Coach filter targets col 6');
assert_test(strpos($adminRencana, 'data-live-filter-col="7"') !== false, 'Admin Catatan filter targets col 7');

// Create temporary coach & login
$adminService = new AdminService();
$cUser = 'coach_flt_' . time();
$createRes = $adminService->create($cUser, 'Coach Filter Tester', 'password123', 'coach');
assert_test($createRes['success'] === true, 'Created test coach');
$cId = $createRes['id'];

$loginPageCoach = curl_get("$baseUrl/login.php", $coachCookie);
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $loginPageCoach, $mC);
curl_post("$baseUrl/login.php", ['csrf_token' => $mC[1] ?? '', 'username' => $cUser, 'password' => 'password123'], $coachCookie);

$coachRencana = curl_get("$baseUrl/rencana_pertemuan.php", $coachCookie);
assert_test(strpos($coachRencana, 'Status Capaian Target</label>') !== false, 'Coach view renders Status Capaian Target filter');
assert_test(strpos($coachRencana, 'Realisasi Sesi</label>') !== false, 'Coach view renders Realisasi Sesi filter');
assert_test(strpos($coachRencana, 'Coach Pembina</label>') === false, 'Coach view hides Coach Pembina filter');
assert_test(strpos($coachRencana, 'data-live-filter-col="6"') !== false, 'Coach Catatan filter dynamically maps to col 6');

// Cleanup
$adminService->delete($cId, 1);
@unlink($adminCookie);
@unlink($coachCookie);

echo "\n=======================================================\n";
echo ">>> ALL FILTER RENCANA PERTEMUAN TESTS PASSED (100%) <<<\n";
echo "=======================================================\n";
