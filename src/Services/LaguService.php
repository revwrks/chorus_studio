<?php
namespace App\Services;

use PDO;
use PDOException;
use Exception;
use Throwable;
use App\Core\Database;
use App\Core\Auth;
/**
 * Lagu (Song) Domain Service
 * Handles song catalog CRUD, link formatting, difficulty level validation, and pagination.
 */
class LaguService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    public function getAll(): array
    {
        return $this->pdo->query('SELECT * FROM lagu ORDER BY judul ASC')->fetchAll();
    }

    public function count(): int
    {
        return (int)$this->pdo->query('SELECT COUNT(*) FROM lagu')->fetchColumn();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM lagu WHERE id_lagu = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function normalizeUrl(?string $url): ?string
    {
        $trimmed = trim((string)$url);
        if ($trimmed === '') {
            return null;
        }
        if (!preg_match('~^https?://~i', $trimmed)) {
            $trimmed = 'https://' . $trimmed;
        }
        return $trimmed;
    }

    public function normalizeTingkat(?string $tingkat): string
    {
        $val = strtolower(trim((string)$tingkat));
        return in_array($val, ['mudah', 'sedang', 'sulit', 'festival'], true) ? $val : 'mudah';
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
            $whereSql = 'WHERE judul LIKE ? OR youtube_link LIKE ? OR iringan_link LIKE ? OR tingkat LIKE ?';
            $params = [$term, $term, $term, $term];
        }

        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM lagu $whereSql");
        $countStmt->execute($params);
        $totalRows = (int)$countStmt->fetchColumn();

        $totalPages = max(1, (int)ceil($totalRows / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $listStmt = $this->pdo->prepare("SELECT * FROM lagu $whereSql ORDER BY id_lagu DESC LIMIT $perPage OFFSET $offset");
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

    public function create(string $judul, ?string $youtubeLink = null, ?string $iringanLink = null, string $tingkat = 'mudah'): array
    {
        $judul = trim($judul);
        if (empty($judul)) {
            return ['success' => false, 'message' => 'Judul lagu tidak boleh kosong.'];
        }

        $youtubeLink = $this->normalizeUrl($youtubeLink);
        $iringanLink = $this->normalizeUrl($iringanLink);
        $tingkat = $this->normalizeTingkat($tingkat);

        try {
            $stmt = $this->pdo->prepare('INSERT INTO lagu (judul, youtube_link, iringan_link, tingkat) VALUES (?, ?, ?, ?)');
            $stmt->execute([$judul, $youtubeLink, $iringanLink, $tingkat]);
            $newId = (int)$this->pdo->lastInsertId();
            ActivityLogService::log('TAMBAH_LAGU', "Menambahkan lagu baru: '$judul' (Tingkat: $tingkat)", 'success');

            return [
                'success' => true,
                'message' => "Lagu '$judul' berhasil ditambahkan.",
                'id' => $newId,
            ];
        } catch (PDOException $e) {
            error_log('[LaguService::create] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan database saat menyimpan data lagu. Silakan coba lagi.'];
        } catch (Exception $e) {
            error_log('[LaguService::create] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses data. Silakan coba lagi.'];
        }
    }

    public function update(int $id, string $judul, ?string $youtubeLink = null, ?string $iringanLink = null, string $tingkat = 'mudah'): array
    {
        $judul = trim($judul);
        if ($id <= 0 || empty($judul)) {
            return ['success' => false, 'message' => 'Data lagu tidak lengkap atau judul kosong.'];
        }

        $youtubeLink = $this->normalizeUrl($youtubeLink);
        $iringanLink = $this->normalizeUrl($iringanLink);
        $tingkat = $this->normalizeTingkat($tingkat);

        try {
            $stmt = $this->pdo->prepare('UPDATE lagu SET judul=?, youtube_link=?, iringan_link=?, tingkat=? WHERE id_lagu=?');
            $stmt->execute([$judul, $youtubeLink, $iringanLink, $tingkat, $id]);
            ActivityLogService::log('EDIT_LAGU', "Memperbarui data lagu: '$judul' (ID: $id)", 'info');

            return ['success' => true, 'message' => "Lagu '$judul' berhasil diperbarui."];
        } catch (PDOException $e) {
            error_log('[LaguService::update] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan database saat memperbarui data lagu. Silakan coba lagi.'];
        } catch (Exception $e) {
            error_log('[LaguService::update] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses data. Silakan coba lagi.'];
        }
    }

    public function delete(int $id): array
    {
        if ($id <= 0) {
            return ['success' => false, 'message' => 'ID lagu tidak valid.'];
        }

        try {
            $lagu = $this->getById($id);
            if (!$lagu) {
                return ['success' => false, 'message' => 'Data lagu tidak ditemukan.'];
            }

            $stmt = $this->pdo->prepare('DELETE FROM lagu WHERE id_lagu=?');
            $stmt->execute([$id]);
            ActivityLogService::log('HAPUS_LAGU', "Menghapus lagu: '{$lagu['judul']}' (ID: $id)", 'danger');

            return ['success' => true, 'message' => "Lagu '{$lagu['judul']}' berhasil dihapus."];
        } catch (PDOException $e) {
            error_log('[LaguService::delete] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menghapus lagu karena masih terkait dengan data lain atau kendala database.'];
        } catch (Exception $e) {
            error_log('[LaguService::delete] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses data. Silakan coba lagi.'];
        }
    }
}

if (!class_exists('LaguService', false)) {
    class_alias(LaguService::class, 'LaguService');
}
