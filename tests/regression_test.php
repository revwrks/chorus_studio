<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}
/**
 * Comprehensive Regression Test Suite for Chorus Studio
 * Tests all CRUD operations for role 'coach' on Murid and Lagu,
 * and tests error handling for unauthorized access without 404s.
 */

$baseUrl = 'http://localhost/chorus_studio';
$cookieFile = sys_get_temp_dir() . '/cookie_regression_' . uniqid() . '.txt';

function http_req($method, $url, $postData = null, $followRedirect = false) {
    global $cookieFile;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $followRedirect);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if ($postData !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headerStr = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    curl_close($ch);

    $location = null;
    if (preg_match('/^Location:\s*(.+)$/mi', $headerStr, $m)) {
        $location = trim($m[1]);
    }

    return [
        'code' => $httpCode,
        'effective_url' => $effectiveUrl,
        'location' => $location,
        'headers' => $headerStr,
        'body' => $body
    ];
}

require __DIR__ . '/../includes/db.php';

// Prepare coach account credentials
$testPass = 'coach123';
$hash = password_hash($testPass, PASSWORD_BCRYPT, ['cost' => 10]);
$pdo->prepare("UPDATE admin SET password=?, role='coach' WHERE username='reva'")->execute([$hash]);

echo "=======================================================\n";
echo "CHORUS STUDIO REGRESSION TEST SUITE (COACH ROLE CRUD)\n";
echo "=======================================================\n";

// 1. LOGIN AS COACH
echo "\n[TEST 1] Logging in as coach (reva)...\n";
$loginPage = http_req('GET', "$baseUrl/login.php");
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $loginPage['body'], $m);
$csrfToken = $m[1] ?? '';
if (!$csrfToken) die("FAILED: Cannot fetch CSRF from login page.\n");

$loginResp = http_req('POST', "$baseUrl/login.php", [
    'csrf_token' => $csrfToken,
    'username' => 'reva',
    'password' => $testPass
]);
if ($loginResp['code'] !== 302) {
    die("FAILED: Login expected 302, got {$loginResp['code']}\n");
}
echo "PASS: Coach logged in.\n";

// Fetch CSRF for operations from murid.php
$muridPage = http_req('GET', "$baseUrl/murid.php");
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $muridPage['body'], $m);
$csrfToken = $m[1] ?? '';
if (!$csrfToken) die("FAILED: Cannot fetch CSRF from murid page.\n");

$errors = [];

// 2. COACH CREATE MURID
echo "\n[TEST 2] Coach: Create Murid...\n";
$muridName = 'Murid_RegTest_' . time();
$createMurid = http_req('POST', "$baseUrl/murid.php", [
    'csrf_token' => $csrfToken,
    'action' => 'create',
    'nama' => $muridName,
    'instagram' => '@testmurid'
]);

$stmt = $pdo->prepare("SELECT id FROM murid WHERE nama=?");
$stmt->execute([$muridName]);
$createdMurid = $stmt->fetch();

if (!$createdMurid) {
    $errors[] = "TEST 2 FAILED: Murid '$muridName' was NOT inserted into database. Location: " . ($createMurid['location'] ?? 'NONE');
    echo "FAIL: Murid not inserted! Location: " . ($createMurid['location'] ?? 'NONE') . "\n";
} else {
    echo "PASS: Murid created with ID {$createdMurid['id']}.\n";
}

// 3. COACH UPDATE MURID
if ($createdMurid) {
    echo "\n[TEST 3] Coach: Update Murid...\n";
    $updatedName = $muridName . '_UPDATED';
    $updateMurid = http_req('POST', "$baseUrl/murid.php", [
        'csrf_token' => $csrfToken,
        'action' => 'update',
        'id' => $createdMurid['id'],
        'nama' => $updatedName,
        'instagram' => '@updatedig'
    ]);

    $stmt = $pdo->prepare("SELECT nama FROM murid WHERE id=?");
    $stmt->execute([$createdMurid['id']]);
    $currentName = $stmt->fetchColumn();

    if ($currentName !== $updatedName) {
        $errors[] = "TEST 3 FAILED: Murid was not updated. Expected '$updatedName', got '$currentName'";
        echo "FAIL: Murid not updated.\n";
    } else {
        echo "PASS: Murid updated successfully.\n";
    }

    // 4. COACH DELETE MURID
    echo "\n[TEST 4] Coach: Delete Murid...\n";
    $delMurid = http_req('POST', "$baseUrl/murid.php", [
        'csrf_token' => $csrfToken,
        'action' => 'delete',
        'id' => $createdMurid['id']
    ]);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM murid WHERE id=?");
    $stmt->execute([$createdMurid['id']]);
    if ((int)$stmt->fetchColumn() > 0) {
        $errors[] = "TEST 4 FAILED: Murid ID {$createdMurid['id']} was not deleted!";
        echo "FAIL: Murid not deleted.\n";
    } else {
        echo "PASS: Murid deleted successfully.\n";
    }
}

// 5. COACH CREATE LAGU
echo "\n[TEST 5] Coach: Create Lagu...\n";
$laguTitle = 'Lagu_RegTest_' . time();
$createLagu = http_req('POST', "$baseUrl/lagu.php", [
    'csrf_token' => $csrfToken,
    'action' => 'create',
    'judul' => $laguTitle,
    'tingkat' => 'mudah'
]);

$stmt = $pdo->prepare("SELECT id_lagu FROM lagu WHERE judul=?");
$stmt->execute([$laguTitle]);
$createdLagu = $stmt->fetch();

if (!$createdLagu) {
    $errors[] = "TEST 5 FAILED: Lagu '$laguTitle' was NOT inserted into database. Location: " . ($createLagu['location'] ?? 'NONE');
    echo "FAIL: Lagu not inserted! Location: " . ($createLagu['location'] ?? 'NONE') . "\n";
} else {
    echo "PASS: Lagu created with ID {$createdLagu['id_lagu']}.\n";
}

// 6. COACH UPDATE LAGU
if ($createdLagu) {
    echo "\n[TEST 6] Coach: Update Lagu...\n";
    $updatedTitle = $laguTitle . '_UPDATED';
    $updateLagu = http_req('POST', "$baseUrl/lagu.php", [
        'csrf_token' => $csrfToken,
        'action' => 'update',
        'id_lagu' => $createdLagu['id_lagu'],
        'judul' => $updatedTitle,
        'tingkat' => 'sedang'
    ]);

    $stmt = $pdo->prepare("SELECT judul, tingkat FROM lagu WHERE id_lagu=?");
    $stmt->execute([$createdLagu['id_lagu']]);
    $currentLagu = $stmt->fetch();

    if (!$currentLagu || $currentLagu['judul'] !== $updatedTitle || $currentLagu['tingkat'] !== 'sedang') {
        $errors[] = "TEST 6 FAILED: Lagu was not updated properly.";
        echo "FAIL: Lagu not updated.\n";
    } else {
        echo "PASS: Lagu updated successfully.\n";
    }

    // 7. COACH DELETE LAGU
    echo "\n[TEST 7] Coach: Delete Lagu...\n";
    $delLagu = http_req('POST', "$baseUrl/lagu.php", [
        'csrf_token' => $csrfToken,
        'action' => 'delete',
        'id' => $createdLagu['id_lagu']
    ]);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM lagu WHERE id_lagu=?");
    $stmt->execute([$createdLagu['id_lagu']]);
    if ((int)$stmt->fetchColumn() > 0) {
        $errors[] = "TEST 7 FAILED: Lagu ID {$createdLagu['id_lagu']} was not deleted!";
        echo "FAIL: Lagu not deleted.\n";
    } else {
        echo "PASS: Lagu deleted successfully.\n";
    }
}

// 8. ERROR HANDLING (NO 404s ON RESTRICTED ACCESS)
echo "\n[TEST 8] Coach unauthorized action -> Proper error handling (No 404)...\n";
$unauth = http_req('POST', "$baseUrl/admin.php", [
    'csrf_token' => $csrfToken,
    'action' => 'create',
    'username' => 'hack_attempt',
    'nama' => 'Hacker',
    'role' => 'admin'
], true);

echo "Admin Final URL: {$unauth['effective_url']}\n";
echo "Admin Final HTTP Status: {$unauth['code']}\n";

if ($unauth['code'] === 404) {
    $errors[] = "TEST 8 FAILED: Unauthorized request to admin resulted in 404 page!";
    echo "FAIL: 404 page encountered on unauthorized redirect!\n";
} else {
    echo "PASS: Did not hit 404 on unauthorized action.\n";
}

$unauthBulk = http_req('GET', "$baseUrl/generate_bulk_pdf.php", null, true);
echo "Bulk PDF Final URL: {$unauthBulk['effective_url']}\n";
echo "Bulk PDF Final HTTP Status: {$unauthBulk['code']}\n";

if ($unauthBulk['code'] === 404) {
    $errors[] = "TEST 8 FAILED: Unauthorized request to generate_bulk_pdf resulted in 404 page!";
    echo "FAIL: 404 page encountered on unauthorized redirect to bulk pdf!\n";
} else {
    echo "PASS: Did not hit 404 on unauthorized bulk pdf.\n";
}

@unlink($cookieFile);

echo "\n=======================================================\n";
if (empty($errors)) {
    echo "ALL REGRESSION TESTS PASSED (GREEN)\n";
    echo "=======================================================\n";
    exit(0);
} else {
    echo "REGRESSION FAILURES (RED):\n";
    foreach ($errors as $e) {
        echo " - $e\n";
    }
    echo "=======================================================\n";
    exit(1);
}
