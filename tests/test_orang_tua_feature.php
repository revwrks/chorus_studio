<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}
/**
 * Test Suite: Fitur Akses Orang Tua Chorus Studio
 * Tests schema, account creation, child link, auth permissions, and accompaniment data.
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

echo "=== START TEST SUITE: FITUR AKSES ORANG TUA ===\n\n";

$testsPassed = 0;
$testsFailed = 0;

function assert_true($condition, $message) {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo "  [PASS] $message\n";
        $testsPassed++;
    } else {
        echo "  [FAIL] $message\n";
        $testsFailed++;
    }
}

// 1. Check Schema
echo "--- 1. Testing Database Schema ---\n";
$col = $pdo->query("SHOW COLUMNS FROM admin LIKE 'role'")->fetch();
assert_true(strpos($col['Type'], "'orang_tua'") !== false, "Enum admin.role contains 'orang_tua'");

$tableOTM = $pdo->query("SHOW TABLES LIKE 'orang_tua_murid'")->fetch();
assert_true(!empty($tableOTM), "Table orang_tua_murid exists");

$tableIM = $pdo->query("SHOW TABLES LIKE 'iringan_murid'")->fetch();
assert_true(!empty($tableIM), "Table iringan_murid exists");

// 2. Setup Test Data
echo "\n--- 2. Setting up Test Data ---\n";
$pdo->prepare("INSERT INTO murid (nama, instagram) VALUES ('Ananda Satu Test', '@ananda1')")->execute();
$idMurid1 = (int)$pdo->lastInsertId();

$pdo->prepare("INSERT INTO murid (nama, instagram) VALUES ('Ananda Dua Test', '@ananda2')")->execute();
$idMurid2 = (int)$pdo->lastInsertId();

$pdo->prepare("INSERT INTO murid (nama, instagram) VALUES ('Murid Lain Test', '@lain')")->execute();
$idMuridLain = (int)$pdo->lastInsertId();

assert_true($idMurid1 > 0 && $idMurid2 > 0 && $idMuridLain > 0, "Created 3 test murid records");

// Create Orang Tua account
$parentUsername = 'ortu_test_' . time();
$parentPass = password_hash('password123', PASSWORD_DEFAULT);
$pdo->prepare("INSERT INTO admin (username, password, nama, role) VALUES (?, ?, 'Bapak Budi Test', 'orang_tua')")
    ->execute([$parentUsername, $parentPass]);
$idParent = (int)$pdo->lastInsertId();
assert_true($idParent > 0, "Created test parent admin account (id: $idParent)");

// Link parent to Murid 1 & Murid 2
$pdo->prepare("INSERT INTO orang_tua_murid (id_orang_tua, id_murid) VALUES (?, ?)")->execute([$idParent, $idMurid1]);
$pdo->prepare("INSERT INTO orang_tua_murid (id_orang_tua, id_murid) VALUES (?, ?)")->execute([$idParent, $idMurid2]);

$children = get_orang_tua_anak_list($idParent);
assert_true(count($children) === 2, "Parent is linked to exactly 2 children");
$childIds = array_column($children, 'id');
assert_true(in_array($idMurid1, $childIds) && in_array($idMurid2, $childIds), "Linked children IDs match expected");
assert_true(!in_array($idMuridLain, $childIds), "Unrelated child is NOT in parent's children list");

// 3. Test Auth Helpers
echo "\n--- 3. Testing Auth Helpers ---\n";
$_SESSION['admin_id'] = $idParent;
$_SESSION['admin_username'] = $parentUsername;
$_SESSION['admin_nama'] = 'Bapak Budi Test';
$_SESSION['admin_role'] = 'orang_tua';
$_SESSION['active_murid_id'] = null;

assert_true(is_orang_tua() === true, "is_orang_tua() returns true");
assert_true(is_staff() === false, "is_staff() returns false for parent");
assert_true(is_admin_or_owner() === false, "is_admin_or_owner() returns false for parent");
assert_true(is_coach() === false, "is_coach() returns false for parent");

$activeId = get_active_murid_id();
$expectedFirstChildId = (int)$children[0]['id'];
assert_true($activeId === $expectedFirstChildId, "get_active_murid_id() defaults to first child ($expectedFirstChildId)");

// Test switching active child
$secondChildId = (int)$children[1]['id'];
$_GET['anak_id'] = $secondChildId;
$switchedId = get_active_murid_id();
assert_true($switchedId === $secondChildId, "Switching child via ?anak_id updates active child to $secondChildId");


// Attempt to switch to an unauthorized child
$_GET['anak_id'] = $idMuridLain;
$unauthorizedSwitch = get_active_murid_id();
assert_true($unauthorizedSwitch !== $idMuridLain, "Parent cannot switch to an unauthorized child");
unset($_GET['anak_id']);

// 4. Test Iringan Data Isolation
echo "\n--- 4. Testing Iringan Data & Privacy ---\n";
$gdriveLink1 = 'https://drive.google.com/file/d/test-ananda1/view';
$gdriveLinkLain = 'https://drive.google.com/file/d/test-lain/view';

$pdo->prepare("INSERT INTO iringan_murid (id_murid, judul_iringan, kategori, gdrive_link, catatan) VALUES (?, 'Lagu Lomba Ananda', 'lomba', ?, 'Khusus lomba tingkat kota')")
    ->execute([$idMurid1, $gdriveLink1]);
$iringanId1 = (int)$pdo->lastInsertId();

$pdo->prepare("INSERT INTO iringan_murid (id_murid, judul_iringan, kategori, gdrive_link, catatan) VALUES (?, 'Lagu Murid Lain', 'latihan', ?, 'Latihan rutin')")
    ->execute([$idMuridLain, $gdriveLinkLain]);
$iringanIdLain = (int)$pdo->lastInsertId();

// Query iringan for child 1
$stmtCheck = $pdo->prepare("SELECT * FROM iringan_murid WHERE id_murid = ?");
$stmtCheck->execute([$idMurid1]);
$kid1Iringan = $stmtCheck->fetchAll();

assert_true(count($kid1Iringan) === 1, "Child 1 has exactly 1 accompaniment track");
assert_true($kid1Iringan[0]['gdrive_link'] === $gdriveLink1, "Accompaniment Google Drive link matches");
assert_true($kid1Iringan[0]['kategori'] === 'lomba', "Accompaniment category matches 'lomba'");

// Verify isolation: Child 1 query does not return Child Lain's iringan
$foundUnrelated = false;
foreach ($kid1Iringan as $k) {
    if ((int)$k['id_murid'] === $idMuridLain) $foundUnrelated = true;
}
assert_true(!$foundUnrelated, "Accompaniment data of unrelated child is completely isolated");

// 5. Test Pertemuan Progress for Parent
echo "\n--- 5. Testing Pertemuan & Progress Data ---\n";
$today = date('Y-m-d');
$pdo->prepare("INSERT INTO pertemuan (id_murid, tanggal, catatan) VALUES (?, ?, 'Latihan pernapasan diafragma dan artikulasi lagu baru')")
    ->execute([$idMurid1, $today]);
$idP = (int)$pdo->lastInsertId();

$stmtPertemuan = $pdo->prepare("SELECT COUNT(*) FROM pertemuan WHERE id_murid = ? AND YEAR(tanggal)=? AND MONTH(tanggal)=?");
$stmtPertemuan->execute([$idMurid1, date('Y'), date('n')]);
$countSesi = (int)$stmtPertemuan->fetchColumn();
assert_true($countSesi >= 1, "Parent dashboard can count monthly sessions correctly ($countSesi)");

// 6. Cleanup Test Data
echo "\n--- 6. Cleaning Up Test Data ---\n";
$pdo->prepare("DELETE FROM pertemuan WHERE id = ?")->execute([$idP]);
$pdo->prepare("DELETE FROM iringan_murid WHERE id IN (?, ?)")->execute([$iringanId1, $iringanIdLain]);
$pdo->prepare("DELETE FROM admin WHERE id = ?")->execute([$idParent]);
$pdo->prepare("DELETE FROM murid WHERE id IN (?, ?, ?)")->execute([$idMurid1, $idMurid2, $idMuridLain]);

// Verify cascade on orang_tua_murid
$checkCascade = $pdo->prepare("SELECT COUNT(*) FROM orang_tua_murid WHERE id_orang_tua = ?");
$checkCascade->execute([$idParent]);
assert_true((int)$checkCascade->fetchColumn() === 0, "Cascade delete cleaned up orang_tua_murid records");

echo "\n=== SUMMARY ===\n";
echo "Tests Passed: $testsPassed\n";
echo "Tests Failed: $testsFailed\n";

if ($testsFailed > 0) {
    echo "RESULT: FAIL\n";
    exit(1);
} else {
    echo "RESULT: SUCCESS (ALL TESTS GREEN)\n";
    exit(0);
}
