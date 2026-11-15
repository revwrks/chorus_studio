<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Services\MuridService;
use App\Services\PertemuanService;
use App\Services\RefleksiService;
use App\Services\IringanService;
use App\Services\RencanaPertemuanService;
use App\Core\Auth;
use Throwable;
use PDO;

class DashboardController extends BaseController
{
    private MuridService $muridService;
    private PertemuanService $pertemuanService;
    private RefleksiService $refleksiService;
    private IringanService $iringanService;
    private RencanaPertemuanService $rencanaService;

    public function __construct(
        ?MuridService $muridService = null,
        ?PertemuanService $pertemuanService = null,
        ?RefleksiService $refleksiService = null,
        ?IringanService $iringanService = null,
        ?RencanaPertemuanService $rencanaService = null
    ) {
        parent::__construct();
        $this->muridService = $muridService ?? new MuridService($this->pdo);
        $this->pertemuanService = $pertemuanService ?? new PertemuanService($this->pdo);
        $this->refleksiService = $refleksiService ?? new RefleksiService($this->pdo);
        $this->iringanService = $iringanService ?? new IringanService($this->pdo);
        $this->rencanaService = $rencanaService ?? new RencanaPertemuanService($this->pdo);
    }

    public function handle(): void
    {
        $this->requireLogin();

        // Handle GET AJAX action for realisasi detail
        if (isset($_GET['action']) && $_GET['action'] === 'get_realisasi_detail') {
            $this->handleRealisasiDetailAjax();
            return;
        }

        if (Auth::isOrangTua()) {
            $this->handleOrangTua();
        } else {
            $this->handleStaff();
        }
    }

    private function handleRealisasiDetailAjax(): void
    {
        $mId = (int)($_GET['murid_id'] ?? 0);
        $bulanDipilih = isset($_GET['bulan']) ? intval($_GET['bulan']) : (int)date('n');
        $tahunDipilih = isset($_GET['tahun']) ? intval($_GET['tahun']) : (int)date('Y');
        $reqTahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : $tahunDipilih;
        $reqBulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : $bulanDipilih;
        $isCoach = $this->currentUserRole() === 'coach';
        $reqCoachScope = (isset($_GET['coach_scope']) && $_GET['coach_scope'] === '1' && $isCoach) ? $this->currentUserId() : null;

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

    private function handleOrangTua(): void
    {
        $namaBulan = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        $anakList = Auth::getOrangTuaAnakList();
        $activeChild = Auth::getActiveMuridData();
        $activeChildId = Auth::getActiveMuridId();

        $availableYears = [];
        $availableMonthsByYear = [];

        if ($activeChildId) {
            try {
                $stmtPeriods = $this->pdo->prepare("
                    SELECT DISTINCT YEAR(tanggal) as yr, MONTH(tanggal) as mo 
                    FROM pertemuan 
                    WHERE id_murid = ?
                    UNION
                    SELECT DISTINCT tahun as yr, bulan as mo 
                    FROM refleksi 
                    WHERE id_murid = ?
                    ORDER BY yr DESC, mo DESC
                ");
                $stmtPeriods->execute([$activeChildId, $activeChildId]);
                $periodRows = $stmtPeriods->fetchAll(PDO::FETCH_ASSOC);

                foreach ($periodRows as $pr) {
                    $yr = (int)$pr['yr'];
                    $mo = (int)$pr['mo'];
                    if ($yr > 0 && $mo >= 1 && $mo <= 12) {
                        if (!in_array($yr, $availableYears, true)) {
                            $availableYears[] = $yr;
                        }
                        if (!isset($availableMonthsByYear[$yr])) {
                            $availableMonthsByYear[$yr] = [];
                        }
                        if (!in_array($mo, $availableMonthsByYear[$yr], true)) {
                            $availableMonthsByYear[$yr][] = $mo;
                        }
                    }
                }
            } catch (Throwable $e) {
                error_log("Failed to fetch available periods for parent dashboard: " . $e->getMessage());
            }
        }

        if (empty($availableYears)) {
            $currentYr = (int)date('Y');
            $currentMo = (int)date('n');
            $availableYears = [$currentYr];
            $availableMonthsByYear[$currentYr] = [$currentMo];
        }

        if (isset($_GET['tahun']) && in_array((int)$_GET['tahun'], $availableYears, true)) {
            $tahunDipilih = (int)$_GET['tahun'];
        } else {
            $tahunDipilih = $availableYears[0];
        }

        $availableMonthsForYear = $availableMonthsByYear[$tahunDipilih] ?? [];
        sort($availableMonthsForYear);

        if (isset($_GET['bulan']) && in_array((int)$_GET['bulan'], $availableMonthsForYear, true)) {
            $bulanDipilih = (int)$_GET['bulan'];
        } else {
            $bulanDipilih = end($availableMonthsForYear) ?: (int)date('n');
        }

        $totalSesiBulanIni = 0;
        $totalLaguBulanIni = 0;
        $totalIringan = 0;
        $targetPertemuanBulanIni = 0;
        $riwayatPertemuan = [];
        $refleksiBulan = null;

        if ($activeChildId) {
            try {
                $totalSesiBulanIni = (int)$this->pertemuanService->getMonthlyCountForMurid($activeChildId, $tahunDipilih, $bulanDipilih);
                $totalLaguBulanIni = (int)$this->pertemuanService->getDistinctSongsCountForMurid($activeChildId, $tahunDipilih, $bulanDipilih);
                $totalIringan = (int)$this->iringanService->countForMurid($activeChildId);
                $riwayatPertemuan = $this->pertemuanService->getMonthlyListForMurid($activeChildId, $tahunDipilih, $bulanDipilih) ?: [];
                $refleksiBulan = $this->refleksiService->getEffectiveRefleksi($activeChildId, $tahunDipilih, $bulanDipilih);
                $targetPertemuanBulanIni = (int)$this->rencanaService->getMonthlyTargetForMurid($activeChildId, $tahunDipilih, $bulanDipilih);
            } catch (Throwable $e) {
                error_log("Parent dashboard data fetch error: " . $e->getMessage());
            }
        }

        $this->render('dashboard/orang_tua', [
            'isOrtu' => true,
            'anakList' => $anakList,
            'activeChild' => $activeChild,
            'activeChildId' => $activeChildId,
            'availableYears' => $availableYears,
            'availableMonthsByYear' => $availableMonthsByYear,
            'availableMonthsForYear' => $availableMonthsForYear,
            'tahunDipilih' => $tahunDipilih,
            'bulanDipilih' => $bulanDipilih,
            'namaBulan' => $namaBulan,
            'totalSesiBulanIni' => $totalSesiBulanIni,
            'totalLaguBulanIni' => $totalLaguBulanIni,
            'totalIringan' => $totalIringan,
            'targetPertemuanBulanIni' => $targetPertemuanBulanIni,
            'riwayatPertemuan' => $riwayatPertemuan,
            'refleksiBulan' => $refleksiBulan,
        ]);
    }

    private function handleStaff(): void
    {
        $isCoach = $this->currentUserRole() === 'coach';
        $namaBulan = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];
        $tahunSekarang = (int)date('Y');
        $tahunMulai = $tahunSekarang - 2;

        $staffAvailableYears = [];
        $staffAvailableMonthsByYear = [];

        $curYr = (int)date('Y');
        $curMo = (int)date('n');

        try {
            $stmtStaffPeriods = $this->pdo->query("
                SELECT DISTINCT YEAR(tanggal) as yr, MONTH(tanggal) as mo 
                FROM pertemuan 
                UNION
                SELECT DISTINCT tahun as yr, bulan as mo 
                FROM rencana_pertemuan 
                ORDER BY yr DESC, mo DESC
            ");
            $staffPeriodRows = $stmtStaffPeriods->fetchAll(PDO::FETCH_ASSOC);

            foreach ($staffPeriodRows as $spr) {
                $yr = (int)$spr['yr'];
                $mo = (int)$spr['mo'];
                if ($yr > 0 && $mo >= 1 && $mo <= 12) {
                    if (!in_array($yr, $staffAvailableYears, true)) {
                        $staffAvailableYears[] = $yr;
                    }
                    if (!isset($staffAvailableMonthsByYear[$yr])) {
                        $staffAvailableMonthsByYear[$yr] = [];
                    }
                    if (!in_array($mo, $staffAvailableMonthsByYear[$yr], true)) {
                        $staffAvailableMonthsByYear[$yr][] = $mo;
                    }
                }
            }
        } catch (Throwable $e) {
            error_log("Failed to fetch staff periods: " . $e->getMessage());
        }

        if (!in_array($curYr, $staffAvailableYears, true)) {
            $staffAvailableYears[] = $curYr;
        }
        if (!isset($staffAvailableMonthsByYear[$curYr])) {
            $staffAvailableMonthsByYear[$curYr] = [];
        }
        if (!in_array($curMo, $staffAvailableMonthsByYear[$curYr], true)) {
            $staffAvailableMonthsByYear[$curYr][] = $curMo;
        }
        rsort($staffAvailableYears);

        if (isset($_GET['tahun']) && in_array((int)$_GET['tahun'], $staffAvailableYears, true)) {
            $tahunDipilih = (int)$_GET['tahun'];
        } else {
            $tahunDipilih = $staffAvailableYears[0];
        }

        $staffAvailableMonthsForYear = $staffAvailableMonthsByYear[$tahunDipilih] ?? [];
        sort($staffAvailableMonthsForYear);

        if (isset($_GET['bulan']) && in_array((int)$_GET['bulan'], $staffAvailableMonthsForYear, true)) {
            $bulanDipilih = (int)$_GET['bulan'];
        } else {
            $bulanDipilih = end($staffAvailableMonthsForYear) ?: (int)date('n');
        }

        $totalMuridCount = $this->muridService->count();
        $activeMuridCount = 0;
        try {
            $activeMuridCount = (int)$this->pdo->query("SELECT COUNT(*) FROM murid WHERE status = 'Aktif'")->fetchColumn();
        } catch (Throwable $e) {}

        // Fetch coach rows
        $stmtCoach = $this->pdo->prepare("SELECT a.id, COALESCE(NULLIF(a.nama,''), a.username) as nama,
                                  COUNT(DISTINCT p.id_murid) as total_murid,
                                  COUNT(p.id) as total_pertemuan
                                  FROM admin a
                                  LEFT JOIN pertemuan p ON p.coach_id=a.id AND YEAR(p.tanggal)=? AND MONTH(p.tanggal)=?
                                  WHERE a.role IN ('coach','owner')
                                  GROUP BY a.id
                                  ORDER BY total_murid DESC, nama ASC");
        $stmtCoach->execute([$tahunDipilih, $bulanDipilih]);
        $coachRows = $stmtCoach->fetchAll();

        $detailStmt = $this->pdo->prepare('SELECT p.coach_id, p.tanggal, m.nama AS murid_nama, l.judul AS lagu_judul
                                    FROM pertemuan p
                                    LEFT JOIN murid m ON p.id_murid=m.id
                                    LEFT JOIN lagu l ON p.id_lagu=l.id_lagu
                                    WHERE p.coach_id IS NOT NULL AND YEAR(p.tanggal)=? AND MONTH(p.tanggal)=?
                                    ORDER BY p.tanggal DESC');
        $detailStmt->execute([$tahunDipilih, $bulanDipilih]);
        $detailByCoach = [];
        foreach ($detailStmt->fetchAll() as $row) {
            $detailByCoach[$row['coach_id']][] = $row;
        }

        $stmtCoachMurid = $this->pdo->prepare('SELECT p.coach_id, m.nama, COUNT(p.id) as total_pertemuan
                                         FROM pertemuan p
                                         JOIN murid m ON p.id_murid = m.id
                                         WHERE p.coach_id IS NOT NULL AND YEAR(p.tanggal)=? AND MONTH(p.tanggal)=?
                                         GROUP BY p.coach_id, m.id
                                         ORDER BY total_pertemuan DESC, m.nama ASC');
        $stmtCoachMurid->execute([$tahunDipilih, $bulanDipilih]);
        $coachMuridRows = $stmtCoachMurid->fetchAll();
        $muridByCoach = [];
        foreach ($coachMuridRows as $row) {
            $muridByCoach[$row['coach_id']][] = $row;
        }

        $this->render('dashboard/staff', [
            'isOrtu' => false,
            'isCoach' => $isCoach,
            'pdo' => $this->pdo,
            'namaBulan' => $namaBulan,
            'tahunDipilih' => $tahunDipilih,
            'bulanDipilih' => $bulanDipilih,
            'tahunMulai' => $tahunMulai,
            'tahunSekarang' => $tahunSekarang,
            'staffAvailableYears' => $staffAvailableYears,
            'staffAvailableMonthsByYear' => $staffAvailableMonthsByYear,
            'staffAvailableMonthsForYear' => $staffAvailableMonthsForYear,
            'totalMuridCount' => $totalMuridCount,
            'activeMuridCount' => $activeMuridCount,
            'rencanaService' => $this->rencanaService,
            'coachRows' => $coachRows,
            'detailByCoach' => $detailByCoach,
            'muridByCoach' => $muridByCoach,
            'detailRowsPerPage' => 10,
        ]);
    }
}
