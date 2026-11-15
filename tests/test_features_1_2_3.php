<?php
/**
 * Test Suite for:
 * Fitur 1: Client-side Instant Live Search & Multi-Filter + Live Counter
 * Fitur 2: Client-side Image Compressor & Preview for Student Photos
 * Fitur 3: Modal CRUD via AJAX + Toast Notification (JSON response)
 */

require_once __DIR__ . '/../includes/classes/Database.php';

$baseUrl = 'http://localhost/chorus_studio';
$cookieFile = tempnam(sys_get_temp_dir(), 'chorus_cookie_');

function http_req($url, $method = 'GET', $data = [], $headers = []) {
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
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
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

function extract_csrf($html) {
    if (preg_match('/name="csrf_token"\s+value="([^"]+)"/', $html, $m)) {
        return $m[1];
    }
    return '';
}

function assert_test($cond, $msg) {
    if ($cond) {
        echo "  [PASS] $msg\n";
    } else {
        echo "  [FAIL] $msg\n";
        exit(1);
    }
}

echo "=======================================================\n";
echo "TEST SUITE: FITUR 1, 2, 3 + STUDENT PHOTO PREVIEW\n";
echo "=======================================================\n\n";

// 1. Static Verification of Markup & Client-side Contracts
echo "--- 1. Verification of Frontend Contracts & Guidelines ---\n";

$muridHtml = file_exists(__DIR__ . '/../views/murid/index.php') ? file_get_contents(__DIR__ . '/../views/murid/index.php') : file_get_contents(__DIR__ . '/../murid.php');
$laguHtml = file_exists(__DIR__ . '/../views/lagu/index.php') ? file_get_contents(__DIR__ . '/../views/lagu/index.php') : file_get_contents(__DIR__ . '/../lagu.php');
$iringanHtml = file_exists(__DIR__ . '/../views/iringan/index.php') ? file_get_contents(__DIR__ . '/../views/iringan/index.php') : file_get_contents(__DIR__ . '/../iringan.php');
$pertemuanHtml = file_exists(__DIR__ . '/../views/pertemuan/index.php') ? file_get_contents(__DIR__ . '/../views/pertemuan/index.php') : file_get_contents(__DIR__ . '/../pertemuan.php');
$footerHtml = file_get_contents(__DIR__ . '/../includes/footer.php');
$headerHtml = file_get_contents(__DIR__ . '/../includes/header.php');
$mainCss = file_get_contents(__DIR__ . '/../style/main.css');

// Fitur 1
assert_test(strpos($muridHtml, 'data-live-search="#tableMurid"') !== false, "murid.php has data-live-search target #tableMurid");
assert_test(strpos($muridHtml, 'id="counterMurid"') !== false, "murid.php has live counter badge #counterMurid");
assert_test(strpos($laguHtml, 'data-live-search="#tableLagu"') !== false, "lagu.php has data-live-search target #tableLagu");
assert_test(strpos($laguHtml, 'data-live-filter-col="4"') !== false, "lagu.php has Tingkat live filter column");
assert_test(strpos($laguHtml, 'id="counterLagu"') !== false, "lagu.php has live counter badge #counterLagu");
assert_test(strpos($iringanHtml, 'data-live-search="#tableIringan"') !== false, "iringan.php has data-live-search target #tableIringan");
assert_test(strpos($iringanHtml, 'data-live-filter-col="2"') !== false, "iringan.php has Kategori live filter column");
assert_test(strpos($iringanHtml, 'id="counterIringan"') !== false, "iringan.php has live counter badge #counterIringan");
assert_test(strpos($pertemuanHtml, 'data-live-search="#tablePertemuan"') !== false, "pertemuan.php has data-live-search target #tablePertemuan");
assert_test(strpos($pertemuanHtml, 'id="counterPertemuan"') !== false, "pertemuan.php has live counter badge #counterPertemuan");

// Fitur 2
assert_test(strpos($muridHtml, 'data-preview-target="#previewAdd"') !== false, "murid.php Add Modal has data-preview-target");
assert_test(strpos($muridHtml, 'data-placeholder-target="#placeholderAdd"') !== false, "murid.php Add Modal has data-placeholder-target");
assert_test(strpos($muridHtml, 'data-compress-image') !== false, "murid.php has data-compress-image");
assert_test(strpos($muridHtml, 'data-compress-info="#compressInfoAdd"') !== false, "murid.php has data-compress-info element");
assert_test(strpos($muridHtml, 'thumb-preview-circle') !== false, "murid.php uses thumb-preview-circle styling");
assert_test(strpos($muridHtml, 'preview-placeholder-circle') !== false, "murid.php uses preview-placeholder-circle styling");
assert_test(strpos($footerHtml, 'compressImageFile') !== false, "footer.php contains canvas compressor engine compressImageFile()");
assert_test(strpos($mainCss, '.compress-badge') !== false, "main.css defines .compress-badge");

// Fitur 3
assert_test(strpos($headerHtml, 'id="toastContainer"') !== false, "header.php includes #toastContainer");
assert_test(strpos($footerHtml, 'window.showToast') !== false, "footer.php exports window.showToast");
assert_test(strpos($footerHtml, 'form[data-ajax-form]') !== false, "footer.php handles form[data-ajax-form] asynchronously");
assert_test(strpos($footerHtml, 'modalDeleteConfirm') !== false, "footer.php supports AJAX delete confirmation with toast");
assert_test(strpos($muridHtml, 'data-ajax-form data-refresh-region="#muridListRegion"') !== false, "murid.php forms use data-ajax-form & #muridListRegion");
assert_test(strpos($laguHtml, 'data-ajax-form data-refresh-region="#laguListRegion"') !== false, "lagu.php forms use data-ajax-form & #laguListRegion");
assert_test(strpos($iringanHtml, 'data-ajax-form data-refresh-region="#iringanListRegion"') !== false, "iringan.php forms use data-ajax-form & #iringanListRegion");
assert_test(strpos($pertemuanHtml, 'data-ajax-form data-refresh-region="#pertemuanListRegion"') !== false, "pertemuan.php forms use data-ajax-form & #pertemuanListRegion");

echo "\n--- 2. End-to-End AJAX API Verification ---\n";

// Login as admin
$loginPage = http_req("$baseUrl/login.php");
$csrf = extract_csrf($loginPage['body']);
$loginRes = http_req("$baseUrl/login.php", 'POST', [
    'csrf_token' => $csrf,
    'username' => 'admin',
    'password' => 'admin123',
]);
assert_test($loginRes['code'] === 302, "Admin login successful");

// Fetch murid.php to get csrf token
$pageMurid = http_req("$baseUrl/murid.php");
$csrfMurid = extract_csrf($pageMurid['body']);

// AJAX Create Murid
$ajaxMuridRes = http_req("$baseUrl/murid.php", 'POST', [
    'csrf_token' => $csrfMurid,
    'action' => 'create',
    'nama' => 'Test AJAX Student',
    'instagram' => '@testajax',
    'is_ajax' => '1',
], ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);

assert_test($ajaxMuridRes['code'] === 200, "AJAX Create Murid returned HTTP 200");
$jsonMurid = json_decode($ajaxMuridRes['body'], true);
assert_test(is_array($jsonMurid) && !empty($jsonMurid['success']), "AJAX Create Murid response is valid JSON success");

// Find created murid ID
$db = Database::getConnection();
$createdMurid = $db->query("SELECT * FROM murid WHERE nama = 'Test AJAX Student' ORDER BY id DESC LIMIT 1")->fetch();
assert_test(!empty($createdMurid), "Created murid found in database");
$testMuridId = (int)$createdMurid['id'];

// AJAX Create Lagu
$pageLagu = http_req("$baseUrl/lagu.php");
$csrfLagu = extract_csrf($pageLagu['body']);
$ajaxLaguRes = http_req("$baseUrl/lagu.php", 'POST', [
    'csrf_token' => $csrfLagu,
    'action' => 'create',
    'judul' => 'Test AJAX Song',
    'tingkat' => 'mudah',
    'youtube_link' => 'https://youtube.com/watch?v=123',
    'iringan_link' => 'https://drive.google.com/test',
    'is_ajax' => '1',
], ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);

assert_test($ajaxLaguRes['code'] === 200, "AJAX Create Lagu returned HTTP 200");
$jsonLagu = json_decode($ajaxLaguRes['body'], true);
assert_test(is_array($jsonLagu) && !empty($jsonLagu['success']), "AJAX Create Lagu response is valid JSON success");

$createdLagu = $db->query("SELECT * FROM lagu WHERE judul = 'Test AJAX Song' ORDER BY id_lagu DESC LIMIT 1")->fetch();
assert_test(!empty($createdLagu), "Created lagu found in database");
$testLaguId = (int)$createdLagu['id_lagu'];

// AJAX Create Iringan
$pageIringan = http_req("$baseUrl/iringan.php");
$csrfIringan = extract_csrf($pageIringan['body']);
$ajaxIringanRes = http_req("$baseUrl/iringan.php", 'POST', [
    'csrf_token' => $csrfIringan,
    'action' => 'create',
    'id_murid' => $testMuridId,
    'id_lagu' => $testLaguId,
    'judul_iringan' => 'Test AJAX Iringan Track',
    'kategori' => 'latihan',
    'gdrive_link' => 'https://drive.google.com/file/d/test12345/view',
    'catatan' => 'Test notes AJAX',
    'is_ajax' => '1',
], ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);

assert_test($ajaxIringanRes['code'] === 200, "AJAX Create Iringan returned HTTP 200");
$jsonIringan = json_decode($ajaxIringanRes['body'], true);
assert_test(is_array($jsonIringan) && !empty($jsonIringan['success']), "AJAX Create Iringan response is valid JSON success");

$createdIringan = $db->query("SELECT * FROM iringan_murid WHERE judul_iringan = 'Test AJAX Iringan Track' ORDER BY id DESC LIMIT 1")->fetch();
assert_test(!empty($createdIringan), "Created iringan found in database");
$testIringanId = (int)$createdIringan['id'];

// AJAX Create Pertemuan
$pagePertemuan = http_req("$baseUrl/pertemuan.php");
$csrfPertemuan = extract_csrf($pagePertemuan['body']);
$ajaxPertemuanRes = http_req("$baseUrl/pertemuan.php", 'POST', [
    'csrf_token' => $csrfPertemuan,
    'action' => 'create',
    'id_murid' => $testMuridId,
    'id_lagu' => $testLaguId,
    'tanggal' => date('Y-m-d'),
    'catatan' => 'Latihan AJAX session',
    'is_ajax' => '1',
], ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);

assert_test($ajaxPertemuanRes['code'] === 200, "AJAX Create Pertemuan returned HTTP 200");
$jsonPertemuan = json_decode($ajaxPertemuanRes['body'], true);
assert_test(is_array($jsonPertemuan) && !empty($jsonPertemuan['success']), "AJAX Create Pertemuan response is valid JSON success");

$createdPertemuan = $db->query("SELECT * FROM pertemuan WHERE catatan = 'Latihan AJAX session' ORDER BY id DESC LIMIT 1")->fetch();
assert_test(!empty($createdPertemuan), "Created pertemuan found in database");
$testPertemuanId = (int)$createdPertemuan['id'];

// Cleanup test items via AJAX delete
echo "\n--- 3. Testing AJAX Delete & Cleanup ---\n";

$delPertemuan = http_req("$baseUrl/pertemuan.php", 'POST', [
    'csrf_token' => $csrfPertemuan,
    'action' => 'delete',
    'id' => $testPertemuanId,
    'is_ajax' => '1',
], ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);
assert_test($delPertemuan['code'] === 200 && json_decode($delPertemuan['body'], true)['success'], "AJAX Delete Pertemuan successful");

$delIringan = http_req("$baseUrl/iringan.php", 'POST', [
    'csrf_token' => $csrfIringan,
    'action' => 'delete',
    'id' => $testIringanId,
    'is_ajax' => '1',
], ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);
assert_test($delIringan['code'] === 200 && json_decode($delIringan['body'], true)['success'], "AJAX Delete Iringan successful");

$delLagu = http_req("$baseUrl/lagu.php", 'POST', [
    'csrf_token' => $csrfLagu,
    'action' => 'delete',
    'id' => $testLaguId,
    'is_ajax' => '1',
], ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);
assert_test($delLagu['code'] === 200 && json_decode($delLagu['body'], true)['success'], "AJAX Delete Lagu successful");

$delMurid = http_req("$baseUrl/murid.php", 'POST', [
    'csrf_token' => $csrfMurid,
    'action' => 'delete',
    'id' => $testMuridId,
    'is_ajax' => '1',
], ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);
assert_test($delMurid['code'] === 200 && json_decode($delMurid['body'], true)['success'], "AJAX Delete Murid successful");

@unlink($cookieFile);
echo "\n=======================================================\n";
echo ">>> ALL FITUR 1, 2, 3 TESTS PASSED (100% GREEN) <<<\n";
echo "=======================================================\n";
