<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}
require_once __DIR__ . '/../includes/auth.php';

echo "=======================================================\n";
echo "TEST: MURID STATUS & RENCANA PERTEMUAN SERVICE DOMAIN\n";
echo "=======================================================\n\n";

function assert_test(bool $condition, string $message): void {
    if ($condition) {
        echo "PASS: $message\n";
    } else {
        echo "FAIL: $message\n";
        exit(1);
    }
}

$muridService = new MuridService();
$rencanaService = new RencanaPertemuanService();

// --- 1. Test Murid Status ---
echo "--- 1. Testing Murid Status CRUD & Filtering ---\n";

// Create Active Murid
$resAktif = $muridService->create('Test Murid Aktif', 'test_aktif', null, 'aktif');
assert_test($resAktif['success'] === true, 'Murid aktif created successfully');
$idAktif = $resAktif['id'];

// Create Non-Aktif Murid
$resNonAktif = $muridService->create('Test Murid NonAktif', 'test_nonaktif', null, 'non_aktif');
assert_test($resNonAktif['success'] === true, 'Murid non-aktif created successfully');
$idNonAktif = $resNonAktif['id'];

// Verify status in DB
$dataAktif = $muridService->getById($idAktif);
$dataNonAktif = $muridService->getById($idNonAktif);
assert_test($dataAktif['status'] === 'aktif', 'dataAktif has status aktif');
assert_test($dataNonAktif['status'] === 'non_aktif', 'dataNonAktif has status non_aktif');

// Test update status
$resUpdateStatus = $muridService->updateStatus($idAktif, 'non_aktif');
assert_test($resUpdateStatus['success'] === true, 'updateStatus to non_aktif succeeded');
$dataUpdated = $muridService->getById($idAktif);
assert_test($dataUpdated['status'] === 'non_aktif', 'Murid status successfully updated in DB');

// Revert to aktif
$muridService->updateStatus($idAktif, 'aktif');

// Test getAllActive only returns active students
$allActive = $muridService->getAllActive();
$activeIds = array_column($allActive, 'id');
assert_test(in_array($idAktif, $activeIds), 'idAktif is present in getAllActive');
assert_test(!in_array($idNonAktif, $activeIds), 'idNonAktif is NOT present in getAllActive');


// --- 2. Test RencanaPertemuanService ---
echo "\n--- 2. Testing RencanaPertemuanService ---\n";

$tahunTest = (int)date('Y');
$bulanTest = (int)date('n');

// Set single target
$resSet = $rencanaService->setTarget($idAktif, $tahunTest, $bulanTest, 6, 'Latihan intensif konser');
assert_test($resSet['success'] === true, 'setTarget succeeded');

$targetVal = $rencanaService->getMonthlyTargetForMurid($idAktif, $tahunTest, $bulanTest);
assert_test($targetVal === 6, 'getMonthlyTargetForMurid returns correct target (6)');

// Test getListForPeriod only fetches active students
$listPeriod = $rencanaService->getListForPeriod($tahunTest, $bulanTest);
$listMuridIds = array_column($listPeriod, 'murid_id');
assert_test(in_array($idAktif, $listMuridIds), 'Active student is included in getListForPeriod');
assert_test(!in_array($idNonAktif, $listMuridIds), 'Non-active student is excluded from getListForPeriod');

// Find active student in list
$studentRow = null;
foreach ($listPeriod as $row) {
    if ($row['murid_id'] === $idAktif) {
        $studentRow = $row;
        break;
    }
}
assert_test($studentRow !== null && (int)$studentRow['target_pertemuan'] === 6, 'Student row in period list has target 6');
assert_test($studentRow['catatan'] === 'Latihan intensif konser', 'Student row has correct catatan');

// Test batchSetTarget
$batchData = [
    ['id_murid' => $idAktif, 'target_pertemuan' => 8, 'catatan' => 'Update via batch']
];
$resBatch = $rencanaService->batchSetTarget($batchData, $tahunTest, $bulanTest);
assert_test($resBatch['success'] === true, 'batchSetTarget succeeded');
$targetUpdated = $rencanaService->getMonthlyTargetForMurid($idAktif, $tahunTest, $bulanTest);
assert_test($targetUpdated === 8, 'batchSetTarget updated target to 8');

// Test Period Options Generator
echo "\n--- 3. Testing Period Options Generator ---\n";
$periodOptions = RencanaPertemuanService::getPeriodOptions($tahunTest, $bulanTest);
assert_test(isset($periodOptions['current']) && isset($periodOptions['next']), 'Period options has current and next keys');
assert_test(count($periodOptions['options']) >= 12, 'Period options has at least 12 months ahead');
assert_test($periodOptions['options'][0]['is_current'] === true, 'First option is marked as current');

// Test Summary KPI & Modal data
echo "\n--- 4. Testing Summary KPI & Modal Breakdown ---\n";
$summary = $rencanaService->getSummary($tahunTest, $bulanTest);
assert_test(isset($summary['total_realisasi']) && isset($summary['total_target']), 'Summary has realisasi and target');
assert_test($summary['total_target'] >= 8, 'Total target includes test student');
assert_test(is_array($summary['detail_list']), 'Summary has detail_list for modal');

// --- 5. Clean up test data ---
echo "\n--- 5. Cleanup ---\n";
$muridService->delete($idAktif);
$muridService->delete($idNonAktif);

// Verify cascade deletion in rencana_pertemuan
$afterTarget = $rencanaService->getMonthlyTargetForMurid($idAktif, $tahunTest, $bulanTest);
assert_test($afterTarget === 0, 'Cascade delete removed target in rencana_pertemuan');

echo "\n=======================================================\n";
echo "ALL SERVICE DOMAIN TESTS PASSED SUCCESSFULLY!\n";
echo "=======================================================\n";
