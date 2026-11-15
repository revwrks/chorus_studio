<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Services\RefleksiService;
use App\Services\MuridService;

class RefleksiController extends BaseController
{
    private RefleksiService $refleksiService;
    private MuridService $muridService;

    public function __construct(
        ?RefleksiService $refleksiService = null,
        ?MuridService $muridService = null
    ) {
        parent::__construct();
        $this->refleksiService = $refleksiService ?? new RefleksiService($this->pdo);
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

        $paginated = $this->refleksiService->getPaginated(1, 9999);
        $rows = $paginated['data'] ?? [];
        $totalRows = count($rows);

        $allMurid = $this->muridService->getAll();
        $availableYears = array_unique(array_filter(array_map(fn($r) => (int)($r['tahun'] ?? 0), $rows)));
        rsort($availableYears);

        $this->render('refleksi/index', [
            'rows' => $rows,
            'totalRows' => $totalRows,
            'allMurid' => $allMurid,
            'availableYears' => $availableYears,
            'refleksiService' => $this->refleksiService,
        ]);
    }

    private function handlePost(): void
    {
        $this->verifyCsrf();
        $action = $_POST['action'] ?? '';
        $isAjax = $this->isAjax();

        if ($action === 'create') {
            $res = $this->refleksiService->create(
                (int)($_POST['id_murid'] ?? 0),
                (int)($_POST['tahun'] ?? date('Y')),
                (int)($_POST['bulan'] ?? date('n')),
                $_POST['refleksi'] ?? '',
                $_POST['rencana'] ?? '',
                $_POST['saran'] ?? ''
            );
            if ($isAjax) {
                $this->json($res);
            }
            $this->setFlash($res['success'] ? 'success' : 'danger', $res['message']);
            $this->redirect('refleksi.php');
        }

        if ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $res = $this->refleksiService->update(
                $id,
                (int)($_POST['id_murid'] ?? 0),
                (int)($_POST['tahun'] ?? date('Y')),
                (int)($_POST['bulan'] ?? date('n')),
                $_POST['refleksi'] ?? '',
                $_POST['rencana'] ?? '',
                $_POST['saran'] ?? ''
            );
            if ($isAjax) {
                $this->json($res);
            }
            $this->setFlash($res['success'] ? 'success' : 'danger', $res['message']);
            $this->redirect('refleksi.php');
        }

        if ($action === 'delete') {
            $this->requireRole(['owner', 'admin']);
            $id = (int)($_POST['id'] ?? 0);
            $res = $this->refleksiService->delete($id);
            if ($isAjax) {
                $this->json($res);
            }
            $this->setFlash($res['success'] ? 'success' : 'danger', $res['message']);
            $this->redirect('refleksi.php');
        }

        if ($isAjax) {
            $this->json(['success' => false, 'message' => 'Aksi tidak valid.']);
        }

        $this->redirect('refleksi.php');
    }
}
