<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Services\AdminService;

class ProfilController extends BaseController
{
    private AdminService $adminService;

    public function __construct(?AdminService $adminService = null)
    {
        parent::__construct();
        $this->adminService = $adminService ?? new AdminService($this->pdo);
    }

    public function handle(): void
    {
        $this->requireLogin();
        $currentId = (int)$this->currentUserId();

        $userData = $this->adminService->getById($currentId);
        if (!$userData) {
            $this->setFlash('danger', 'Akun pengguna tidak ditemukan.');
            $this->redirect('index.php');
        }

        $roleKey = $userData['role'] ?? 'orang_tua';
        $roleLabels = [
            'owner' => 'Owner',
            'admin' => 'Admin',
            'coach' => 'Coach',
            'orang_tua' => 'Orang Tua',
        ];
        $roleLabel = $roleLabels[$roleKey] ?? ucfirst($roleKey);

        $connectedKids = Auth::isOrangTua() ? Auth::getOrangTuaAnakList($currentId) : [];

        if ($this->isPost()) {
            $this->handlePost($currentId);
            return;
        }

        $this->render('profil/index', [
            'userData' => $userData,
            'roleKey' => $roleKey,
            'roleLabel' => $roleLabel,
            'connectedKids' => $connectedKids,
            'currentId' => $currentId,
            'adminService' => $this->adminService,
        ]);
    }

    private function handlePost(int $currentId): void
    {
        $this->verifyCsrf();

        $username = trim($_POST['username'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $passwordBaru = $_POST['password_baru'] ?? '';
        $konfirmasiPassword = $_POST['konfirmasi_password'] ?? '';

        if (!empty($passwordBaru)) {
            if ($passwordBaru !== $konfirmasiPassword) {
                $this->setFlash('danger', 'Password baru dan konfirmasi password tidak cocok.');
                $this->redirect('profil.php');
            }
            if (strlen($passwordBaru) < 4) {
                $this->setFlash('danger', 'Password baru minimal terdiri dari 4 karakter.');
                $this->redirect('profil.php');
            }
        }

        $res = $this->adminService->updateOwnProfile($currentId, $username, $nama, $passwordBaru ?: null);
        $this->setFlash($res['success'] ? 'success' : 'danger', $res['message']);
        $this->redirect('profil.php');
    }
}
