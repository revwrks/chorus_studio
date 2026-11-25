<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Services\MuridService;
use App\Services\RencanaPertemuanService;
use App\Core\Auth;
use Throwable;
use PDO;

class RencanaPertemuanController extends BaseController
{
    private MuridService $muridService;
    private RencanaPertemuanService $rencanaService;

    public function __construct(
        ?MuridService $muridService = null,
        ?RencanaPertemuanService $rencanaService = null
    ) {
        parent::__construct();
        $this->muridService = $muridService ?? new MuridService($this->pdo);
        $this->rencanaService = $rencanaService ?? new RencanaPertemuanService($this->pdo);
    }

    public function handle(): void
    {
        $this->requireLogin();
        $this->requireRole(['owner', 'admin', 'coach']);

        $currentUserRole = $this->currentUserRole();
        $currentUserId = $this->currentUserId();
        $isCoach = ($currentUserRole === 'coach');
        $coachId = $isCoach ? $currentUserId : null;

        $curYear = (int)date('Y');
        $curMonth = (int)date('n');

        $tahunDipilih = isset($_GET['tahun']) ? max(2020, (int)$_GET['tahun']) : $curYear;
        $bulanDipilih = isset($_GET['bulan']) ? max(1, min(12, (int)$_GET['bulan'])) : $curMonth;

        // Handle GET AJAX action for realisasi detail
        if (isset($_GET['action']) && $_GET['action'] === 'get_realisasi_detail') {
            $this->handleRealisasiDetailAjax($tahunDipilih, $bulanDipilih, $isCoach, $coachId);
            return;
        }

        // Handle POST actions
        if ($this->isPost()) {
            $this->handlePost($tahunDipilih, $bulanDipilih, $coachId);
            return;
        }

        // Fetch period options
        $periodData = RencanaPertemuanService::getPeriodOptions($tahunDipilih, $bulanDipilih);
        $curPeriod = $periodData['current'];
        $nextPeriod = $periodData['next'];

        $isCurrentSelected = ($tahunDipilih === $curPeriod['tahun'] && $bulanDipilih === $curPeriod['bulan']);
        $isNextSelected = ($tahunDipilih === $nextPeriod['tahun'] && $bulanDipilih === $nextPeriod['bulan']);

        // Fetch list of active students for this period
        $studentPlans = $this->rencanaService->getListForPeriod($tahunDipilih, $bulanDipilih, $coachId);
        $totalStudents = count($studentPlans);

        // Fetch summary metrics
        $summary = $this->rencanaService->getSummary($tahunDipilih, $bulanDipilih, $coachId);

        // For coach role: list other active students not yet in this month's plan
        $allActiveStudents = $this->muridService->getAllActive();
        $currentPlanMuridIds = array_column($studentPlans, 'murid_id');
        $unplannedActiveStudents = array_filter($allActiveStudents, fn($m) => !in_array((int)$m['id'], $currentPlanMuridIds, true));

        // Fetch all coaches for Admin/Owner assignment dropdown
        $coachesList = [];
        if (!$isCoach) {
            try {
                $stmtCoaches = $this->pdo->query("SELECT id, COALESCE(NULLIF(nama, ''), username) AS nama FROM admin WHERE role IN ('coach', 'owner') ORDER BY nama ASC");
                $coachesList = $stmtCoaches->fetchAll();
            } catch (Throwable $e) {}
        }

        $this->render('rencana_pertemuan/index', [
            'tahunDipilih' => $tahunDipilih,
            'bulanDipilih' => $bulanDipilih,
            'isCoach' => $isCoach,
            'coachId' => $coachId,
            'currentUserId' => $coachId,
            'periodData' => $periodData,
            'curPeriod' => $curPeriod,
            'nextPeriod' => $nextPeriod,
            'isCurrentSelected' => $isCurrentSelected,
            'isNextSelected' => $isNextSelected,
            'studentPlans' => $studentPlans,
            'totalStudents' => $totalStudents,
            'summary' => $summary,
            'unplannedActiveStudents' => $unplannedActiveStudents,
            'coachesList' => $coachesList,
            'rencanaService' => $this->rencanaService,
        ]);
    }

    private function handleRealisasiDetailAjax(int $tahunDipilih, int $bulanDipilih, bool $isCoach, ?int $coachId): void
    {
        $mId = (int)($_GET['murid_id'] ?? 0);
        $reqTahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : $tahunDipilih;
        $reqBulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : $bulanDipilih;
        $reqCoachScope = $isCoach ? $coachId : null;

        $murid = $this->muridService->getById($mId);
        if (!$murid) {
            $this->json(['success' => false, 'message' => 'Data murid tidak ditemukan.']);
        }

        $dates = $this->rencanaService->getRealisasiDates($mId, $reqTahun, $reqBulan, $reqCoachScope);
        $namaBulanFull = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $this->json([
            'success' => true,
            'murid' => [
                'id' => (int)$murid['id'],
                'nama' => $murid['nama'],
                'foto' => $murid['foto'] ?? null,
            ],
            'periode' => [
                'tahun' => $reqTahun,
                'bulan' => $reqBulan,
                'label' => ($namaBulanFull[$reqBulan] ?? $reqBulan) . ' ' . $reqTahun,
            ],
            'total' => count($dates),
            'pertemuan' => $dates,
        ]);
    }

    private function handlePost(int $tahunDipilih, int $bulanDipilih, ?int $coachId): void
    {
        $this->verifyCsrf();
        $action = $_POST['action'] ?? '';
        $isAjax = $this->isAjax();

        $postTahun = isset($_POST['tahun']) ? (int)$_POST['tahun'] : $tahunDipilih;
        $postBulan = isset($_POST['bulan']) ? (int)$_POST['bulan'] : $bulanDipilih;
        $isCoach = Auth::isCoach();

        if ($action === 'save_single') {
            $idMurid = (int)($_POST['id_murid'] ?? 0);
            $target = max(0, (int)($_POST['target_pertemuan'] ?? 4));
            $catatan = $_POST['catatan'] ?? null;
            $targetCoach = $isCoach
                ? $coachId
                : (!empty($_POST['coach_id']) ? (int)$_POST['coach_id'] : $coachId);

            $res = $this->rencanaService->setTarget($idMurid, $postTahun, $postBulan, $target, $catatan, $targetCoach);
            if ($isAjax) {
                $this->json($res);
            }
            $this->setFlash($res['success'] ? 'success' : 'danger', $res['message']);
            $this->redirect("rencana_pertemuan.php?tahun=$postTahun&bulan=$postBulan");
        }

        if ($action === 'save_batch') {
            $targets = $_POST['targets'] ?? [];
            $catatans = $_POST['catatans'] ?? [];
            $coaches = $_POST['coaches'] ?? [];

            $items = [];
            foreach ($targets as $mId => $targetVal) {
                $mId = (int)$mId;
                if ($mId <= 0) continue;
                $itemCoachId = $isCoach
                    ? $coachId
                    : (!empty($coaches[$mId]) ? (int)$coaches[$mId] : $coachId);
                $items[] = [
                    'id_murid' => $mId,
                    'target_pertemuan' => max(0, (int)$targetVal),
                    'catatan' => $catatans[$mId] ?? null,
                    'coach_id' => $itemCoachId,
                ];
            }

            $res = $this->rencanaService->batchSetTarget($items, $postTahun, $postBulan, $coachId);
            if ($isAjax) {
                $this->json($res);
            }
            $this->setFlash($res['success'] ? 'success' : 'danger', $res['message']);
            $this->redirect("rencana_pertemuan.php?tahun=$postTahun&bulan=$postBulan");
        }

        if ($isAjax) {
            $this->json(['success' => false, 'message' => 'Aksi tidak valid.']);
        }
        $this->redirect("rencana_pertemuan.php?tahun=$tahunDipilih&bulan=$bulanDipilih");
    }
}
