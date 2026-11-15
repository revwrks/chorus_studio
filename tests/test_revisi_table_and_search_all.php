<?php
/**
 * Test suite to verify User Request revisions:
 * 1. Iringan Orang Tua formatted as table
 * 2. Search & filter operating across all records (not limited to 10) with client-side pagination
 * 3. Renaming of 'Refleksi Bulanan' to 'Refleksi' across all pages, services, and PDF reports
 */

require_once __DIR__ . '/../includes/db.php';

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
echo "TEST SUITE: REVISI IRINGAN TABLE, SEARCH ALL, & REFLEKSI\n";
echo "=======================================================\n\n";

// --- PART 1: Lagu Iringan Orang Tua Table ---
echo "--- 1. Testing Lagu Iringan Orang Tua Table Layout ---\n";
$iringanContent = file_exists(__DIR__ . '/../views/iringan/index.php')
    ? file_get_contents(__DIR__ . '/../views/iringan/index.php')
    : file_get_contents(__DIR__ . '/../iringan.php');
assert_test(strpos($iringanContent, 'id="tableIringanOrtu"') !== false, "iringan.php contains #tableIringanOrtu table");
assert_test(strpos($iringanContent, 'data-live-search="#tableIringanOrtu"') !== false, "Live search targets #tableIringanOrtu");
assert_test(strpos($iringanContent, 'data-table-target="#tableIringanOrtu"') !== false, "Filter targets #tableIringanOrtu");
assert_test(strpos($iringanContent, '<th>Judul Lagu</th>') !== false, "Table header contains Judul Lagu");
assert_test(strpos($iringanContent, '<th>Catatan dari Coach</th>') !== false, "Table header contains Catatan dari Coach");
assert_test(strpos($iringanContent, 'Buka Google Drive') !== false, "Table contains Buka Google Drive button");
assert_test(strpos($iringanContent, 'data-pagination-table="#tableIringanOrtu"') !== false, "Table has client-side pagination container");

// --- PART 2: Search & Filter Not Limited to 10 Rows ---
echo "\n--- 2. Testing Search & Filter Operating Across All Records ---\n";
$pages = [
    'murid.php' => ['#tableMurid', '$muridService->getAll()'],
    'lagu.php' => ['#tableLagu', '$laguService->getAll()'],
    'pertemuan.php' => ['#tablePertemuan', 'getPaginated(1, 9999)'],
    'iringan.php' => ['#tableIringan', 'getPaginated(1, 9999)'],
    'refleksi.php' => ['#tableRefleksi', 'getPaginated(1, 9999)'],
    'admin.php' => ['#tableAdmin', '$adminService->getAll()'],
];

foreach ($pages as $file => $spec) {
    [$tableId, $fetcher] = $spec;
    $baseName = str_replace('.php', '', $file);
    $viewPath = __DIR__ . '/../views/' . $baseName . '/index.php';
    $ctrlPath = __DIR__ . '/../src/Controllers/' . ucfirst($baseName) . 'Controller.php';
    $content = (file_exists($viewPath) ? file_get_contents($viewPath) : '') .
               (file_exists($ctrlPath) ? file_get_contents($ctrlPath) : '') .
               file_get_contents(__DIR__ . '/../' . $file);
    assert_test(strpos($content, $fetcher) !== false || strpos($content, '$this->' . ltrim($fetcher, '$')) !== false, "$file fetches all records via $fetcher");
    assert_test(strpos($content, "data-pagination-table=\"$tableId\"") !== false, "$file has pagination-container for $tableId");
    assert_test(strpos($content, 'data-limit="10"') !== false, "$file supports 10 items limit pill");
    assert_test(strpos($content, 'data-limit="all"') !== false, "$file supports 'Semua' limit pill");
}

$footerContent = file_get_contents(__DIR__ . '/../includes/footer.php');
assert_test(strpos($footerContent, 'paginationEl.querySelector') !== false, "footer.php handles client-side pagination slicing");
assert_test(strpos($footerContent, 'table._currentPage = 1') !== false, "footer.php resets page on new search or filter change");

// --- PART 3: Renaming of 'Refleksi Bulanan' -> 'Refleksi' ---
echo "\n--- 3. Testing 'Refleksi Bulanan' -> 'Refleksi' Renaming ---\n";
$headerContent = file_get_contents(__DIR__ . '/../includes/header.php');
assert_test(strpos($headerContent, '<i class="fa fa-lightbulb me-2"></i>Refleksi</a>') !== false, "header.php sidebar displays 'Refleksi'");
assert_test(strpos($headerContent, 'Refleksi Bulanan') === false, "header.php does NOT contain 'Refleksi Bulanan'");

$refleksiFileContent = file_exists(__DIR__ . '/../views/refleksi/index.php')
    ? file_get_contents(__DIR__ . '/../views/refleksi/index.php')
    : file_get_contents(__DIR__ . '/../refleksi.php');
assert_test(strpos($refleksiFileContent, 'Refleksi Bulanan') === false, "refleksi.php does NOT contain 'Refleksi Bulanan'");
assert_test(strpos($refleksiFileContent, '<i class="fa fa-lightbulb me-2" style="color:var(--gold);"></i>Refleksi</h3>') !== false, "refleksi.php has clean Refleksi header");

$refleksiServiceContent = file_get_contents(__DIR__ . '/../includes/classes/RefleksiService.php');
assert_test(strpos($refleksiServiceContent, 'Refleksi bulanan') === false, "RefleksiService does NOT contain 'Refleksi bulanan'");

$reportPdfContent = file_get_contents(__DIR__ . '/../includes/report_pdf.php');
assert_test(strpos($reportPdfContent, "\$drawSection('Refleksi',") !== false, "report_pdf.php prints 'Refleksi' header");
assert_test(strpos($reportPdfContent, "\$drawSection('Refleksi Bulanan',") === false, "report_pdf.php does NOT print 'Refleksi Bulanan'");

echo "\n=======================================================\n";
if (empty($errors)) {
    echo ">>> ALL USER REQUEST REVISION TESTS PASSED (100%) <<<\n";
    echo "=======================================================\n";
    exit(0);
} else {
    echo ">>> SOME TESTS FAILED: " . count($errors) . " failures <<<\n";
    echo "=======================================================\n";
    exit(1);
}
