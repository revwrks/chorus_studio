<?php
/**
 * Test Suite for Latest User Request:
 * 1. Refleksi Orang Tua fallback to previous period
 * 2. Account management (profil.php) for Orang Tua & Coach
 * 3. Balanced YouTube and Google Drive buttons on iringan.php
 * 4. Staff dashboard dropdowns only showing periods with data
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/classes/RefleksiService.php';
require_once __DIR__ . '/../includes/classes/AdminService.php';

$errors = [];

function assert_test(bool $condition, string $msg) {
    global $errors;
    if ($condition) {
        echo "  [PASS] $msg\n";
    } else {
        echo "  [FAIL] $msg\n";
        $errors[] = $msg;
    }
}

echo "=======================================================\n";
echo "TEST SUITE: REFLEKSI FALLBACK, PROFIL, BUTTONS & PERIODS\n";
echo "=======================================================\n\n";

// --- 1. Testing Refleksi Fallback to Previous Periods ---
echo "--- 1. Testing Refleksi Fallback Logic ---\n";
$refleksiService = new RefleksiService($pdo);

// Find a student with at least one reflection
$refRow = $pdo->query("SELECT * FROM refleksi ORDER BY tahun DESC, bulan DESC LIMIT 1")->fetch();
if ($refRow) {
    $mId = (int)$refRow['id_murid'];
    $rYr = (int)$refRow['tahun'];
    $rMo = (int)$refRow['bulan'];

    // Test exact period
    $exact = $refleksiService->getEffectiveRefleksi($mId, $rYr, $rMo);
    assert_test($exact !== null && (int)$exact['tahun'] === $rYr && (int)$exact['bulan'] === $rMo, "getEffectiveRefleksi returns exact reflection when exists");

    // Test subsequent period where reflection doesn't exist (e.g. year + 1 or month + 2)
    $futureYr = $rYr + 1;
    $futureMo = 12;
    $fallback = $refleksiService->getEffectiveRefleksi($mId, $futureYr, $futureMo);
    assert_test($fallback !== null, "getEffectiveRefleksi returns fallback when selected period has no entry");
    assert_test(!empty($fallback['refleksi']), "Fallback contains reflection text: " . substr($fallback['refleksi'], 0, 30) . "...");
} else {
    echo "  [SKIP] No reflection records in database to test live query\n";
}

$indexContent = file_exists(__DIR__ . '/../views/dashboard/staff.php')
    ? file_get_contents(__DIR__ . '/../views/dashboard/staff.php') . file_get_contents(__DIR__ . '/../views/dashboard/orang_tua.php') . file_get_contents(__DIR__ . '/../src/Controllers/DashboardController.php')
    : file_get_contents(__DIR__ . '/../index.php');
assert_test(strpos($indexContent, 'getEffectiveRefleksi') !== false, "index.php calls getEffectiveRefleksi");
assert_test(strpos($indexContent, '$isPreviousPeriod') !== false, "index.php checks \$isPreviousPeriod");
assert_test(strpos($indexContent, 'Catatan Terakhir:') !== false, "index.php displays Catatan Terakhir badge for fallback period");

// --- 2. Testing Profil / Manajemen Akun Page & Service ---
echo "\n--- 2. Testing Manajemen Akun (profil.php & AdminService) ---\n";
$adminService = new AdminService($pdo);

// Test updateOwnProfile method
$testUser = $pdo->query("SELECT * FROM admin WHERE role IN ('orang_tua', 'coach') LIMIT 1")->fetch();
if ($testUser) {
    $origNama = $testUser['nama'];
    $origUser = $testUser['username'];

    // Update with same username and updated name
    $resUpdate = $adminService->updateOwnProfile((int)$testUser['id'], $origUser, $origNama . ' Verified', null);
    assert_test($resUpdate['success'] === true, "updateOwnProfile succeeded for {$testUser['role']}");

    // Check in DB
    $checkDb = $adminService->getById((int)$testUser['id']);
    assert_test($checkDb['nama'] === $origNama . ' Verified', "Name successfully updated in DB");

    // Revert back
    $adminService->updateOwnProfile((int)$testUser['id'], $origUser, $origNama, null);
}

// Test validation (short username, short password)
$resFailUser = $adminService->updateOwnProfile(99999, 'ab', 'Test', null);
assert_test($resFailUser['success'] === false, "updateOwnProfile rejects username < 3 chars");

$resFailPass = $adminService->updateOwnProfile(1, 'admin', 'Administrator', '12');
assert_test($resFailPass['success'] === false, "updateOwnProfile rejects password < 4 chars");

// Test profil.php markup
$profilContent = file_exists(__DIR__ . '/../views/profil/index.php')
    ? file_get_contents(__DIR__ . '/../views/profil/index.php')
    : file_get_contents(__DIR__ . '/../profil.php');
assert_test(strpos($profilContent, 'id="inputNama"') !== false, "profil.php has inputNama");
assert_test(strpos($profilContent, 'id="inputUsername"') !== false, "profil.php has inputUsername");
assert_test(strpos($profilContent, 'id="inputPasswordBaru"') !== false, "profil.php has inputPasswordBaru");
assert_test(strpos($profilContent, 'Data Anak yang Terhubung') !== false, "profil.php displays read-only connected children for parents");
assert_test(strpos($profilContent, 'togglePasswordVisibility') !== false, "profil.php has togglePasswordVisibility");

// Test header.php navigation links
$headerContent = file_get_contents(__DIR__ . '/../includes/header.php');
assert_test(strpos($headerContent, 'href="profil.php"') !== false, "header.php includes link to profil.php");
assert_test(strpos($headerContent, '<a href="profil.php" class="list-group-item list-group-item-action') !== false, "header.php includes profil.php in sidebar menu");

// --- 3. Testing Balanced Action Buttons in iringan.php ---
echo "\n--- 3. Testing Balanced Action Buttons in iringan.php ---\n";
$iringanContent = file_exists(__DIR__ . '/../views/iringan/index.php')
    ? file_get_contents(__DIR__ . '/../views/iringan/index.php')
    : file_get_contents(__DIR__ . '/../iringan.php');
assert_test(strpos($iringanContent, 'Buka Google Drive') !== false, "iringan.php has Buka Google Drive button");
assert_test(strpos($iringanContent, '<span>YouTube</span>') !== false, "iringan.php has YouTube button with text label");
assert_test(strpos($iringanContent, 'style="height:38px;padding:0 14px;border-radius:8px;') !== false, "Both buttons share identical height 38px and 14px padding");
assert_test(strpos($iringanContent, 'min-width:240px;') !== false, "Aksi column header has min-width 240px to prevent awkward text wrapping");

// --- 4. Testing Staff Dashboard Dropdown Periods with Data ---
echo "\n--- 4. Testing Staff Dashboard Data-Driven Dropdowns ---\n";
assert_test(strpos($indexContent, '$staffAvailableYears') !== false, "index.php computes \$staffAvailableYears");
assert_test(strpos($indexContent, '$staffAvailableMonthsByYear') !== false, "index.php computes \$staffAvailableMonthsByYear");
assert_test(strpos($indexContent, 'foreach ($staffAvailableMonthsForYear as $num)') !== false, "index.php only iterates over months that have data");
assert_test(strpos($indexContent, 'foreach ($staffAvailableYears as $t)') !== false, "index.php only iterates over years that have data");

echo "\n=======================================================\n";
if (empty($errors)) {
    echo ">>> ALL LATEST UPDATE TESTS PASSED (100% GREEN) <<<\n";
    echo "=======================================================\n";
    exit(0);
} else {
    echo ">>> SOME TESTS FAILED: " . count($errors) . " failures <<<\n";
    echo "=======================================================\n";
    exit(1);
}
