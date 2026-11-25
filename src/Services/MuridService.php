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

    private function safeDeleteUpload(?string $relPath): void
    {
        if ($relPath === null || trim($relPath) === '') {
            return;
        }

        $rootDir = defined('CHORUS_ROOT') ? CHORUS_ROOT : dirname(__DIR__, 2);
        $uploadsBase = realpath($rootDir . '/assets/uploads');
        if ($uploadsBase === false) {
            return;
        }

        $target = realpath($rootDir . '/' . ltrim($relPath, '/'));
        if ($target !== false && str_starts_with($target, $uploadsBase . DIRECTORY_SEPARATOR) && is_file($target)) {
            @unlink($target);
        }
    }

    public function handleFotoUpload(?array $file, ?string &$uploadError = null): string|false|null
    {
        if (!$file || empty($file['name']) || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $uploadError = 'Gagal mengunggah file foto (kode error: ' . (int)$file['error'] . ').';
            return false;
        }

        // Limit file size (Maksimal 5MB di server)
        if (!isset($file['size']) || $file['size'] <= 0 || $file['size'] > 5 * 1024 * 1024) {
            $uploadError = 'Ukuran file foto tidak valid atau melebihi batas maksimal 5MB.';
            return false;
        }

        if (empty($file['tmp_name']) || !is_file($file['tmp_name'])) {
            $uploadError = 'File sementara unggahan tidak ditemukan.';
            return false;
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

        // Check MIME type via finfo when available
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : false;
            if ($finfo) {
                finfo_close($finfo);
            }
            if (!in_array($mime, $allowedMimes, true)) {
                $uploadError = 'Tipe MIME file foto tidak valid. Hanya diperbolehkan: JPG, PNG, WEBP.';
                return false;
            }
        }

        // Always validate image dimensions and true MIME type
        $imgInfo = @getimagesize($file['tmp_name']);
        if ($imgInfo === false) {
            $uploadError = 'File yang diunggah bukan gambar yang valid.';
            return false;
        }

        $width = (int)($imgInfo[0] ?? 0);
        $height = (int)($imgInfo[1] ?? 0);
        $detectedMime = $imgInfo['mime'] ?? '';
        if ($width <= 0 || $height <= 0 || !in_array($detectedMime, $allowedMimes, true)) {
            $uploadError = 'Format gambar tidak didukung. Hanya diperbolehkan: JPG, PNG, WEBP.';
            return false;
        }

        $mimeToExt = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        $ext = $mimeToExt[$detectedMime];

        // Generate secure filename strictly from verified MIME type
        $new = 'murid_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $rootDir = defined('CHORUS_ROOT') ? CHORUS_ROOT : dirname(__DIR__, 2);
        $uploadDir = $rootDir . '/assets/uploads';
        $dest = $uploadDir . '/' . $new;

        if (!is_dir($uploadDir)) {
            if (!@mkdir($uploadDir, 0755, true)) {
                $uploadError = 'Gagal membuat folder penyimpanan foto di server.';
                return false;
            }
        }

        // Re-encode via GD when available to strip EXIF and embedded payloads
        $gdMap = [
            'image/jpeg' => ['imagecreatefromjpeg', 'imagejpeg'],
            'image/png' => ['imagecreatefrompng', 'imagepng'],
            'image/webp' => ['imagecreatefromwebp', 'imagewebp'],
        ];
        [$createFn, $saveFn] = $gdMap[$detectedMime];

        if (function_exists($createFn) && function_exists($saveFn)) {
            $srcImg = @$createFn($file['tmp_name']);
            if (!$srcImg) {
                $uploadError = 'File gambar rusak atau gagal diproses.';
                return false;
            }

            $canvas = imagecreatetruecolor($width, $height);
            if (!$canvas) {
                imagedestroy($srcImg);
                $uploadError = 'Gagal memproses kanvas gambar.';
                return false;
            }

            if ($detectedMime === 'image/png' || $detectedMime === 'image/webp') {
                imagealphablending($canvas, false);
                imagesavealpha($canvas, true);
                $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
                imagefilledrectangle($canvas, 0, 0, $width, $height, $transparent);
            }

            imagecopy($canvas, $srcImg, 0, 0, 0, 0, $width, $height);

            $saved = match ($detectedMime) {
                'image/jpeg' => @$saveFn($canvas, $dest, 88),
                'image/png' => @$saveFn($canvas, $dest, 6),
                'image/webp' => @$saveFn($canvas, $dest, 88),
            };

            imagedestroy($srcImg);
            imagedestroy($canvas);

            if ($saved) {
                return 'assets/uploads/' . $new;
            }

            $uploadError = 'Gagal menyimpan gambar hasil pemrosesan.';
            return false;
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
            $this->safeDeleteUpload($fotoPath);
            error_log('[MuridService::create] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan database saat menyimpan data murid. Silakan coba lagi.'];
        } catch (Exception $e) {
            $this->safeDeleteUpload($fotoPath);
            error_log('[MuridService::create] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses data. Silakan coba lagi.'];
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

        $newFotoPath = null;
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
            $newFotoPath = $fotoPath;

            if ($fotoPath !== null) {
                $stmt = $this->pdo->prepare('UPDATE murid SET nama=?, instagram=?, foto=?, status=? WHERE id=?');
                $stmt->execute([$nama, $instagram, $fotoPath, $currentStatus, $id]);

                // Delete old file only AFTER DB update succeeds
                if ($oldFoto && $oldFoto !== $fotoPath) {
                    $this->safeDeleteUpload($oldFoto);
                }
            } else {
                $stmt = $this->pdo->prepare('UPDATE murid SET nama=?, instagram=?, status=? WHERE id=?');
                $stmt->execute([$nama, $instagram, $currentStatus, $id]);
            }

            ActivityLogService::log('EDIT_MURID', "Memperbarui profil murid: '$nama'", 'info');

            return ['success' => true, 'message' => "Data murid '$nama' berhasil diperbarui."];
        } catch (PDOException $e) {
            $this->safeDeleteUpload($newFotoPath);
            error_log('[MuridService::update] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan database saat memperbarui data murid. Silakan coba lagi.'];
        } catch (Exception $e) {
            $this->safeDeleteUpload($newFotoPath);
            error_log('[MuridService::update] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses data. Silakan coba lagi.'];
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
            error_log('[MuridService::updateStatus] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat mengubah status murid. Silakan coba lagi.'];
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

            $stmt = $this->pdo->prepare('DELETE FROM murid WHERE id=?');
            $stmt->execute([$id]);

            // Delete uploaded file only AFTER DB delete succeeds
            $this->safeDeleteUpload($murid['foto'] ?? null);

            ActivityLogService::log('HAPUS_MURID', "Menghapus data murid: '{$murid['nama']}'", 'danger');

            return ['success' => true, 'message' => "Murid '{$murid['nama']}' berhasil dihapus."];
        } catch (PDOException $e) {
            error_log('[MuridService::delete] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan database saat menghapus data murid. Silakan coba lagi.'];
        } catch (Exception $e) {
            error_log('[MuridService::delete] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses data. Silakan coba lagi.'];
        }
    }
}

if (!class_exists('MuridService', false)) {
    class_alias(MuridService::class, 'MuridService');
}
