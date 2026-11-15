<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3 form-inline flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
      <h3 class="mb-0 fw-bold" style="font-family:'Outfit',sans-serif;letter-spacing:-0.02em;"><i class="fa fa-users me-2" style="color:var(--primary);"></i>Data Murid</h3>
      <span class="filter-counter-badge" id="counterMurid">Total: <?= count($rows) ?> murid</span>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
      <div class="position-relative">
        <input id="searchMuridLive" data-live-search="#tableMurid" data-counter-target="#counterMurid" class="form-control form-control-sm" style="max-width:220px;padding-left:32px;border-radius:8px;" placeholder="Cari nama / ig…">
        <i class="fa fa-search position-absolute top-50 translate-middle-y text-muted" style="left:12px;font-size:0.82rem;"></i>
      </div>
      <div class="dropdown filter-dropdown-container" data-table-target="#tableMurid">
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
            <label class="filter-label">Status Instagram</label>
            <select data-table-target="#tableMurid" data-live-filter-col="1" class="form-select form-select-sm">
              <option value="">Semua Instagram</option>
              <option value=":has-value">Punya IG</option>
              <option value=":empty">Tanpa IG</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="filter-label">Status Foto Profil</label>
            <select data-table-target="#tableMurid" data-live-filter-col="2" class="form-select form-select-sm">
              <option value="">Semua Foto</option>
              <option value=":has-value">Ada Foto</option>
              <option value=":empty">Tanpa Foto</option>
            </select>
          </div>
          <div>
            <label class="filter-label">Status Murid</label>
            <select data-table-target="#tableMurid" data-live-filter-col="3" class="form-select form-select-sm">
              <option value="">Semua Status</option>
              <option value="Aktif">Aktif</option>
              <option value="Non-Aktif">Non-Aktif</option>
            </select>
          </div>
        </div>
      </div>
      <button class="btn btn-primary btn-add btn-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalAddMurid" style="border-radius:8px;font-weight:600;"><i class="fa fa-plus-circle me-1"></i> Tambah Murid</button>
    </div>
  </div>
  <div id="muridListRegion" data-async-region>
  <div class="card bento-hero-card p-3 p-md-4 mb-4">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="tableMurid">
        <thead>
          <tr>
            <th>Nama</th>
            <th>Instagram</th>
            <th>Foto</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $modalsHtml = '';
          foreach ($rows as $r): 
            $ig = trim($r['instagram'] ?? '');
          ?>
            <tr>
              <td><strong><?= htmlspecialchars($r['nama']) ?></strong></td>
              <td data-has-val="<?= $ig !== '' ? '1' : '0' ?>">
                <?php
                if ($ig) {
                  $display = $ig;
                  $url = $ig;
                  if (!preg_match('~^https?://~', $ig)) {
                    $handle = ltrim($ig, '@');
                    $display = '@' . $handle;
                    $url = 'https://instagram.com/' . $handle;
                  }
                  echo '<a target="_blank" rel="noopener" href="' . htmlspecialchars($url) . '"><i class="fab fa-instagram me-1" style="color:var(--accent-2);"></i>' . htmlspecialchars($display) . '</a>';
                } else {
                  echo '<span class="text-muted">-</span>';
                }
                ?>
              </td>
              <td data-has-val="<?= !empty($r['foto']) ? '1' : '0' ?>">
                <?php if (!empty($r['foto'])): ?>
                  <div class="thumb-zoom-wrapper" data-zoom-src="<?= htmlspecialchars($r['foto']) ?>" data-zoom-title="<?= htmlspecialchars($r['nama']) ?>" title="Klik untuk memperbesar foto <?= htmlspecialchars($r['nama']) ?>">
                    <img src="<?= htmlspecialchars($r['foto']) ?>" class="thumb" alt="Foto <?= htmlspecialchars($r['nama']) ?>">
                    <span class="zoom-badge-icon" aria-hidden="true"><i class="fa fa-search-plus"></i></span>
                  </div>
                <?php else: ?>
                  <span class="avatar-empty-badge" title="Belum ada foto"><i class="fa fa-user text-muted opacity-50"></i></span>
                <?php endif; ?>
              </td>
              <?php $st = ($r['status'] ?? 'aktif') === 'non_aktif' ? 'non_aktif' : 'aktif'; ?>
              <td data-status="<?= $st ?>">
                <?php if ($st === 'aktif'): ?>
                  <span class="badge badge-status-aktif"><i class="fa fa-check-circle me-1"></i>Aktif</span>
                <?php else: ?>
                  <span class="badge badge-status-nonaktif"><i class="fa fa-pause-circle me-1"></i>Non-Aktif</span>
                <?php endif; ?>
              </td>
              <td>
                <button class="btn btn-sm btn-edit" data-bs-toggle="modal" data-bs-target="#modalEditMurid<?= $r['id'] ?>"><i class="fa fa-edit"></i></button>
                <form method="post" action="murid.php" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $r['id'] ?>">
                  <button class="btn btn-sm btn-delete" type="button" data-bs-toggle="modal" data-bs-target="#modalDeleteConfirm" data-delete-title="Hapus Murid" data-delete-message="Murid <?= htmlspecialchars($r['nama'] ?? '', ENT_QUOTES) ?> akan dihapus. Tindakan ini tidak bisa dibatalkan." data-delete-confirm="Hapus Murid"><i class="fa fa-trash"></i></button>
                </form>
              </td>
            </tr>
            <?php ob_start(); ?>
            <div class="modal fade" id="modalEditMurid<?= $r['id'] ?>" tabindex="-1">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="post" action="murid.php" enctype="multipart/form-data" data-ajax-form data-refresh-region="#muridListRegion">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <div class="modal-header">
                      <h5 class="modal-title"><i class="fa fa-user-edit me-2" style="color:var(--primary);"></i>Edit Murid</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-3 text-center">
                        <div class="img-preview-wrapper">
                          <img id="previewEdit<?= $r['id'] ?>" class="thumb-preview-circle" src="<?= !empty($r['foto']) ? htmlspecialchars($r['foto']) : '' ?>" data-original-src="<?= !empty($r['foto']) ? htmlspecialchars($r['foto']) : '' ?>" data-zoom-src="<?= !empty($r['foto']) ? htmlspecialchars($r['foto']) : '' ?>" data-zoom-title="Foto <?= htmlspecialchars($r['nama']) ?>" style="<?= !empty($r['foto']) ? 'cursor:zoom-in;' : 'display:none;' ?>" alt="Preview Foto" title="Klik untuk memperbesar">
                          <div id="placeholderEdit<?= $r['id'] ?>" class="preview-placeholder-circle" style="<?= !empty($r['foto']) ? 'display:none;' : '' ?>">
                            <i class="fa fa-user fa-2x mb-1 opacity-50"></i>
                            <span style="font-size:0.65rem;">Belum ada foto</span>
                          </div>
                        </div>
                        <div class="d-flex justify-content-center">
                          <button type="button" id="btnZoomEdit<?= $r['id'] ?>" data-zoom-src="<?= !empty($r['foto']) ? htmlspecialchars($r['foto']) : '' ?>" data-zoom-title="Foto <?= htmlspecialchars($r['nama']) ?>" class="btn btn-link btn-sm text-decoration-none p-0 mt-1" style="<?= !empty($r['foto']) ? '' : 'display:none;' ?>font-size:0.75rem;"><i class="fa fa-search-plus me-1"></i>Perbesar Foto</button>
                        </div>
                        <div id="compressInfoEdit<?= $r['id'] ?>" class="mt-1" style="display:none;"></div>
                      </div>
                      <div class="mb-3"><label class="form-label fw-semibold">Nama <span class="text-danger">*</span></label><input name="nama" class="form-control" required value="<?= htmlspecialchars($r['nama']) ?>" autocomplete="off"></div>
                      <div class="mb-3">
                        <label class="form-label fw-semibold">Status Murid <span class="text-danger">*</span></label>
                        <select name="status" class="form-select">
                          <option value="aktif" <?= ($r['status'] ?? 'aktif') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                          <option value="non_aktif" <?= ($r['status'] ?? 'aktif') === 'non_aktif' ? 'selected' : '' ?>>Non-Aktif</option>
                        </select>
                      </div>
                      <div class="mb-3"><label class="form-label fw-semibold">Instagram (opsional)</label><input name="instagram" class="form-control" placeholder="@username" value="<?= htmlspecialchars($r['instagram'] ?? '') ?>" autocomplete="off"></div>
                      <div class="mb-3"><label class="form-label fw-semibold">Ganti Foto Profil</label><input name="foto" data-preview-target="#previewEdit<?= $r['id'] ?>" data-placeholder-target="#placeholderEdit<?= $r['id'] ?>" data-compress-info="#compressInfoEdit<?= $r['id'] ?>" data-zoom-btn="#btnZoomEdit<?= $r['id'] ?>" data-compress-image type="file" accept="image/*" class="form-control"><div class="form-text small text-muted">Format: JPG, PNG, WEBP. Otomatis dikompres sebelum diunggah.</div></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-success"><i class="fa fa-save me-1"></i> Simpan Perubahan</button></div>
                  </form>
                </div>
              </div>
            </div>
            <?php $modalsHtml .= ob_get_clean(); endforeach; ?>
          <?php if (!$rows): ?>
            <tr>
              <td colspan="5" class="text-muted text-center"><?= $search !== '' ? 'Tidak ada murid yang cocok dengan pencarian.' : 'Belum ada data murid.' ?></td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pagination-container" data-pagination-table="#tableMurid">
      <div class="d-flex align-items-center gap-3 flex-wrap">
        <small class="text-muted pagination-summary">Menampilkan 1-<?= min(10, $totalRows) ?> dari <?= $totalRows ?> data</small>
        <div class="limit-switch">
          <button type="button" class="limit-pill active" data-limit="10">10</button>
          <button type="button" class="limit-pill" data-limit="20">20</button>
          <button type="button" class="limit-pill" data-limit="100">100</button>
          <button type="button" class="limit-pill" data-limit="all">Semua</button>
        </div>
      </div>
      <nav aria-label="Pagination murid" class="pagination-nav">
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
  <?= $modalsHtml ?>
  </div>
</div>

<div class="modal fade" id="modalAddMurid" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="murid.php" enctype="multipart/form-data" data-ajax-form data-refresh-region="#muridListRegion">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-user-plus me-2" style="color:var(--success);"></i>Tambah Murid</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3 text-center">
            <div class="img-preview-wrapper">
              <img id="previewAdd" class="thumb-preview-circle" src="" style="display:none;cursor:zoom-in;" alt="Preview Foto" data-zoom-src="" data-zoom-title="Preview Foto Murid" title="Klik untuk memperbesar">
              <div id="placeholderAdd" class="preview-placeholder-circle">
                <i class="fa fa-user fa-2x mb-1 opacity-50"></i>
                <span style="font-size:0.65rem;">Belum ada foto</span>
              </div>
            </div>
            <div class="d-flex justify-content-center">
              <button type="button" id="btnZoomAdd" data-zoom-src="" data-zoom-title="Preview Foto Baru" class="btn btn-link btn-sm text-decoration-none p-0 mt-1" style="display:none;font-size:0.75rem;"><i class="fa fa-search-plus me-1"></i>Perbesar Foto</button>
            </div>
            <div id="compressInfoAdd" class="mt-1" style="display:none;"></div>
          </div>
          <div class="mb-3"><label class="form-label fw-semibold">Nama <span class="text-danger">*</span></label><input name="nama" class="form-control" placeholder="Contoh: Chelsea Olivia" required autocomplete="off"></div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Status Murid</label>
            <select name="status" class="form-select">
              <option value="aktif" selected>Aktif</option>
              <option value="non_aktif">Non-Aktif</option>
            </select>
          </div>
          <div class="mb-3"><label class="form-label fw-semibold">Instagram (opsional)</label><input name="instagram" class="form-control" placeholder="@username" autocomplete="off"></div>
          <div class="mb-3"><label class="form-label fw-semibold">Foto</label><input name="foto" data-preview-target="#previewAdd" data-placeholder-target="#placeholderAdd" data-compress-info="#compressInfoAdd" data-zoom-btn="#btnZoomAdd" data-compress-image type="file" accept="image/*" class="form-control"><div class="form-text small text-muted">Format: JPG, PNG, WEBP. Otomatis dikompres sebelum diunggah.</div></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-success"><i class="fa fa-save me-1"></i> Simpan</button></div>
      </form>
    </div>
  </div>
</div>
