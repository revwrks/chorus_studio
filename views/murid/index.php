<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3 form-inline flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
      <h1 class="page-title mb-0"><i class="fa fa-users me-2" style="color:var(--primary);" aria-hidden="true"></i>Data Murid</h1>
      <span class="filter-counter-badge" id="counterMurid">Total: <?= count($rows) ?> murid</span>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
      <div class="position-relative">
        <input type="search" id="searchMuridLive" data-live-search="#tableMurid" data-counter-target="#counterMurid" class="form-control form-control-sm" style="max-width:220px;padding-left:32px;border-radius:8px;" placeholder="Cari nama / ig…" aria-label="Cari murid...">
        <i class="fa fa-search position-absolute top-50 translate-middle-y text-muted" style="left:12px;font-size:0.82rem;" aria-hidden="true"></i>
      </div>
      <div class="dropdown filter-dropdown-container" data-table-target="#tableMurid">
        <button class="btn btn-sm btn-filter-trigger dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="border-radius:8px;">
          <i class="fa fa-sliders-h me-1" style="color:var(--accent);" aria-hidden="true"></i> Filter
          <span class="filter-count-badge ms-1" style="display:none;">0</span>
        </button>
        <div class="dropdown-menu filter-dropdown-menu shadow-lg">
          <div class="filter-header">
            <span class="filter-title"><i class="fa fa-sliders-h me-1 text-accent" aria-hidden="true"></i> Filter Data</span>
            <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 btn-reset-filters"><i class="fa fa-undo me-1" aria-hidden="true"></i>Reset</button>
          </div>
          <div class="mb-3">
            <label for="filterIgMurid" class="filter-label">Status Instagram</label>
            <select id="filterIgMurid" data-table-target="#tableMurid" data-live-filter-col="1" class="form-select form-select-sm">
              <option value="">Semua Instagram</option>
              <option value=":has-value">Punya IG</option>
              <option value=":empty">Tanpa IG</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="filterFotoMurid" class="filter-label">Status Foto Profil</label>
            <select id="filterFotoMurid" data-table-target="#tableMurid" data-live-filter-col="2" class="form-select form-select-sm">
              <option value="">Semua Foto</option>
              <option value=":has-value">Ada Foto</option>
              <option value=":empty">Tanpa Foto</option>
            </select>
          </div>
          <div>
            <label for="filterStatusMurid" class="filter-label">Status Murid</label>
            <select id="filterStatusMurid" data-table-target="#tableMurid" data-live-filter-col="3" class="form-select form-select-sm">
              <option value="">Semua Status</option>
              <option value="Aktif">Aktif</option>
              <option value="Non-Aktif">Non-Aktif</option>
            </select>
          </div>
        </div>
      </div>
      <button type="button" class="btn btn-primary btn-add btn-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalAddMurid" style="border-radius:8px;font-weight:600;"><i class="fa fa-plus-circle me-1" aria-hidden="true"></i> Tambah Murid</button>
    </div>
  </div>
  <?php
  $searchVal = $search ?? '';
  $totalRowsCount = $totalRows ?? count($rows);
  ?>
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
            $mId = (int)($r['id'] ?? 0);
            $mNama = (string)($r['nama'] ?? '');
            $mFoto = (string)($r['foto'] ?? '');
            $ig = trim((string)($r['instagram'] ?? ''));
          ?>
            <tr>
              <td><strong><?= e($mNama) ?></strong></td>
              <td data-has-val="<?= $ig !== '' ? '1' : '0' ?>">
                <?php
                if ($ig !== '') {
                  $display = $ig;
                  $url = $ig;
                  if (!preg_match('~^https?://~', $ig)) {
                    $handle = ltrim($ig, '@');
                    $display = '@' . $handle;
                    $url = 'https://instagram.com/' . $handle;
                  }
                  echo '<a target="_blank" rel="noopener" href="' . e($url) . '"><i class="fab fa-instagram me-1" style="color:var(--accent-ink);" aria-hidden="true"></i>' . e($display) . '</a>';
                } else {
                  echo '<span class="text-muted">-</span>';
                }
                ?>
              </td>
              <td data-has-val="<?= $mFoto !== '' ? '1' : '0' ?>">
                <?php if ($mFoto !== ''): ?>
                  <button type="button" class="thumb-zoom-wrapper border-0 bg-transparent p-0" data-zoom-src="<?= e($mFoto) ?>" data-zoom-title="<?= e($mNama) ?>" title="Klik untuk memperbesar foto <?= e($mNama) ?>" aria-label="Perbesar foto <?= e($mNama) ?>">
                    <img src="<?= e($mFoto) ?>" class="thumb" alt="Foto <?= e($mNama) ?>">
                    <span class="zoom-badge-icon" aria-hidden="true"><i class="fa fa-search-plus"></i></span>
                  </button>
                <?php else: ?>
                  <span class="avatar-empty-badge" title="Belum ada foto"><i class="fa fa-user text-muted opacity-50" aria-hidden="true"></i></span>
                <?php endif; ?>
              </td>
              <?php $st = ($r['status'] ?? 'aktif') === 'non_aktif' ? 'non_aktif' : 'aktif'; ?>
              <td data-status="<?= e($st) ?>" data-filter-val="<?= e($st) ?>">
                <?php if ($st === 'aktif'): ?>
                  <span class="badge badge-status-aktif"><i class="fa fa-check-circle me-1" aria-hidden="true"></i>Aktif</span>
                <?php else: ?>
                  <span class="badge badge-status-nonaktif"><i class="fa fa-pause-circle me-1" aria-hidden="true"></i>Non-Aktif</span>
                <?php endif; ?>
              </td>
              <td>
                <button type="button" class="btn btn-sm btn-edit" data-bs-toggle="modal" data-bs-target="#modalEditMurid<?= $mId ?>" aria-label="Edit murid <?= e($mNama) ?>"><i class="fa fa-edit" aria-hidden="true"></i></button>
                <form method="post" action="murid.php" class="d-inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $mId ?>">
                  <button class="btn btn-sm btn-delete" type="button" data-bs-toggle="modal" data-bs-target="#modalDeleteConfirm" data-delete-title="Hapus Murid" data-delete-message="Murid <?= e($mNama) ?> akan dihapus. Tindakan ini tidak bisa dibatalkan." data-delete-confirm="Hapus Murid" aria-label="Hapus murid <?= e($mNama) ?>"><i class="fa fa-trash" aria-hidden="true"></i></button>
                </form>
              </td>
            </tr>
            <?php ob_start(); ?>
            <div class="modal fade" id="modalEditMurid<?= $mId ?>" tabindex="-1" aria-labelledby="modalEditMuridLabel<?= $mId ?>" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="post" action="murid.php" enctype="multipart/form-data" data-ajax-form data-refresh-region="#muridListRegion">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= $mId ?>">
                    <div class="modal-header">
                      <h5 class="modal-title" id="modalEditMuridLabel<?= $mId ?>"><i class="fa fa-user-edit me-2" style="color:var(--primary);" aria-hidden="true"></i>Edit Murid</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-3 text-center">
                        <div class="img-preview-wrapper">
                          <img id="previewEdit<?= $mId ?>" class="thumb-preview-circle" src="<?= $mFoto !== '' ? e($mFoto) : '' ?>" data-original-src="<?= $mFoto !== '' ? e($mFoto) : '' ?>" data-zoom-src="<?= $mFoto !== '' ? e($mFoto) : '' ?>" data-zoom-title="Foto <?= e($mNama) ?>" style="<?= $mFoto !== '' ? 'cursor:zoom-in;' : 'display:none;' ?>" alt="Preview Foto <?= e($mNama) ?>" title="Klik untuk memperbesar">
                          <div id="placeholderEdit<?= $mId ?>" class="preview-placeholder-circle" style="<?= $mFoto !== '' ? 'display:none;' : '' ?>">
                            <i class="fa fa-user fa-2x mb-1 opacity-50" aria-hidden="true"></i>
                            <span style="font-size:0.65rem;">Belum ada foto</span>
                          </div>
                        </div>
                        <div class="d-flex justify-content-center">
                          <button type="button" id="btnZoomEdit<?= $mId ?>" data-zoom-src="<?= $mFoto !== '' ? e($mFoto) : '' ?>" data-zoom-title="Foto <?= e($mNama) ?>" class="btn btn-link btn-sm text-decoration-none p-0 mt-1" style="<?= $mFoto !== '' ? '' : 'display:none;' ?>font-size:0.75rem;"><i class="fa fa-search-plus me-1" aria-hidden="true"></i>Perbesar Foto</button>
                        </div>
                        <div id="compressInfoEdit<?= $mId ?>" class="mt-1" style="display:none;"></div>
                      </div>
                      <div class="mb-3">
                        <label for="editMuridNama_<?= $mId ?>" class="form-label fw-semibold">Nama <span class="text-danger">*</span></label>
                        <input id="editMuridNama_<?= $mId ?>" name="nama" class="form-control" required value="<?= e($mNama) ?>" autocomplete="off">
                      </div>
                      <div class="mb-3">
                        <label for="editMuridStatus_<?= $mId ?>" class="form-label fw-semibold">Status Murid <span class="text-danger">*</span></label>
                        <select id="editMuridStatus_<?= $mId ?>" name="status" class="form-select">
                          <option value="aktif" <?= $st === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                          <option value="non_aktif" <?= $st === 'non_aktif' ? 'selected' : '' ?>>Non-Aktif</option>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label for="editMuridIg_<?= $mId ?>" class="form-label fw-semibold">Instagram (opsional)</label>
                        <input id="editMuridIg_<?= $mId ?>" name="instagram" class="form-control" placeholder="@username" value="<?= e($r['instagram'] ?? '') ?>" autocomplete="off">
                      </div>
                      <div class="mb-3">
                        <label for="editMuridFoto_<?= $mId ?>" class="form-label fw-semibold">Ganti Foto Profil</label>
                        <input id="editMuridFoto_<?= $mId ?>" name="foto" data-preview-target="#previewEdit<?= $mId ?>" data-placeholder-target="#placeholderEdit<?= $mId ?>" data-compress-info="#compressInfoEdit<?= $mId ?>" data-zoom-btn="#btnZoomEdit<?= $mId ?>" data-compress-image type="file" accept="image/*" class="form-control">
                        <div class="form-text small text-muted">Format: JPG, PNG, WEBP. Otomatis dikompres sebelum diunggah.</div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                      <button type="submit" class="btn btn-success"><i class="fa fa-save me-1" aria-hidden="true"></i> Simpan Perubahan</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <?php $modalsHtml .= ob_get_clean(); endforeach; ?>
          <?php if (!$rows): ?>
            <tr>
              <td colspan="5" class="text-muted text-center"><?= $searchVal !== '' ? 'Tidak ada murid yang cocok dengan pencarian.' : 'Belum ada data murid.' ?></td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pagination-container" data-pagination-table="#tableMurid">
      <div class="d-flex align-items-center gap-3 flex-wrap">
        <small class="text-muted pagination-summary">Menampilkan 1-<?= min(10, $totalRowsCount) ?> dari <?= $totalRowsCount ?> data</small>
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
            <button type="button" class="page-link page-arrow" aria-label="Halaman sebelumnya" title="Halaman sebelumnya"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
          </li>
          <li class="page-item disabled"><span class="page-link page-status-pill">Hal. 1 / <?= max(1, (int)ceil($totalRowsCount / 10)) ?></span></li>
          <li class="page-item page-next <?= $totalRowsCount <= 10 ? 'disabled' : '' ?>">
            <button type="button" class="page-link page-arrow" aria-label="Halaman berikutnya" title="Halaman berikutnya"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
          </li>
        </ul>
      </nav>
    </div>
  </div>
  <?= $modalsHtml ?>
  </div>
</div>

<div class="modal fade" id="modalAddMurid" tabindex="-1" aria-labelledby="modalAddMuridLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="murid.php" enctype="multipart/form-data" data-ajax-form data-refresh-region="#muridListRegion">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <div class="modal-header">
          <h5 class="modal-title" id="modalAddMuridLabel"><i class="fa fa-user-plus me-2" style="color:var(--success);" aria-hidden="true"></i>Tambah Murid</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3 text-center">
            <div class="img-preview-wrapper">
              <img id="previewAdd" class="thumb-preview-circle" src="" style="display:none;cursor:zoom-in;" alt="Preview Foto" data-zoom-src="" data-zoom-title="Preview Foto Murid" title="Klik untuk memperbesar">
              <div id="placeholderAdd" class="preview-placeholder-circle">
                <i class="fa fa-user fa-2x mb-1 opacity-50" aria-hidden="true"></i>
                <span style="font-size:0.65rem;">Belum ada foto</span>
              </div>
            </div>
            <div class="d-flex justify-content-center">
              <button type="button" id="btnZoomAdd" data-zoom-src="" data-zoom-title="Preview Foto Baru" class="btn btn-link btn-sm text-decoration-none p-0 mt-1" style="display:none;font-size:0.75rem;"><i class="fa fa-search-plus me-1" aria-hidden="true"></i>Perbesar Foto</button>
            </div>
            <div id="compressInfoAdd" class="mt-1" style="display:none;"></div>
          </div>
          <div class="mb-3">
            <label for="addMuridNama" class="form-label fw-semibold">Nama <span class="text-danger">*</span></label>
            <input id="addMuridNama" name="nama" class="form-control" placeholder="Contoh: Chelsea Olivia" required autocomplete="off">
          </div>
          <div class="mb-3">
            <label for="addMuridStatus" class="form-label fw-semibold">Status Murid</label>
            <select id="addMuridStatus" name="status" class="form-select">
              <option value="aktif" selected>Aktif</option>
              <option value="non_aktif">Non-Aktif</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="addMuridIg" class="form-label fw-semibold">Instagram (opsional)</label>
            <input id="addMuridIg" name="instagram" class="form-control" placeholder="@username" autocomplete="off">
          </div>
          <div class="mb-3">
            <label for="addMuridFoto" class="form-label fw-semibold">Foto</label>
            <input id="addMuridFoto" name="foto" data-preview-target="#previewAdd" data-placeholder-target="#placeholderAdd" data-compress-info="#compressInfoAdd" data-zoom-btn="#btnZoomAdd" data-compress-image type="file" accept="image/*" class="form-control">
            <div class="form-text small text-muted">Format: JPG, PNG, WEBP. Otomatis dikompres sebelum diunggah.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success"><i class="fa fa-save me-1" aria-hidden="true"></i> Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
