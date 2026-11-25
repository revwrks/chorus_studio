<?php
namespace App\Services;

use PDO;
use PDOException;
use Exception;
use Throwable;
use App\Core\Database;
use App\Core\Auth;
/**
 * Admin (User Account) Domain Service
 * Handles user authentication, staff & parent accounts, and multi-child relations with transactions.
 */
class AdminService
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    public function getAll(): array
    {
        return $this->pdo->query('SELECT * FROM admin ORDER BY id DESC')->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM admin WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function countOwners(): int
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM admin WHERE role='owner'")->fetchColumn();
    }

    public function getOrtuMuridMap(): array
    {
        $map = [];
        $stmtOM = $this->pdo->query('SELECT otm.id_orang_tua, m.id as murid_id, m.nama 
                                      FROM orang_tua_murid otm 
                                      JOIN murid m ON otm.id_murid = m.id 
                                      ORDER BY m.nama ASC');
        foreach ($stmtOM->fetchAll() as $row) {
            $map[$row['id_orang_tua']][] = $row;
        }
        return $map;
    }

    public function create(string $username, string $nama, string $password, string $role, array $muridIds = [], string $actorRole = 'owner'): array
    {
        $username = trim($username);
        $nama = trim($nama);
        $role = in_array($role, ['owner', 'admin', 'coach', 'orang_tua'], true) ? $role : 'admin';
        $muridIds = array_map('intval', array_filter($muridIds));

        if ($actorRole !== 'owner' && $role === 'owner') {
            return ['success' => false, 'message' => 'Hanya Owner yang dapat membuat akun Owner.'];
        }

        if (empty($username) || empty($nama) || empty($password)) {
            return ['success' => false, 'message' => 'Username, nama lengkap, dan password wajib diisi.'];
        }

        if (strlen($username) < 3 || strlen($username) > 50) {
            return ['success' => false, 'message' => 'Username harus memiliki panjang antara 3 hingga 50 karakter.'];
        }

        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password minimal terdiri dari 6 karakter.'];
        }

        if ($role === 'orang_tua' && empty($muridIds)) {
            return ['success' => false, 'message' => 'Akun Orang Tua wajib dihubungkan dengan minimal satu anak.'];
        }

        // Check if username exists
        $checkStmt = $this->pdo->prepare('SELECT COUNT(*) FROM admin WHERE username = ?');
        $checkStmt->execute([$username]);
        if ((int)$checkStmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => "Username '$username' sudah terdaftar. Silakan pilih username lain."];
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare('INSERT INTO admin (username, password, nama, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$username, $passwordHash, $nama, $role]);
            $newAdminId = (int)$this->pdo->lastInsertId();

            if ($role === 'orang_tua' && !empty($muridIds)) {
                $insertOtm = $this->pdo->prepare('INSERT INTO orang_tua_murid (id_orang_tua, id_murid) VALUES (?, ?)');
                foreach ($muridIds as $mId) {
                    if ($mId > 0) {
                        $insertOtm->execute([$newAdminId, $mId]);
                    }
                }
            }
            $this->pdo->commit();
            $roleLabel = ucfirst($role);
            ActivityLogService::log('USER_CREATE', "Membuat akun {$roleLabel} baru: {$nama} ({$username})", 'success');

            return [
                'success' => true,
                'message' => "Akun '$username' berhasil ditambahkan.",
                'id' => $newAdminId,
            ];
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('[AdminService::create] ' . $e->getMessage());
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return ['success' => false, 'message' => 'Gagal menyimpan: Terjadi duplikasi data unik (misalnya username sudah dipakai).'];
            }
            return ['success' => false, 'message' => 'Terjadi kesalahan database saat memproses akun. Silakan coba lagi.'];
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('[AdminService::create] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses data. Silakan coba lagi.'];
        }
    }

    public function update(int $id, string $username, string $nama, ?string $password, string $role, array $muridIds = [], string $actorRole = 'owner'): array
    {
        $id = (int)$id;
        $username = trim($username);
        $nama = trim($nama);
        $role = in_array($role, ['owner', 'admin', 'coach', 'orang_tua'], true) ? $role : 'admin';
        $muridIds = array_map('intval', array_filter($muridIds));

        if ($id <= 0 || empty($username) || empty($nama)) {
            return ['success' => false, 'message' => 'Data akun tidak lengkap.'];
        }

        $stmtExisting = $this->pdo->prepare('SELECT id, username, role FROM admin WHERE id = ?');
        $stmtExisting->execute([$id]);
        $existing = $stmtExisting->fetch();
        if (!$existing) {
            return ['success' => false, 'message' => 'Akun yang akan diedit tidak ditemukan.'];
        }

        if ($actorRole !== 'owner' && ($existing['role'] === 'owner' || $role === 'owner')) {
            return ['success' => false, 'message' => 'Hanya Owner yang dapat mengubah atau menetapkan role Owner.'];
        }

        // Check last owner protection
        $ownerCount = $this->countOwners();
        if ($existing['role'] === 'owner' && $ownerCount <= 1 && $role !== 'owner') {
            return ['success' => false, 'message' => 'Tidak dapat mengubah role Owner terakhir. Sistem membutuhkan minimal satu Owner.'];
        }

        // Check duplicate username
        $dupStmt = $this->pdo->prepare('SELECT COUNT(*) FROM admin WHERE username = ? AND id != ?');
        $dupStmt->execute([$username, $id]);
        if ((int)$dupStmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => "Username '$username' sudah digunakan oleh akun lain."];
        }

        if ($role === 'orang_tua' && empty($muridIds)) {
            return ['success' => false, 'message' => 'Akun Orang Tua wajib dihubungkan dengan minimal satu anak.'];
        }

        try {
            $this->pdo->beginTransaction();

            if (!empty($password)) {
                if (strlen($password) < 6) {
                    $this->pdo->rollBack();
                    return ['success' => false, 'message' => 'Password baru minimal terdiri dari 6 karakter.'];
                }
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $this->pdo->prepare('UPDATE admin SET username=?, nama=?, role=?, password=? WHERE id=?')
                    ->execute([$username, $nama, $role, $hash, $id]);
            } else {
                $this->pdo->prepare('UPDATE admin SET username=?, nama=?, role=? WHERE id=?')
                    ->execute([$username, $nama, $role, $id]);
            }

            // Sync relation orang_tua_murid
            $this->pdo->prepare('DELETE FROM orang_tua_murid WHERE id_orang_tua = ?')->execute([$id]);
            if ($role === 'orang_tua' && !empty($muridIds)) {
                $insertOtm = $this->pdo->prepare('INSERT INTO orang_tua_murid (id_orang_tua, id_murid) VALUES (?, ?)');
                foreach ($muridIds as $mId) {
                    if ($mId > 0) {
                        $insertOtm->execute([$id, $mId]);
                    }
                }
            }
            $this->pdo->commit();
            ActivityLogService::log('USER_UPDATE', "Memperbarui data akun pengguna: {$nama} ({$username}, role: {$role})", 'info');

            return ['success' => true, 'message' => "Akun '$username' berhasil diperbarui."];
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('[AdminService::update] ' . $e->getMessage());
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return ['success' => false, 'message' => 'Gagal menyimpan: Terjadi duplikasi data unik (misalnya username sudah dipakai).'];
            }
            return ['success' => false, 'message' => 'Terjadi kesalahan database saat memproses akun. Silakan coba lagi.'];
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('[AdminService::update] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses data. Silakan coba lagi.'];
        }
    }

    public function delete(int $id, int $currentUserId, string $actorRole = 'owner'): array
    {
        if ($id <= 0) {
            return ['success' => false, 'message' => 'ID akun tidak valid.'];
        }

        if ($id === $currentUserId) {
            return ['success' => false, 'message' => 'Anda tidak dapat menghapus akun Anda sendiri saat sedang login.'];
        }

        $target = $this->getById($id);
        if (!$target) {
            return ['success' => false, 'message' => 'Akun tidak ditemukan.'];
        }

        if ($actorRole !== 'owner' && $target['role'] === 'owner') {
            return ['success' => false, 'message' => 'Hanya Owner yang dapat menghapus akun Owner.'];
        }

        $ownerCount = $this->countOwners();
        if ($target['role'] === 'owner' && $ownerCount <= 1) {
            return ['success' => false, 'message' => 'Tidak dapat menghapus satu-satunya akun Owner.'];
        }

        try {
            $this->pdo->prepare('DELETE FROM admin WHERE id=?')->execute([$id]);
            ActivityLogService::log('USER_DELETE', "Menghapus akun pengguna: {$target['nama']} ({$target['username']})", 'danger');
            return ['success' => true, 'message' => "Akun '{$target['username']}' berhasil dihapus."];
        } catch (PDOException $e) {
            error_log('[AdminService::delete] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menghapus akun karena kendala database. Silakan coba lagi.'];
        } catch (Exception $e) {
            error_log('[AdminService::delete] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses data. Silakan coba lagi.'];
        }
    }

    /**
     * Memperbarui informasi profil akun milik pengguna sendiri.
     * Digunakan oleh semua role (termasuk Orang Tua dan Coach) untuk mengganti nama, username, dan password.
     * Tidak mengubah role atau anak yang terhubung.
     */
    public function updateOwnProfile(int $id, string $username, string $nama, ?string $password = null): array
    {
        $id = (int)$id;
        $username = trim($username);
        $nama = trim($nama);

        if ($id <= 0 || empty($username) || empty($nama)) {
            return ['success' => false, 'message' => 'Username dan nama lengkap wajib diisi.'];
        }

        if (strlen($username) < 3 || strlen($username) > 50) {
            return ['success' => false, 'message' => 'Username harus memiliki panjang antara 3 hingga 50 karakter.'];
        }

        $existing = $this->getById($id);
        if (!$existing) {
            return ['success' => false, 'message' => 'Akun tidak ditemukan.'];
        }

        // Cek apakah username dipakai oleh akun lain
        $dupStmt = $this->pdo->prepare('SELECT COUNT(*) FROM admin WHERE username = ? AND id != ?');
        $dupStmt->execute([$username, $id]);
        if ((int)$dupStmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => "Username '$username' sudah digunakan oleh akun lain. Silakan pilih username lain."];
        }

        $passwordHash = null;
        if ($password !== null && trim($password) !== '') {
            $p = trim($password);
            if (strlen($p) < 6) {
                return ['success' => false, 'message' => 'Password baru minimal terdiri dari 6 karakter.'];
            }
            $passwordHash = password_hash($p, PASSWORD_DEFAULT);
        }

        try {
            if ($passwordHash !== null) {
                $stmt = $this->pdo->prepare('UPDATE admin SET username = ?, nama = ?, password = ? WHERE id = ?');
                $stmt->execute([$username, $nama, $passwordHash, $id]);
            } else {
                $stmt = $this->pdo->prepare('UPDATE admin SET username = ?, nama = ? WHERE id = ?');
                $stmt->execute([$username, $nama, $id]);
            }

            // Perbarui sesi aktif jika pengguna yang mengedit adalah akun yang sedang login
            if (!empty($_SESSION['admin_id']) && (int)$_SESSION['admin_id'] === $id) {
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_nama'] = $nama;
            }

            return ['success' => true, 'message' => 'Profil akun Anda berhasil diperbarui.'];
        } catch (PDOException $e) {
            error_log('[AdminService::updateOwnProfile] ' . $e->getMessage());
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return ['success' => false, 'message' => "Username '$username' sudah dipakai akun lain."];
            }
            return ['success' => false, 'message' => 'Terjadi kesalahan database saat memperbarui profil. Silakan coba lagi.'];
        } catch (Exception $e) {
            error_log('[AdminService::updateOwnProfile] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses data. Silakan coba lagi.'];
        }
    }
}

if (!class_exists('AdminService', false)) {
    class_alias(AdminService::class, 'AdminService');
}
