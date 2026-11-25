<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

require_once __DIR__ . '/../includes/auth.php';

use App\Core\Auth;
use App\Core\Database;
use App\Services\AdminService;
use App\Services\MuridService;

$baseUrl = 'http://localhost/chorus_studio';
$pdo = Database::getConnection();
$failures = 0;

function assert_sec(bool $condition, string $label, string $detail = ''): void
{
    global $failures;
    if ($condition) {
        echo "  [PASS] {$label}\n";
    } else {
        $failures++;
        echo "  [FAIL] {$label}" . ($detail !== '' ? " ({$detail})" : '') . "\n";
    }
}

function sec_http(string $url, string $method = 'GET', ?array $postData = null, ?string $cookieFile = null, array $headers = []): array
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if ($cookieFile !== null) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }

    if ($postData !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $response = curl_exec($ch);
    if ($response === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return ['code' => 0, 'location' => null, 'headers' => '', 'body' => '', 'error' => $err];
    }

    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerStr = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    curl_close($ch);

    $location = null;
    if (preg_match('/^Location:\s*(.+)$/mi', $headerStr, $m)) {
        $location = trim($m[1]);
    }

    return [
        'code' => $httpCode,
        'location' => $location,
        'headers' => $headerStr,
        'body' => $body,
    ];
}

function extract_csrf_token(string $html): string
{
    if (preg_match('/name="csrf_token"\s+value="([^"]+)"/', $html, $m)) {
        return $m[1];
    }
    return '';
}

$createdMuridIds = [];
$createdAdminIds = [];
$cookieFiles = [];

try {
    echo "=======================================================\n";
    echo "TEST SUITE: SECURITY HARDENING & RBAC VERIFICATION\n";
    echo "=======================================================\n\n";

    // 1. Direct HTTP GET to protected files & directories must return 403
    echo "--- 1. Direct HTTP Access Restrictions (.htaccess) ---\n";
    $protectedPaths = [
        '/src/Core/Database.php',
        '/views/auth/login.php',
        '/setup/demo_seeder.sql',
        '/setup/migration_activity_log.php',
        '/tests/regression_test.php',
        '/config.php',
        '/config.example.php',
        '/README.md',
        '/includes/auth.php',
    ];
    foreach ($protectedPaths as $path) {
        $res = sec_http($baseUrl . $path, 'GET');
        assert_sec($res['code'] === 403, "GET {$path} returns HTTP 403", "got HTTP {$res['code']}");
    }

    // 2. Auth::currentUserRole() returns '' when unset
    echo "\n--- 2. Auth::currentUserRole() Default Fail-Closed ---\n";
    $prevRole = $_SESSION['admin_role'] ?? null;
    unset($_SESSION['admin_role']);
    $roleWhenUnset = Auth::currentUserRole();
    assert_sec($roleWhenUnset === '', "Auth::currentUserRole() returns '' when \$_SESSION['admin_role'] is unset", "got '" . $roleWhenUnset . "'");
    if ($prevRole !== null) {
        $_SESSION['admin_role'] = $prevRole;
    }

    // 3. orang_tua IDOR protection on index.php?action=get_realisasi_detail
    echo "\n--- 3. Parent IDOR Protection on Realisasi Detail ---\n";
    $stmtAria = $pdo->prepare('SELECT id, password FROM admin WHERE username = ? AND role = ? LIMIT 1');
    $stmtAria->execute(['ortu_aria', 'orang_tua']);
    $ortuAria = $stmtAria->fetch(PDO::FETCH_ASSOC);

    $ortuLoginUser = 'ortu_aria';
    $ortuLoginPass = 'ortu123';
    $ortuIdForIdor = $ortuAria ? (int)$ortuAria['id'] : 0;

    if (!$ortuAria || !password_verify($ortuLoginPass, $ortuAria['password'])) {
        // Create temporary parent account linked to one temp student so IDOR test always runs cleanly
        $muridSvcTmp = new MuridService($pdo);
        $linkedRes = $muridSvcTmp->create('SecTest Linked Kid ' . time(), '@linked', null, 'aktif');
        if (!empty($linkedRes['id'])) {
            $createdMuridIds[] = (int)$linkedRes['id'];
            $ortuLoginUser = 'sec_ortu_' . time();
            $ortuLoginPass = 'ortu123';
            $adminSvcTmp = new AdminService($pdo);
            $tmpOrtuRes = $adminSvcTmp->create($ortuLoginUser, 'SecTest Parent', $ortuLoginPass, 'orang_tua', [(int)$linkedRes['id']], 'owner');
            if (!empty($tmpOrtuRes['id'])) {
                $ortuIdForIdor = (int)$tmpOrtuRes['id'];
                $createdAdminIds[] = $ortuIdForIdor;
            }
        }
    }

    $stmtUnlinked = $pdo->prepare(
        'SELECT m.id FROM murid m WHERE m.id NOT IN (SELECT id_murid FROM orang_tua_murid WHERE id_orang_tua = ?) LIMIT 1'
    );
    $stmtUnlinked->execute([$ortuIdForIdor]);
    $unlinkedMuridId = (int)$stmtUnlinked->fetchColumn();
    if ($unlinkedMuridId <= 0) {
        $muridSvcTmp = new MuridService($pdo);
        $unlinkedRes = $muridSvcTmp->create('SecTest Unlinked Kid ' . time(), '@unlinked', null, 'aktif');
        $unlinkedMuridId = (int)($unlinkedRes['id'] ?? 0);
        if ($unlinkedMuridId > 0) {
            $createdMuridIds[] = $unlinkedMuridId;
        }
    }

    $ortuCookie = tempnam(sys_get_temp_dir(), 'sec_ortu_');
    $cookieFiles[] = $ortuCookie;
    $ortuLoginPage = sec_http($baseUrl . '/login.php', 'GET', null, $ortuCookie);
    $ortuCsrf = extract_csrf_token($ortuLoginPage['body']);
    $ortuLoginRes = sec_http($baseUrl . '/login.php', 'POST', [
        'csrf_token' => $ortuCsrf,
        'username' => $ortuLoginUser,
        'password' => $ortuLoginPass,
    ], $ortuCookie);
    assert_sec($ortuLoginRes['code'] === 302 && $ortuLoginRes['location'] === 'index.php', "Parent ({$ortuLoginUser}) logged in successfully", "code={$ortuLoginRes['code']}, loc={$ortuLoginRes['location']}");

    $idorRes = sec_http(
        $baseUrl . '/index.php?action=get_realisasi_detail&murid_id=' . $unlinkedMuridId,
        'GET',
        null,
        $ortuCookie,
        ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']
    );
    $idorJson = json_decode($idorRes['body'], true);
    assert_sec(
        $idorRes['code'] === 403 && is_array($idorJson) && ($idorJson['success'] ?? true) === false,
        "orang_tua requesting unlinked murid_id={$unlinkedMuridId} returns HTTP 403 and success === false",
        "code={$idorRes['code']}, body={$idorRes['body']}"
    );

    // 4. Admin role privilege escalation protection in AdminService
    echo "\n--- 4. AdminService Owner Privilege Escalation Protection ---\n";
    $adminService = new AdminService($pdo);
    $ts = time();
    $tempOwnerUsername = 'sec_owner_' . $ts;
    $tempCoachUsername = 'sec_coach_' . $ts;
    $tempAdminActorUsername = 'sec_admin_' . $ts;

    $ownerRes = $adminService->create($tempOwnerUsername, 'SecTest Owner', 'pass1234', 'owner', [], 'owner');
    assert_sec(!empty($ownerRes['success']) && !empty($ownerRes['id']), 'Setup temp owner succeeded');
    $tempOwnerId = (int)($ownerRes['id'] ?? 0);
    if ($tempOwnerId > 0) $createdAdminIds[] = $tempOwnerId;

    $coachRes = $adminService->create($tempCoachUsername, 'SecTest Coach', 'pass1234', 'coach', [], 'owner');
    assert_sec(!empty($coachRes['success']) && !empty($coachRes['id']), 'Setup temp coach succeeded');
    $tempCoachId = (int)($coachRes['id'] ?? 0);
    if ($tempCoachId > 0) $createdAdminIds[] = $tempCoachId;

    $adminActorRes = $adminService->create($tempAdminActorUsername, 'SecTest Admin', 'pass1234', 'admin', [], 'owner');
    assert_sec(!empty($adminActorRes['success']) && !empty($adminActorRes['id']), 'Setup temp admin actor succeeded');
    $tempAdminActorId = (int)($adminActorRes['id'] ?? 0);
    if ($tempAdminActorId > 0) $createdAdminIds[] = $tempAdminActorId;

    $createOwnerByAdmin = $adminService->create('sec_illegal_owner_' . $ts, 'Illegal Owner', 'pass1234', 'owner', [], 'admin');
    assert_sec($createOwnerByAdmin['success'] === false, "AdminService::create rejects role='owner' when actorRole='admin'");
    if (!empty($createOwnerByAdmin['id'])) {
        $createdAdminIds[] = (int)$createOwnerByAdmin['id'];
    }

    $promoteToOwnerByAdmin = $adminService->update($tempCoachId, $tempCoachUsername, 'SecTest Coach', null, 'owner', [], 'admin');
    assert_sec($promoteToOwnerByAdmin['success'] === false, "AdminService::update rejects promoting coach to 'owner' when actorRole='admin'");

    $editOwnerByAdmin = $adminService->update($tempOwnerId, $tempOwnerUsername, 'Tampered Owner', null, 'admin', [], 'admin');
    assert_sec($editOwnerByAdmin['success'] === false, "AdminService::update rejects editing an 'owner' when actorRole='admin'");

    $deleteOwnerByAdmin = $adminService->delete($tempOwnerId, $tempAdminActorId, 'admin');
    assert_sec($deleteOwnerByAdmin['success'] === false, "AdminService::delete rejects deleting an 'owner' when actorRole='admin'");

    $ownerStillThere = $adminService->getById($tempOwnerId);
    assert_sec($ownerStillThere !== null && $ownerStillThere['role'] === 'owner', 'Temp owner remains intact with role=owner in DB');

    // 5. Coach cannot tamper coach_id on rencana_pertemuan.php (save_single)
    echo "\n--- 5. Coach Tampered coach_id Prevention on Rencana Pertemuan ---\n";
    $muridService = new MuridService($pdo);
    $tempMuridRes = $muridService->create('SecTest Murid ' . $ts, '@sectest', null, 'aktif');
    assert_sec(!empty($tempMuridRes['success']) && !empty($tempMuridRes['id']), 'Setup temp active murid succeeded');
    $tempMuridId = (int)($tempMuridRes['id'] ?? 0);
    if ($tempMuridId > 0) $createdMuridIds[] = $tempMuridId;

    $coachCookie = tempnam(sys_get_temp_dir(), 'sec_coach_');
    $cookieFiles[] = $coachCookie;
    $coachLoginPage = sec_http($baseUrl . '/login.php', 'GET', null, $coachCookie);
    $coachLoginCsrf = extract_csrf_token($coachLoginPage['body']);
    $coachLoginRes = sec_http($baseUrl . '/login.php', 'POST', [
        'csrf_token' => $coachLoginCsrf,
        'username' => $tempCoachUsername,
        'password' => 'pass1234',
    ], $coachCookie);
    assert_sec($coachLoginRes['code'] === 302 && $coachLoginRes['location'] === 'index.php', 'Temp coach logged in via HTTP');

    $rencanaPage = sec_http($baseUrl . '/rencana_pertemuan.php', 'GET', null, $coachCookie);
    $rencanaCsrf = extract_csrf_token($rencanaPage['body']);
    assert_sec($rencanaCsrf !== '', 'Extracted CSRF token from rencana_pertemuan.php');

    $saveTamperRes = sec_http($baseUrl . '/rencana_pertemuan.php', 'POST', [
        'csrf_token' => $rencanaCsrf,
        'action' => 'save_single',
        'id_murid' => $tempMuridId,
        'tahun' => 2099,
        'bulan' => 1,
        'target_pertemuan' => 5,
        'catatan' => 'Security tamper test',
        'coach_id' => $tempOwnerId, // Tampered coach_id belonging to another user
        'is_ajax' => '1',
    ], $coachCookie, ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);
    $saveTamperJson = json_decode($saveTamperRes['body'], true);
    assert_sec($saveTamperRes['code'] === 200 && !empty($saveTamperJson['success']), 'save_single returned JSON success');

    $stmtCheckPlan = $pdo->prepare('SELECT coach_id FROM rencana_pertemuan WHERE id_murid = ? AND tahun = 2099 AND bulan = 1 LIMIT 1');
    $stmtCheckPlan->execute([$tempMuridId]);
    $storedCoachId = (int)$stmtCheckPlan->fetchColumn();
    assert_sec(
        $storedCoachId === $tempCoachId && $storedCoachId !== $tempOwnerId,
        "DB row stores coach's own ID ({$tempCoachId}) instead of tampered coach_id ({$tempOwnerId})",
        "stored={$storedCoachId}"
    );

    // 6. generate_bulk_pdf.php?action=init via GET or POST without CSRF token
    echo "\n--- 6. Bulk PDF Init Method & CSRF Enforcement ---\n";
    $adminCookie = tempnam(sys_get_temp_dir(), 'sec_admin_');
    $cookieFiles[] = $adminCookie;
    $adminLoginPage = sec_http($baseUrl . '/login.php', 'GET', null, $adminCookie);
    $adminLoginCsrf = extract_csrf_token($adminLoginPage['body']);
    $adminLoginRes = sec_http($baseUrl . '/login.php', 'POST', [
        'csrf_token' => $adminLoginCsrf,
        'username' => $tempOwnerUsername,
        'password' => 'pass1234',
    ], $adminCookie);
    assert_sec($adminLoginRes['code'] === 302 && $adminLoginRes['location'] === 'index.php', 'Temp owner logged in via HTTP');

    $bulkGetRes = sec_http($baseUrl . '/generate_bulk_pdf.php?action=init&tahun=2026&bulan=11', 'GET', null, $adminCookie);
    assert_sec(
        in_array($bulkGetRes['code'], [403, 405], true) && $bulkGetRes['code'] !== 200,
        "GET generate_bulk_pdf.php?action=init is blocked (HTTP {$bulkGetRes['code']}, not 200)",
        "got HTTP {$bulkGetRes['code']}"
    );

    $bulkPostNoCsrf = sec_http(
        $baseUrl . '/generate_bulk_pdf.php',
        'POST',
        ['action' => 'init', 'tahun' => 2026, 'bulan' => 11],
        $adminCookie,
        ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']
    );
    assert_sec(
        $bulkPostNoCsrf['code'] === 403,
        'POST generate_bulk_pdf.php?action=init without CSRF token returns HTTP 403',
        "got HTTP {$bulkPostNoCsrf['code']}"
    );

    // 7. logout.php GET vs POST with CSRF
    echo "\n--- 7. Logout CSRF Protection (GET ignored, POST with CSRF logs out) ---\n";
    $logoutGetRes = sec_http($baseUrl . '/logout.php', 'GET', null, $adminCookie);
    assert_sec($logoutGetRes['code'] === 302 && $logoutGetRes['location'] === 'index.php', 'GET logout.php redirects back to index.php without destroying session', "code={$logoutGetRes['code']}, loc={$logoutGetRes['location']}");

    $indexStillLoggedIn = sec_http($baseUrl . '/index.php', 'GET', null, $adminCookie);
    assert_sec($indexStillLoggedIn['code'] === 200, 'Session remains active after GET logout.php (index.php returns HTTP 200)', "got HTTP {$indexStillLoggedIn['code']}");

    $logoutCsrf = extract_csrf_token($indexStillLoggedIn['body']);
    assert_sec($logoutCsrf !== '', 'Extracted CSRF token for POST logout from index.php');

    $logoutPostRes = sec_http($baseUrl . '/logout.php', 'POST', ['csrf_token' => $logoutCsrf], $adminCookie);
    assert_sec($logoutPostRes['code'] === 302 && $logoutPostRes['location'] === 'login.php', 'POST logout.php with valid CSRF redirects to login.php', "code={$logoutPostRes['code']}, loc={$logoutPostRes['location']}");

    $indexAfterLogout = sec_http($baseUrl . '/index.php', 'GET', null, $adminCookie);
    assert_sec($indexAfterLogout['code'] === 302 && $indexAfterLogout['location'] === 'login.php', 'After POST logout, GET index.php redirects to login.php', "code={$indexAfterLogout['code']}, loc={$indexAfterLogout['location']}");

    // 8. error.php ignores arbitrary msg parameter
    echo "\n--- 8. error.php Phishing / Arbitrary Message Injection Protection ---\n";
    $errRes = sec_http($baseUrl . '/error.php?code=403&msg=INJECTED_PHISHING_TEXT', 'GET');
    assert_sec($errRes['code'] === 403, 'error.php?code=403 returns HTTP 403', "got HTTP {$errRes['code']}");
    assert_sec(strpos($errRes['body'], 'INJECTED_PHISHING_TEXT') === false, 'error.php does NOT render INJECTED_PHISHING_TEXT in body');
    assert_sec(strpos($errRes['body'], 'Akses Ditolak (403)') !== false, 'error.php renders clean fixed 403 message');

    // 9. Static checks on views/murid/index.php & assets/js/app.js
    echo "\n--- 9. Frontend XSS & Filter Attribute Contracts ---\n";
    $muridView = file_get_contents(__DIR__ . '/../views/murid/index.php');
    $appJs = file_get_contents(__DIR__ . '/../assets/js/app.js');

    assert_sec(
        strpos($muridView, 'data-status="<?= e($st) ?>" data-filter-val="<?= e($st) ?>"') !== false,
        'views/murid/index.php contains data-filter-val on status cell'
    );
    assert_sec(
        strpos($appJs, 'msgSpan.textContent') !== false,
        'assets/js/app.js uses textContent for toast message'
    );
    assert_sec(
        strpos($appJs, 'createTextNode') !== false && strpos($appJs, 'titleEl.textContent') !== false,
        'assets/js/app.js uses textContent / createTextNode for lightbox title'
    );
    assert_sec(
        strpos($appJs, 'if (e.defaultPrevented) return;') !== false,
        'assets/js/app.js checks e.defaultPrevented on form submit listeners'
    );
} finally {
    // Cleanup all temporary DB records and cookies
    if (!empty($createdMuridIds)) {
        $placeholders = implode(',', array_fill(0, count($createdMuridIds), '?'));
        $pdo->prepare("DELETE FROM rencana_pertemuan WHERE id_murid IN ({$placeholders})")->execute($createdMuridIds);
        $pdo->prepare("DELETE FROM orang_tua_murid WHERE id_murid IN ({$placeholders})")->execute($createdMuridIds);
        $pdo->prepare("DELETE FROM murid WHERE id IN ({$placeholders})")->execute($createdMuridIds);
    }
    if (!empty($createdAdminIds)) {
        $placeholders = implode(',', array_fill(0, count($createdAdminIds), '?'));
        $pdo->prepare("DELETE FROM orang_tua_murid WHERE id_orang_tua IN ({$placeholders})")->execute($createdAdminIds);
        $pdo->prepare("DELETE FROM admin WHERE id IN ({$placeholders})")->execute($createdAdminIds);
    }
    foreach ($cookieFiles as $cf) {
        if (is_file($cf)) {
            @unlink($cf);
        }
    }
}

echo "\n=======================================================\n";
if ($failures === 0) {
    echo ">>> ALL SECURITY HARDENING TESTS PASSED (100% GREEN) <<<\n";
    echo "=======================================================\n";
    exit(0);
}

echo ">>> SECURITY HARDENING TESTS FAILED: {$failures} failure(s) <<<\n";
echo "=======================================================\n";
exit(1);
