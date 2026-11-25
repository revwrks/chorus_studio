<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Services\ActivityLogService;
use App\Core\Auth;
use App\Core\View;

class AuthController extends BaseController
{
    public function handle(): void
    {
        $this->login();
    }

    public function login(): void
    {
        if (Auth::isLoggedIn()) {
            $this->redirect('index.php');
        }

        $styleVersion = @filemtime(dirname(__DIR__, 2) . '/style/main.css') ?: time();
        $faviconVersion = @filemtime(dirname(__DIR__, 2) . '/favicon.ico') ?: $styleVersion;

        $err = '';
        if ($this->isPost()) {
            $this->verifyCsrf();

            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $rateKey = hash('sha256', $ip . '|' . strtolower(trim($username)));
            $now = time();

            $rateDir = dirname(__DIR__, 2) . '/tmp/ratelimit';
            if (!is_dir($rateDir)) {
                @mkdir($rateDir, 0750, true);
            }
            $useFileLimiter = is_dir($rateDir) && is_writable($rateDir);
            $rateFile = $rateDir . '/' . $rateKey . '.json';

            if ($useFileLimiter && mt_rand(1, 20) === 1) {
                foreach (glob($rateDir . '/*.json') ?: [] as $oldFile) {
                    if (is_file($oldFile) && (@filemtime($oldFile) ?: $now) < ($now - 3600)) {
                        @unlink($oldFile);
                    }
                }
            }

            $rateState = ['attempts' => 0, 'first_attempt' => 0, 'lockout_until' => 0];
            if ($useFileLimiter && is_file($rateFile)) {
                $decoded = json_decode((string)@file_get_contents($rateFile), true);
                if (is_array($decoded)) {
                    $rateState = array_merge($rateState, $decoded);
                }
            } elseif (!$useFileLimiter && isset($_SESSION['login_rate'][$rateKey]) && is_array($_SESSION['login_rate'][$rateKey])) {
                $rateState = array_merge($rateState, $_SESSION['login_rate'][$rateKey]);
            }

            if ((int)$rateState['lockout_until'] > $now) {
                $remaining = (int)$rateState['lockout_until'] - $now;
                $err = 'Terlalu banyak percobaan login gagal. Silakan coba lagi dalam ' . $remaining . ' detik.';
            } else {
                if ((int)$rateState['first_attempt'] > 0 && ($now - (int)$rateState['first_attempt']) > 300) {
                    $rateState = ['attempts' => 0, 'first_attempt' => 0, 'lockout_until' => 0];
                }

                $cleanInput = preg_replace('/[\s_\-]+/', '', strtolower($username));
                $stmt = $this->pdo->prepare('SELECT * FROM admin WHERE username = ? OR REPLACE(LOWER(username), " ", "") = ? LIMIT 1');
                $stmt->execute([$username, $cleanInput]);
                $a = $stmt->fetch();

                if ($a && password_verify($password, $a['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['admin_id'] = $a['id'];
                    $_SESSION['admin_username'] = $a['username'];
                    $_SESSION['admin_nama'] = $a['nama'];
                    $_SESSION['admin_role'] = $a['role'] ?? 'admin';

                    if (($a['role'] ?? '') === 'orang_tua') {
                        $stmtKids = $this->pdo->prepare('SELECT id_murid FROM orang_tua_murid WHERE id_orang_tua = ? ORDER BY id ASC LIMIT 1');
                        $stmtKids->execute([$a['id']]);
                        $_SESSION['active_murid_id'] = (int)$stmtKids->fetchColumn() ?: null;
                    }

                    if ($useFileLimiter && is_file($rateFile)) {
                        @unlink($rateFile);
                    }
                    unset($_SESSION['login_rate'][$rateKey], $_SESSION['login_attempts'], $_SESSION['login_lockout_time']);

                    ActivityLogService::log('LOGIN', 'Pengguna ' . ($a['nama'] ?: $a['username']) . ' (' . $a['username'] . ') berhasil login ke sistem', 'info', (int)$a['id'], $a['nama'] ?: $a['username'], $a['role'] ?? 'admin');

                    $this->redirect('index.php');
                } else {
                    if ((int)$rateState['attempts'] === 0 || ($now - (int)$rateState['first_attempt']) > 300) {
                        $rateState['first_attempt'] = $now;
                        $rateState['attempts'] = 1;
                    } else {
                        $rateState['attempts'] = (int)$rateState['attempts'] + 1;
                    }
                    $rateState['lockout_until'] = 0;

                    ActivityLogService::log('LOGIN_GAGAL', 'Percobaan login gagal untuk username: ' . $username, 'danger', null, $username, 'guest');

                    if ($rateState['attempts'] >= 5) {
                        $rateState['lockout_until'] = $now + 300;
                        $err = 'Terlalu banyak percobaan login gagal. Anda diblokir selama 5 menit.';
                    } else {
                        $err = 'Login gagal. Sisa percobaan: ' . (5 - $rateState['attempts']);
                    }

                    if ($useFileLimiter) {
                        @file_put_contents($rateFile, json_encode($rateState), LOCK_EX);
                    } else {
                        $_SESSION['login_rate'][$rateKey] = $rateState;
                    }
                }
            }
        }

        View::render('auth/login', [
            'err' => $err,
            'styleVersion' => $styleVersion,
            'faviconVersion' => $faviconVersion,
        ], false);
    }

    public function logout(): void
    {
        if (!$this->isPost()) {
            if (Auth::isLoggedIn()) {
                $this->redirect('index.php');
            }
            $this->redirect('login.php');
        }
        $this->verifyCsrf();
        Auth::logout();
        $this->redirect('login.php');
    }
}
