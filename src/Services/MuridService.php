<?php
namespace App\Services;

use PDO;
use PDOException;
use Exception;
use Throwable;
use App\Core\Database;
use App\Core\Auth;
require_once __DIR__ . '/ActivityLogService.php';

/**
 * Murid (Student) Domain Service
 * Handles all student CRUD, photo upload, validation, and search/pagination.
 */
class MuridService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    public function normalizeStatus(?string $status): string
    {
        $s = strtolower(trim((string)$status));
        return in_array($s, ['non_aktif', 'non-aktif', 'nonaktif', 'inactive'], true) ? 'non_aktif' : 'aktif';
    }

    public function getAll(?string $status = null): array
    {
        if ($status !== null && trim($status) !== '') {
            $norm = $this->normalizeStatus($status);
            $stmt = $this->pdo->prepare('SELECT * FROM murid WHERE status = ? ORDER BY nama ASC');
            $stmt->execute([$norm]);
            return $stmt->fetchAll();
        }
        return $this->pdo->query('SELECT * FROM murid ORDER BY nama ASC')->fetchAll();
    }

    public function getAllActive(): array
    {
        return $this->getAll('aktif');
    }

    public function count(?string $status = null): int
    {
        if ($status !== null && trim($status) !== '') {
            $norm = $this->normalizeStatus($status);
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM murid WHERE status = ?');
            $stmt->execute([$norm]);
            return (int)$stmt->fetchColumn();
        }
        return (int)$this->pdo->query('SELECT COUNT(*) FROM murid')->fetchColumn();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM murid WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getPaginated(int $page = 1, int $perPage = 10, string $search = '', ?string $status = null): array
    {
        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $search = trim($search);

        $params = [];
        $whereClauses = [];

        if ($search !== '') {
            $term = '%' . $search . '%';
            $whereClauses[] = '(nama LIKE ? OR instagram LIKE ?)';
            $params[] = $term;
            $params[] = $term;
        }

        if ($status !== null && trim($status) !== '') {
            $norm = $this->normalizeStatus($status);
            $whereClauses[] = 'status = ?';
            $params[] = $norm;
        }

        $whereSql = $whereClauses ? ('WHERE ' . implode(' AND ', $whereClauses)) : '';

        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM murid $whereSql");
        $countStmt->execute($params);
        $totalRows = (int)$countStmt->fetchColumn();

        $totalPages = max(1, (int)ceil($totalRows / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $listStmt = $this->pdo->prepare("SELECT * FROM murid $whereSql ORDER BY id DESC LIMIT $perPage OFFSET $offset");
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

    public function handleFotoUpload(?array $file, ?string &$uploadError = null): string|false|null
    {
        if (!$file || empty($file['name']) || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $uploadError = 'Gagal mengunggah file foto (kode error: ' . $file['error'] . ').';
            return false;
        }

        // Limit file size (Maksimal 5MB di server, foto kamera besar otomatis dikompres ke ~150KB di browser)
        if ($file['size'] > 5 * 1024 * 1024) {
            $uploadError = 'Ukuran file foto maksimal adalah 5MB.';
            return false;
        }

        // Check extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowedExts, true)) {
            $uploadError = 'Format file foto tidak didukung. Hanya diperbolehkan: JPG, JPEG, PNG, GIF, WEBP.';
            return false;
        }

        // Check MIME type
        if (function_exists('finfo_open') && !empty($file['tmp_name'])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/pjpeg', 'image/x-png'];
            if (!in_array($mime, $allowedMimes, true)) {
                $uploadError = 'Tipe MIME file foto tidak valid.';
                return false;
            }
        }

        // Regenerate unique file name
        $new = bin2hex(random_bytes(16)) . '.' . $ext;
        $uploadDir = __DIR__ . '/../../assets/uploads';
        $dest = $uploadDir . '/' . $new;

        if (!is_dir($uploadDir)) {
            if (!@mkdir($uploadDir, 0755, true)) {
                $uploadError = 'Gagal membuat folder penyimpanan foto di server.';
                return false;
            }
        }

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return 'assets/uploads/' . $new;
        }

        $uploadError = 'Gagal memindahkan file foto yang diunggah ke folder penyimpanan.';
        return false;
    }

    public function create(string $nama, ?string $instagram = null, ?array $file = null, string $status = 'aktif'): array
    {
        $nama = trim($nama);
        $instagram = trim((string)$instagram);
        $instagram = $instagram === '' ? null : $instagram;
        $status = $this->normalizeStatus($status);

        if (empty($nama)) {
            return ['success' => false, 'message' => 'Nama murid tidak boleh kosong.'];
        }

        $uploadError = null;
        $fotoPath = $file ? $this->handleFotoUpload($file, $uploadError) : null;
        if ($fotoPath === false) {
            return ['success' => false, 'message' => $uploadError ?: 'Gagal mengunggah foto.'];
        }

        try {
            $stmt = $this->pdo->prepare('INSERT INTO murid (nama, instagram, foto, status) VALUES (?, ?, ?, ?)');
            $stmt->execute([$nama, $instagram, $fotoPath, $status]);
            $newId = (int)$this->pdo->lastInsertId();
            $statusLabel = $status === 'aktif' ? 'Aktif' : 'Non-Aktif';
            ActivityLogService::log('TAMBAH_MURID', "Menambahkan murid baru: '$nama' (Status: $statusLabel)", 'success');

            return [
                'success' => true,
                'message' => "Data murid '$nama' berhasil ditambahkan.",
                'id' => $newId,
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Gagal memproses data murid: ' . $e->getMessage()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()];
        }
    }

    public function update(int $id, string $nama, ?string $instagram = null, ?array $file = null, ?string $status = null): array
    {
        $nama = trim($nama);
        $instagram = trim((string)$instagram);
        $instagram = $instagram === '' ? null : $instagram;

        if ($id <= 0 || empty($nama)) {
            return ['success' => false, 'message' => 'Nama murid tidak boleh kosong.'];
        }

        try {
            $oldMurid = $this->getById($id);
            if (!$oldMurid) {
                return ['success' => false, 'message' => 'Data murid tidak ditemukan.'];
            }
            $oldFoto = $oldMurid['foto'] ?? null;
            $currentStatus = $status !== null ? $this->normalizeStatus($status) : ($oldMurid['status'] ?? 'aktif');

            $uploadError = null;
            $fotoPath = $file ? $this->handleFotoUpload($file, $uploadError) : null;
            if ($fotoPath === false) {
                return ['success' => false, 'message' => $uploadError ?: 'Gagal mengunggah foto.'];
            }

            if ($fotoPath !== null) {
                $stmt = $this->pdo->prepare('UPDATE murid SET nama=?, instagram=?, foto=?, status=? WHERE id=?');
                $stmt->execute([$nama, $instagram, $fotoPath, $currentStatus, $id]);

                // Delete old file if exists and different
                if ($oldFoto && file_exists(__DIR__ . '/../../' . $oldFoto) && $oldFoto !== $fotoPath) {
                    @unlink(__DIR__ . '/../../' . $oldFoto);
                }
            } else {
                $stmt = $this->pdo->prepare('UPDATE murid SET nama=?, instagram=?, status=? WHERE id=?');
                $stmt->execute([$nama, $instagram, $currentStatus, $id]);
            }

            ActivityLogService::log('EDIT_MURID', "Memperbarui profil murid: '$nama'", 'info');

            return ['success' => true, 'message' => "Data murid '$nama' berhasil diperbarui."];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Gagal memperbarui data murid: ' . $e->getMessage()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()];
        }
    }

    public function updateStatus(int $id, string $status): array
    {
        if ($id <= 0) {
            return ['success' => false, 'message' => 'ID murid tidak valid.'];
        }

        $norm = $this->normalizeStatus($status);
        try {
            $murid = $this->getById($id);
            if (!$murid) {
                return ['success' => false, 'message' => 'Data murid tidak ditemukan.'];
            }

            $stmt = $this->pdo->prepare('UPDATE murid SET status = ? WHERE id = ?');
            $stmt->execute([$norm, $id]);

            $label = $norm === 'aktif' ? 'Aktif' : 'Non-Aktif';
            ActivityLogService::log('STATUS_MURID', "Mengubah status murid '{$murid['nama']}' menjadi $label", 'warning');

            return [
                'success' => true,
                'message' => "Status murid '{$murid['nama']}' berhasil diubah menjadi $label.",
                'status' => $norm,
                'label' => $label
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Gagal mengubah status murid: ' . $e->getMessage()];
        }
    }

    public function delete(int $id): array
    {
        if ($id <= 0) {
            return ['success' => false, 'message' => 'ID murid tidak valid.'];
        }

        try {
            $murid = $this->getById($id);
            if (!$murid) {
                return ['success' => false, 'message' => 'Data murid tidak ditemukan.'];
            }

            if (!empty($murid['foto']) && file_exists(__DIR__ . '/../../' . $murid['foto'])) {
                @unlink(__DIR__ . '/../../' . $murid['foto']);
            }

            $stmt = $this->pdo->prepare('DELETE FROM murid WHERE id=?');
            $stmt->execute([$id]);

            ActivityLogService::log('HAPUS_MURID', "Menghapus data murid: '{$murid['nama']}'", 'danger');

            return ['success' => true, 'message' => "Murid '{$murid['nama']}' berhasil dihapus."];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Gagal menghapus data murid: ' . $e->getMessage()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()];
        }
    }
}

if (!class_exists('MuridService', false)) {
    class_alias(MuridService::class, 'MuridService');
}
