<?php
namespace App\Services;

use PDO;
use PDOException;
use Exception;
use Throwable;
use App\Core\Database;
use App\Core\Auth;
/**
 * Activity Log Domain Service
 * Handles recording, querying, capping, and formatting system activity logs.
 * Strictly capped at 250 items for maximum performance and low database footprint.
 */
class ActivityLogService
{
    private PDO $pdo;
    private const MAX_CAP = 250;
    private static bool $tableVerified = false;
    private static ?array $appConfig = null;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    /**
     * Ensure the activity_log table exists (lazy fallback only)
     */
    private function ensureTable(): void
    {
        if (self::$tableVerified) {
            return;
        }
        self::$tableVerified = true;
        $this->ensureTableExists();
    }

    /**
     * Create the activity_log table if missing
     */
    public function ensureTableExists(): void
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `activity_log` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT DEFAULT NULL,
                `user_nama` VARCHAR(150) DEFAULT NULL,
                `user_role` VARCHAR(50) NOT NULL DEFAULT 'system',
                `action` VARCHAR(50) NOT NULL,
                `tipe` ENUM('info', 'success', 'warning', 'danger') NOT NULL DEFAULT 'info',
                `deskripsi` TEXT NOT NULL,
                `ip_address` VARCHAR(45) DEFAULT NULL,
                `user_agent` VARCHAR(255) DEFAULT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                KEY `idx_created_at` (`created_at`),
                KEY `idx_action` (`action`),
                KEY `idx_user_id` (`user_id`),
                CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`) REFERENCES `admin` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $this->pdo->exec($sql);
            self::$tableVerified = true;
        } catch (Throwable $e) {
            error_log('[ActivityLogService::ensureTableExists] ' . $e->getMessage());
        }
    }

    private function isMissingTableException(Throwable $e): bool
    {
        return $e instanceof PDOException && (string)$e->getCode() === '42S02';
    }

    private static function loadConfig(): array
    {
        if (self::$appConfig !== null) {
            return self::$appConfig;
        }

        $rootDir = defined('CHORUS_ROOT') ? CHORUS_ROOT : dirname(__DIR__, 2);
        $configFile = $rootDir . '/config.php';
        $loaded = file_exists($configFile) ? require $configFile : [];
        self::$appConfig = is_array($loaded) ? $loaded : [];
        return self::$appConfig;
    }

    private function getClientIp(): string
    {
        $config = self::loadConfig();
        $trustProxy = !empty($config['trust_proxy_headers']) && $config['trust_proxy_headers'] === true;

        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $ip = $remoteAddr;

        if ($trustProxy) {
            $candidate = $_SERVER['HTTP_CF_CONNECTING_IP']
                ?? $_SERVER['HTTP_X_FORWARDED_FOR']
                ?? $remoteAddr;
            if (strpos($candidate, ',') !== false) {
                $candidate = trim(explode(',', $candidate)[0]);
            } else {
                $candidate = trim($candidate);
            }
            if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                $ip = $candidate;
            }
        }

        return substr(trim($ip), 0, 45);
    }

    /**
     * Record an activity log entry
     */
    public static function log(
        string $action,
        string $deskripsi,
        string $tipe = 'info',
        ?int $userId = null,
        ?string $userNama = null,
        ?string $userRole = null
    ): bool {
        try {
            $service = new self();
            return $service->record($action, $deskripsi, $tipe, $userId, $userNama, $userRole);
        } catch (Throwable $e) {
            error_log("ActivityLogService::log Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Record instance method
     */
    public function record(
        string $action,
        string $deskripsi,
        string $tipe = 'info',
        ?int $userId = null,
        ?string $userNama = null,
        ?string $userRole = null
    ): bool {
        try {
            // Determine User ID, Name, and Role
            if ($userId === null && isset($_SESSION['admin_id'])) {
                $userId = (int)$_SESSION['admin_id'];
            }

            if ($userNama === null) {
                $userNama = $_SESSION['admin_nama'] ?? $_SESSION['admin_username'] ?? ($userId ? 'User #' . $userId : 'Sistem');
            }

            if ($userRole === null) {
                $userRole = $_SESSION['admin_role'] ?? ($userId ? 'admin' : 'system');
            }

            // Ensure valid foreign key reference if user_id is provided
            if ($userId !== null && $userId > 0) {
                $chk = $this->pdo->prepare('SELECT COUNT(*) FROM admin WHERE id = ?');
                $chk->execute([$userId]);
                if ((int)$chk->fetchColumn() === 0) {
                    $userId = null; // User doesn't exist in admin table (e.g. deleted or guest)
                }
            } else {
                $userId = null;
            }

            // Get client IP address
            $ip = $this->getClientIp();

            // Get user agent
            $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'CLI/Unknown', 0, 255);

            // Normalize type
            $validTypes = ['info', 'success', 'warning', 'danger'];
            if (!in_array($tipe, $validTypes, true)) {
                $tipe = 'info';
            }

            $params = [
                $userId,
                substr($userNama, 0, 150),
                substr($userRole, 0, 50),
                strtoupper(substr($action, 0, 50)),
                $tipe,
                trim($deskripsi),
                $ip,
                $userAgent
            ];

            $sql = 'INSERT INTO activity_log (user_id, user_nama, user_role, action, tipe, deskripsi, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
            try {
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
            } catch (PDOException $e) {
                if ($this->isMissingTableException($e) && !self::$tableVerified) {
                    $this->ensureTable();
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute($params);
                } else {
                    throw $e;
                }
            }

            // Enforce maximum cap of 250 items
            $this->enforceCap();

            return true;
        } catch (Throwable $e) {
            error_log('[ActivityLogService::record] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enforce the maximum cap of 250 items in the table
     */
    public function enforceCap(): void
    {
        try {
            $count = (int)$this->pdo->query('SELECT COUNT(*) FROM activity_log')->fetchColumn();
            if ($count > self::MAX_CAP) {
                // Delete rows beyond the latest 250
                $this->pdo->exec("DELETE FROM activity_log WHERE id NOT IN (
                    SELECT id FROM (SELECT id FROM activity_log ORDER BY id DESC LIMIT " . self::MAX_CAP . ") AS keep_rows
                )");
            }
        } catch (Throwable $e) {
            error_log("ActivityLogService::enforceCap Error: " . $e->getMessage());
        }
    }

    /**
     * Get activity logs strictly capped at 250 items maximum
     */
    public function getLogs(int $limit = self::MAX_CAP, ?string $search = null, ?string $action = null, ?string $role = null): array
    {
        $limit = min(self::MAX_CAP, max(1, $limit));

        $params = [];
        $where = [];

        if ($search !== null && trim($search) !== '') {
            $term = '%' . trim($search) . '%';
            $where[] = '(user_nama LIKE ? OR action LIKE ? OR deskripsi LIKE ? OR user_role LIKE ? OR ip_address LIKE ?)';
            $params = array_merge($params, [$term, $term, $term, $term, $term]);
        }

        if ($action !== null && trim($action) !== '' && $action !== 'all') {
            $where[] = 'action = ?';
            $params[] = strtoupper(trim($action));
        }

        if ($role !== null && trim($role) !== '' && $role !== 'all') {
            $where[] = 'user_role = ?';
            $params[] = strtolower(trim($role));
        }

        $whereSql = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

        $sql = "SELECT al.*, 
                       COALESCE(NULLIF(a.nama, ''), al.user_nama) AS resolved_nama,
                       COALESCE(NULLIF(a.role, ''), al.user_role) AS resolved_role
                FROM activity_log al
                LEFT JOIN admin a ON al.user_id = a.id
                {$whereSql}
                ORDER BY al.id DESC
                LIMIT {$limit}";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
        } catch (PDOException $e) {
            if ($this->isMissingTableException($e) && !self::$tableVerified) {
                $this->ensureTable();
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $rows = $stmt->fetchAll();
            } else {
                error_log('[ActivityLogService::getLogs] ' . $e->getMessage());
                $rows = [];
            }
        }

        // Enrich rows with formatted presentation attributes
        return array_map([$this, 'enrichLogData'], $rows);
    }

    /**
     * Enrich raw log row with UI badges, relative time, and icons
     */
    public function enrichLogData(array $row): array
    {
        $action = strtoupper($row['action'] ?? '');
        $role = strtolower($row['user_role'] ?? 'system');
        $tipe = strtolower($row['tipe'] ?? 'info');

        // Relative time calculation
        $time = strtotime($row['created_at']);
        $diff = time() - $time;
        if ($diff < 60) {
            $relativeTime = 'Baru saja';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            $relativeTime = $mins . ' menit yang lalu';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            $relativeTime = $hours . ' jam yang lalu';
        } elseif ($diff < 172800) {
            $relativeTime = 'Kemarin, ' . date('H:i', $time);
        } else {
            $relativeTime = date('d M Y, H:i', $time);
        }

        // Action styling & icon mapping
        $actionMeta = $this->getActionMeta($action, $tipe);

        // Role badge mapping
        $roleMeta = $this->getRoleMeta($role);

        // Simple browser/device detector from user agent
        $deviceMeta = $this->parseUserAgent($row['user_agent'] ?? '');

        return array_merge($row, [
            'relative_time' => $relativeTime,
            'formatted_date' => date('d M Y, H:i:s', $time),
            'action_label' => $actionMeta['label'],
            'action_icon' => $actionMeta['icon'],
            'action_badge_class' => $actionMeta['badge_class'],
            'role_label' => $roleMeta['label'],
            'role_badge_class' => $roleMeta['badge_class'],
            'device_icon' => $deviceMeta['icon'],
            'device_name' => $deviceMeta['name'],
        ]);
    }

    /**
     * Metadata mapping for actions
     */
    private function getActionMeta(string $action, string $tipe): array
    {
        $map = [
            'LOGIN' => ['label' => 'Login Masuk', 'icon' => 'fa-sign-in-alt', 'badge_class' => 'badge-log-login'],
            'LOGOUT' => ['label' => 'Logout Keluar', 'icon' => 'fa-sign-out-alt', 'badge_class' => 'badge-log-logout'],
            'LOGIN_GAGAL' => ['label' => 'Login Gagal', 'icon' => 'fa-shield-alt', 'badge_class' => 'badge-log-danger'],
            'TAMBAH_MURID' => ['label' => 'Tambah Murid', 'icon' => 'fa-user-plus', 'badge_class' => 'badge-log-success'],
            'EDIT_MURID' => ['label' => 'Edit Murid', 'icon' => 'fa-user-edit', 'badge_class' => 'badge-log-info'],
            'HAPUS_MURID' => ['label' => 'Hapus Murid', 'icon' => 'fa-user-times', 'badge_class' => 'badge-log-danger'],
            'STATUS_MURID' => ['label' => 'Status Murid', 'icon' => 'fa-toggle-on', 'badge_class' => 'badge-log-warning'],
            'TAMBAH_PERTEMUAN' => ['label' => 'Tambah Pertemuan', 'icon' => 'fa-calendar-plus', 'badge_class' => 'badge-log-success'],
            'EDIT_PERTEMUAN' => ['label' => 'Edit Pertemuan', 'icon' => 'fa-calendar-check', 'badge_class' => 'badge-log-info'],
            'HAPUS_PERTEMUAN' => ['label' => 'Hapus Pertemuan', 'icon' => 'fa-calendar-times', 'badge_class' => 'badge-log-danger'],
            'RENCANA_PERTEMUAN' => ['label' => 'Rencana Pertemuan', 'icon' => 'fa-bullseye', 'badge_class' => 'badge-log-primary'],
            'TAMBAH_LAGU' => ['label' => 'Tambah Lagu', 'icon' => 'fa-music', 'badge_class' => 'badge-log-success'],
            'EDIT_LAGU' => ['label' => 'Edit Lagu', 'icon' => 'fa-edit', 'badge_class' => 'badge-log-info'],
            'HAPUS_LAGU' => ['label' => 'Hapus Lagu', 'icon' => 'fa-trash-alt', 'badge_class' => 'badge-log-danger'],
            'TAMBAH_IRINGAN' => ['label' => 'Tambah Iringan', 'icon' => 'fa-headphones', 'badge_class' => 'badge-log-success'],
            'EDIT_IRINGAN' => ['label' => 'Edit Iringan', 'icon' => 'fa-edit', 'badge_class' => 'badge-log-info'],
            'HAPUS_IRINGAN' => ['label' => 'Hapus Iringan', 'icon' => 'fa-trash-alt', 'badge_class' => 'badge-log-danger'],
            'SIMPAN_REFLEKSI' => ['label' => 'Simpan Refleksi', 'icon' => 'fa-lightbulb', 'badge_class' => 'badge-log-warning'],
            'USER_CREATE' => ['label' => 'Buat Akun', 'icon' => 'fa-user-shield', 'badge_class' => 'badge-log-success'],
            'USER_UPDATE' => ['label' => 'Update Akun', 'icon' => 'fa-user-cog', 'badge_class' => 'badge-log-info'],
            'USER_DELETE' => ['label' => 'Hapus Akun', 'icon' => 'fa-user-slash', 'badge_class' => 'badge-log-danger'],
        ];

        if (isset($map[$action])) {
            return $map[$action];
        }

        // Default fallback based on type
        $badgeClass = match ($tipe) {
            'success' => 'badge-log-success',
            'warning' => 'badge-log-warning',
            'danger' => 'badge-log-danger',
            default => 'badge-log-info'
        };

        return [
            'label' => ucwords(strtolower(str_replace('_', ' ', $action))),
            'icon' => 'fa-circle-info',
            'badge_class' => $badgeClass
        ];
    }

    /**
     * Metadata mapping for roles
     */
    private function getRoleMeta(string $role): array
    {
        return match ($role) {
            'owner' => ['label' => 'Owner', 'badge_class' => 'role-owner'],
            'admin' => ['label' => 'Admin', 'badge_class' => 'role-admin'],
            'coach' => ['label' => 'Coach', 'badge_class' => 'role-coach'],
            'orang_tua' => ['label' => 'Orang Tua', 'badge_class' => 'role-ortu'],
            default => ['label' => 'Sistem', 'badge_class' => 'role-system']
        };
    }

    /**
     * Parse simple browser & platform from user agent
     */
    private function parseUserAgent(string $ua): array
    {
        $name = 'Browser Web';
        $icon = 'fa-globe';

        if (stripos($ua, 'Mobile') !== false || stripos($ua, 'Android') !== false || stripos($ua, 'iPhone') !== false) {
            $icon = 'fa-mobile-screen-button';
            $name = 'Ponsel Mobile';
        } elseif (stripos($ua, 'Chrome') !== false) {
            $icon = 'fa-chrome';
            $name = 'Google Chrome';
        } elseif (stripos($ua, 'Firefox') !== false) {
            $icon = 'fa-firefox-browser';
            $name = 'Mozilla Firefox';
        } elseif (stripos($ua, 'Safari') !== false) {
            $icon = 'fa-safari';
            $name = 'Apple Safari';
        } elseif (stripos($ua, 'Edge') !== false) {
            $icon = 'fa-edge';
            $name = 'Microsoft Edge';
        }

        return ['icon' => $icon, 'name' => $name];
    }

    /**
     * Get aggregate statistics for dashboard / log header
     */
    public function getStats(): array
    {
        $queryStats = function (): array {
            $total = (int)$this->pdo->query('SELECT COUNT(*) FROM activity_log')->fetchColumn();
            $totalCapped = min(self::MAX_CAP, $total);

            $today = (int)$this->pdo->query("SELECT COUNT(*) FROM activity_log WHERE DATE(created_at) = CURDATE()")->fetchColumn();

            $topUserStmt = $this->pdo->query("SELECT user_nama, COUNT(*) as cnt FROM activity_log WHERE user_nama IS NOT NULL AND user_nama != '' GROUP BY user_nama ORDER BY cnt DESC LIMIT 1");
            $topUserRow = $topUserStmt->fetch();
            $topUser = $topUserRow ? $topUserRow['user_nama'] : '-';

            $topActionStmt = $this->pdo->query("SELECT action, COUNT(*) as cnt FROM activity_log GROUP BY action ORDER BY cnt DESC LIMIT 1");
            $topActionRow = $topActionStmt->fetch();
            $topAction = $topActionRow ? ucwords(strtolower(str_replace('_', ' ', $topActionRow['action']))) : '-';

            return [
                'total' => $totalCapped,
                'today' => $today,
                'top_user' => $topUser,
                'top_action' => $topAction,
            ];
        };

        try {
            return $queryStats();
        } catch (PDOException $e) {
            if ($this->isMissingTableException($e) && !self::$tableVerified) {
                $this->ensureTable();
                try {
                    return $queryStats();
                } catch (Throwable $retryEx) {
                    error_log('[ActivityLogService::getStats] ' . $retryEx->getMessage());
                }
            } else {
                error_log('[ActivityLogService::getStats] ' . $e->getMessage());
            }
        } catch (Throwable $e) {
            error_log('[ActivityLogService::getStats] ' . $e->getMessage());
        }

        return [
            'total' => 0,
            'today' => 0,
            'top_user' => '-',
            'top_action' => '-',
        ];
    }

    /**
     * Clear all logs (only for owner)
     */
    public function clearLogs(): bool
    {
        try {
            $this->pdo->exec('TRUNCATE TABLE activity_log');
            self::log('LOG_RESET', 'Seluruh riwayat activity log telah dibersihkan oleh Owner', 'warning');
            return true;
        } catch (PDOException $e) {
            if ($this->isMissingTableException($e) && !self::$tableVerified) {
                $this->ensureTable();
                try {
                    $this->pdo->exec('TRUNCATE TABLE activity_log');
                    self::log('LOG_RESET', 'Seluruh riwayat activity log telah dibersihkan oleh Owner', 'warning');
                    return true;
                } catch (Throwable $retryEx) {
                    error_log('[ActivityLogService::clearLogs] ' . $retryEx->getMessage());
                    return false;
                }
            }
            error_log('[ActivityLogService::clearLogs] ' . $e->getMessage());
            return false;
        } catch (Throwable $e) {
            error_log('[ActivityLogService::clearLogs] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Populate initial activity logs from existing data if table is empty
     */
    public function seedInitialLogsIfEmpty(): void
    {
        try {
            try {
                $count = (int)$this->pdo->query('SELECT COUNT(*) FROM activity_log')->fetchColumn();
            } catch (PDOException $e) {
                if ($this->isMissingTableException($e) && !self::$tableVerified) {
                    $this->ensureTable();
                    $count = (int)$this->pdo->query('SELECT COUNT(*) FROM activity_log')->fetchColumn();
                } else {
                    throw $e;
                }
            }
            if ($count > 0) {
                return;
            }

            // 1. Seed from Admin accounts
            $admins = $this->pdo->query('SELECT id, nama, username, role, created_at FROM admin ORDER BY id ASC')->fetchAll();
            foreach ($admins as $adm) {
                $roleLabel = ucfirst($adm['role']);
                $name = $adm['nama'] ?: $adm['username'];
                $this->insertHistoricalLog(
                    (int)$adm['id'],
                    $name,
                    $adm['role'],
                    'USER_CREATE',
                    'success',
                    "Akun {$roleLabel} dibuat: {$name} ({$adm['username']})",
                    $adm['created_at']
                );
            }

            // 2. Seed from Murid registrations
            $murids = $this->pdo->query('SELECT id, nama, created_at, status FROM murid ORDER BY id ASC')->fetchAll();
            foreach ($murids as $m) {
                $statusTxt = ($m['status'] ?? 'aktif') === 'aktif' ? 'Aktif' : 'Non-Aktif';
                $this->insertHistoricalLog(
                    null,
                    'Administrator',
                    'owner',
                    'TAMBAH_MURID',
                    'success',
                    "Mendaftarkan murid baru: {$m['nama']} (Status: {$statusTxt})",
                    $m['created_at']
                );
            }

            // 3. Seed from Pertemuan sessions
            $pertemuan = $this->pdo->query('SELECT p.id, p.tanggal, p.created_at, m.nama as murid_nama, a.nama as coach_nama, a.id as coach_id, a.role as coach_role 
                                            FROM pertemuan p 
                                            JOIN murid m ON p.id_murid = m.id 
                                            LEFT JOIN admin a ON p.coach_id = a.id 
                                            ORDER BY p.id DESC LIMIT 40')->fetchAll();
            foreach ($pertemuan as $pt) {
                $coachName = $pt['coach_nama'] ?: 'Coach';
                $tglFormatted = date('d/m/Y', strtotime($pt['tanggal']));
                $this->insertHistoricalLog(
                    $pt['coach_id'] ? (int)$pt['coach_id'] : null,
                    $coachName,
                    $pt['coach_role'] ?: 'coach',
                    'TAMBAH_PERTEMUAN',
                    'success',
                    "Mencatat sesi latihan {$pt['murid_nama']} pada tanggal {$tglFormatted}",
                    $pt['created_at']
                );
            }

            // 4. Seed from Rencana Pertemuan
            $rencana = $this->pdo->query('SELECT r.tahun, r.bulan, r.target_pertemuan, r.created_at, m.nama as murid_nama, a.nama as coach_nama, a.id as coach_id 
                                          FROM rencana_pertemuan r 
                                          JOIN murid m ON r.id_murid = m.id 
                                          LEFT JOIN admin a ON r.coach_id = a.id 
                                          ORDER BY r.id DESC LIMIT 30')->fetchAll();
            foreach ($rencana as $rc) {
                $coachName = $rc['coach_nama'] ?: 'Admin';
                $this->insertHistoricalLog(
                    $rc['coach_id'] ? (int)$rc['coach_id'] : null,
                    $coachName,
                    'admin',
                    'RENCANA_PERTEMUAN',
                    'info',
                    "Menetapkan rencana pertemuan {$rc['murid_nama']}: {$rc['target_pertemuan']} sesi (Periode {$rc['bulan']}/{$rc['tahun']})",
                    $rc['created_at']
                );
            }

            // 5. Seed from Refleksi
            $refleksi = $this->pdo->query('SELECT rf.tahun, rf.bulan, rf.created_at, m.nama as murid_nama 
                                           FROM refleksi rf 
                                           JOIN murid m ON rf.id_murid = m.id 
                                           ORDER BY rf.id DESC LIMIT 20')->fetchAll();
            foreach ($refleksi as $rf) {
                $this->insertHistoricalLog(
                    null,
                    'Coach',
                    'coach',
                    'SIMPAN_REFLEKSI',
                    'warning',
                    "Menyimpan catatan refleksi bulanan untuk {$rf['murid_nama']} (Periode {$rf['bulan']}/{$rf['tahun']})",
                    $rf['created_at']
                );
            }

            // Enforce cap
            $this->enforceCap();
        } catch (Throwable $e) {
            error_log("ActivityLogService::seedInitialLogsIfEmpty Error: " . $e->getMessage());
        }
    }

    /**
     * Helper to insert historical log with custom timestamp
     */
    private function insertHistoricalLog(
        ?int $userId,
        string $userNama,
        string $userRole,
        string $action,
        string $tipe,
        string $deskripsi,
        ?string $createdAt = null
    ): void {
        try {
            $ts = $createdAt ?: date('Y-m-d H:i:s');
            $stmt = $this->pdo->prepare('INSERT INTO activity_log (user_id, user_nama, user_role, action, tipe, deskripsi, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $userId,
                substr($userNama, 0, 150),
                substr($userRole, 0, 50),
                strtoupper(substr($action, 0, 50)),
                $tipe,
                $deskripsi,
                '127.0.0.1',
                'Chorus Studio System',
                $ts
            ]);
        } catch (Throwable $e) {
            // ignore duplicate/constraint
        }
    }
}

if (!class_exists('ActivityLogService', false)) {
    class_alias(ActivityLogService::class, 'ActivityLogService');
}
