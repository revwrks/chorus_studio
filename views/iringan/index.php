
<div class="container-fluid">
    <?php if ($isOrtu): ?>
        <!-- ==================== TAMPILAN PORTAL ORANG TUA ==================== -->
        <?php if (!$activeChildId): ?>
            <div class="card p-4 text-center">
                <div class="mb-2"><i class="fa fa-info-circle fa-2x text-muted"></i></div>
                <h5>Akun Orang Tua Belum Terhubung</h5>
                <p class="text-muted mb-0">Akun Anda saat ini belum dihubungkan ke data murid manapun. Silakan hubungi admin atau coach studio.</p>
            </div>
        <?php else: ?>
            <?php
            $allDisplayRows = $iringanService->getHybridForMurid($activeChildId);
            ?>

            <!-- Unified Mobile & Desktop Toolbar -->
            <div class="d-flex justify-content-between align-items-center mb-3 form-inline flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <h3 class="mb-0 fw-bold" style="font-family:'Outfit',sans-serif;letter-spacing:-0.02em;">
                        <i class="fa fa-headphones me-2" style="color:var(--accent);"></i>Lagu Iringan Latihan & Lomba
                    </h3>
                    <span class="filter-counter-badge" id="counterIringanOrtu">Total: <?= count($allDisplayRows) ?> iringan</span>
                </div>
                <div class="d-flex gap-2 flex-wrap align-items-center">
                    <div class="position-relative">
                        <input id="searchIringanOrtuLive" data-live-search="#tableIringanOrtu" data-counter-target="#counterIringanOrtu" class="form-control form-control-sm" style="max-width:240px;padding-left:32px;border-radius:8px;" placeholder="Cari lagu / catatan…">
                        <i class="fa fa-search position-absolute top-50 translate-middle-y text-muted" style="left:12px;font-size:0.82rem;"></i>
                    </div>
                    <div class="dropdown filter-dropdown-container" data-table-target="#tableIringanOrtu">
                        <button class="btn btn-sm btn-filter-trigger dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="border-radius:8px;">
                            <i class="fa fa-sliders-h me-1" style="color:var(--accent);"></i> Filter
                            <span class="filter-count-badge ms-1" style="display:none;">0</span>
                        </button>
                        <div class="dropdown-menu filter-dropdown-menu shadow-lg">
                            <div class="filter-header">
                                <span class="filter-title"><i class="fa fa-sliders-h me-1 text-accent"></i> Filter Data</span>
                                <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 btn-reset-filters"><i class="fa fa-undo me-1"></i>Reset</button>
                            </div>
                            <div class="mb-2">
                                <label class="filter-label">Kategori Iringan</label>
                                <select data-table-target="#tableIringanOrtu" data-live-filter-col="2" class="form-select form-select-sm">
                                    <option value="">Semua Kategori</option>
                                    <option value="latihan">Iringan Latihan</option>
                                    <option value="lomba">Iringan Lomba</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($activeChild): ?>
                <div class="text-muted small mb-3">
                    <i class="fa fa-info-circle me-1" style="color:var(--accent);"></i>
                    Daftar musik pengiring Google Drive untuk ananda <strong><?= htmlspecialchars($activeChild['nama']) ?></strong>
                </div>
            <?php endif; ?>

            <!-- List Iringan Table -->
            <?php if (empty($allDisplayRows)): ?>
                <div class="card bento-hero-card p-5 text-center">
                    <div class="mb-3">
                        <div style="width:64px;height:64px;margin:0 auto;background:rgba(197,160,89,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                            <i class="fa fa-music fa-2x" style="color:var(--accent);"></i>
                        </div>
                    </div>
                    <h5 class="fw-semibold">Belum Ada Lagu Iringan</h5>
                    <p class="text-muted mb-0" style="max-width:500px;margin:0 auto;">
                        Coach belum menambahkan link lagu iringan Google Drive untuk ananda <?= htmlspecialchars($activeChild['nama']) ?>. Hubungi coach saat sesi latihan untuk meminta link iringan.
                    </p>
                </div>
            <?php else: ?>
                <div class="card bento-hero-card p-3 p-md-4 mb-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tableIringanOrtu">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:48px;" class="text-center">#</th>
                                    <th>Judul Lagu</th>
                                    <th style="width:130px;">Kategori</th>
                                    <th>Catatan dari Coach</th>
                                    <th style="min-width:240px;" class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($allDisplayRows as $item): 
                                    $kat = strtolower($item['kategori'] ?? 'latihan');
                                    $badgeStyle = ($kat === 'lomba') 
                                        ? 'background:rgba(168,144,98,0.18);color:#8a7548;border:1px solid rgba(168,144,98,0.3);' 
                                        : (($kat === 'latihan') ? 'background:rgba(74,107,93,0.14);color:var(--primary);border:1px solid rgba(74,107,93,0.25);' : 'background:rgba(46,134,222,0.14);color:#2e86de;');
                                    $isMaster = !empty($item['is_from_master']);
                                ?>
                                    <tr>
                                        <td class="text-center text-muted fw-semibold" style="font-variant-numeric:tabular-nums;"><?= $no++ ?></td>
                                        <td>
                                            <div class="fw-bold" style="font-family:'Outfit',sans-serif;font-size:0.95rem;color:var(--text);">
                                                <?= htmlspecialchars($item['judul_iringan']) ?>
                                            </div>
                                            <?php if ($isMaster): ?>
                                                <span class="badge bg-secondary" style="font-size:0.68rem;opacity:0.85;">Materi Studio</span>
                                            <?php endif; ?>
                                        </td>
                                        <td data-filter-val="<?= htmlspecialchars($kat) ?>">
                                            <span class="badge" style="<?= $badgeStyle ?>font-size:0.75rem;padding:4px 10px;border-radius:12px;font-weight:600;">
                                                <i class="fa <?= $kat === 'lomba' ? 'fa-trophy' : 'fa-play-circle' ?> me-1"></i><?= ucfirst($kat) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($item['catatan'])): ?>
                                                <div class="small text-muted" style="line-height:1.45;">
                                                    <i class="fa fa-info-circle me-1" style="color:var(--accent);"></i><?= htmlspecialchars($item['catatan']) ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                            <?php if (!empty($item['coach_nama'])): ?>
                                                <div class="text-muted" style="font-size:0.75rem;margin-top:2px;">
                                                    <i class="fa fa-user-edit me-1"></i>Oleh: <?= htmlspecialchars($item['coach_nama']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2 justify-content-end align-items-center text-nowrap">
                                                <a href="<?= htmlspecialchars($item['gdrive_link']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-2 text-nowrap" style="height:38px;padding:0 14px;border-radius:8px;font-weight:600;font-size:0.84rem;" title="Buka di Google Drive" aria-label="Buka Google Drive">
                                                    <i class="fab fa-google-drive" style="font-size:0.95rem;"></i>
                                                    <span>Buka Google Drive</span>
                                                </a>
                                                <?php if (!empty($item['youtube_link'])): ?>
                                                    <a href="<?= htmlspecialchars($item['youtube_link']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-2 text-nowrap" style="height:38px;padding:0 14px;border-radius:8px;font-weight:600;font-size:0.84rem;" title="Lihat referensi YouTube" aria-label="Lihat referensi YouTube">
                                                        <i class="fab fa-youtube text-danger" style="font-size:1rem;"></i>
                                                        <span>YouTube</span>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pagination-container" data-pagination-table="#tableIringanOrtu">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <small class="text-muted pagination-summary">Menampilkan 1-<?= min(10, count($allDisplayRows)) ?> dari <?= count($allDisplayRows) ?> iringan</small>
                        <div class="limit-switch">
                            <button type="button" class="limit-pill active" data-limit="10">10</button>
                            <button type="button" class="limit-pill" data-limit="20">20</button>
                            <button type="button" class="limit-pill" data-limit="all">Semua</button>
                        </div>
                    </div>
                    <nav aria-label="Pagination iringan orang tua" class="pagination-nav">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item page-prev disabled">
                                <button type="button" class="page-link page-arrow" aria-label="Halaman sebelumnya" title="Halaman sebelumnya"><i class="fa fa-chevron-left"></i></button>
                            </li>
                            <li class="page-item disabled"><span class="page-link page-status-pill">Hal. 1 / <?= max(1, (int)ceil(count($allDisplayRows) / 10)) ?></span></li>
                            <li class="page-item page-next <?= count($allDisplayRows) <= 10 ? 'disabled' : '' ?>">
                                <button type="button" class="page-link page-arrow" aria-label="Halaman berikutnya" title="Halaman berikutnya"><i class="fa fa-chevron-right"></i></button>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    <?php else: ?>
        <!-- ==================== TAMPILAN STAFF (OWNER, ADMIN, COACH) ==================== -->
        <div class="d-flex justify-content-between align-items-center mb-3 form-inline flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h3 class="mb-0 fw-bold" style="font-family:'Outfit',sans-serif;letter-spacing:-0.02em;"><i class="fa fa-headphones me-2" style="color:var(--primary);"></i>Data Lagu Iringan</h3>
                <span class="filter-counter-badge" id="counterIringan">Total: <?= count($rows) ?> iringan</span>
            </div>
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <div class="position-relative">
                    <input id="searchIringanLive" data-live-search="#tableIringan" data-counter-target="#counterIringan" class="form-control form-control-sm" style="max-width:240px;padding-left:32px;border-radius:8px;" placeholder="Cari iringan / murid…">
                    <i class="fa fa-search position-absolute top-50 translate-middle-y text-muted" style="left:12px;font-size:0.82rem;"></i>
                </div>
                <div class="dropdown filter-dropdown-container" data-table-target="#tableIringan">
                    <button class="btn btn-sm btn-filter-trigger dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="border-radius:8px;">
                        <i class="fa fa-sliders-h me-1" style="color:var(--accent);"></i> Filter
                        <span class="filter-count-badge ms-1" style="display:none;">0</span>
                    </button>
                    <div class="dropdown-menu filter-dropdown-menu shadow-lg">
                        <div class="filter-header">
                            <span class="filter-title"><i class="fa fa-sliders-h me-1 text-accent"></i> Filter Data</span>
                            <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 btn-reset-filters"><i class="fa fa-undo me-1"></i>Reset</button>
                        </div>
                        <div class="mb-3">
                            <label class="filter-label">Kategori Iringan</label>
                            <select data-table-target="#tableIringan" data-live-filter-col="2" class="form-select form-select-sm">
                                <option value="">Semua Kategori</option>
                                <option value="latihan">Latihan</option>
                                <option value="lomba">Lomba</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="filter-label">Catatan Khusus</label>
                            <select data-table-target="#tableIringan" data-live-filter-col="4" class="form-select form-select-sm">
                                <option value="">Semua Catatan</option>
                                <option value=":has-value">Ada Catatan</option>
                                <option value=":empty">Tanpa Catatan</option>
                            </select>
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary btn-add btn-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalAddIringan" style="border-radius:8px;font-weight:600;">
                    <i class="fa fa-plus-circle me-1"></i> Tambah Iringan
                </button>
            </div>
        </div>

        <div id="iringanListRegion" data-async-region>
        <div class="card bento-hero-card p-3 p-md-4 mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tableIringan">
                    <thead>
                        <tr>
                            <th>Murid</th>
                            <th>Judul Iringan</th>
                            <th>Kategori</th>
                            <th>Google Drive</th>
                            <th>Catatan</th>
                            <th>Ditambahkan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $modalsHtml = '';
                        foreach ($rows as $r):
                            $kat = $r['kategori'];
                            $badgeStyle = ($kat === 'lomba') 
                                ? 'background:rgba(168,144,98,0.18);color:#8a7548;' 
                                : (($kat === 'latihan') ? 'background:rgba(74,107,93,0.14);color:var(--primary);' : 'background:rgba(46,134,222,0.14);color:#2e86de;');
                        ?>
                            <tr>
                                <td>
                                    <strong><i class="fa fa-user me-1 text-primary"></i><?= htmlspecialchars($r['murid_nama'] ?? '-') ?></strong>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($r['judul_iringan']) ?></strong>
                                    <?php if (!empty($r['master_judul']) && $r['master_judul'] !== $r['judul_iringan']): ?>
                                        <div class="small text-muted">(Master: <?= htmlspecialchars($r['master_judul']) ?>)</div>
                                    <?php endif; ?>
                                </td>
                                <td data-filter-val="<?= strtolower($kat) ?>">
                                    <span class="badge" style="<?= $badgeStyle ?>font-weight:600;padding:5px 12px;border-radius:20px;">
                                        <?= ucfirst($kat) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= htmlspecialchars($r['gdrive_link']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary" style="font-size:0.8rem;">
                                        <i class="fab fa-google-drive me-1"></i>Link Drive
                                    </a>
                                </td>
                                <td data-has-val="<?= (!empty($r['catatan']) && trim($r['catatan']) !== '-' && trim($r['catatan']) !== '') ? '1' : '0' ?>"><span class="text-muted small"><?= htmlspecialchars($r['catatan'] ?: '-') ?></span></td>
                                <td><small class="text-muted"><?= htmlspecialchars($r['creator_nama'] ?: '-') ?></small></td>
                                <td>
                                    <button class="btn btn-sm btn-edit" data-bs-toggle="modal" data-bs-target="#modalEditIringan<?= $r['id'] ?>" aria-label="Edit iringan <?= htmlspecialchars($r['judul_iringan']) ?>">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <form method="post" action="iringan.php" style="display:inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <button class="btn btn-sm btn-delete" type="button" data-bs-toggle="modal" data-bs-target="#modalDeleteConfirm" data-delete-title="Hapus Lagu Iringan" data-delete-message="Lagu iringan <?= htmlspecialchars($r['judul_iringan'], ENT_QUOTES) ?> untuk <?= htmlspecialchars($r['murid_nama'] ?? '', ENT_QUOTES) ?> akan dihapus. Tindakan ini tidak bisa dibatalkan." data-delete-confirm="Hapus Iringan" aria-label="Hapus iringan <?= htmlspecialchars($r['judul_iringan']) ?>">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <?php ob_start(); ?>
                            <div class="modal fade" id="modalEditIringan<?= $r['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" action="iringan.php" data-ajax-form data-refresh-region="#iringanListRegion">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="fa fa-edit me-2" style="color:var(--gold);"></i>Edit Lagu Iringan</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-2">
                                                    <label class="form-label">Murid</label>
                                                    <select name="id_murid" class="form-select" required>
                                                        <?php foreach ($allMurid as $m): ?>
                                                            <option value="<?= $m['id'] ?>" <?= ((int)$r['id_murid'] === (int)$m['id']) ? 'selected' : '' ?>><?= htmlspecialchars($m['nama']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label">Hubungkan Lagu Studio (opsional)</label>
                                                    <select name="id_lagu" class="form-select">
                                                        <option value="">-- Tanpa Lagu Studio (Lagu Bebas / Lomba) --</option>
                                                        <?php foreach ($allLagu as $l): ?>
                                                            <option value="<?= $l['id_lagu'] ?>" <?= ((int)$r['id_lagu'] === (int)$l['id_lagu']) ? 'selected' : '' ?>><?= htmlspecialchars($l['judul']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label">Judul Iringan</label>
                                                    <input name="judul_iringan" class="form-control" required value="<?= htmlspecialchars($r['judul_iringan']) ?>">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label">Kategori</label>
                                                    <select name="kategori" class="form-select" required>
                                                        <option value="latihan" <?= $kat === 'latihan' ? 'selected' : '' ?>>Latihan Rutin</option>
                                                        <option value="lomba" <?= $kat === 'lomba' ? 'selected' : '' ?>>Iringan Lomba</option>
                                                        <option value="lainnya" <?= $kat === 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label">Link Google Drive</label>
                                                    <input name="gdrive_link" type="url" class="form-control" placeholder="https://drive.google.com/..." required value="<?= htmlspecialchars($r['gdrive_link']) ?>">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label">Catatan untuk Murid / Orang Tua</label>
                                                    <textarea name="catatan" class="form-control" rows="2" placeholder="Contoh: Nada dasar F, tempo 100 untuk lomba bulan depan"><?= htmlspecialchars($r['catatan'] ?? '') ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button class="btn btn-success">Simpan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php $modalsHtml .= ob_get_clean(); ?>
                        <?php endforeach; ?>

                        <?php if (!$rows): ?>
                            <tr>
                                <td colspan="7" class="text-muted text-center py-4">
                                    <?= ($search !== '' || $filterMurid > 0 || $filterKategori !== '') ? 'Tidak ada data iringan yang sesuai pencarian.' : 'Belum ada data lagu iringan yang ditambahkan.' ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pagination-container" data-pagination-table="#tableIringan">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <small class="text-muted pagination-summary">Menampilkan 1-<?= min(10, $totalRows) ?> dari <?= $totalRows ?> data</small>
                    <div class="limit-switch">
                        <button type="button" class="limit-pill active" data-limit="10">10</button>
                        <button type="button" class="limit-pill" data-limit="20">20</button>
                        <button type="button" class="limit-pill" data-limit="100">100</button>
                        <button type="button" class="limit-pill" data-limit="all">Semua</button>
                    </div>
                </div>
                <nav aria-label="Pagination iringan" class="pagination-nav">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item page-prev disabled">
                            <button type="button" class="page-link page-arrow" aria-label="Halaman sebelumnya" title="Halaman sebelumnya"><i class="fa fa-chevron-left"></i></button>
                        </li>
                        <li class="page-item disabled"><span class="page-link page-status-pill">Hal. 1 / <?= max(1, (int)ceil($totalRows / 10)) ?></span></li>
                        <li class="page-item page-next <?= $totalRows <= 10 ? 'disabled' : '' ?>">
                            <button type="button" class="page-link page-arrow" aria-label="Halaman berikutnya" title="Halaman berikutnya"><i class="fa fa-chevron-right"></i></button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        </div>

        <?= $modalsHtml ?>

        <!-- Modal Tambah Iringan -->
        <div class="modal fade" id="modalAddIringan" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post" action="iringan.php" data-ajax-form data-refresh-region="#iringanListRegion">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="create">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fa fa-plus-circle me-2" style="color:var(--success);"></i>Tambah Lagu Iringan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-2">
                                <label class="form-label">Pilih Murid</label>
                                <select name="id_murid" class="form-select" required>
                                    <option value="">-- Pilih Murid --</option>
                                    <?php foreach ($allMurid as $m): ?>
                                        <option value="<?= $m['id'] ?>" <?= ($filterMurid === (int)$m['id']) ? 'selected' : '' ?>><?= htmlspecialchars($m['nama']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Pilih dari Master Lagu Studio (opsional)</label>
                                <select name="id_lagu" id="addSelectLagu" class="form-select" onchange="autoFillJudulLagu(this)">
                                    <option value="" data-judul="" data-link="">-- Tanpa Lagu Studio (Lagu Bebas / Khusus Lomba) --</option>
                                    <?php foreach ($allLagu as $l): ?>
                                        <option value="<?= $l['id_lagu'] ?>" data-judul="<?= htmlspecialchars($l['judul']) ?>" data-link="<?= htmlspecialchars($l['iringan_link'] ?? '') ?>">
                                            <?= htmlspecialchars($l['judul']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Judul Iringan</label>
                                <input name="judul_iringan" id="addJudulIringan" class="form-control" placeholder="Contoh: Indonesia Raya (Minus One)" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Kategori</label>
                                <select name="kategori" class="form-select" required>
                                    <option value="latihan">Latihan Rutin</option>
                                    <option value="lomba">Iringan Lomba</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Link Google Drive</label>
                                <input name="gdrive_link" id="addGdriveLink" type="url" class="form-control" placeholder="https://drive.google.com/..." required>
                                <small class="text-muted" style="font-size:0.75rem;">Pastikan izin berbagi link Google Drive sudah diatur ke 'Siapa saja yang memiliki link'.</small>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Catatan untuk Murid / Orang Tua</label>
                                <textarea name="catatan" class="form-control" rows="2" placeholder="Contoh: Nada dasar F, tempo 100 untuk latihan persiapan lomba"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button class="btn btn-success">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        function autoFillJudulLagu(selectElem) {
            const selected = selectElem.options[selectElem.selectedIndex];
            const judul = selected.getAttribute('data-judul') || '';
            const link = selected.getAttribute('data-link') || '';
            const inputJudul = document.getElementById('addJudulIringan');
            const inputLink = document.getElementById('addGdriveLink');
            if (judul && inputJudul && !inputJudul.value) {
                inputJudul.value = judul;
            }
            if (link && inputLink && !inputLink.value) {
                inputLink.value = link;
            }
        }
        </script>
    <?php endif; ?>
</div>

