<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Services\PertemuanService;
use App\Services\MuridService;
use App\Services\LaguService;
use Throwable;
use PDO;

class PertemuanController extends BaseController
{
    private PertemuanService $pertemuanService;
    private MuridService $muridService;
    private LaguService $laguService;

    public function __construct(
        ?PertemuanService $pertemuanService = null,
        ?MuridService $muridService = null,
        ?LaguService $laguService = null
    ) {
        parent::__construct();
        $this->pertemuanService = $pertemuanService ?? new PertemuanService($this->pdo);
        $this->muridService = $muridService ?? new MuridService($this->pdo);
        $this->laguService = $laguService ?? new LaguService($this->pdo);
    }

    public function handle(): void
    {
        $this->requireLogin();
        $this->requireRole(['owner', 'admin', 'coach']);

        $isCoach = $this->currentUserRole() === 'coach';
        $currentUserId = $this->currentUserId();
        $role = $this->currentUserRole();

        if ($this->isPost()) {
            $this->handlePost($role, $currentUserId);
            return;
        }

        $paginated = $this->pertemuanService->getPaginated(1, 9999);
        $rows = $paginated['data'] ?? [];
        $totalRows = count($rows);
        $search = trim($_GET['q'] ?? '');

        $allMurid = $this->muridService->getAll();
        $allLagu = $this->laguService->getAll();

        $periodeList = [];
        $namaBulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        foreach ($rows as $r) {
            if (!empty($r['tanggal'])) {
                $ym = substr($r['tanggal'], 0, 7);
                if (!isset($periodeList[$ym])) {
                    $yr = (int)substr($ym, 0, 4);
                    $mo = (int)substr($ym, 5, 2);
                    $periodeList[$ym] = ($namaBulanIndo[$mo] ?? $mo) . ' ' . $yr;
                }
            }
        }
        krsort($periodeList);

        $coachList = [];
        try {
            $stmtCoaches = $this->pdo->query("SELECT id, COALESCE(NULLIF(nama,''), username) as nama FROM admin WHERE role IN ('coach', 'owner') ORDER BY nama ASC");
            $coachList = $stmtCoaches->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {}

        $this->render('pertemuan/index', [
            'rows' => $rows,
            'totalRows' => $totalRows,
            'search' => $search,
            'allMurid' => $allMurid,
            'allLagu' => $allLagu,
            'periodeList' => $periodeList,
            'coachList' => $coachList,
            'isCoach' => $isCoach,
            'currentUserId' => $currentUserId,
            'role' => $role,
            'pertemuanService' => $this->pertemuanService,
        ]);
    }

    private function handlePost(string $role, ?int $currentUserId): void
    {
        $this->verifyCsrf();
        $action = $_POST['action'] ?? '';
        $isAjax = $this->isAjax();

        if ($action === 'create') {
            $res = $this->pertemuanService->create(
                (int)($_POST['id_murid'] ?? 0),
                !empty($_POST['id_lagu']) ? (int)$_POST['id_lagu'] : null,
                $_POST['tanggal'] ?? '',
                $_POST['catatan'] ?? '',
                $currentUserId
            );
            if ($isAjax) {
                $this->json(['success' => $res['success'], 'message' => $res['message'], 'action' => 'create']);
            }
            $this->setFlash($res['success'] ? 'success' : 'danger', $res['message']);
            $this->redirect('pertemuan.php');
        }

        if ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $res = $this->pertemuanService->update(
                $id,
                (int)($_POST['id_murid'] ?? 0),
                !empty($_POST['id_lagu']) ? (int)$_POST['id_lagu'] : null,
                $_POST['tanggal'] ?? '',
                $_POST['catatan'] ?? '',
                $role,
                $currentUserId
            );
            if ($isAjax) {
                $this->json(['success' => $res['success'], 'message' => $res['message'], 'action' => 'update']);
            }
            $this->setFlash($res['success'] ? 'success' : 'danger', $res['message']);
            $this->redirect('pertemuan.php');
        }

        if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $res = $this->pertemuanService->delete($id, $role, $currentUserId);
            if ($isAjax) {
                $this->json(['success' => $res['success'], 'message' => $res['message'], 'action' => 'delete']);
            }
            $this->setFlash($res['success'] ? 'success' : 'danger', $res['message']);
            $this->redirect('pertemuan.php');
        }

        if ($isAjax) {
            $this->json(['success' => false, 'message' => 'Aksi tidak valid.']);
        }

        $this->redirect('pertemuan.php');
    }
}
