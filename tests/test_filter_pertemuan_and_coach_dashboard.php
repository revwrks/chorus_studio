<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}
/**
 * Test Suite:
 * 1. Filter di Jadwal Pertemuan (pertemuan.php):
 *    - Periode Bulan filter (col 0)
 *    - Nama Murid filter (col 1)
 *    - Status Materi Lagu filter (col 2)
 *    - Coach filter (col 3)
 *    - Precision data-filter-val attributes
 * 2. Dashboard Coach sama dengan Owner (index.php):
 *    - Top KPI cards: Total Murid, Pertemuan/Target, Coach Terdaftar
 *    - "Murid Dilatih per Coach" table present for coach
 *    - Modal Pertemuan/Target Detail has "Coach Pembina" for coach
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/classes/AdminService.php';

function assert_test(bool $cond, string $msg): void {
    if ($cond) {
        echo "  [PASS] $msg\n";
    } else {
        echo "  [FAIL] $msg\n";
        exit(1);
    }
}

echo "=======================================================\n";
echo "TEST: FILTER JADWAL PERTEMUAN & DASHBOARD COACH = OWNER\n";
echo "=======================================================\n\n";

// --- PART 1: Filter Markup di pertemuan.php ---
echo "--- 1. Testing Filter Markup in pertemuan.php ---\n";
$pertemuanHtml = file_exists(__DIR__ . '/../views/pertemuan/index.php')
    ? file_get_contents(__DIR__ . '/../views/pertemuan/index.php')
    : file_get_contents(__DIR__ . '/../pertemuan.php');

assert_test(strpos($pertemuanHtml, 'data-table-target="#tablePertemuan"') !== false, "pertemuan.php has filter-dropdown-container targeting #tablePertemuan");
assert_test(strpos($pertemuanHtml, 'btn-filter-trigger') !== false, "pertemuan.php has btn-filter-trigger button");
assert_test(strpos($pertemuanHtml, 'btn-reset-filters') !== false, "pertemuan.php has btn-reset-filters button");

// Filters presence
assert_test(strpos($pertemuanHtml, 'data-live-filter-col="0"') !== false, "pertemuan.php has Periode Bulan filter on col 0");
assert_test(strpos($pertemuanHtml, 'Periode Bulan</label>') !== false, "pertemuan.php has Periode Bulan label");

assert_test(strpos($pertemuanHtml, 'data-live-filter-col="1"') !== false, "pertemuan.php has Nama Murid filter on col 1");
assert_test(strpos($pertemuanHtml, 'Nama Murid</label>') !== false, "pertemuan.php has Nama Murid label");

assert_test(strpos($pertemuanHtml, 'data-live-filter-col="2"') !== false, "pertemuan.php has Status Materi Lagu filter on col 2");
assert_test(strpos($pertemuanHtml, 'Status Materi Lagu</label>') !== false, "pertemuan.php has Status Materi Lagu label");

assert_test(strpos($pertemuanHtml, 'data-live-filter-col="3"') !== false, "pertemuan.php has Coach filter on col 3");
assert_test(strpos($pertemuanHtml, 'Coach</label>') !== false, "pertemuan.php has Coach label");

// Data attributes on rows
assert_test(strpos($pertemuanHtml, 'data-filter-val="<?= $ym ?>"') !== false, "pertemuan.php sets data-filter-val with Year-Month on Date cell");
assert_test(strpos($pertemuanHtml, 'data-filter-val="<?= htmlspecialchars($r[\'murid_nama\']') !== false, "pertemuan.php sets data-filter-val on Murid cell");
assert_test(strpos($pertemuanHtml, 'data-has-val="<?= $hasLagu ? \'1\' : \'0\' ?>"') !== false, "pertemuan.php sets data-has-val on Lagu cell");
assert_test(strpos($pertemuanHtml, 'data-filter-val="<?= htmlspecialchars($r[\'coach_nama\']') !== false, "pertemuan.php sets data-filter-val on Coach cell");

// Uniform table header
assert_test(strpos($pertemuanHtml, '<th>Coach</th>') !== false, "pertemuan.php has permanent Coach column in thead");

echo "\n--- 2. Testing Coach Dashboard Identity with Owner in index.php ---\n";
$indexContent = file_exists(__DIR__ . '/../views/dashboard/staff.php')
    ? file_get_contents(__DIR__ . '/../views/dashboard/staff.php') . file_get_contents(__DIR__ . '/../src/Controllers/DashboardController.php')
    : file_get_contents(__DIR__ . '/../index.php');

// Check that Card 3 uses "Coach Terdaftar"
assert_test(preg_match('/<h[1-6][^>]*>.*?Coach Terdaftar\s*<\/h[1-6]>/s', $indexContent) === 1, "index.php has Coach Terdaftar card heading without role gating");

// Check that Murid Dilatih per Coach is NOT hidden from coach
assert_test(strpos($indexContent, "<?php if (!\$isCoach): ?>\n    <div class=\"card p-3\">\n      <div class=\"d-flex justify-content-between align-items-center mb-2\">\n        <h5 class=\"mb-0\" style=\"font-family:'Outfit',sans-serif;font-weight:600;\"><i class=\"fa fa-chart-bar me-2\"") === false, "index.php does NOT wrap 'Murid Dilatih per Coach' in !isCoach");
assert_test(preg_match('/<h[1-6][^>]*>.*?Murid Dilatih per Coach\s*<\/h[1-6]>/s', $indexContent) === 1, "index.php renders 'Murid Dilatih per Coach' card for all staff");

// Check that Modal Pertemuan/Target detail shows Coach Pembina column for all staff
assert_test(preg_match('/<th[^>]*>Capaian<\/th>\s*<th>Coach Pembina<\/th>/', $indexContent) === 1, "index.php shows Coach Pembina header in modal for all staff");

// Check that summary calculates studio-wide
assert_test(
    strpos($indexContent, '$this->rencanaService->getSummary($tahunDipilih, $bulanDipilih, null)') !== false
    || strpos($indexContent, '$rencanaService->getSummary($tahunDipilih, $bulanDipilih, null)') !== false,
    "index.php calculates studio-wide target summary without coach restriction"
);

// Check HTTP responses for coach vs owner
$baseUrl = 'http://localhost/chorus_studio';
$adminCookie = tempnam(sys_get_temp_dir(), 'test_admin_');
$coachCookie = tempnam(sys_get_temp_dir(), 'test_coach_');

function http_req(string $url, string $method = 'GET', ?array $post = null, ?string $cookie = null): array {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    if ($cookie) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    }
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($post !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        }
    }
    $res = curl_exec($ch);
    $hSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => substr($res, $hSize)];
}

// 1. Login Admin/Owner
$adminLoginGet = http_req("$baseUrl/login.php", 'GET', null, $adminCookie);
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $adminLoginGet['body'], $m);
$csrf = $m[1] ?? '';
$adminLogin = http_req("$baseUrl/login.php", 'POST', ['csrf_token' => $csrf, 'username' => 'admin', 'password' => 'admin123'], $adminCookie);
assert_test($adminLogin['code'] === 302, "Admin logged in");

// 2. Create or Login Coach
$adminService = new AdminService();
$coachUser = 'coach_test_' . time();
$coachCreate = $adminService->create($coachUser, 'Coach Tester', 'password123', 'coach');
assert_test($coachCreate['success'], "Created test coach user: $coachUser");

$coachLoginGet = http_req("$baseUrl/login.php", 'GET', null, $coachCookie);
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $coachLoginGet['body'], $m2);
$csrf2 = $m2[1] ?? '';
$coachLogin = http_req("$baseUrl/login.php", 'POST', ['csrf_token' => $csrf2, 'username' => $coachUser, 'password' => 'password123'], $coachCookie);
assert_test($coachLogin['code'] === 302, "Coach logged in");

// 3. Fetch index.php as Coach and Owner
$ownerResp = http_req("$baseUrl/index.php", 'GET', null, $adminCookie);
$coachResp = http_req("$baseUrl/index.php", 'GET', null, $coachCookie);

assert_test($ownerResp['code'] === 200, "Owner index.php returned 200");
assert_test($coachResp['code'] === 200, "Coach index.php returned 200");

// Both see Coach Terdaftar
assert_test(strpos($coachResp['body'], 'Coach Terdaftar') !== false, "Coach sees 'Coach Terdaftar' card on dashboard");
assert_test(strpos($ownerResp['body'], 'Coach Terdaftar') !== false, "Owner sees 'Coach Terdaftar' card on dashboard");

// Both see Murid Dilatih per Coach
assert_test(strpos($coachResp['body'], 'Murid Dilatih per Coach') !== false, "Coach sees 'Murid Dilatih per Coach' table on dashboard");
assert_test(strpos($ownerResp['body'], 'Murid Dilatih per Coach') !== false, "Owner sees 'Murid Dilatih per Coach' table on dashboard");

// Both see Coach Pembina in target breakdown modal
assert_test(strpos($coachResp['body'], '<th>Coach Pembina</th>') !== false, "Coach sees 'Coach Pembina' in modal table");
assert_test(strpos($ownerResp['body'], '<th>Coach Pembina</th>') !== false, "Owner sees 'Coach Pembina' in modal table");

// 4. Fetch pertemuan.php as Coach and Owner
$coachPertemuan = http_req("$baseUrl/pertemuan.php", 'GET', null, $coachCookie);
assert_test($coachPertemuan['code'] === 200, "Coach pertemuan.php returned 200");
assert_test(strpos($coachPertemuan['body'], 'data-live-filter-col="0"') !== false, "Coach has Periode Bulan filter in pertemuan.php");
assert_test(strpos($coachPertemuan['body'], 'data-live-filter-col="1"') !== false, "Coach has Nama Murid filter in pertemuan.php");
assert_test(strpos($coachPertemuan['body'], 'data-live-filter-col="2"') !== false, "Coach has Status Materi Lagu filter in pertemuan.php");
assert_test(strpos($coachPertemuan['body'], 'data-live-filter-col="3"') !== false, "Coach has Coach filter in pertemuan.php");

// Cleanup test coach
if (!empty($coachCreate['id'])) {
    $adminService->delete((int)$coachCreate['id'], 1);
}
@unlink($adminCookie);
@unlink($coachCookie);

echo "\n=======================================================\n";
echo ">>> ALL TESTS PASSED (100% GREEN) <<<\n";
echo "=======================================================\n";
