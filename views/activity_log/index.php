<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 form-inline">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h3 class="mb-0"><i class="fa fa-history me-2" style="color:var(--primary);"></i>Activity Log</h3>
                <span class="filter-counter-badge" id="counterActivityLog">Total: <?= $totalLogs ?> log (Maks. 250)</span>
            </div>
            <p class="text-muted small mb-0 mt-1">
                Rekaman jejak audit aktivitas sistem Chorus Studio, dibatasi maksimal 250 catatan terbaru untuk menjaga kecepatan sistem.
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <?php if ($isOwner): ?>
                <button type="button" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalClearLogs" title="Bersihkan riwayat log">
                    <i class="fa fa-trash-alt"></i> Bersihkan Log
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Stats Bento Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card bento-hero-card p-3 h-100" style="border-top:3px solid var(--primary);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="badge bento-badge mb-1" style="background:rgba(35,72,59,0.1);color:var(--primary);">Total Log Disimpan</span>
                        <div class="bento-metric-number" style="color:var(--primary);font-size:1.6rem;"><?= $stats['total'] ?><span class="text-muted fs-6 fw-normal"> / 250</span></div>
                    </div>
                    <div class="bento-icon-box" style="background:rgba(35,72,59,0.1);color:var(--primary);width:40px;height:40px;border-radius:10px;">
                        <i class="fa fa-database"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card bento-hero-card p-3 h-100" style="border-top:3px solid #2e7d32;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="badge bento-badge mb-1" style="background:rgba(46,125,50,0.12);color:#2e7d32;">Hari Ini</span>
                        <div class="bento-metric-number" style="color:#2e7d32;font-size:1.6rem;"><?= $stats['today'] ?></div>
                    </div>
                    <div class="bento-icon-box" style="background:rgba(46,125,50,0.12);color:#2e7d32;width:40px;height:40px;border-radius:10px;">
                        <i class="fa fa-clock-rotate-left"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card bento-hero-card p-3 h-100" style="border-top:3px solid var(--accent);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="badge bento-badge mb-1" style="background:rgba(197,160,89,0.15);color:var(--accent);">User Teraktif</span>
                        <h6 class="mb-0 fw-bold mt-1 text-truncate" style="max-width:130px;font-family:'Outfit',sans-serif;" title="<?= htmlspecialchars($stats['top_user']) ?>"><?= htmlspecialchars($stats['top_user']) ?></h6>
                    </div>
                    <div class="bento-icon-box" style="background:rgba(197,160,89,0.15);color:var(--accent);width:40px;height:40px;border-radius:10px;">
                        <i class="fa fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card bento-hero-card p-3 h-100" style="border-top:3px solid #1976d2;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="badge bento-badge mb-1" style="background:rgba(25,118,210,0.12);color:#1976d2;">Aksi Terbanyak</span>
                        <h6 class="mb-0 fw-bold mt-1 text-truncate" style="max-width:130px;font-family:'Outfit',sans-serif;" title="<?= htmlspecialchars($stats['top_action']) ?>"><?= htmlspecialchars($stats['top_action']) ?></h6>
                    </div>
                    <div class="bento-icon-box" style="background:rgba(25,118,210,0.12);color:#1976d2;width:40px;height:40px;border-radius:10px;">
                        <i class="fa fa-bolt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card with Filter & Table -->
    <div class="card bento-hero-card p-3 p-sm-4 mb-4">
        <!-- Filter Toolbar -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Search Live -->
                <div class="position-relative" style="min-width:240px;">
                    <input id="searchActivityLive" 
                           data-live-search="#tableActivityLog" 
                           data-counter-target="#counterActivityLog" 
                           class="form-control form-control-sm" 
                           style="padding-left:32px;" 
                           placeholder="Cari pengguna, aksi, deskripsi…">
                    <i class="fa fa-search position-absolute top-50 translate-middle-y text-muted" style="left:12px;font-size:0.82rem;"></i>
                </div>

                <!-- Filter Action Column (col index 3) -->
                <select class="form-select form-select-sm" 
                        style="width:auto;min-width:160px;" 
                        data-table-target="#tableActivityLog" 
                        data-live-filter-col="3"
                        aria-label="Filter berdasarkan tipe aksi">
                    <option value="">Semua Tipe Aksi</option>
                    <option value="Login">Login & Sesi</option>
                    <option value="Murid">Aktivitas Murid</option>
                    <option value="Pertemuan">Pertemuan & Latihan</option>
                    <option value="Rencana">Rencana Pertemuan</option>
                    <option value="Lagu">Data Lagu</option>
                    <option value="Iringan">Lagu Iringan</option>
                    <option value="Refleksi">Refleksi</option>
                    <option value="Akun">Manajemen Akun</option>
                </select>

                <!-- Filter Role Column (col index 2) -->
                <select class="form-select form-select-sm" 
                        style="width:auto;min-width:140px;" 
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
                <i class="fa fa-info-circle me-1"></i> Klik baris untuk melihat detail teknis
            </div>
        </div>

        <!-- Table Responsive -->
        <div class="table-responsive">
            <table id="tableActivityLog" class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th style="width:170px;">Waktu & Tanggal</th>
                        <th style="width:190px;">Pengguna</th>
                        <th style="width:170px;">Aksi</th>
                        <th>Deskripsi Aktivitas</th>
                        <th style="width:180px;" class="d-none d-lg-table-cell">Perangkat & IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <div class="mb-2"><i class="fa fa-clipboard-list fa-3x opacity-50"></i></div>
                                <h6 class="fw-bold">Belum Ada Aktivitas yang Tercatat</h6>
                                <p class="small text-muted mb-0">Setiap tindakan penting seperti login, perubahan murid, pertemuan, dan rencana sesi akan otomatis tercatat di sini.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $idx => $log): ?>
                            <tr>
                                <td class="text-muted small"><?= $idx + 1 ?></td>
                                <td>
                                    <div class="fw-semibold text-body" style="font-size:0.86rem;"><?= htmlspecialchars($log['relative_time']) ?></div>
                                    <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($log['formatted_date']) ?></div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-initial rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" 
                                             style="width:32px;height:32px;font-size:0.78rem;background:linear-gradient(135deg, var(--primary), var(--primary-dark));">
                                            <?= strtoupper(substr($log['resolved_nama'] ?: 'S', 0, 1)) ?>
                                        </div>
                                        <div style="line-height:1.2;">
                                            <div class="fw-semibold text-truncate" style="max-width:130px;" title="<?= htmlspecialchars($log['resolved_nama']) ?>">
                                                <?= htmlspecialchars($log['resolved_nama']) ?>
                                            </div>
                                            <span class="role-badge-log <?= htmlspecialchars($log['role_badge_class']) ?> mt-1">
                                                <?= htmlspecialchars($log['role_label']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-log <?= htmlspecialchars($log['action_badge_class']) ?>">
                                        <i class="fa <?= htmlspecialchars($log['action_icon']) ?>"></i>
                                        <span><?= htmlspecialchars($log['action_label']) ?></span>
                                    </span>
                                </td>
                                <td>
                                    <div class="text-body" style="font-size:0.88rem;line-height:1.4;">
                                        <?= htmlspecialchars($log['deskripsi']) ?>
                                    </div>
                                    <!-- Mobile technical info snippet -->
                                    <div class="d-lg-none text-muted mt-1" style="font-size:0.72rem;">
                                        <i class="fa <?= htmlspecialchars($log['device_icon']) ?> me-1"></i>
                                        <span><?= htmlspecialchars($log['ip_address'] ?? '127.0.0.1') ?></span>
                                    </div>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fa <?= htmlspecialchars($log['device_icon']) ?> text-muted" title="<?= htmlspecialchars($log['device_name']) ?>"></i>
                                        <div>
                                            <code style="font-size:0.76rem;background:rgba(74,107,93,0.08);padding:2px 6px;border-radius:4px;color:var(--text-secondary);"><?= htmlspecialchars($log['ip_address'] ?? '127.0.0.1') ?></code>
                                            <div class="text-muted text-truncate" style="font-size:0.72rem;max-width:130px;" title="<?= htmlspecialchars($log['user_agent'] ?? '') ?>">
                                                <?= htmlspecialchars($log['device_name']) ?>
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
                <small class="text-muted pagination-summary">Menampilkan 1-<?= min(25, $totalLogs) ?> dari <?= $totalLogs ?> data (Maks. 250)</small>
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
                        <button type="button" class="page-link page-arrow" aria-label="Halaman sebelumnya" title="Halaman sebelumnya"><i class="fa fa-chevron-left"></i></button>
                    </li>
                    <li class="page-item disabled"><span class="page-link page-status-pill">Hal. 1 / <?= max(1, (int)ceil($totalLogs / 25)) ?></span></li>
                    <li class="page-item page-next <?= $totalLogs <= 25 ? 'disabled' : '' ?>">
                        <button type="button" class="page-link page-arrow" aria-label="Halaman berikutnya" title="Halaman berikutnya"><i class="fa fa-chevron-right"></i></button>
                    </li>
                </ul>
            </nav>
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
                        <i class="fa fa-exclamation-triangle me-2"></i>Konfirmasi Bersihkan Log
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body py-3">
                    <p class="text-body mb-2">
                        Apakah Anda yakin ingin menghapus seluruh riwayat <strong>Activity Log</strong>?
                    </p>
                    <p class="small text-muted mb-0">
                        Tindakan ini tidak dapat dibatalkan. Riwayat lama akan dikosongkan dan pencatatan akan dimulai kembali dari aktivitas berikutnya.
                    </p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm px-3">
                        <i class="fa fa-trash-alt me-1"></i> Ya, Bersihkan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set default initial page size to 25 on load
    const table = document.querySelector('#tableActivityLog');
    if (table) {
        table._pageSize = 25;
        if (typeof window.initLiveTableFilters === 'function') {
            window.initLiveTableFilters();
        }
    }
});
</script>

