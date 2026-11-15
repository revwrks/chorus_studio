<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Services\LaguService;

class LaguController extends BaseController
{
    private LaguService $laguService;

    public function __construct(?LaguService $laguService = null)
    {
        parent::__construct();
        $this->laguService = $laguService ?? new LaguService($this->pdo);
    }

    public function handle(): void
    {
        $this->requireLogin();
        $this->requireRole(['owner', 'admin', 'coach']);

        if ($this->isPost()) {
            $this->handlePost();
            return;
        }

        $rows = $this->laguService->getAll();
        $totalRows = count($rows);

        $this->render('lagu/index', [
            'rows' => $rows,
            'totalRows' => $totalRows,
            'laguService' => $this->laguService,
        ]);
    }

    private function handlePost(): void
    {
        $this->verifyCsrf();
        $action = $_POST['action'] ?? '';
        $isAjax = $this->isAjax();

        if ($action === 'create') {
            $res = $this->laguService->create(
                $_POST['judul'] ?? '',
                $_POST['youtube_link'] ?? null,
                $_POST['iringan_link'] ?? null,
                $_POST['tingkat'] ?? 'mudah'
            );
            if ($isAjax) {
                $this->json(['success' => $res['success'], 'message' => $res['message'], 'action' => 'create']);
            }
            $this->setFlash($res['success'] ? 'success' : 'danger', $res['message']);
            $this->redirect('lagu.php');
        }

        if ($action === 'update') {
            $id = (int)($_POST['id_lagu'] ?? $_POST['id'] ?? 0);
            $res = $this->laguService->update(
                $id,
                $_POST['judul'] ?? '',
                $_POST['youtube_link'] ?? null,
                $_POST['iringan_link'] ?? null,
                $_POST['tingkat'] ?? 'mudah'
            );
            if ($isAjax) {
                $this->json(['success' => $res['success'], 'message' => $res['message'], 'action' => 'update']);
            }
            $this->setFlash($res['success'] ? 'success' : 'danger', $res['message']);
            $this->redirect('lagu.php');
        }

        if ($action === 'delete') {
            $id = (int)($_POST['id_lagu'] ?? $_POST['id'] ?? 0);
            $res = $this->laguService->delete($id);
            if ($isAjax) {
                $this->json(['success' => $res['success'], 'message' => $res['message'], 'action' => 'delete']);
            }
            $this->setFlash($res['success'] ? 'success' : 'danger', $res['message']);
            $this->redirect('lagu.php');
        }

        if ($isAjax) {
            $this->json(['success' => false, 'message' => 'Aksi tidak valid.']);
        }

        $this->redirect('lagu.php');
    }
}
