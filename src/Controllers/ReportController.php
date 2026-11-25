<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Auth;
use PDO;

class ReportController extends BaseController
{
    public function handle(): void
    {
        $this->index();
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->requireRole(['owner', 'admin', 'coach']);

        $tahunSekarang = (int)date('Y');
        $bulanSekarang = (int)date('n');
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $muridList = $this->pdo->query('SELECT id, nama FROM murid ORDER BY nama')->fetchAll();
        $tahunList = $this->pdo->query("SELECT DISTINCT YEAR(tanggal) AS y FROM pertemuan
                                  UNION
                                  SELECT DISTINCT tahun AS y FROM refleksi
                                  ORDER BY y DESC")->fetchAll(PDO::FETCH_COLUMN);

        $selectedYear = isset($_GET['tahun']) ? intval($_GET['tahun']) : (in_array($tahunSekarang, $tahunList, true) ? $tahunSekarang : ($tahunList[0] ?? $tahunSekarang));
        $bulanList = [];
        if (!empty($tahunList)) {
            $stmtBulan = $this->pdo->prepare("SELECT DISTINCT MONTH(tanggal) AS m FROM pertemuan WHERE YEAR(tanggal)=?
                                      UNION
                                      SELECT DISTINCT bulan AS m FROM refleksi WHERE tahun=?
                                      ORDER BY m ASC");
            $stmtBulan->execute([$selectedYear, $selectedYear]);
            $bulanList = $stmtBulan->fetchAll(PDO::FETCH_COLUMN);
        }

        $selectedMonth = isset($_GET['bulan']) ? intval($_GET['bulan']) : (in_array($bulanSekarang, $bulanList, true) ? $bulanSekarang : ($bulanList[0] ?? $bulanSekarang));
        $canSingle = !empty($muridList) && !empty($tahunList) && !empty($bulanList);
        $canBulk = !empty($tahunList) && !empty($bulanList);

        $this->render('laporan/index', [
            'tahunSekarang' => $tahunSekarang,
            'bulanSekarang' => $bulanSekarang,
            'namaBulan' => $namaBulan,
            'muridList' => $muridList,
            'tahunList' => $tahunList,
            'bulanList' => $bulanList,
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'canSingle' => $canSingle,
            'canBulk' => $canBulk,
        ]);
    }

    public function generateSingle(): void
    {
        $this->requireLogin();
        require_once dirname(__DIR__, 2) . '/includes/report_pdf.php';

        $id_murid = intval($_GET['id_murid'] ?? 0);
        $tahun = intval($_GET['tahun'] ?? date('Y'));
        $bulan = intval($_GET['bulan'] ?? date('n'));
        if (!$id_murid) {
            http_response_code(400);
            die('id_murid required');
        }
        if ($tahun < 2000 || $tahun > 2100 || $bulan < 1 || $bulan > 12) {
            http_response_code(400);
            die('Parameter tahun atau bulan tidak valid.');
        }

        if (Auth::isOrangTua()) {
            $allowedKids = array_map('intval', array_column(Auth::getOrangTuaAnakList(), 'id'));
            if (!in_array($id_murid, $allowedKids, true)) {
                http_response_code(403);
                die('Akses ditolak: Anda hanya dapat mengunduh laporan untuk anak Anda sendiri.');
            }
        }

        $report = build_report_pdf($this->pdo, $id_murid, $tahun, $bulan);
        if (!$report) {
            http_response_code(404);
            die('Murid tidak ditemukan');
        }

        $pdf = $report['pdf'];
        $fileName = $report['file_name'];
        $pdf->Output('D', $fileName);
        exit;
    }

    public function generateBulk(): void
    {
        $this->requireLogin();
        $this->requireRole(['owner', 'admin']);
        require_once dirname(__DIR__, 2) . '/includes/report_pdf.php';

        $tahun = intval($_POST['tahun'] ?? $_GET['tahun'] ?? date('Y'));
        $bulan = intval($_POST['bulan'] ?? $_GET['bulan'] ?? date('n'));
        $action = $_POST['action'] ?? $_GET['action'] ?? 'view';
        $isApiAction = in_array($action, ['init', 'generate', 'zip', 'cleanup'], true);

        if ($tahun < 2000 || $tahun > 2100 || $bulan < 1 || $bulan > 12) {
            if ($isApiAction) {
                $this->json(['success' => false, 'status' => 'error', 'message' => 'Parameter tahun atau bulan tidak valid.'], 400);
            }
            http_response_code(400);
            die('Parameter tahun atau bulan tidak valid.');
        }

        $baseDir = dirname(__DIR__, 2);
        $tmpBase = $baseDir . '/assets/tmp/bulk_reports';
        if (!is_dir($tmpBase)) {
            @mkdir($tmpBase, 0750, true);
        }

        if ($action === 'init') {
            if (!$this->isPost()) {
                $this->json(['success' => false, 'status' => 'error', 'message' => 'Metode permintaan harus POST.'], 405);
            }
            $this->verifyCsrf();

            // Prune stale temp directories older than 3600s
            $now = time();
            foreach (@scandir($tmpBase) ?: [] as $entry) {
                if ($entry === '.' || $entry === '..') continue;
                $entryPath = $tmpBase . '/' . $entry;
                if (is_dir($entryPath) && (@filemtime($entryPath) ?: $now) < ($now - 3600)) {
                    foreach (glob($entryPath . '/*') ?: [] as $staleFile) {
                        if (is_file($staleFile)) @unlink($staleFile);
                    }
                    @rmdir($entryPath);
                }
            }

            $jobId = bin2hex(random_bytes(16));
            $tmpDir = $tmpBase . '/job_' . $jobId;
            if (!is_dir($tmpDir)) {
                @mkdir($tmpDir, 0750, true);
            }

            if (!isset($_SESSION['bulk_pdf_jobs']) || !is_array($_SESSION['bulk_pdf_jobs'])) {
                $_SESSION['bulk_pdf_jobs'] = [];
            }
            $_SESSION['bulk_pdf_jobs'][$jobId] = [
                'dir' => $tmpDir,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'user_id' => $this->currentUserId(),
                'created_at' => $now,
            ];

            $stmtMurid = $this->pdo->prepare('SELECT DISTINCT m.id, m.nama
                                        FROM murid m
                                        INNER JOIN pertemuan p ON p.id_murid=m.id
                                        WHERE YEAR(p.tanggal)=? AND MONTH(p.tanggal)=?
                                        UNION
                                        SELECT DISTINCT m.id, m.nama
                                        FROM murid m
                                        INNER JOIN refleksi r ON r.id_murid=m.id
                                        WHERE r.tahun=? AND r.bulan=?
                                        ORDER BY nama');
            $stmtMurid->execute([$tahun, $bulan, $tahun, $bulan]);
            $muridRows = $stmtMurid->fetchAll();

            $this->json([
                'success' => true,
                'status' => 'ok',
                'message' => 'Inisialisasi berhasil.',
                'job_id' => $jobId,
                'data' => $muridRows,
            ]);
        }

        if ($action === 'generate') {
            if (!$this->isPost()) {
                $this->json(['success' => false, 'status' => 'error', 'message' => 'Metode permintaan harus POST.'], 405);
            }
            $this->verifyCsrf();

            $jobId = trim((string)($_POST['job_id'] ?? $_GET['job_id'] ?? ''));
            if (!preg_match('/^[a-f0-9]{16,32}$/', $jobId)) {
                $this->json(['success' => false, 'status' => 'error', 'message' => 'Job ID tidak valid.'], 403);
            }
            if (!isset($_SESSION['bulk_pdf_jobs'][$jobId])) {
                $this->json(['success' => false, 'status' => 'error', 'message' => 'Sesi bulk job tidak ditemukan.'], 404);
            }

            $tmpDir = $_SESSION['bulk_pdf_jobs'][$jobId]['dir'];
            if (!is_dir($tmpDir)) {
                @mkdir($tmpDir, 0750, true);
            }

            $id_murid = intval($_POST['id_murid'] ?? 0);
            if ($id_murid <= 0) {
                $this->json(['success' => false, 'status' => 'error', 'message' => 'ID murid tidak valid.'], 400);
            }

            $report = build_report_pdf($this->pdo, $id_murid, $tahun, $bulan);
            if (!$report) {
                $this->json(['success' => false, 'status' => 'error', 'message' => 'Data murid tidak ditemukan.'], 404);
            }

            $pdf = $report['pdf'];
            $fileName = $report['file_name'];
            $pdf->Output('F', $tmpDir . '/' . $fileName);

            $this->json(['success' => true, 'status' => 'ok', 'message' => 'PDF berhasil dibuat.']);
        }

        if ($action === 'zip') {
            if ($this->isPost()) {
                $this->verifyCsrf();
            }

            $jobId = trim((string)($_POST['job_id'] ?? $_GET['job_id'] ?? ''));
            if (!preg_match('/^[a-f0-9]{16,32}$/', $jobId)) {
                $this->json(['success' => false, 'status' => 'error', 'message' => 'Job ID tidak valid.'], 403);
            }
            if (!isset($_SESSION['bulk_pdf_jobs'][$jobId])) {
                $this->json(['success' => false, 'status' => 'error', 'message' => 'Sesi bulk job tidak ditemukan.'], 404);
            }

            $tmpDir = $_SESSION['bulk_pdf_jobs'][$jobId]['dir'];
            $zipName = "Laporan_Semua_Murid_{$bulan}_{$tahun}.zip";
            $zipPath = $tmpDir . '/' . $zipName;
            $files = glob("$tmpDir/*.pdf") ?: [];

            if (empty($files)) {
                $this->json(['success' => false, 'status' => 'error', 'message' => 'Tidak ada file PDF untuk dikompres.'], 400);
            }

            if (class_exists('ZipArchive')) {
                $zip = new \ZipArchive();
                if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                    foreach ($files as $file) {
                        $zip->addFile($file, basename($file));
                    }
                    $zip->close();
                }
            } else {
                require_once $baseDir . '/includes/lib/pclzip.lib.php';
                $archive = new \PclZip($zipPath);
                $archive->create($files, PCLZIP_OPT_REMOVE_PATH, $tmpDir);
            }

            if (!file_exists($zipPath) || filesize($zipPath) === 0) {
                $this->json(['success' => false, 'status' => 'error', 'message' => 'Gagal membuat arsip ZIP.'], 500);
            }

            $this->json([
                'success' => true,
                'status' => 'ok',
                'message' => 'Arsip ZIP berhasil dibuat.',
                'download_url' => "generate_bulk_pdf.php?tahun=$tahun&bulan=$bulan&action=download&job_id=$jobId",
            ]);
        }

        if ($action === 'download') {
            $jobId = trim((string)($_GET['job_id'] ?? ''));
            if (!preg_match('/^[a-f0-9]{16,32}$/', $jobId)) {
                http_response_code(403);
                die('Job ID tidak valid.');
            }
            if (!isset($_SESSION['bulk_pdf_jobs'][$jobId])) {
                http_response_code(404);
                die('Sesi unduhan tidak ditemukan atau sudah kedaluwarsa.');
            }

            $tmpDir = $_SESSION['bulk_pdf_jobs'][$jobId]['dir'];
            $zipName = "Laporan_Semua_Murid_{$bulan}_{$tahun}.zip";
            $zipPath = $tmpDir . '/' . $zipName;
            if (file_exists($zipPath)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . basename($zipPath) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($zipPath));
                if (ob_get_level() > 0) {
                    ob_end_clean();
                }
                flush();
                $handle = fopen($zipPath, 'rb');
                while (!feof($handle)) {
                    echo fread($handle, 1024 * 1024);
                    flush();
                }
                fclose($handle);
                $allFiles = glob("$tmpDir/*.*");
                if ($allFiles) array_map('unlink', $allFiles);
                @rmdir($tmpDir);
                unset($_SESSION['bulk_pdf_jobs'][$jobId]);
                exit;
            } else {
                http_response_code(404);
                die('File ZIP tidak ditemukan atau gagal dibuat.');
            }
        }

        if ($action === 'cleanup') {
            if (!$this->isPost()) {
                $this->json(['success' => false, 'status' => 'error', 'message' => 'Metode permintaan harus POST.'], 405);
            }
            $this->verifyCsrf();

            $jobId = trim((string)($_POST['job_id'] ?? $_GET['job_id'] ?? ''));
            if (!preg_match('/^[a-f0-9]{16,32}$/', $jobId)) {
                $this->json(['success' => false, 'status' => 'error', 'message' => 'Job ID tidak valid.'], 403);
            }
            if (!isset($_SESSION['bulk_pdf_jobs'][$jobId])) {
                $this->json(['success' => false, 'status' => 'error', 'message' => 'Sesi bulk job tidak ditemukan.'], 404);
            }

            $tmpDir = $_SESSION['bulk_pdf_jobs'][$jobId]['dir'];
            $allFiles = glob("$tmpDir/*.*");
            if ($allFiles) array_map('unlink', $allFiles);
            @rmdir($tmpDir);
            unset($_SESSION['bulk_pdf_jobs'][$jobId]);

            $this->json(['success' => true, 'status' => 'ok', 'message' => 'File sementara berhasil dibersihkan']);
        }

        $this->render('laporan/bulk', [
            'tahun' => $tahun,
            'bulan' => $bulan,
        ]);
    }
}
