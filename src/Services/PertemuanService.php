<?php
namespace App\Services;

use PDO;
use PDOException;
use Exception;
use Throwable;
use App\Core\Database;
use App\Core\Auth;
/**
 * Pertemuan (Meeting/Session) Domain Service
 * Handles meetings CRUD, coach ownership constraints, statistics, and monthly parent aggregations.
 */
class PertemuanService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM pertemuan WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getPaginated(int $page = 1, int $perPage = 10, string $search = ''): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $search = trim($search);

        $params = [];
        $whereSql = '';

        if ($search !== '') {
            $term = '%' . $search . '%';
            $whereSql = "WHERE m.nama LIKE ? OR l.judul LIKE ? OR COALESCE(NULLIF(a.nama,''), a.username) LIKE ? OR p.catatan LIKE ? OR CAST(p.tanggal AS CHAR) LIKE ?";
            $params = [$term, $term, $term, $term, $term];
        }

        $countStmt = $this->pdo->prepare("SELECT COUNT(*)
                                            FROM pertemuan p
                                            LEFT JOIN murid m ON p.id_murid=m.id
                                            LEFT JOIN lagu l ON p.id_lagu=l.id_lagu
                                            LEFT JOIN admin a ON p.coach_id=a.id
                                            $whereSql");
        $countStmt->execute($params);
        $totalRows = (int)$countStmt->fetchColumn();

        $totalPages = max(1, (int)ceil($totalRows / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $listStmt = $this->pdo->prepare("SELECT p.*, m.nama as murid_nama, l.judul as lagu_judul, COALESCE(NULLIF(a.nama,''), a.username) as coach_nama
                                           FROM pertemuan p
                                           LEFT JOIN murid m ON p.id_murid=m.id
                                           LEFT JOIN lagu l ON p.id_lagu=l.id_lagu
                                           LEFT JOIN admin a ON p.coach_id=a.id
                                           $whereSql
                                           ORDER BY p.tanggal DESC, p.id DESC
                                           LIMIT $perPage OFFSET $offset");
        $listStmt->execute($params);
        $rows = $listStmt->fetchAll();

        return [
            'data' => $rows,
            'total' => $totalRows,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'perPage' => $perPage,
            'offset' => $offset,
        ];
    }

    public function create(int $idMurid, ?int $idLagu, string $tanggal, string $catatan, ?int $coachId = null): array
    {
        if ($idMurid <= 0) {
            return ['success' => false, 'message' => 'Silakan pilih murid terlebih dahulu.'];
        }

        $checkMurid = $this->pdo->prepare('SELECT COUNT(*) FROM murid WHERE id = ?');
        $checkMurid->execute([$idMurid]);
        if ((int)$checkMurid->fetchColumn() === 0) {
            return ['success' => false, 'message' => 'Murid yang dipilih tidak ditemukan dalam database.'];
        }

        $tanggal = trim($tanggal);
        if (empty($tanggal) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $tanggal, $m) || !checkdate((int)$m[2], (int)$m[3], (int)$m[1])) {
            return ['success' => false, 'message' => 'Format tanggal tidak valid. Gunakan format YYYY-MM-DD yang benar.'];
        }

        if ($idLagu !== null && $idLagu > 0) {
            $checkLagu = $this->pdo->prepare('SELECT COUNT(*) FROM lagu WHERE id_lagu = ?');
            $checkLagu->execute([$idLagu]);
            if ((int)$checkLagu->fetchColumn() === 0) {
                $idLagu = null;
            }
        } else {
            $idLagu = null;
        }

        try {
            $stmt = $this->pdo->prepare('INSERT INTO pertemuan (id_murid, id_lagu, tanggal, catatan, coach_id) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$idMurid, $idLagu, $tanggal, trim($catatan), $coachId]);
            $newId = (int)$this->pdo->lastInsertId();
            $tglFmt = date('d/m/Y', strtotime($tanggal));
            ActivityLogService::log('TAMBAH_PERTEMUAN', "Mencatat sesi latihan pertemuan tanggal {$tglFmt}", 'success');

            return [
                'success' => true,
                'message' => 'Catatan pertemuan berhasil ditambahkan.',
                'id' => $newId,
            ];
        } catch (PDOException $e) {
            error_log('[PertemuanService::create] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan database saat menyimpan data pertemuan. Silakan coba lagi.'];
        } catch (Exception $e) {
            error_log('[PertemuanService::create] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses data. Silakan coba lagi.'];
        }
    }

    public function update(int $id, int $idMurid, ?int $idLagu, string $tanggal, string $catatan, string $userRole, int $userId): array
    {
        if ($id <= 0) {
            return ['success' => false, 'message' => 'ID pertemuan tidak valid.'];
        }

        if ($idMurid <= 0) {
            return ['success' => false, 'message' => 'Silakan pilih murid terlebih dahulu.'];
        }

        $tanggal = trim($tanggal);
        if (empty($tanggal) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $tanggal, $m) || !checkdate((int)$m[2], (int)$m[3], (int)$m[1])) {
            return ['success' => false, 'message' => 'Format tanggal tidak valid. Gunakan format YYYY-MM-DD yang benar.'];
        }

        $meeting = $this->getById($id);
        if (!$meeting) {
            return ['success' => false, 'message' => 'Data pertemuan tidak ditemukan.'];
        }

        if ($idLagu !== null && $idLagu > 0) {
            $checkLagu = $this->pdo->prepare('SELECT COUNT(*) FROM lagu WHERE id_lagu = ?');
            $checkLagu->execute([$idLagu]);
            if ((int)$checkLagu->fetchColumn() === 0) {
                $idLagu = null;
            }
        } else {
            $idLagu = null;
        }

        try {
            if ($userRole === 'coach') {
                if ((int)$meeting['coach_id'] !== $userId) {
                    return ['success' => false, 'message' => 'Anda hanya dapat mengedit pertemuan yang Anda bimbing sendiri.'];
                }
                $stmt = $this->pdo->prepare('UPDATE pertemuan SET id_murid=?, id_lagu=?, tanggal=?, catatan=? WHERE id=? AND coach_id=?');
                $stmt->execute([$idMurid, $idLagu, $tanggal, trim($catatan), $id, $userId]);
            } else {
                $stmt = $this->pdo->prepare('UPDATE pertemuan SET id_murid=?, id_lagu=?, tanggal=?, catatan=? WHERE id=?');
                $stmt->execute([$idMurid, $idLagu, $tanggal, trim($catatan), $id]);
            }

            $tglFmt = date('d/m/Y', strtotime($tanggal));
            ActivityLogService::log('EDIT_PERTEMUAN', "Memperbarui catatan sesi pertemuan ID: {$id} ({$tglFmt})", 'info');

            return ['success' => true, 'message' => 'Catatan pertemuan berhasil diperbarui.'];
        } catch (PDOException $e) {
            error_log('[PertemuanService::update] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan database saat memperbarui data pertemuan. Silakan coba lagi.'];
        } catch (Exception $e) {
            error_log('[PertemuanService::update] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses data. Silakan coba lagi.'];
        }
    }

    public function delete(int $id, string $userRole, int $userId): array
    {
        if ($id <= 0) {
            return ['success' => false, 'message' => 'ID pertemuan tidak valid.'];
        }

        $meeting = $this->getById($id);
        if (!$meeting) {
            return ['success' => false, 'message' => 'Data pertemuan tidak ditemukan.'];
        }

        try {
            if ($userRole === 'coach') {
                if ((int)$meeting['coach_id'] !== $userId) {
                    return ['success' => false, 'message' => 'Anda hanya dapat menghapus pertemuan yang Anda dampingi.'];
                }
                $stmt = $this->pdo->prepare('DELETE FROM pertemuan WHERE id=? AND coach_id=?');
                $stmt->execute([$id, $userId]);
            } else {
                $stmt = $this->pdo->prepare('DELETE FROM pertemuan WHERE id=?');
                $stmt->execute([$id]);
            }

            ActivityLogService::log('HAPUS_PERTEMUAN', "Menghapus data sesi pertemuan ID: {$id}", 'danger');

            return ['success' => true, 'message' => 'Data pertemuan berhasil dihapus.'];
        } catch (PDOException $e) {
            error_log('[PertemuanService::delete] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan database saat menghapus data pertemuan. Silakan coba lagi.'];
        } catch (Exception $e) {
            error_log('[PertemuanService::delete] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses data. Silakan coba lagi.'];
        }
    }

    public function getMonthlyCountForMurid(int $muridId, int $year, int $month): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM pertemuan WHERE id_murid = ? AND YEAR(tanggal) = ? AND MONTH(tanggal) = ?');
        $stmt->execute([$muridId, $year, $month]);
        return (int)$stmt->fetchColumn();
    }

    public function getDistinctSongsCountForMurid(int $muridId, int $year, int $month): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(DISTINCT id_lagu) FROM pertemuan WHERE id_murid = ? AND id_lagu IS NOT NULL AND YEAR(tanggal) = ? AND MONTH(tanggal) = ?');
        $stmt->execute([$muridId, $year, $month]);
        return (int)$stmt->fetchColumn();
    }

    public function getMonthlyListForMurid(int $muridId, int $year, int $month): array
    {
        $stmt = $this->pdo->prepare("SELECT p.*, l.judul as lagu_judul, l.iringan_link, l.tingkat, COALESCE(NULLIF(a.nama,''), a.username) as coach_nama
                                      FROM pertemuan p
                                      LEFT JOIN lagu l ON p.id_lagu = l.id_lagu
                                      LEFT JOIN admin a ON p.coach_id = a.id
                                      WHERE p.id_murid = ? AND YEAR(p.tanggal) = ? AND MONTH(p.tanggal) = ?
                                      ORDER BY p.tanggal DESC, p.id DESC");
        $stmt->execute([$muridId, $year, $month]);
        return $stmt->fetchAll();
    }
}

if (!class_exists('PertemuanService', false)) {
    class_alias(PertemuanService::class, 'PertemuanService');
}
