<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Services\MuridService;

class MuridController extends BaseController
{
    private MuridService $muridService;

    public function __construct(?MuridService $muridService = null)
    {
        parent::__construct();
        $this->muridService = $muridService ?? new MuridService($this->pdo);
    }

    public function handle(): void
    {
        $this->requireLogin();
        $this->requireRole(['owner', 'admin', 'coach']);

        if ($this->isPost()) {
            $this->handlePost();
            return;
        }

        $rows = $this->muridService->getAll();
        $totalRows = count($rows);

        $this->render('murid/index', [
            'rows' => $rows,
            'totalRows' => $totalRows,
            'muridService' => $this->muridService,
        ]);
    }

    private function handlePost(): void
    {
        $this->verifyCsrf();
        $action = $_POST['action'] ?? '';
        $isAjax = $this->isAjax();

        if ($action === 'create') {
            $res = $this->muridService->create(
                $_POST['nama'] ?? '',
                $_POST['instagram'] ?? null,
                $_FILES['foto'] ?? null,
                $_POST['status'] ?? 'aktif'
            );
            if ($isAjax) {
                $this->json(['success' => $res['success'], 'message' => $res['message'], 'action' => 'create']);
            }
            $this->setFlash($res['success'] ? 'success' : 'danger', $res['message']);
            $this->redirect('murid.php');
        }

        if ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $res = $this->muridService->update(
                $id,
                $_POST['nama'] ?? '',
                $_POST['instagram'] ?? null,
                $_FILES['foto'] ?? null,
                $_POST['status'] ?? null
            );
            if ($isAjax) {
                $this->json(['success' => $res['success'], 'message' => $res['message'], 'action' => 'update']);
            }
            $this->setFlash($res['success'] ? 'success' : 'danger', $res['message']);
            $this->redirect('murid.php');
        }

        if ($action === 'update_status') {
            $id = (int)($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? 'aktif';
            $res = $this->muridService->updateStatus($id, $status);
            if ($isAjax) {
                $this->json($res);
            }
            $this->setFlash($res['success'] ? 'success' : 'danger', $res['message']);
            $this->redirect('murid.php');
        }

        if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $res = $this->muridService->delete($id);
            if ($isAjax) {
                $this->json(['success' => $res['success'], 'message' => $res['message'], 'action' => 'delete']);
            }
            $this->setFlash($res['success'] ? 'success' : 'danger', $res['message']);
            $this->redirect('murid.php');
        }

        if ($isAjax) {
            $this->json(['success' => false, 'message' => 'Aksi tidak valid.']);
        }

        $this->redirect('murid.php');
    }
}
