<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Services\AdminService;
use App\Services\MuridService;

class AdminController extends BaseController
{
    private AdminService $adminService;
    private MuridService $muridService;

    public function __construct(
        ?AdminService $adminService = null,
        ?MuridService $muridService = null
    ) {
        parent::__construct();
        $this->adminService = $adminService ?? new AdminService($this->pdo);
        $this->muridService = $muridService ?? new MuridService($this->pdo);
    }

    public function handle(): void
    {
        $this->requireLogin();
        $this->requireRole(['owner', 'admin']);

        $currentId = $this->currentUserId();

        if ($this->isPost()) {
            $this->handlePost($currentId);
            return;
        }

        $rows = $this->adminService->getAll();
        $ownerCount = $this->adminService->countOwners();
        $allMurid = $this->muridService->getAll();
        $ortuMuridMap = $this->adminService->getOrtuMuridMap();

        $this->render('admin/index', [
            'rows' => $rows,
            'ownerCount' => $ownerCount,
            'allMurid' => $allMurid,
            'ortuMuridMap' => $ortuMuridMap,
            'currentId' => $currentId,
            'adminService' => $this->adminService,
        ]);
    }

    private function handlePost(?int $currentId): void
    {
        $this->verifyCsrf();
        $action = $_POST['action'] ?? '';
        $isAjax = $this->isAjax();

        if ($action === 'create') {
            $muridIds = isset($_POST['murid_ids']) && is_array($_POST['murid_ids']) ? array_map('intval', $_POST['murid_ids']) : [];
            $res = $this->adminService->create(
                $_POST['username'] ?? '',
                $_POST['nama'] ?? '',
                $_POST['password'] ?? '',
                $_POST['role'] ?? 'admin',
                $muridIds
            );
            if ($isAjax) {
                $this->json(['success' => $res['success'], 'message' => $res['message'], 'action' => 'create']);
            }
            $this->setFlash($res['success'] ? 'success' : 'danger', $res['message']);
            $this->redirect('admin.php');
        }

        if ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $muridIds = isset($_POST['murid_ids']) && is_array($_POST['murid_ids']) ? array_map('intval', $_POST['murid_ids']) : [];
            $res = $this->adminService->update(
                $id,
                $_POST['username'] ?? '',
                $_POST['nama'] ?? '',
                !empty($_POST['password']) ? $_POST['password'] : null,
                $_POST['role'] ?? 'admin',
                $muridIds
            );
            if ($isAjax) {
                $this->json(['success' => $res['success'], 'message' => $res['message'], 'action' => 'update']);
            }
            $this->setFlash($res['success'] ? 'success' : 'danger', $res['message']);
            $this->redirect('admin.php');
        }

        if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $res = $this->adminService->delete($id, $currentId);
            if ($isAjax) {
                $this->json(['success' => $res['success'], 'message' => $res['message'], 'action' => 'delete']);
            }
            $this->setFlash($res['success'] ? 'success' : 'danger', $res['message']);
            $this->redirect('admin.php');
        }

        if ($isAjax) {
            $this->json(['success' => false, 'message' => 'Aksi tidak valid.']);
        }

        $this->redirect('admin.php');
    }
}
