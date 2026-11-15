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
            if (isset($_SESSION['login_lockout_time']) && $_SESSION['login_lockout_time'] > time()) {
                $remaining = $_SESSION['login_lockout_time'] - time();
                $err = 'Terlalu banyak percobaan login gagal. Silakan coba lagi dalam ' . $remaining . ' detik.';
            } else {
                $this->verifyCsrf();
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';

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

                    unset($_SESSION['login_attempts']);
                    unset($_SESSION['login_lockout_time']);

                    ActivityLogService::log('LOGIN', 'Pengguna ' . ($a['nama'] ?: $a['username']) . ' (' . $a['username'] . ') berhasil login ke sistem', 'info', (int)$a['id'], $a['nama'] ?: $a['username'], $a['role'] ?? 'admin');

                    $this->redirect('index.php');
                } else {
                    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                    ActivityLogService::log('LOGIN_GAGAL', 'Percobaan login gagal untuk username: ' . $username, 'danger', null, $username, 'guest');
                    if ($_SESSION['login_attempts'] >= 5) {
                        $_SESSION['login_lockout_time'] = time() + 120;
                        $err = 'Terlalu banyak percobaan login gagal. Anda diblokir selama 2 menit.';
                    } else {
                        $err = 'Login gagal. Sisa percobaan: ' . (5 - $_SESSION['login_attempts']);
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
        Auth::logout();
        $this->redirect('login.php');
    }
}
