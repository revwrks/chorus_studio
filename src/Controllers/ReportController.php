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
            die('id_murid required');
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

        $tahun = intval($_GET['tahun'] ?? date('Y'));
        $bulan = intval($_GET['bulan'] ?? date('n'));
        $action = $_GET['action'] ?? 'view';

        $baseDir = dirname(__DIR__, 2);
        $tmpBase = $baseDir . '/assets/tmp/bulk_reports';
        if (!is_dir($tmpBase)) @mkdir($tmpBase, 0777, true);
        $tmpDir = $tmpBase . '/bulk_' . $tahun . '_' . $bulan . '_' . $this->currentUserId();

        if ($action === 'init') {
            header('Content-Type: application/json');
            if (!is_dir($tmpDir)) @mkdir($tmpDir, 0777, true);
            $oldFiles = glob("$tmpDir/*.*");
            if ($oldFiles) array_map('unlink', $oldFiles);

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
            echo json_encode(['status' => 'ok', 'data' => $muridRows]);
            exit;
        }

        if ($action === 'generate') {
            header('Content-Type: application/json');
            $id_murid = intval($_POST['id_murid'] ?? 0);
            if ($id_murid) {
                $report = build_report_pdf($this->pdo, $id_murid, $tahun, $bulan);
                if ($report) {
                    $pdf = $report['pdf'];
                    $fileName = $report['file_name'];
                    $pdf->Output('F', $tmpDir . '/' . $fileName);
                }
            }
            echo json_encode(['status' => 'ok']);
            exit;
        }

        if ($action === 'zip') {
            header('Content-Type: application/json');
            $zipName = "Laporan_Semua_Murid_{$bulan}_{$tahun}.zip";
            $zipPath = $tmpDir . '/' . $zipName;
            $files = glob("$tmpDir/*.pdf");

            if (class_exists('ZipArchive')) {
                $zip = new \ZipArchive();
                if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
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

            echo json_encode(['status' => 'ok', 'download_url' => "generate_bulk_pdf.php?tahun=$tahun&bulan=$bulan&action=download"]);
            exit;
        }

        if ($action === 'download') {
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
                ob_clean();
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
                exit;
            } else {
                die('File ZIP tidak ditemukan atau gagal dibuat.');
            }
        }

        if ($action === 'cleanup') {
            header('Content-Type: application/json');
            $allFiles = glob("$tmpDir/*.*");
            if ($allFiles) array_map('unlink', $allFiles);
            @rmdir($tmpDir);
            echo json_encode(['status' => 'ok', 'message' => 'File sementara berhasil dibersihkan']);
            exit;
        }

        $this->render('laporan/bulk', [
            'tahun' => $tahun,
            'bulan' => $bulan,
        ]);
    }
}
