<?php
namespace App\Services;

use PDO;
use PDOException;
use Exception;
use Throwable;
use App\Core\Database;
use App\Core\Auth;
/**
 * Refleksi (Monthly Reflection) Domain Service
 * Handles monthly reflection evaluations, unique constraint validation (murid+year+month), and parent viewing.
 */
class RefleksiService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT r.*, m.nama as murid_nama FROM refleksi r JOIN murid m ON r.id_murid=m.id WHERE r.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getByMuridMonthYear(int $muridId, int $year, int $month): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM refleksi WHERE id_murid = ? AND tahun = ? AND bulan = ? LIMIT 1');
        $stmt->execute([$muridId, $year, $month]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Mengambil refleksi untuk murid pada periode tertentu.
     * Jika belum ada catatan baru pada bulan tersebut, otomatis mengambil refleksi
     * dari bulan/periode sebelumnya yang paling baru agar orang tua tetap dapat melihat arahan coach.
     */
    public function getEffectiveRefleksi(int $muridId, int $year, int $month): ?array
    {
        // 1. Cek apakah ada refleksi persis pada tahun dan bulan yang dipilih
        $exact = $this->getByMuridMonthYear($muridId, $year, $month);
        if ($exact) {
            return $exact;
        }

        // 2. Jika tidak ada, ambil refleksi terakhir yang ada sebelum atau pada periode tersebut
        $stmtPrev = $this->pdo->prepare('
            SELECT * FROM refleksi 
            WHERE id_murid = ? AND (tahun < ? OR (tahun = ? AND bulan <= ?))
            ORDER BY tahun DESC, bulan DESC, id DESC 
            LIMIT 1
        ');
        $stmtPrev->execute([$muridId, $year, $year, $month]);
        $prev = $stmtPrev->fetch();
        if ($prev) {
            return $prev;
        }

        // 3. Fallback terakhir: jika tidak ada di masa lalu, ambil refleksi paling baru yang tersedia
        $stmtLatest = $this->pdo->prepare('
            SELECT * FROM refleksi 
            WHERE id_murid = ? 
            ORDER BY tahun DESC, bulan DESC, id DESC 
            LIMIT 1
        ');
        $stmtLatest->execute([$muridId]);
        $latest = $stmtLatest->fetch();
        return $latest ?: null;
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
            $whereSql = "WHERE m.nama LIKE ? OR CONCAT(r.tahun, '-', LPAD(r.bulan, 2, '0')) LIKE ? OR r.refleksi LIKE ? OR r.saran LIKE ? OR r.rencana_perbaikan LIKE ?";
            $params = [$term, $term, $term, $term, $term];
        }

        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM refleksi r LEFT JOIN murid m ON r.id_murid=m.id $whereSql");
        $countStmt->execute($params);
        $totalRows = (int)$countStmt->fetchColumn();

        $totalPages = max(1, (int)ceil($totalRows / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $listStmt = $this->pdo->prepare("SELECT r.*, m.nama as murid_nama
                                           FROM refleksi r
                                           LEFT JOIN murid m ON r.id_murid=m.id
                                           $whereSql
                                           ORDER BY r.tahun DESC, r.bulan DESC, r.id DESC
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

    public function create(int $idMurid, int $tahun, int $bulan, string $refleksi, string $rencana, string $saran): array
    {
        if ($idMurid <= 0) {
            return ['success' => false, 'message' => 'Silakan pilih murid terlebih dahulu.'];
        }

        if ($bulan < 1 || $bulan > 12) {
            return ['success' => false, 'message' => 'Bulan harus bernilai antara 1 sampai 12.'];
        }

        if ($tahun < 2000 || $tahun > 2100) {
            return ['success' => false, 'message' => 'Tahun tidak valid.'];
        }

        $checkMurid = $this->pdo->prepare('SELECT nama FROM murid WHERE id = ?');
        $checkMurid->execute([$idMurid]);
        $muridNama = $checkMurid->fetchColumn();
        if (!$muridNama) {
            return ['success' => false, 'message' => 'Murid yang dipilih tidak ditemukan.'];
        }

        // Check duplicate constraint
        $checkDup = $this->pdo->prepare('SELECT COUNT(*) FROM refleksi WHERE id_murid = ? AND tahun = ? AND bulan = ?');
        $checkDup->execute([$idMurid, $tahun, $bulan]);
        if ((int)$checkDup->fetchColumn() > 0) {
            return [
                'success' => false,
                'message' => "Refleksi untuk murid '$muridNama' pada periode $tahun-$bulan sudah ada. Silakan gunakan fitur edit.",
            ];
        }

        try {
            $stmt = $this->pdo->prepare('INSERT INTO refleksi (id_murid, tahun, bulan, refleksi, rencana_perbaikan, saran) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$idMurid, $tahun, $bulan, trim($refleksi), trim($rencana), trim($saran)]);
            $newId = (int)$this->pdo->lastInsertId();

            return [
                'success' => true,
                'message' => "Refleksi untuk '$muridNama' berhasil disimpan.",
                'id' => $newId,
            ];
        } catch (PDOException $e) {
            error_log('[RefleksiService::create] ' . $e->getMessage());
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'unik_murid_bulan') !== false) {
                return ['success' => false, 'message' => 'Gagal menyimpan: Refleksi untuk murid ini pada bulan dan tahun tersebut sudah pernah dibuat.'];
            }
            return ['success' => false, 'message' => 'Terjadi kesalahan database saat menyimpan refleksi. Silakan coba lagi.'];
        } catch (Exception $e) {
            error_log('[RefleksiService::create] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses data. Silakan coba lagi.'];
        }
    }

    public function update(int $id, int $idMurid, int $tahun, int $bulan, string $refleksi, string $rencana, string $saran): array
    {
        if ($id <= 0) {
            return ['success' => false, 'message' => 'ID refleksi tidak valid.'];
        }

        if ($idMurid <= 0) {
            return ['success' => false, 'message' => 'Silakan pilih murid terlebih dahulu.'];
        }

        if ($bulan < 1 || $bulan > 12) {
            return ['success' => false, 'message' => 'Bulan harus bernilai antara 1 sampai 12.'];
        }

        if ($tahun < 2000 || $tahun > 2100) {
            return ['success' => false, 'message' => 'Tahun tidak valid.'];
        }

        $checkMurid = $this->pdo->prepare('SELECT nama FROM murid WHERE id = ?');
        $checkMurid->execute([$idMurid]);
        $muridNama = $checkMurid->fetchColumn();
        if (!$muridNama) {
            return ['success' => false, 'message' => 'Murid yang dipilih tidak ditemukan.'];
        }

        // Check duplicate for other records
        $checkDup = $this->pdo->prepare('SELECT COUNT(*) FROM refleksi WHERE id_murid = ? AND tahun = ? AND bulan = ? AND id != ?');
        $checkDup->execute([$idMurid, $tahun, $bulan, $id]);
        if ((int)$checkDup->fetchColumn() > 0) {
            return [
                'success' => false,
                'message' => "Periode $tahun-$bulan untuk murid '$muridNama' sudah ada di catatan lain.",
            ];
        }

        try {
            $stmt = $this->pdo->prepare('UPDATE refleksi SET id_murid=?, tahun=?, bulan=?, refleksi=?, rencana_perbaikan=?, saran=? WHERE id=?');
            $stmt->execute([$idMurid, $tahun, $bulan, trim($refleksi), trim($rencana), trim($saran), $id]);

            return ['success' => true, 'message' => "Refleksi untuk '$muridNama' berhasil diperbarui."];
        } catch (PDOException $e) {
            error_log('[RefleksiService::update] ' . $e->getMessage());
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'unik_murid_bulan') !== false) {
                return ['success' => false, 'message' => 'Gagal menyimpan: Refleksi untuk murid ini pada bulan dan tahun tersebut sudah pernah dibuat.'];
            }
            return ['success' => false, 'message' => 'Terjadi kesalahan database saat memperbarui refleksi. Silakan coba lagi.'];
        } catch (Exception $e) {
            error_log('[RefleksiService::update] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses data. Silakan coba lagi.'];
        }
    }

    public function delete(int $id): array
    {
        if ($id <= 0) {
            return ['success' => false, 'message' => 'ID refleksi tidak valid.'];
        }

        $ref = $this->getById($id);
        if (!$ref) {
            return ['success' => false, 'message' => 'Data refleksi tidak ditemukan.'];
        }

        try {
            $stmt = $this->pdo->prepare('DELETE FROM refleksi WHERE id=?');
            $stmt->execute([$id]);

            return ['success' => true, 'message' => "Refleksi untuk '{$ref['murid_nama']}' berhasil dihapus."];
        } catch (PDOException $e) {
            error_log('[RefleksiService::delete] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan database saat menghapus data refleksi. Silakan coba lagi.'];
        } catch (Exception $e) {
            error_log('[RefleksiService::delete] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses data. Silakan coba lagi.'];
        }
    }
}

if (!class_exists('RefleksiService', false)) {
    class_alias(RefleksiService::class, 'RefleksiService');
}
