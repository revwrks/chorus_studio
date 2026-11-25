<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}
/**
 * Test Navbar Child Switcher Contrast & Theming
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

echo "=== TEST NAVBAR CHILD SWITCHER CONTRAST & THEMING ===\n\n";

// 1. Check style/main.css contains theme-aware classes
$css = file_get_contents(__DIR__ . '/../style/main.css');

assert_true(strpos($css, '.nav-child-btn') !== false, "CSS defines .nav-child-btn");
assert_true(strpos($css, '.nav-child-pill') !== false, "CSS defines .nav-child-pill");
assert_true(strpos($css, 'color: var(--text) !important') !== false, "Pill text uses var(--text)");
assert_true(
    preg_match('/html\.theme-dark[^{]*\{[^}]*--surface:[^}]*--text:[^}]*--border:/s', $css) === 1
    && preg_match('/\.nav-child-btn,\s*\.nav-child-pill\s*\{[^}]*var\(--text\)[^}]*var\(--surface\)[^}]*var\(--border\)/s', $css) === 1,
    "CSS defines dark theme tokens (--surface, --text, --border) consumed by .nav-child-btn"
);
assert_true(strpos($css, '.dropdown-menu') !== false, "CSS defines theme-aware .dropdown-menu");

// 2. Check includes/header.php does NOT contain btn-outline-light or hardcoded white text
$header = file_get_contents(__DIR__ . '/../includes/header.php');

assert_true(strpos($header, 'btn-outline-light') === false, "header.php does NOT use btn-outline-light");
assert_true(strpos($header, 'color:rgba(255,255,255') === false, "header.php does NOT hardcode white color for child text");
assert_true(strpos($header, 'nav-child-btn') !== false, "header.php uses nav-child-btn for multi-child");
assert_true(strpos($header, 'nav-child-pill') !== false, "header.php uses nav-child-pill for single-child");

// 3. Test HTTP rendering for a parent with 1 child
$parentCookie = sys_get_temp_dir() . '/cookie_contrast_' . uniqid() . '.txt';

// Create 2 test students: Alexa and Ben
$stmt1 = $pdo->prepare("SELECT id FROM murid WHERE nama = 'Alexa Contrast Test'");
$stmt1->execute();
$alexaId = $stmt1->fetchColumn();
if (!$alexaId) {
    $pdo->prepare("INSERT INTO murid (nama) VALUES ('Alexa Contrast Test')")->execute();
    $alexaId = (int)$pdo->lastInsertId();
}

$stmt2 = $pdo->prepare("SELECT id FROM murid WHERE nama = 'Ben Contrast Test'");
$stmt2->execute();
$benId = $stmt2->fetchColumn();
if (!$benId) {
    $pdo->prepare("INSERT INTO murid (nama) VALUES ('Ben Contrast Test')")->execute();
    $benId = (int)$pdo->lastInsertId();
}

// Create parent account linked to Alexa
$username = 'parent_contrast_' . time();
$pass = 'test1234';
$hash = password_hash($pass, PASSWORD_DEFAULT);
$pdo->prepare("INSERT INTO admin (username, password, nama, role) VALUES (?, ?, 'Mama Alexa', 'orang_tua')")
    ->execute([$username, $hash]);
$parentId = (int)$pdo->lastInsertId();

$pdo->prepare("INSERT INTO orang_tua_murid (id_orang_tua, id_murid) VALUES (?, ?)")
    ->execute([$parentId, $alexaId]);

// Login via HTTP
function http_post($url, $data, $cookieFile) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

function http_get($url, $cookieFile) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

// Fetch login page for CSRF
$loginHtml = http_get('http://localhost/chorus_studio/login.php', $parentCookie);
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $loginHtml, $m);
$csrf = $m[1] ?? '';

// Post login
http_post('http://localhost/chorus_studio/login.php', [
    'username' => $username,
    'password' => $pass,
    'csrf_token' => $csrf
], $parentCookie);

// 3.1 Verify single child navbar
$dashHtml = http_get('http://localhost/chorus_studio/index.php', $parentCookie);
assert_true(strpos($dashHtml, 'nav-child-pill') !== false, "Single child parent gets nav-child-pill in navbar");
assert_true(strpos($dashHtml, 'Alexa Contrast Test') !== false, "Navbar displays Alexa Contrast Test");
assert_true(strpos($dashHtml, 'btn-outline-light') === false, "Navbar has NO btn-outline-light");

// 3.2 Now link second child (Ben) to Mama Alexa to test multi-child dropdown
$pdo->prepare("INSERT INTO orang_tua_murid (id_orang_tua, id_murid) VALUES (?, ?)")
    ->execute([$parentId, $benId]);

$dashHtmlMulti = http_get('http://localhost/chorus_studio/index.php', $parentCookie);
assert_true(strpos($dashHtmlMulti, 'nav-child-btn') !== false, "Multi child parent gets nav-child-btn in navbar");
assert_true(strpos($dashHtmlMulti, 'dropdown-menu') !== false, "Navbar contains dropdown-menu for child selection");
assert_true(strpos($dashHtmlMulti, 'Ben Contrast Test') !== false, "Dropdown contains second child Ben Contrast Test");
assert_true(strpos($dashHtmlMulti, 'btn-outline-light') === false, "Multi-child button has NO btn-outline-light");

// Cleanup
$pdo->prepare("DELETE FROM orang_tua_murid WHERE id_orang_tua = ?")->execute([$parentId]);
$pdo->prepare("DELETE FROM admin WHERE id = ?")->execute([$parentId]);
$pdo->prepare("DELETE FROM murid WHERE id IN (?, ?)")->execute([$alexaId, $benId]);
@unlink($parentCookie);

echo "\n>>> ALL CONTRAST & NAVBAR TESTS PASSED (100% SUKSES) <<<\n";

function assert_true($cond, $msg) {
    if ($cond) {
        echo " [PASS] $msg\n";
    } else {
        echo " [FAIL] $msg\n";
        exit(1);
    }
}
