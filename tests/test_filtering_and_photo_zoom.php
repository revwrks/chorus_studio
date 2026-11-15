<?php
/**
 * Test Suite for:
 * 1. Filtering Data on Murid, Lagu, Iringan, Pertemuan, Admin
 * 2. Image Compression Validation (2MB limit check, server safety margin, promise handling)
 * 3. Photo Zoom / Lightbox Viewer for Student Photos (Table & Modal preview)
 */

require_once __DIR__ . '/../includes/classes/Database.php';
require_once __DIR__ . '/../includes/classes/MuridService.php';

function assert_test($cond, $msg) {
    if ($cond) {
        echo "  [PASS] $msg\n";
    } else {
        echo "  [FAIL] $msg\n";
        exit(1);
    }
}

echo "=======================================================\n";
echo "TEST: FILTERING, IMAGE COMPRESSION & PHOTO ZOOM VIEWER\n";
echo "=======================================================\n\n";

$muridHtml = file_exists(__DIR__ . '/../views/murid/index.php') ? file_get_contents(__DIR__ . '/../views/murid/index.php') : file_get_contents(__DIR__ . '/../murid.php');
$laguHtml = file_exists(__DIR__ . '/../views/lagu/index.php') ? file_get_contents(__DIR__ . '/../views/lagu/index.php') : file_get_contents(__DIR__ . '/../lagu.php');
$iringanHtml = file_exists(__DIR__ . '/../views/iringan/index.php') ? file_get_contents(__DIR__ . '/../views/iringan/index.php') : file_get_contents(__DIR__ . '/../iringan.php');
$pertemuanHtml = file_exists(__DIR__ . '/../views/pertemuan/index.php') ? file_get_contents(__DIR__ . '/../views/pertemuan/index.php') : file_get_contents(__DIR__ . '/../pertemuan.php');
$adminHtml = file_exists(__DIR__ . '/../views/admin/index.php') ? file_get_contents(__DIR__ . '/../views/admin/index.php') : file_get_contents(__DIR__ . '/../admin.php');
$footerHtml = file_get_contents(__DIR__ . '/../includes/footer.php');
$cssContent = file_get_contents(__DIR__ . '/../style/main.css');

echo "--- 1. Testing Filtering Markup on All Pages ---\n";

// murid.php filters
assert_test(strpos($muridHtml, 'data-table-target="#tableMurid" data-live-filter-col="1"') !== false, "murid.php has Instagram filter dropdown");
assert_test(strpos($muridHtml, 'value=":has-value">Punya IG') !== false, "murid.php has Punya IG filter option");
assert_test(strpos($muridHtml, 'value=":empty">Tanpa IG') !== false, "murid.php has Tanpa IG filter option");
assert_test(strpos($muridHtml, 'data-table-target="#tableMurid" data-live-filter-col="2"') !== false, "murid.php has Foto filter dropdown");
assert_test(strpos($muridHtml, 'value=":has-value">Ada Foto') !== false, "murid.php has Ada Foto filter option");
assert_test(strpos($muridHtml, 'value=":empty">Tanpa Foto') !== false, "murid.php has Tanpa Foto filter option");

// lagu.php filters
assert_test(strpos($laguHtml, 'data-table-target="#tableLagu" data-live-filter-col="4"') !== false, "lagu.php has Tingkat filter dropdown");
assert_test(strpos($laguHtml, 'data-table-target="#tableLagu" data-live-filter-col="2"') !== false, "lagu.php has YouTube filter dropdown");
assert_test(strpos($laguHtml, 'data-table-target="#tableLagu" data-live-filter-col="3"') !== false, "lagu.php has Iringan filter dropdown");

// iringan.php filters
assert_test(strpos($iringanHtml, 'data-table-target="#tableIringan" data-live-filter-col="2"') !== false, "iringan.php has Kategori filter dropdown");
assert_test(strpos($iringanHtml, 'data-table-target="#tableIringan" data-live-filter-col="4"') !== false, "iringan.php has Catatan filter dropdown");

// pertemuan.php filters
assert_test(strpos($pertemuanHtml, 'data-table-target="#tablePertemuan" data-live-filter-col="2"') !== false, "pertemuan.php has Lagu filter dropdown");

// admin.php filters
assert_test(strpos($adminHtml, 'data-table-target="#tableAdmin" data-live-filter-col="3"') !== false, "admin.php has Role filter dropdown");
assert_test(strpos($adminHtml, 'value="coach">Coach') !== false, "admin.php has Coach role filter option");

// Single Button Filter Container, Trigger, Badge & Reset in all 5 pages
foreach (['murid' => $muridHtml, 'lagu' => $laguHtml, 'iringan' => $iringanHtml, 'pertemuan' => $pertemuanHtml, 'admin' => $adminHtml] as $pageName => $html) {
    assert_test(strpos($html, 'filter-dropdown-container') !== false, "$pageName.php has filter-dropdown-container");
    assert_test(strpos($html, 'btn-filter-trigger') !== false, "$pageName.php has btn-filter-trigger single button");
    assert_test(strpos($html, 'filter-dropdown-menu') !== false, "$pageName.php has filter-dropdown-menu popover");
    assert_test(strpos($html, 'filter-count-badge') !== false, "$pageName.php has filter-count-badge");
    assert_test(strpos($html, 'btn-reset-filters') !== false, "$pageName.php has btn-reset-filters button");
}

// Precision data attributes on table cells
assert_test(strpos($muridHtml, 'data-has-val=') !== false, "murid.php rows have data-has-val for IG/Foto");
assert_test(strpos($laguHtml, 'data-has-val=') !== false, "lagu.php rows have data-has-val for YT/Iringan");
assert_test(strpos($laguHtml, 'data-filter-val=') !== false, "lagu.php rows have data-filter-val for Tingkat");
assert_test(strpos($iringanHtml, 'data-filter-val=') !== false, "iringan.php rows have data-filter-val for Kategori");
assert_test(strpos($iringanHtml, 'data-has-val=') !== false, "iringan.php rows have data-has-val for Catatan");
assert_test(strpos($pertemuanHtml, 'data-has-val=') !== false, "pertemuan.php rows have data-has-val for Lagu");
assert_test(strpos($adminHtml, 'data-filter-val=') !== false, "admin.php rows have data-filter-val for Role");

// CSS styling for Single Filter Button
assert_test(strpos($cssContent, '.btn-filter-trigger') !== false, "main.css defines .btn-filter-trigger");
assert_test(strpos($cssContent, '.filter-dropdown-menu') !== false, "main.css defines .filter-dropdown-menu");
assert_test(strpos($cssContent, '.btn-reset-filters') !== false, "main.css defines .btn-reset-filters");

// footer filter engine support for :has-value, :empty, data-filter-val, and reset handler
assert_test(strpos($footerHtml, "selectedVal === ':has-value'") !== false, "footer.php filter engine supports :has-value matching");
assert_test(strpos($footerHtml, "selectedVal === ':empty'") !== false, "footer.php filter engine supports :empty matching");
assert_test(strpos($footerHtml, 'cleanFilterVal') !== false, "footer.php filter engine normalizes strings with cleanFilterVal");
assert_test(strpos($footerHtml, 'btn-reset-filters') !== false, "footer.php has click listener for btn-reset-filters");

echo "\n--- 2. Testing Photo Zoom / Lightbox Feature ---\n";

assert_test(strpos($muridHtml, 'thumb-zoom-wrapper') !== false, "murid.php table uses thumb-zoom-wrapper");
assert_test(strpos($muridHtml, 'data-zoom-src=') !== false, "murid.php table has data-zoom-src attribute");
assert_test(strpos($muridHtml, 'zoom-badge-icon') !== false, "murid.php table has zoom-badge-icon");
assert_test(strpos($muridHtml, 'btnZoomAdd') !== false, "murid.php Add modal has btnZoomAdd button");
assert_test(strpos($muridHtml, 'btnZoomEdit') !== false, "murid.php Edit modal has btnZoomEdit button");

assert_test(strpos($footerHtml, 'id="modalImageZoom"') !== false, "footer.php includes modalImageZoom Lightbox modal");
assert_test(strpos($footerHtml, 'id="modalImageZoomImg"') !== false, "modalImageZoom contains large image display target");
assert_test(strpos($footerHtml, "closest('[data-zoom-src]')") !== false, "footer.php has click listener for data-zoom-src");

assert_test(strpos($cssContent, '.thumb-zoom-wrapper') !== false, "main.css defines .thumb-zoom-wrapper with zoom-in cursor");
assert_test(strpos($cssContent, '.modal-image-zoom') !== false, "main.css defines .modal-image-zoom styling");

echo "\n--- 3. Testing Image Compression & Upload Limits ---\n";

assert_test(strpos($footerHtml, '_compressionPromise') !== false, "footer.php stores _compressionPromise to prevent race conditions on fast submit");
assert_test(strpos($footerHtml, 'await fileInput._compressionPromise') !== false, "footer.php submit listener awaits any ongoing compression");

$cookieFile = tempnam(sys_get_temp_dir(), 'chorus_cookie_');
$baseUrl = 'http://localhost/chorus_studio';

function http_call($url, $method = 'GET', $data = [], $headers = []) {
    global $cookieFile;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerStr = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    curl_close($ch);

    return ['code' => $httpCode, 'headers' => $headerStr, 'body' => $body];
}

function get_token($html) {
    if (preg_match('/name="csrf_token"\s+value="([^"]+)"/', $html, $m)) {
        return $m[1];
    }
    return '';
}

// 1. Login as admin
$loginPage = http_call("$baseUrl/login.php");
$csrf = get_token($loginPage['body']);
$loginRes = http_call("$baseUrl/login.php", 'POST', [
    'csrf_token' => $csrf,
    'username' => 'admin',
    'password' => 'admin123',
]);
assert_test($loginRes['code'] === 302, "Admin login successful");

// 2. Fetch murid.php for CSRF
$pageMurid = http_call("$baseUrl/murid.php");
$csrfMurid = get_token($pageMurid['body']);

// 3. Create dummy image file
$testImgPath = tempnam(sys_get_temp_dir(), 'test_photo_') . '.jpg';
file_put_contents($testImgPath, base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA='));

$cfile = new CURLFile($testImgPath, 'image/jpeg', 'camera_shot.jpg');
$postData = [
    'csrf_token' => $csrfMurid,
    'action' => 'create',
    'nama' => 'Murid Zoom & Comp Test',
    'instagram' => '@zoom_comp',
    'foto' => $cfile,
    'is_ajax' => '1',
];

$uploadRes = http_call("$baseUrl/murid.php", 'POST', $postData, ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);
assert_test($uploadRes['code'] === 200, "HTTP Photo Upload returned 200");
$jsonRes = json_decode($uploadRes['body'], true);
assert_test(!empty($jsonRes['success']), "HTTP Photo Upload returned JSON success");

$db = Database::getConnection();
$muridInDb = $db->query("SELECT * FROM murid WHERE nama = 'Murid Zoom & Comp Test' ORDER BY id DESC LIMIT 1")->fetch();
assert_test(!empty($muridInDb) && !empty($muridInDb['foto']), "Murid photo saved in database: " . ($muridInDb['foto'] ?? 'none'));

// 4. Test handleFotoUpload enforces max size
$largeFileObj = [
    'name' => 'huge_photo.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => $testImgPath,
    'error' => UPLOAD_ERR_OK,
    'size' => 6 * 1024 * 1024, // 6MB simulated
];
$uploadErr = null;
$largeRes = (new MuridService())->handleFotoUpload($largeFileObj, $uploadErr);
assert_test($largeRes === false && strpos($uploadErr, '5MB') !== false, "handleFotoUpload enforces max ceiling on server: " . $uploadErr);

// Cleanup
if (!empty($muridInDb['id'])) {
    (new MuridService())->delete((int)$muridInDb['id']);
    if (!empty($muridInDb['foto']) && file_exists(__DIR__ . '/../' . $muridInDb['foto'])) {
        @unlink(__DIR__ . '/../' . $muridInDb['foto']);
    }
}
@unlink($testImgPath);
@unlink($cookieFile);

echo "\n=======================================================\n";
echo ">>> ALL FILTERING, COMPRESSION & ZOOM TESTS PASSED <<<\n";
echo "=======================================================\n";
