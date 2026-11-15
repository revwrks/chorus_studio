<?php
namespace App\Services;

use PDO;
use PDOException;
use Exception;
use Throwable;
use App\Core\Database;
use App\Core\Auth;
/**
 * Rencana Pertemuan (Meeting Plan / Target) Domain Service
 * Handles planning monthly vocal session targets per student, tracking realization vs target,
 * generating period options, and supporting coach/admin/parent dashboards.
 */
class RencanaPertemuanService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    /**
     * Get specific plan entry for a student in a month and year.
     */
    public function getTarget(int $idMurid, int $tahun, int $bulan): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT rp.*, m.nama AS murid_nama, m.foto AS murid_foto,
                   COALESCE(NULLIF(a.nama, ""), a.username) AS coach_nama
            FROM rencana_pertemuan rp
            JOIN murid m ON rp.id_murid = m.id
            LEFT JOIN admin a ON rp.coach_id = a.id
            WHERE rp.id_murid = ? AND rp.tahun = ? AND rp.bulan = ?
            LIMIT 1
        ');
        $stmt->execute([$idMurid, $tahun, $bulan]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Get target meeting count for a student in a given period.
     * Returns 0 if no plan exists.
     */
    public function getMonthlyTargetForMurid(int $idMurid, int $tahun, int $bulan): int
    {
        $stmt = $this->pdo->prepare('
            SELECT target_pertemuan 
            FROM rencana_pertemuan 
            WHERE id_murid = ? AND tahun = ? AND bulan = ?
            LIMIT 1
        ');
        $stmt->execute([$idMurid, $tahun, $bulan]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (int)$val : 0;
    }

    /**
     * Set or update a single student's meeting target for a month and year.
     */
    public function setTarget(int $idMurid, int $tahun, int $bulan, int $targetPertemuan, ?string $catatan = null, ?int $coachId = null): array
    {
        if ($idMurid <= 0) {
            return ['success' => false, 'message' => 'Murid tidak valid.'];
        }
        if ($tahun < 2020 || $tahun > 2100) {
            return ['success' => false, 'message' => 'Tahun tidak valid.'];
        }
        if ($bulan < 1 || $bulan > 12) {
            return ['success' => false, 'message' => 'Bulan tidak valid.'];
        }

        $targetPertemuan = max(0, $targetPertemuan);
        $catatan = trim((string)$catatan);
        $catatan = $catatan === '' ? null : $catatan;

        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO rencana_pertemuan (id_murid, coach_id, tahun, bulan, target_pertemuan, catatan)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    target_pertemuan = VALUES(target_pertemuan),
                    coach_id = COALESCE(VALUES(coach_id), coach_id),
                    catatan = VALUES(catatan),
                    updated_at = CURRENT_TIMESTAMP
            ');
            $stmt->execute([$idMurid, $coachId, $tahun, $bulan, $targetPertemuan, $catatan]);

            ActivityLogService::log('RENCANA_PERTEMUAN', "Mengatur target rencana pertemuan murid (ID: $idMurid) periode {$bulan}/{$tahun} menjadi {$targetPertemuan} sesi", 'info');

            return [
                'success' => true,
                'message' => 'Rencana pertemuan berhasil disimpan.',
                'target' => $targetPertemuan
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Gagal menyimpan rencana pertemuan: ' . $e->getMessage()];
        }
    }

    /**
     * Batch save meeting targets for multiple students.
     * $items is an array of ['id_murid' => int, 'target_pertemuan' => int, 'catatan' => ?string, 'coach_id' => ?int]
     */
    public function batchSetTarget(array $items, int $tahun, int $bulan, ?int $defaultCoachId = null): array
    {
        if (empty($items)) {
            return ['success' => false, 'message' => 'Tidak ada data target yang dikirim.'];
        }

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare('
                INSERT INTO rencana_pertemuan (id_murid, coach_id, tahun, bulan, target_pertemuan, catatan)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    target_pertemuan = VALUES(target_pertemuan),
                    coach_id = COALESCE(VALUES(coach_id), coach_id),
                    catatan = VALUES(catatan),
                    updated_at = CURRENT_TIMESTAMP
            ');

            $countSaved = 0;
            foreach ($items as $item) {
                $idMurid = (int)($item['id_murid'] ?? 0);
                if ($idMurid <= 0) continue;

                $target = max(0, (int)($item['target_pertemuan'] ?? 4));
                $catatan = trim((string)($item['catatan'] ?? ''));
                $catatan = $catatan === '' ? null : $catatan;
                $coachId = !empty($item['coach_id']) ? (int)$item['coach_id'] : $defaultCoachId;

                $stmt->execute([$idMurid, $coachId, $tahun, $bulan, $target, $catatan]);
                $countSaved++;
            }

            $this->pdo->commit();
            ActivityLogService::log('RENCANA_PERTEMUAN', "Menyimpan serentak rencana pertemuan periode {$bulan}/{$tahun} untuk {$countSaved} murid", 'info');

            return [
                'success' => true,
                'message' => "Berhasil menyimpan rencana pertemuan untuk $countSaved murid.",
                'count' => $countSaved
            ];
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['success' => false, 'message' => 'Gagal menyimpan data rencana pertemuan: ' . $e->getMessage()];
        }
    }

    /**
     * Get list of active students for the management table in Rencana Pertemuan.
     * If coachId is provided, filters to students associated with that coach (either through meetings or existing plans),
     * while still allowing adding other active students.
     */
    public function getListForPeriod(int $tahun, int $bulan, ?int $coachId = null): array
    {
        $params = [$tahun, $bulan, $tahun, $bulan];
        $whereCoach = '';

        if ($coachId !== null && $coachId > 0) {
            $whereCoach = 'AND (rp.coach_id = ? OR p_stat.coach_match > 0)';
            $params[] = $coachId;
        }

        // Subquery for actual meetings in this period
        $sql = "
            SELECT 
                m.id AS murid_id,
                m.nama,
                m.foto,
                m.instagram,
                m.status,
                rp.id AS rencana_id,
                COALESCE(rp.target_pertemuan, 4) AS target_pertemuan,
                rp.target_pertemuan IS NOT NULL AS has_plan,
                rp.catatan,
                rp.coach_id,
                COALESCE(NULLIF(a.nama, ''), a.username) AS coach_nama,
                COALESCE(p_stat.total_pertemuan, 0) AS realisasi_pertemuan
            FROM murid m
            LEFT JOIN rencana_pertemuan rp 
                ON rp.id_murid = m.id AND rp.tahun = ? AND rp.bulan = ?
            LEFT JOIN admin a 
                ON rp.coach_id = a.id
            LEFT JOIN (
                SELECT 
                    id_murid, 
                    COUNT(id) AS total_pertemuan,
                    SUM(CASE WHEN coach_id = " . ($coachId ? (int)$coachId : 'coach_id') . " THEN 1 ELSE 0 END) AS coach_match
                FROM pertemuan
                WHERE YEAR(tanggal) = ? AND MONTH(tanggal) = ?
                GROUP BY id_murid
            ) p_stat ON p_stat.id_murid = m.id
            WHERE m.status = 'aktif'
            $whereCoach
            ORDER BY m.nama ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$r) {
            $target = (int)$r['target_pertemuan'];
            $realisasi = (int)$r['realisasi_pertemuan'];
            $r['persentase'] = $target > 0 ? (int)round(($realisasi / $target) * 100) : 0;
            $r['is_tercapai'] = $target > 0 && $realisasi >= $target;
        }
        unset($r);

        return $rows;
    }

    /**
     * Get summary KPI: Total Realisasi / Total Target, plus breakdown list for modal.
     * If coachId is provided, aggregates for that coach's scope.
     */
    public function getSummary(int $tahun, int $bulan, ?int $coachId = null): array
    {
        if ($coachId !== null && $coachId > 0) {
            // Realisasi sesi yang dilakukan coach tersebut
            $stmtRealisasi = $this->pdo->prepare('
                SELECT COUNT(*) 
                FROM pertemuan 
                WHERE coach_id = ? AND YEAR(tanggal) = ? AND MONTH(tanggal) = ?
            ');
            $stmtRealisasi->execute([$coachId, $tahun, $bulan]);
            $totalRealisasi = (int)$stmtRealisasi->fetchColumn();

            // Total target untuk murid-murid yang direncanakan oleh/dengan coach ini
            $stmtTarget = $this->pdo->prepare('
                SELECT COALESCE(SUM(target_pertemuan), 0)
                FROM rencana_pertemuan rp
                JOIN murid m ON rp.id_murid = m.id
                WHERE (rp.coach_id = ? OR rp.id_murid IN (
                    SELECT DISTINCT id_murid FROM pertemuan WHERE coach_id = ? AND YEAR(tanggal) = ? AND MONTH(tanggal) = ?
                ))
                AND rp.tahun = ? AND rp.bulan = ?
            ');
            $stmtTarget->execute([$coachId, $coachId, $tahun, $bulan, $tahun, $bulan]);
            $totalTarget = (int)$stmtTarget->fetchColumn();

            // List murid untuk modal detail
            $stmtList = $this->pdo->prepare('
                SELECT 
                    m.id AS murid_id,
                    m.nama,
                    m.foto,
                    COALESCE(rp.target_pertemuan, 0) AS target_pertemuan,
                    COALESCE(p_stat.realisasi, 0) AS realisasi,
                    COALESCE(NULLIF(a.nama, ""), a.username) AS coach_nama
                FROM murid m
                LEFT JOIN rencana_pertemuan rp 
                    ON rp.id_murid = m.id AND rp.tahun = ? AND rp.bulan = ?
                LEFT JOIN admin a 
                    ON rp.coach_id = a.id
                LEFT JOIN (
                    SELECT id_murid, COUNT(id) AS realisasi
                    FROM pertemuan
                    WHERE coach_id = ? AND YEAR(tanggal) = ? AND MONTH(tanggal) = ?
                    GROUP BY id_murid
                ) p_stat ON p_stat.id_murid = m.id
                WHERE (
                    (rp.coach_id = ? AND rp.target_pertemuan > 0)
                    OR p_stat.realisasi > 0
                )
                ORDER BY realisasi DESC, m.nama ASC
            ');
            $stmtList->execute([$tahun, $bulan, $coachId, $tahun, $bulan, $coachId]);
            $detailList = $stmtList->fetchAll();
        } else {
            // Owner / Admin scope: semua pertemuan & semua target di studio
            $stmtRealisasi = $this->pdo->prepare('
                SELECT COUNT(*) 
                FROM pertemuan 
                WHERE YEAR(tanggal) = ? AND MONTH(tanggal) = ?
            ');
            $stmtRealisasi->execute([$tahun, $bulan]);
            $totalRealisasi = (int)$stmtRealisasi->fetchColumn();

            $stmtTarget = $this->pdo->prepare('
                SELECT COALESCE(SUM(rp.target_pertemuan), 0)
                FROM rencana_pertemuan rp
                JOIN murid m ON rp.id_murid = m.id
                WHERE rp.tahun = ? AND rp.bulan = ?
            ');
            $stmtTarget->execute([$tahun, $bulan]);
            $totalTarget = (int)$stmtTarget->fetchColumn();

            $stmtList = $this->pdo->prepare('
                SELECT 
                    m.id AS murid_id,
                    m.nama,
                    m.foto,
                    COALESCE(rp.target_pertemuan, 0) AS target_pertemuan,
                    COALESCE(p_stat.realisasi, 0) AS realisasi,
                    COALESCE(NULLIF(a.nama, ""), a.username) AS coach_nama
                FROM murid m
                LEFT JOIN rencana_pertemuan rp 
                    ON rp.id_murid = m.id AND rp.tahun = ? AND rp.bulan = ?
                LEFT JOIN admin a 
                    ON rp.coach_id = a.id
                LEFT JOIN (
                    SELECT id_murid, COUNT(id) AS realisasi
                    FROM pertemuan
                    WHERE YEAR(tanggal) = ? AND MONTH(tanggal) = ?
                    GROUP BY id_murid
                ) p_stat ON p_stat.id_murid = m.id
                WHERE (rp.target_pertemuan > 0 OR p_stat.realisasi > 0)
                ORDER BY (COALESCE(p_stat.realisasi, 0) >= COALESCE(rp.target_pertemuan, 0) AND COALESCE(rp.target_pertemuan, 0) > 0) ASC,
                         p_stat.realisasi DESC, m.nama ASC
            ');
            $stmtList->execute([$tahun, $bulan, $tahun, $bulan]);
            $detailList = $stmtList->fetchAll();
        }

        foreach ($detailList as &$item) {
            $t = (int)$item['target_pertemuan'];
            $r = (int)$item['realisasi'];
            $item['persentase'] = $t > 0 ? (int)round(($r / $t) * 100) : ($r > 0 ? 100 : 0);
            $item['is_tercapai'] = $t > 0 && $r >= $t;
        }
        unset($item);

        return [
            'total_realisasi' => $totalRealisasi,
            'total_target' => $totalTarget,
            'total_murid_terencana' => count(array_filter($detailList, fn($d) => (int)$d['target_pertemuan'] > 0)),
            'detail_list' => $detailList
        ];
    }

    /**
     * Generates standard period options starting from current month up to December next year.
     * Used for the Month/Year picker.
     */
    public static function getPeriodOptions(int $selectedTahun, int $selectedBulan): array
    {
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $curYear = (int)date('Y');
        $curMonth = (int)date('n');
        $nextMonth = $curMonth === 12 ? 1 : $curMonth + 1;
        $nextMonthYear = $curMonth === 12 ? $curYear + 1 : $curYear;
        $maxYear = $curYear + 1;

        $options = [];
        for ($y = $curYear; $y <= $maxYear; $y++) {
            $startM = ($y === $curYear) ? $curMonth : 1;
            for ($m = $startM; $m <= 12; $m++) {
                $isCurrent = ($y === $curYear && $m === $curMonth);
                $isNext = ($y === $nextMonthYear && $m === $nextMonth);
                
                $suffix = '';
                if ($isCurrent) {
                    $suffix = ' (Bulan Ini)';
                } elseif ($isNext) {
                    $suffix = ' (Bulan Depan)';
                }

                $options[] = [
                    'tahun' => $y,
                    'bulan' => $m,
                    'label' => $namaBulan[$m] . ' ' . $y . $suffix,
                    'is_selected' => ($y === $selectedTahun && $m === $selectedBulan),
                    'is_current' => $isCurrent,
                    'is_next' => $isNext,
                ];
            }
        }

        return [
            'current' => ['tahun' => $curYear, 'bulan' => $curMonth],
            'next' => ['tahun' => $nextMonthYear, 'bulan' => $nextMonth],
            'options' => $options
        ];
    }

    /**
     * Get detail of realized meetings for a specific student in a specific period (year & month).
     */
    public function getRealisasiDates(int $muridId, int $tahun, int $bulan, ?int $coachId = null): array
    {
        $params = [$muridId, $tahun, $bulan];
        $whereCoach = '';
        if ($coachId !== null && $coachId > 0) {
            $whereCoach = 'AND p.coach_id = ?';
            $params[] = $coachId;
        }

        $sql = "
            SELECT 
                p.id,
                p.tanggal,
                p.catatan,
                p.coach_id,
                l.judul AS lagu_judul,
                COALESCE(NULLIF(a.nama, ''), a.username) AS coach_nama
            FROM pertemuan p
            LEFT JOIN lagu l ON p.id_lagu = l.id_lagu
            LEFT JOIN admin a ON p.coach_id = a.id
            WHERE p.id_murid = ? AND YEAR(p.tanggal) = ? AND MONTH(p.tanggal) = ?
            {$whereCoach}
            ORDER BY p.tanggal ASC, p.id ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $namaHari = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        foreach ($rows as &$r) {
            $ts = strtotime($r['tanggal']);
            $dayName = $namaHari[date('l', $ts)] ?? date('l', $ts);
            $dayNum = date('j', $ts);
            $monthNum = (int)date('n', $ts);
            $yearNum = date('Y', $ts);

            $r['tanggal_formatted'] = "{$dayName}, {$dayNum} {$namaBulan[$monthNum]} {$yearNum}";
            $r['tanggal_short'] = date('d/m/Y', $ts);
            $r['lagu_judul'] = $r['lagu_judul'] ?: null;
            $r['catatan'] = $r['catatan'] ?: null;
            $r['coach_nama'] = $r['coach_nama'] ?: 'Coach';
        }
        unset($r);

        return $rows;
    }
}

if (!class_exists('RencanaPertemuanService', false)) {
    class_alias(RencanaPertemuanService::class, 'RencanaPertemuanService');
}
