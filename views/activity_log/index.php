<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 form-inline">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h1 class="page-title"><i class="fa fa-history me-2" style="color:var(--primary);" aria-hidden="true"></i>Activity Log</h1>
                <span class="filter-counter-badge" id="counterActivityLog">Total: <?= e((string)$totalLogs) ?> log (Maks. 250)</span>
            </div>
            <p class="text-muted small mb-0 mt-1">
                Rekaman jejak audit aktivitas sistem Chorus Studio, dibatasi maksimal 250 catatan terbaru untuk menjaga kecepatan sistem.
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <?php if ($isOwner): ?>
                <button type="button" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalClearLogs" title="Bersihkan riwayat log">
                    <i class="fa fa-trash-alt" aria-hidden="true"></i> Bersihkan Log
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Stats KPI Cards -->
    <div class="kpi-grid mb-4">
        <div class="metric-card">
            <div class="d-flex align-items-center justify-content-between gap-2">
                <div>
                    <span class="bento-badge mb-1">Total Log Disimpan</span>
                    <div class="bento-metric-number" style="color:var(--primary);font-size:1.6rem;"><?= e((string)($stats['total'] ?? 0)) ?><span class="text-muted fs-6 fw-normal"> / 250</span></div>
                </div>
                <div class="bento-icon-box" style="background:var(--primary-soft);color:var(--primary);width:40px;height:40px;border-radius:10px;">
                    <i class="fa fa-database" aria-hidden="true"></i>
                </div>
            </div>
        </div>
        <div class="metric-card">
            <div class="d-flex align-items-center justify-content-between gap-2">
                <div>
                    <span class="badge-log badge-log-success mb-1">Hari Ini</span>
                    <div class="bento-metric-number" style="color:var(--success);font-size:1.6rem;"><?= e((string)($stats['today'] ?? 0)) ?></div>
                </div>
                <div class="bento-icon-box" style="background:var(--success-soft);color:var(--success);width:40px;height:40px;border-radius:10px;">
                    <i class="fa fa-clock-rotate-left" aria-hidden="true"></i>
                </div>
            </div>
        </div>
        <div class="metric-card">
            <div class="d-flex align-items-center justify-content-between gap-2">
                <div class="min-w-0">
                    <span class="badge-log badge-log-primary mb-1">User Teraktif</span>
                    <div class="h6 mb-0 fw-bold mt-1 text-truncate" title="<?= e($stats['top_user'] ?? '-') ?>"><?= e($stats['top_user'] ?? '-') ?></div>
                </div>
                <div class="bento-icon-box" style="background:var(--accent-soft);color:var(--accent);width:40px;height:40px;border-radius:10px;">
                    <i class="fa fa-user-check" aria-hidden="true"></i>
                </div>
            </div>
        </div>
        <div class="metric-card">
            <div class="d-flex align-items-center justify-content-between gap-2">
                <div class="min-w-0">
                    <span class="badge-log badge-log-info mb-1">Aksi Terbanyak</span>
                    <div class="h6 mb-0 fw-bold mt-1 text-truncate" title="<?= e($stats['top_action'] ?? '-') ?>"><?= e($stats['top_action'] ?? '-') ?></div>
                </div>
                <div class="bento-icon-box" style="background:var(--info-soft);color:var(--info);width:40px;height:40px;border-radius:10px;">
                    <i class="fa fa-bolt" aria-hidden="true"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card with Filter & Table -->
    <div class="card bento-hero-card p-3 p-sm-4 mb-4">
        <!-- Filter Toolbar -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div class="d-flex align-items-center gap-2 flex-wrap w-100 w-lg-auto">
                <!-- Search Live -->
                <div class="position-relative flex-grow-1 flex-sm-grow-0" style="min-width:200px;">
                    <input id="searchActivityLive"
                           data-live-search="#tableActivityLog"
                           data-counter-target="#counterActivityLog"
                           class="form-control form-control-sm"
                           style="padding-left:32px;"
                           placeholder="Cari pengguna, aksi, deskripsi…"
                           aria-label="Cari aktivitas log berdasarkan pengguna, aksi, atau deskripsi">
                    <i class="fa fa-search position-absolute top-50 translate-middle-y text-muted" style="left:12px;font-size:0.82rem;" aria-hidden="true"></i>
                </div>

                <!-- Filter Action Column (col index 3) -->
                <select id="filterLogAction"
                        class="form-select form-select-sm flex-grow-1 flex-sm-grow-0"
                        style="width:auto;min-width:150px;"
                        data-table-target="#tableActivityLog"
                        data-live-filter-col="3"
                        aria-label="Filter berdasarkan tipe aksi">
                    <option value="">Semua Tipe Aksi</option>
                    <option value="Login">Login &amp; Sesi</option>
                    <option value="Murid">Aktivitas Murid</option>
                    <option value="Pertemuan">Pertemuan &amp; Latihan</option>
                    <option value="Rencana">Rencana Pertemuan</option>
                    <option value="Lagu">Data Lagu</option>
                    <option value="Iringan">Lagu Iringan</option>
                    <option value="Refleksi">Refleksi</option>
                    <option value="Akun">Manajemen Akun</option>
                </select>

                <!-- Filter Role Column (col index 2) -->
                <select id="filterLogRole"
                        class="form-select form-select-sm flex-grow-1 flex-sm-grow-0"
                        style="width:auto;min-width:130px;"
                        data-table-target="#tableActivityLog"
                        data-live-filter-col="2"
                        aria-label="Filter berdasarkan role pengguna">
                    <option value="">Semua Role</option>
                    <option value="Owner">Owner</option>
                    <option value="Admin">Admin</option>
                    <option value="Coach">Coach</option>
                    <option value="Orang Tua">Orang Tua</option>
                    <option value="Sistem">Sistem</option>
                </select>
            </div>

            <div class="text-muted small d-none d-sm-block">
                <i class="fa fa-info-circle me-1" aria-hidden="true"></i> Klik baris untuk melihat detail teknis
            </div>
        </div>

        <!-- Table Responsive -->
        <div class="table-responsive">
            <table id="tableActivityLog" data-default-limit="25" class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th style="width:170px;">Waktu &amp; Tanggal</th>
                        <th style="width:190px;">Pengguna</th>
                        <th style="width:170px;">Aksi</th>
                        <th>Deskripsi Aktivitas</th>
                        <th style="width:180px;" class="d-none d-lg-table-cell">Perangkat &amp; IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <div class="mb-2"><i class="fa fa-clipboard-list fa-3x opacity-50" aria-hidden="true"></i></div>
                                <h2 class="h6 fw-bold">Belum Ada Aktivitas yang Tercatat</h2>
                                <p class="small text-muted mb-0">Setiap tindakan penting seperti login, perubahan murid, pertemuan, dan rencana sesi akan otomatis tercatat di sini.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $idx => $log):
                            $resolvedNama = (string)($log['resolved_nama'] ?? '');
                            $actorDisplay = $resolvedNama !== '' ? $resolvedNama : 'Sistem';
                            $initial = mb_strtoupper(mb_substr($actorDisplay, 0, 1, 'UTF-8'), 'UTF-8');
                            $targetInfo = !empty($log['user_id']) ? 'User #' . (string)$log['user_id'] : '-';
                        ?>
                            <tr class="clickable-row"
                                tabindex="0"
                                role="button"
                                aria-label="Lihat detail log aktivitas <?= e($log['action_label'] ?? '') ?> oleh <?= e($actorDisplay) ?>"
                                data-log-id="<?= e((string)($log['id'] ?? '')) ?>"
                                data-log-relative="<?= e($log['relative_time'] ?? '') ?>"
                                data-log-date="<?= e($log['formatted_date'] ?? '') ?>"
                                data-log-actor="<?= e($actorDisplay) ?>"
                                data-log-username="<?= e($log['user_nama'] ?? '-') ?>"
                                data-log-role="<?= e($log['role_label'] ?? 'Sistem') ?>"
                                data-log-role-class="<?= e($log['role_badge_class'] ?? 'role-system') ?>"
                                data-log-action="<?= e($log['action_label'] ?? '') ?>"
                                data-log-action-code="<?= e($log['action'] ?? '') ?>"
                                data-log-action-class="<?= e($log['action_badge_class'] ?? 'badge-log-info') ?>"
                                data-log-target="<?= e($targetInfo) ?>"
                                data-log-detail="<?= e($log['deskripsi'] ?? '') ?>"
                                data-log-ip="<?= e($log['ip_address'] ?? '127.0.0.1') ?>"
                                data-log-device="<?= e($log['device_name'] ?? 'Browser Web') ?>"
                                data-log-ua="<?= e($log['user_agent'] ?? '-') ?>">
                                <td class="text-muted small"><?= e((string)($idx + 1)) ?></td>
                                <td>
                                    <div class="fw-semibold" style="font-size:0.86rem;color:var(--text);"><?= e($log['relative_time'] ?? '') ?></div>
                                    <div class="text-muted" style="font-size:0.75rem;"><?= e($log['formatted_date'] ?? '') ?></div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-initial rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                             style="width:32px;height:32px;font-size:0.78rem;background:linear-gradient(135deg, var(--primary), var(--primary-hover));">
                                            <?= e($initial) ?>
                                        </div>
                                        <div style="line-height:1.2;">
                                            <div class="fw-semibold text-truncate" style="max-width:130px;" title="<?= e($actorDisplay) ?>">
                                                <?= e($actorDisplay) ?>
                                            </div>
                                            <span class="role-badge-log <?= e($log['role_badge_class'] ?? 'role-system') ?> mt-1">
                                                <?= e($log['role_label'] ?? 'Sistem') ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-log <?= e($log['action_badge_class'] ?? 'badge-log-info') ?>">
                                        <i class="fa <?= e($log['action_icon'] ?? 'fa-circle-info') ?>" aria-hidden="true"></i>
                                        <span><?= e($log['action_label'] ?? '') ?></span>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size:0.88rem;line-height:1.4;color:var(--text);">
                                        <?= e($log['deskripsi'] ?? '') ?>
                                    </div>
                                    <!-- Mobile technical info snippet -->
                                    <div class="d-lg-none text-muted mt-1" style="font-size:0.72rem;">
                                        <i class="fa <?= e($log['device_icon'] ?? 'fa-globe') ?> me-1" aria-hidden="true"></i>
                                        <span><?= e($log['ip_address'] ?? '127.0.0.1') ?></span>
                                    </div>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa <?= e($log['device_icon'] ?? 'fa-globe') ?> text-muted" title="<?= e($log['device_name'] ?? '') ?>" aria-hidden="true"></i>
                                        <div>
                                            <code style="font-size:0.76rem;background:var(--primary-soft);padding:2px 6px;border-radius:4px;color:var(--text-secondary);"><?= e($log['ip_address'] ?? '127.0.0.1') ?></code>
                                            <div class="text-muted text-truncate" style="font-size:0.72rem;max-width:130px;" title="<?= e($log['user_agent'] ?? '') ?>">
                                                <?= e($log['device_name'] ?? '') ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Universal Client-Side Pagination Container -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pagination-container" data-pagination-table="#tableActivityLog">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <small class="text-muted pagination-summary">Menampilkan 1-<?= e((string)min(25, $totalLogs)) ?> dari <?= e((string)$totalLogs) ?> data (Maks. 250)</small>
                <div class="limit-switch">
                    <button type="button" class="limit-pill" data-limit="10">10</button>
                    <button type="button" class="limit-pill active" data-limit="25">25</button>
                    <button type="button" class="limit-pill" data-limit="50">50</button>
                    <button type="button" class="limit-pill" data-limit="100">100</button>
                    <button type="button" class="limit-pill" data-limit="all">Semua</button>
                </div>
            </div>
            <nav aria-label="Pagination Activity Log" class="pagination-nav">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item page-prev disabled">
                        <button type="button" class="page-link page-arrow" aria-label="Halaman sebelumnya" title="Halaman sebelumnya"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
                    </li>
                    <li class="page-item disabled"><span class="page-link page-status-pill">Hal. 1 / <?= e((string)max(1, (int)ceil($totalLogs / 25))) ?></span></li>
                    <li class="page-item page-next <?= $totalLogs <= 25 ? 'disabled' : '' ?>">
                        <button type="button" class="page-link page-arrow" aria-label="Halaman berikutnya" title="Halaman berikutnya"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Modal Detail Teknis Activity Log -->
<div class="modal fade" id="modalActivityDetail" tabindex="-1" aria-labelledby="modalActivityDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius:var(--radius);background:var(--modal-bg);">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalActivityDetailLabel">
                    <i class="fa fa-clipboard-check me-2" style="color:var(--primary);" aria-hidden="true"></i>Detail Aktivitas Sistem
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0 g-2 small">
                    <dt class="col-4 text-muted">Waktu</dt>
                    <dd class="col-8 mb-1">
                        <div class="fw-semibold" id="actDetailDate"></div>
                        <div class="text-muted" id="actDetailRelative"></div>
                    </dd>

                    <dt class="col-4 text-muted">Pengguna</dt>
                    <dd class="col-8 mb-1">
                        <div class="fw-semibold" id="actDetailActor"></div>
                        <div class="text-muted">Tercatat sebagai: <span id="actDetailUsername"></span></div>
                        <span class="role-badge-log mt-1" id="actDetailRoleBadge"></span>
                    </dd>

                    <dt class="col-4 text-muted">Aksi</dt>
                    <dd class="col-8 mb-1">
                        <span class="badge-log" id="actDetailActionBadge"></span>
                        <div class="text-muted mt-1">Kode: <code id="actDetailActionCode"></code></div>
                    </dd>

                    <dt class="col-4 text-muted">ID Log / Entitas</dt>
                    <dd class="col-8 mb-1">
                        <span id="actDetailLogId"></span> &bull; <span id="actDetailTarget"></span>
                    </dd>

                    <dt class="col-4 text-muted">Detail</dt>
                    <dd class="col-8 mb-1" id="actDetailDescription" style="white-space:pre-wrap;word-break:break-word;"></dd>

                    <dt class="col-4 text-muted">Alamat IP</dt>
                    <dd class="col-8 mb-1"><code id="actDetailIp"></code></dd>

                    <dt class="col-4 text-muted">Perangkat / UA</dt>
                    <dd class="col-8 mb-0">
                        <div class="fw-semibold" id="actDetailDevice"></div>
                        <div class="text-muted" id="actDetailUa" style="word-break:break-all;font-size:0.75rem;"></div>
                    </dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Bersihkan Log (Hanya Owner) -->
<?php if ($isOwner): ?>
<div class="modal fade" id="modalClearLogs" tabindex="-1" aria-labelledby="modalClearLogsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius:var(--radius);background:var(--modal-bg);">
            <form method="post" action="activity_log.php">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="clear_logs">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-danger fw-bold" id="modalClearLogsLabel">
                        <i class="fa fa-exclamation-triangle me-2" aria-hidden="true"></i>Konfirmasi Bersihkan Log
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body py-3">
                    <p class="mb-2" style="color:var(--text);">
                        Apakah Anda yakin ingin menghapus seluruh riwayat <strong>Activity Log</strong>?
                    </p>
                    <p class="small text-muted mb-0">
                        Tindakan ini tidak dapat dibatalkan. Riwayat lama akan dikosongkan dan pencatatan akan dimulai kembali dari aktivitas berikutnya.
                    </p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm px-3">
                        <i class="fa fa-trash-alt me-1" aria-hidden="true"></i> Ya, Bersihkan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('tableActivityLog');
    const detailModalEl = document.getElementById('modalActivityDetail');
    if (!table || !detailModalEl) return;

    detailModalEl.addEventListener('shown.bs.modal', function() {
        const closeBtn = detailModalEl.querySelector('.btn-close');
        if (closeBtn) closeBtn.focus();
    });

    function openActivityDetail(row) {
        const setTxt = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.textContent = value || '-';
        };

        setTxt('actDetailDate', row.getAttribute('data-log-date'));
        setTxt('actDetailRelative', row.getAttribute('data-log-relative'));
        setTxt('actDetailActor', row.getAttribute('data-log-actor'));
        setTxt('actDetailUsername', row.getAttribute('data-log-username'));
        setTxt('actDetailActionCode', row.getAttribute('data-log-action-code'));
        setTxt('actDetailLogId', '#' + (row.getAttribute('data-log-id') || '-'));
        setTxt('actDetailTarget', row.getAttribute('data-log-target'));
        setTxt('actDetailDescription', row.getAttribute('data-log-detail'));
        setTxt('actDetailIp', row.getAttribute('data-log-ip'));
        setTxt('actDetailDevice', row.getAttribute('data-log-device'));
        setTxt('actDetailUa', row.getAttribute('data-log-ua'));

        const roleBadge = document.getElementById('actDetailRoleBadge');
        if (roleBadge) {
            const roleClass = row.getAttribute('data-log-role-class') || 'role-system';
            roleBadge.className = 'role-badge-log mt-1 ' + roleClass;
            roleBadge.textContent = row.getAttribute('data-log-role') || 'Sistem';
        }

        const actionBadge = document.getElementById('actDetailActionBadge');
        if (actionBadge) {
            const actionClass = row.getAttribute('data-log-action-class') || 'badge-log-info';
            actionBadge.className = 'badge-log ' + actionClass;
            actionBadge.textContent = row.getAttribute('data-log-action') || '-';
        }

        if (typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(detailModalEl).show();
        }
    }

    table.addEventListener('click', function(e) {
        if (e.target.closest('a, button, input, select')) return;
        const row = e.target.closest('tbody tr.clickable-row');
        if (row) openActivityDetail(row);
    });

    table.addEventListener('keydown', function(e) {
        if (e.key !== 'Enter' && e.key !== ' ') return;
        const row = e.target.closest('tbody tr.clickable-row');
        if (!row) return;
        e.preventDefault();
        openActivityDetail(row);
    });
});
</script>
