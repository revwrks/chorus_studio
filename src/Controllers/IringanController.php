<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Services\IringanService;
use App\Services\MuridService;
use App\Services\LaguService;
use App\Core\Auth;

class IringanController extends BaseController
{
    private IringanService $iringanService;
    private MuridService $muridService;
    private LaguService $laguService;

    public function __construct(
        ?IringanService $iringanService = null,
        ?MuridService $muridService = null,
        ?LaguService $laguService = null
    ) {
        parent::__construct();
        $this->iringanService = $iringanService ?? new IringanService($this->pdo);
        $this->muridService = $muridService ?? new MuridService($this->pdo);
        $this->laguService = $laguService ?? new LaguService($this->pdo);
    }

    public function handle(): void
    {
        $this->requireLogin();

        if ($this->isPost()) {
            $this->requireRole(['owner', 'admin', 'coach']);
            $this->handlePost();
            return;
        }

        $isOrtu = Auth::isOrangTua();
        $isStaff = Auth::isStaff();

        $allowedPerPage = [10, 20, 100];
        $perPageInput = (int)($_GET['limit'] ?? 10);
        $perPage = in_array($perPageInput, $allowedPerPage, true) ? $perPageInput : 10;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $search = trim($_GET['q'] ?? '');
        $filterKategori = trim($_GET['kategori'] ?? '');
        $filterMurid = (int)($_GET['murid_id'] ?? 0);

        $activeChild = $isOrtu ? Auth::getActiveMuridData() : null;
        $activeChildId = $isOrtu ? Auth::getActiveMuridId() : null;

        // All murid & lagu for staff modal forms
        $allMurid = $isStaff ? $this->muridService->getAll() : [];
        $allLagu = $isStaff ? $this->laguService->getAll() : [];

        $paginated = $isStaff ? $this->iringanService->getPaginated(1, 9999) : null;
        $rows = $paginated['data'] ?? [];
        $totalRows = count($rows);
        $hybridIringan = ($isOrtu && $activeChild)
            ? $this->iringanService->getHybridForMurid((int)$activeChild['id'])
            : [];

        // Helper building page URLs
        $buildPageUrl = function(int $targetPage, ?int $targetLimit = null) use ($search, $filterKategori, $filterMurid, $perPage): string {
            $query = ['page' => $targetPage, 'limit' => $targetLimit ?? $perPage];
            if ($search !== '') $query['q'] = $search;
            if ($filterKategori !== '') $query['kategori'] = $filterKategori;
            if ($filterMurid > 0) $query['murid_id'] = $filterMurid;
            return '?' . http_build_query($query);
        };

        $this->render('iringan/index', [
            'isOrtu' => $isOrtu,
            'isStaff' => $isStaff,
            'allowedPerPage' => $allowedPerPage,
            'perPage' => $perPage,
            'page' => $page,
            'search' => $search,
            'filterKategori' => $filterKategori,
            'filterMurid' => $filterMurid,
            'activeChild' => $activeChild,
            'activeChildId' => $activeChildId,
            'allMurid' => $allMurid,
            'allLagu' => $allLagu,
            'paginated' => $paginated,
            'rows' => $rows,
            'totalRows' => $totalRows,
            'hybridIringan' => $hybridIringan,
            'buildPageUrl' => $buildPageUrl,
            'iringanService' => $this->iringanService,
        ]);
    }

    private function handlePost(): void
    {
        $this->verifyCsrf();
        $action = $_POST['action'] ?? '';
        $currentUserId = $this->currentUserId();
        $isAjax = $this->isAjax();

        if ($action === 'create') {
            $res = $this->iringanService->create(
                (int)($_POST['id_murid'] ?? 0),
                !empty($_POST['id_lagu']) ? (int)$_POST['id_lagu'] : null,
                $_POST['judul_iringan'] ?? '',
                $_POST['kategori'] ?? 'latihan',
                $_POST['gdrive_link'] ?? '',
                $_POST['catatan'] ?? '',
                $currentUserId
            );
            if ($isAjax) {
                $this->json(['success' => $res['success'], 'message' => $res['message'], 'action' => 'create']);
            }
            $this->setFlash($res['success'] ? 'success' : 'danger', $res['message']);
            $this->redirect('iringan.php');
        }

        if ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $res = $this->iringanService->update(
                $id,
                (int)($_POST['id_murid'] ?? 0),
                !empty($_POST['id_lagu']) ? (int)$_POST['id_lagu'] : null,
                $_POST['judul_iringan'] ?? '',
                $_POST['kategori'] ?? 'latihan',
                $_POST['gdrive_link'] ?? '',
                $_POST['catatan'] ?? ''
            );
            if ($isAjax) {
                $this->json(['success' => $res['success'], 'message' => $res['message'], 'action' => 'update']);
            }
            $this->setFlash($res['success'] ? 'success' : 'danger', $res['message']);
            $this->redirect('iringan.php');
        }

        if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $res = $this->iringanService->delete($id);
            if ($isAjax) {
                $this->json(['success' => $res['success'], 'message' => $res['message'], 'action' => 'delete']);
            }
            $this->setFlash($res['success'] ? 'success' : 'danger', $res['message']);
            $this->redirect('iringan.php');
        }

        if ($isAjax) {
            $this->json(['success' => false, 'message' => 'Aksi tidak valid.']);
        }

        $this->redirect('iringan.php');
    }
}
