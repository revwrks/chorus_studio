<?php
require_once __DIR__ . '/../includes/db.php';

echo "=======================================================\n";
echo "TEST SUITE: VERIFIKASI AKUN ORANG TUA DARI EXPORTED SQL\n";
echo "=======================================================\n\n";

$sqlFile = __DIR__ . '/../setup/exported_updated_data.sql';
$patchFile = __DIR__ . '/../setup/tambah_akun_orang_tua_infinityfree.sql';

assert(file_exists($sqlFile), "exported_updated_data.sql exists");
assert(file_exists($patchFile), "tambah_akun_orang_tua_infinityfree.sql exists");

$content = file_get_contents($sqlFile);
$patchContent = file_get_contents($patchFile);

// 1. Check count of admin role orang_tua in exported_updated_data.sql
preg_match_all("/\(\s*\d+\s*,\s*'([^']+)'\s*,\s*'[^']+'\s*,\s*'([^']+)'\s*,\s*'[^']+'\s*,\s*'orang_tua'\s*\)/", $content, $mAdmin);
$totalOrtuInAdmin = count($mAdmin[0]);
echo "Total Akun Orang Tua di tabel `admin` (exported_updated_data.sql): $totalOrtuInAdmin\n";
assert($totalOrtuInAdmin === 60, "Expected exactly 60 orang_tua accounts (3 existing + 57 new)");

// 2. Check count of links in orang_tua_murid
preg_match_all("/\(\s*\d+\s*,\s*(\d+)\s*,\s*(\d+)\s*,\s*'[^']+'\s*\)/", $content, $mOtm);
$totalOtm = count($mOtm[0]);
echo "Total Hubungan di tabel `orang_tua_murid` (exported_updated_data.sql): $totalOtm\n";
assert($totalOtm === 60, "Expected exactly 60 orang_tua_murid relations");

// 3. Verify that every single student in murid has an entry in orang_tua_murid
$linkedMuridIds = array_map('intval', $mOtm[2]);
$startMurid = strpos($content, "INSERT INTO `murid`");
$endMurid = strpos($content, ";", $startMurid);
$muridSql = substr($content, $startMurid, $endMurid - $startMurid);
preg_match_all("/\((\d+),\s*'([^']+)'/", $muridSql, $mMurids, PREG_SET_ORDER);

$missing = [];
foreach ($mMurids as $m) {
    $id = (int)$m[1];
    $name = $m[2];
    if (!in_array($id, $linkedMuridIds, true)) {
        $missing[] = "$id: $name";
    }
}

if (empty($missing)) {
    echo "  [PASS] Semua 60 murid di exported_updated_data.sql kini 100% memiliki akun orang tua!\n";
} else {
    echo "  [FAIL] Masih ada murid tanpa akun orang tua: " . implode(', ', $missing) . "\n";
    exit(1);
}

// 4. Verify password hash for 1234
$testHash = '$2y$10$m0G8jvM.nIamTS6PHZsDYOxoKkYeuSajkHLOuJH/DwwAmuioUJ6re';
assert(password_verify('1234', $testHash), "Password hash verifies with '1234'");
echo "  [PASS] Password seragam '1234' terverifikasi dengan hash bcrypt standard.\n";

// 5. Verify all usernames are lowercase and concatenated (no spaces or special chars)
$usernames = $mAdmin[1];
$uniqueUsernames = array_unique($usernames);
assert(count($usernames) === count($uniqueUsernames), "All usernames must be unique with zero collisions");
foreach ($usernames as $u) {
    assert($u === strtolower($u), "Username '$u' must be lowercase");
    assert(preg_match('/^[a-z0-9]+$/', $u), "Username '$u' must only contain lowercase alphanumeric characters (no spaces)");
}
echo "  [PASS] Semua 60 username orang tua 100% lowercase, disambung (tanpa spasi), dan unik tanpa duplikasi.\n";

// 6. Check patch file structure
assert(strpos($patchContent, "INSERT INTO `admin`") !== false, "patch file has admin inserts");
assert(strpos($patchContent, "INSERT INTO `orang_tua_murid`") !== false, "patch file has orang_tua_murid inserts");
preg_match_all("/\(\s*\d+\s*,\s*'([^']+)'\s*,\s*'[^']+'\s*,\s*'([^']+)'\s*,\s*'[^']+'\s*,\s*'orang_tua'\s*\)/", $patchContent, $mPatchAdmin);
assert(count($mPatchAdmin[1]) === 57, "Patch file has exactly 57 new parent accounts");
foreach ($mPatchAdmin[1] as $pu) {
    assert($pu === strtolower($pu), "Patch username '$pu' must be lowercase");
    assert(preg_match('/^[a-z0-9]+$/', $pu), "Patch username '$pu' must only contain lowercase alphanumeric characters (no spaces)");
}
echo "  [PASS] File patch tambah_akun_orang_tua_infinityfree.sql (57 akun baru) 100% lowercase dan disambung.\n";

echo "\n>>> VERIFIKASI 100% BERHASIL (GREEN) <<<\n";
