<?php
namespace App\Services;

use PDO;
use PDOException;
use Exception;
use Throwable;
use App\Core\Database;
use App\Core\Auth;
/**
 * Iringan (Accompaniment Track) Domain Service
 * Handles accompaniment tracks (Google Drive minus-one links) for students and hybrid song resolution.
 */
class IringanService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT im.*, m.nama as murid_nama FROM iringan_murid im LEFT JOIN murid m ON im.id_murid=m.id WHERE im.id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function countForMurid(int $muridId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM iringan_murid WHERE id_murid = ?');
        $stmt->execute([$muridId]);
        return (int)$stmt->fetchColumn();
    }

    public function getHybridForMurid(int $muridId, string $search = '', string $kategori = ''): array
    {
        $search = trim($search);
        $kategori = trim($kategori);

        $paramsOrtu = [$muridId];
        $whereOrtu = 'WHERE im.id_murid = ?';
        if ($kategori !== '') {
            $whereOrtu .= ' AND im.kategori = ?';
            $paramsOrtu[] = $kategori;
        }
        if ($search !== '') {
            $whereOrtu .= ' AND (im.judul_iringan LIKE ? OR im.catatan LIKE ?)';
            $paramsOrtu[] = '%' . $search . '%';
            $paramsOrtu[] = '%' . $search . '%';
        }

        $stmtOrtu = $this->pdo->prepare("SELECT im.*, COALESCE(NULLIF(a.nama,''), a.username) as coach_nama, l.judul as master_judul, l.youtube_link
                                           FROM iringan_murid im
                                           LEFT JOIN admin a ON im.created_by = a.id
                                           LEFT JOIN lagu l ON im.id_lagu = l.id_lagu
                                           $whereOrtu
                                           ORDER BY im.kategori ASC, im.id DESC");
        $stmtOrtu->execute($paramsOrtu);
        $customRows = $stmtOrtu->fetchAll();

        // 2. Fetch tracks from session history having master song iringan link
        $assignedLaguIds = array_filter(array_column($customRows, 'id_lagu'));
        $autoRows = [];
        if ($kategori === '' || $kategori === 'latihan') {
            $autoStmt = $this->pdo->prepare("SELECT DISTINCT l.id_lagu, l.judul as judul_iringan, l.iringan_link as gdrive_link, l.youtube_link, 'latihan' as kategori,
                                                      'Tersedia dari materi latihan pertemuan anak' as catatan, NULL as coach_nama, 1 as is_from_master
                                               FROM pertemuan p
                                               JOIN lagu l ON p.id_lagu = l.id_lagu
                                               WHERE p.id_murid = ? AND l.iringan_link IS NOT NULL AND l.iringan_link != ''
                                               ORDER BY l.judul ASC");
            $autoStmt->execute([$muridId]);
            $rawAuto = $autoStmt->fetchAll();
            foreach ($rawAuto as $ra) {
                if (!in_array((int)$ra['id_lagu'], $assignedLaguIds, true)) {
                    if ($search === '' || stripos($ra['judul_iringan'], $search) !== false) {
                        $autoRows[] = $ra;
                    }
                }
            }
        }

        return array_merge($customRows, $autoRows);
    }

    public function getPaginated(int $page = 1, int $perPage = 10, string $search = '', int $filterMurid = 0, string $filterKategori = ''): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $search = trim($search);
        $filterKategori = trim($filterKategori);

        $whereClauses = [];
        $paramsStaff = [];

        if ($search !== '') {
            $whereClauses[] = '(im.judul_iringan LIKE ? OR m.nama LIKE ? OR im.catatan LIKE ?)';
            $term = '%' . $search . '%';
            $paramsStaff[] = $term;
            $paramsStaff[] = $term;
            $paramsStaff[] = $term;
        }

        if ($filterMurid > 0) {
            $whereClauses[] = 'im.id_murid = ?';
            $paramsStaff[] = $filterMurid;
        }

        if ($filterKategori !== '') {
            $whereClauses[] = 'im.kategori = ?';
            $paramsStaff[] = $filterKategori;
        }

        $whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM iringan_murid im LEFT JOIN murid m ON im.id_murid = m.id $whereSql");
        $countStmt->execute($paramsStaff);
        $totalRows = (int)$countStmt->fetchColumn();

        $totalPages = max(1, (int)ceil($totalRows / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $listStmt = $this->pdo->prepare("SELECT im.*, m.nama as murid_nama, COALESCE(NULLIF(a.nama,''), a.username) as creator_nama, l.judul as master_judul
                                           FROM iringan_murid im
                                           LEFT JOIN murid m ON im.id_murid = m.id
                                           LEFT JOIN admin a ON im.created_by = a.id
                                           LEFT JOIN lagu l ON im.id_lagu = l.id_lagu
                                           $whereSql
                                           ORDER BY im.id DESC
                                           LIMIT $perPage OFFSET $offset");
        $listStmt->execute($paramsStaff);
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

    public function create(int $idMurid, ?int $idLagu, string $judulIringan, string $kategori, string $gdriveLink, string $catatan, int $createdBy): array
    {
        if ($idMurid <= 0) {
            return ['success' => false, 'message' => 'Silakan pilih murid terlebih dahulu.'];
        }

        $checkMurid = $this->pdo->prepare('SELECT nama FROM murid WHERE id = ?');
        $checkMurid->execute([$idMurid]);
        $muridNama = $checkMurid->fetchColumn();
        if (!$muridNama) {
            return ['success' => false, 'message' => 'Murid yang dipilih tidak ditemukan.'];
        }

        $idLagu = ($idLagu && $idLagu > 0) ? $idLagu : null;
        $judulIringan = trim($judulIringan);

        if (empty($judulIringan) && $idLagu) {
            $stmtLagu = $this->pdo->prepare('SELECT judul FROM lagu WHERE id_lagu = ?');
            $stmtLagu->execute([$idLagu]);
            $judulIringan = $stmtLagu->fetchColumn() ?: 'Lagu Iringan';
        }

        if (empty($judulIringan)) {
            return ['success' => false, 'message' => 'Judul lagu iringan tidak boleh kosong.'];
        }

        $gdriveLink = trim($gdriveLink);
        if (empty($gdriveLink)) {
            return ['success' => false, 'message' => 'Link Google Drive tidak boleh kosong.'];
        }

        if (!preg_match('~^https?://~i', $gdriveLink)) {
            $gdriveLink = 'https://' . $gdriveLink;
        }

        $kategori = in_array($kategori, ['latihan', 'lomba', 'lainnya'], true) ? $kategori : 'latihan';

        try {
            $stmt = $this->pdo->prepare('INSERT INTO iringan_murid (id_murid, id_lagu, judul_iringan, kategori, gdrive_link, catatan, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$idMurid, $idLagu, $judulIringan, $kategori, $gdriveLink, trim($catatan), $createdBy]);
            $newId = (int)$this->pdo->lastInsertId();
            ActivityLogService::log('TAMBAH_IRINGAN', "Menambahkan lagu iringan '$judulIringan' untuk '$muridNama'", 'success');

            return [
                'success' => true,
                'message' => "Lagu iringan '$judulIringan' untuk '$muridNama' berhasil ditambahkan.",
                'id' => $newId,
            ];
        } catch (PDOException $e) {
            error_log('[IringanService::create] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan database saat menyimpan data iringan. Silakan coba lagi.'];
        } catch (Exception $e) {
            error_log('[IringanService::create] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses data. Silakan coba lagi.'];
        }
    }

    public function update(int $id, int $idMurid, ?int $idLagu, string $judulIringan, string $kategori, string $gdriveLink, string $catatan): array
    {
        $idLagu = ($idLagu && $idLagu > 0) ? $idLagu : null;
        $judulIringan = trim($judulIringan);
        $gdriveLink = trim($gdriveLink);

        if ($id <= 0 || $idMurid <= 0 || empty($judulIringan) || empty($gdriveLink)) {
            return ['success' => false, 'message' => 'Data lagu iringan tidak lengkap.'];
        }

        if (!preg_match('~^https?://~i', $gdriveLink)) {
            $gdriveLink = 'https://' . $gdriveLink;
        }

        $kategori = in_array($kategori, ['latihan', 'lomba', 'lainnya'], true) ? $kategori : 'latihan';

        try {
            $stmt = $this->pdo->prepare('UPDATE iringan_murid SET id_murid = ?, id_lagu = ?, judul_iringan = ?, kategori = ?, gdrive_link = ?, catatan = ? WHERE id = ?');
            $stmt->execute([$idMurid, $idLagu, $judulIringan, $kategori, $gdriveLink, trim($catatan), $id]);
            ActivityLogService::log('EDIT_IRINGAN', "Memperbarui lagu iringan '$judulIringan' (ID: $id)", 'info');

            return ['success' => true, 'message' => "Lagu iringan '$judulIringan' berhasil diperbarui."];
        } catch (PDOException $e) {
            error_log('[IringanService::update] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan database saat memperbarui data iringan. Silakan coba lagi.'];
        } catch (Exception $e) {
            error_log('[IringanService::update] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses data. Silakan coba lagi.'];
        }
    }

    public function delete(int $id): array
    {
        if ($id <= 0) {
            return ['success' => false, 'message' => 'ID iringan tidak valid.'];
        }

        $item = $this->getById($id);
        if (!$item) {
            return ['success' => false, 'message' => 'Data lagu iringan tidak ditemukan.'];
        }

        try {
            $stmtDelete = $this->pdo->prepare('DELETE FROM iringan_murid WHERE id = ?');
            $stmtDelete->execute([$id]);
            ActivityLogService::log('HAPUS_IRINGAN', "Menghapus lagu iringan '{$item['judul_iringan']}' untuk '{$item['murid_nama']}'", 'danger');

            return ['success' => true, 'message' => "Lagu iringan '{$item['judul_iringan']}' untuk '{$item['murid_nama']}' berhasil dihapus."];
        } catch (PDOException $e) {
            error_log('[IringanService::delete] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan database saat menghapus lagu iringan. Silakan coba lagi.'];
        } catch (Exception $e) {
            error_log('[IringanService::delete] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses data. Silakan coba lagi.'];
        }
    }
}

if (!class_exists('IringanService', false)) {
    class_alias(IringanService::class, 'IringanService');
}
