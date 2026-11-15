<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Services\ActivityLogService;

class ActivityLogController extends BaseController
{
    private ActivityLogService $activityLogService;

    public function __construct(?ActivityLogService $activityLogService = null)
    {
        parent::__construct();
        $this->activityLogService = $activityLogService ?? new ActivityLogService($this->pdo);
    }

    public function handle(): void
    {
        $this->requireLogin();
        $this->requireRole(['owner', 'admin']);

        $this->activityLogService->seedInitialLogsIfEmpty();
        $isOwner = $this->isOwner();

        if ($this->isPost()) {
            $this->handlePost($isOwner);
            return;
        }

        $logs = $this->activityLogService->getLogs(250);
        $stats = $this->activityLogService->getStats();
        $totalLogs = count($logs);

        $this->render('activity_log/index', [
            'logs' => $logs,
            'stats' => $stats,
            'totalLogs' => $totalLogs,
            'isOwner' => $isOwner,
            'activityLogService' => $this->activityLogService,
        ]);
    }

    private function handlePost(bool $isOwner): void
    {
        $this->verifyCsrf();
        $action = $_POST['action'] ?? '';
        $isAjax = $this->isAjax();

        if ($action === 'clear_logs') {
            if (!$isOwner) {
                if ($isAjax) {
                    $this->json(['success' => false, 'message' => 'Hanya Owner yang memiliki izin membersihkan activity log.']);
                }
                $this->setFlash('danger', 'Hanya Owner yang memiliki izin membersihkan activity log.');
                $this->redirect('activity_log.php');
            }

            $res = $this->activityLogService->clearLogs();
            if ($isAjax) {
                $this->json(['success' => $res, 'message' => $res ? 'Activity log berhasil dibersihkan.' : 'Gagal membersihkan log.']);
            }
            $this->setFlash($res ? 'success' : 'danger', $res ? 'Activity log berhasil dibersihkan.' : 'Gagal membersihkan log.');
            $this->redirect('activity_log.php');
        }

        if ($isAjax) {
            $this->json(['success' => false, 'message' => 'Aksi tidak valid.']);
        }

        $this->redirect('activity_log.php');
    }
}
