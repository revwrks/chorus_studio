<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3 form-inline flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
      <h1 class="page-title"><i class="fa fa-music me-2" style="color:var(--primary);" aria-hidden="true"></i>Data Lagu</h1>
      <span class="filter-counter-badge" id="counterLagu">Total: <?= e((string)count($rows)) ?> lagu</span>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
      <div class="position-relative">
        <input id="searchLaguLive" data-live-search="#tableLagu" data-counter-target="#counterLagu" class="form-control form-control-sm" style="max-width:200px;padding-left:32px;border-radius:8px;" placeholder="Cari judul lagu…" aria-label="Cari judul lagu">
        <i class="fa fa-search position-absolute top-50 translate-middle-y text-muted" style="left:12px;font-size:0.82rem;" aria-hidden="true"></i>
      </div>
      <div class="dropdown filter-dropdown-container" data-table-target="#tableLagu">
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
            <label class="filter-label" for="filterLaguTingkat">Tingkat Kesulitan</label>
            <select id="filterLaguTingkat" data-table-target="#tableLagu" data-live-filter-col="4" class="form-select form-select-sm">
              <option value="">Semua Tingkat</option>
              <option value="mudah">Mudah</option>
              <option value="sedang">Sedang</option>
              <option value="sulit">Sulit</option>
              <option value="festival">Festival</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="filter-label" for="filterLaguYoutube">Link YouTube</label>
            <select id="filterLaguYoutube" data-table-target="#tableLagu" data-live-filter-col="2" class="form-select form-select-sm">
              <option value="">Semua YouTube</option>
              <option value=":has-value">Ada Link YT</option>
              <option value=":empty">Tanpa Link YT</option>
            </select>
          </div>
          <div>
            <label class="filter-label" for="filterLaguIringan">Musik Iringan</label>
            <select id="filterLaguIringan" data-table-target="#tableLagu" data-live-filter-col="3" class="form-select form-select-sm">
              <option value="">Semua Iringan</option>
              <option value=":has-value">Ada Iringan</option>
              <option value=":empty">Tanpa Iringan</option>
            </select>
          </div>
        </div>
      </div>
      <button type="button" class="btn btn-primary btn-add btn-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalTambahLagu" style="border-radius:8px;font-weight:600;"><i class="fa fa-plus-circle me-1" aria-hidden="true"></i> Tambah Lagu</button>
    </div>
  </div>
  <div id="laguListRegion" data-async-region>
  <div class="card bento-hero-card p-3 p-md-4 mb-4">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="tableLagu">
        <thead>
          <tr>
            <th>No</th>
            <th>Judul</th>
            <th>YouTube</th>
            <th>Iringan</th>
            <th>Tingkat</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $no = ($offset ?? 0) + 1;
          $modalsHtml = '';
          $tingkatClasses = [
            'mudah' => 'badge-level-mudah',
            'sedang' => 'badge-level-menengah',
            'sulit' => 'badge-level-sulit',
            'festival' => 'badge-level-festival',
          ];
          foreach ($rows as $r):
            $laguId = (string)($r['id_lagu'] ?? '');
            $judul = (string)($r['judul'] ?? '');
            $ytLink = (string)($r['youtube_link'] ?? '');
            $iringanLink = (string)($r['iringan_link'] ?? '');
            $tingkatRaw = strtolower((string)($r['tingkat'] ?? 'mudah'));
            $badgeClass = $tingkatClasses[$tingkatRaw] ?? 'badge-level-mudah';
          ?>
            <tr>
              <td><?= e((string)($no++)) ?></td>
              <td><strong><i class="fa fa-compact-disc me-1" style="color:var(--primary);" aria-hidden="true"></i><?= e($judul) ?></strong></td>
              <td data-has-val="<?= $ytLink !== '' ? '1' : '0' ?>"><?= $ytLink !== '' ? '<a target="_blank" rel="noopener" href="' . e($ytLink) . '"><i class="fab fa-youtube me-1 text-danger" aria-hidden="true"></i>Link</a>' : '<span class="text-muted">-</span>' ?></td>
              <td data-has-val="<?= $iringanLink !== '' ? '1' : '0' ?>"><?= $iringanLink !== '' ? '<a target="_blank" rel="noopener" href="' . e($iringanLink) . '"><i class="fa fa-headphones me-1" style="color:var(--accent);" aria-hidden="true"></i>Link</a>' : '<span class="text-muted">-</span>' ?></td>
              <td data-filter-val="<?= e($tingkatRaw) ?>"><span class="<?= e($badgeClass) ?>"><?= e(ucfirst($tingkatRaw)) ?></span></td>
              <td>
                <button type="button" class="btn btn-sm btn-edit" data-bs-toggle="modal" data-bs-target="#modalEditLagu<?= e($laguId) ?>" aria-label="Edit lagu <?= e($judul) ?>"><i class="fa fa-edit" aria-hidden="true"></i></button>
                <form method="post" action="lagu.php" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= e($laguId) ?>">
                  <button class="btn btn-sm btn-delete" type="button" data-bs-toggle="modal" data-bs-target="#modalDeleteConfirm" data-delete-title="Hapus Lagu" data-delete-message="Lagu <?= e($judul) ?> akan dihapus. Tindakan ini tidak bisa dibatalkan." data-delete-confirm="Hapus Lagu" aria-label="Hapus lagu <?= e($judul) ?>"><i class="fa fa-trash" aria-hidden="true"></i></button>
                </form>
              </td>
            </tr>
            <?php ob_start(); ?>
            <div class="modal fade" id="modalEditLagu<?= e($laguId) ?>" tabindex="-1" aria-labelledby="modalEditLaguLabel<?= e($laguId) ?>" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="post" action="lagu.php" data-ajax-form data-refresh-region="#laguListRegion">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id_lagu" value="<?= e($laguId) ?>">
                    <div class="modal-header">
                      <h5 class="modal-title" id="modalEditLaguLabel<?= e($laguId) ?>"><i class="fa fa-edit me-2" style="color:var(--accent);" aria-hidden="true"></i>Edit Lagu</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-3">
                        <label class="form-label fw-semibold" for="editLaguJudul<?= e($laguId) ?>">Judul Lagu <span class="text-danger">*</span></label>
                        <input id="editLaguJudul<?= e($laguId) ?>" name="judul" class="form-control" required value="<?= e($judul) ?>" autocomplete="off">
                      </div>
                      <div class="mb-3">
                        <label class="form-label fw-semibold" for="editLaguYoutube<?= e($laguId) ?>">Link YouTube (opsional)</label>
                        <input id="editLaguYoutube<?= e($laguId) ?>" name="youtube_link" class="form-control" placeholder="https://youtube.com/..." value="<?= e($ytLink) ?>">
                      </div>
                      <div class="mb-3">
                        <label class="form-label fw-semibold" for="editLaguIringan<?= e($laguId) ?>">Link Iringan (opsional)</label>
                        <input id="editLaguIringan<?= e($laguId) ?>" name="iringan_link" class="form-control" placeholder="https://drive.google.com/..." value="<?= e($iringanLink) ?>">
                      </div>
                      <div class="mb-3">
                        <label class="form-label fw-semibold" for="editLaguTingkat<?= e($laguId) ?>">Tingkat Kesulitan</label>
                        <select id="editLaguTingkat<?= e($laguId) ?>" name="tingkat" class="form-select">
                          <?php foreach (['mudah', 'sedang', 'sulit', 'festival'] as $lvl): ?>
                            <option value="<?= e($lvl) ?>" <?= ($tingkatRaw === $lvl ? 'selected' : '') ?>><?= e(ucfirst($lvl)) ?></option>
                          <?php endforeach; ?>
                        </select>
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
              <td colspan="6" class="text-muted text-center"><?= ($search ?? '') !== '' ? 'Tidak ada lagu yang cocok dengan pencarian.' : 'Belum ada data lagu.' ?></td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pagination-container" data-pagination-table="#tableLagu">
      <div class="d-flex align-items-center gap-3 flex-wrap">
        <small class="text-muted pagination-summary">Menampilkan 1-<?= e((string)min(10, $totalRows)) ?> dari <?= e((string)$totalRows) ?> data</small>
        <div class="limit-switch">
          <button type="button" class="limit-pill active" data-limit="10">10</button>
          <button type="button" class="limit-pill" data-limit="20">20</button>
          <button type="button" class="limit-pill" data-limit="100">100</button>
          <button type="button" class="limit-pill" data-limit="all">Semua</button>
        </div>
      </div>
      <nav aria-label="Pagination lagu" class="pagination-nav">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item page-prev disabled">
            <button type="button" class="page-link page-arrow" aria-label="Halaman sebelumnya" title="Halaman sebelumnya"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
          </li>
          <li class="page-item disabled"><span class="page-link page-status-pill">Hal. 1 / <?= e((string)max(1, (int)ceil($totalRows / 10))) ?></span></li>
          <li class="page-item page-next <?= $totalRows <= 10 ? 'disabled' : '' ?>">
            <button type="button" class="page-link page-arrow" aria-label="Halaman berikutnya" title="Halaman berikutnya"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
          </li>
        </ul>
      </nav>
    </div>
  </div>
  <?= $modalsHtml ?>
  </div>
</div>

<div class="modal fade" id="modalTambahLagu" tabindex="-1" aria-labelledby="modalTambahLaguLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="lagu.php" data-ajax-form data-refresh-region="#laguListRegion">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTambahLaguLabel"><i class="fa fa-music me-2" style="color:var(--success);" aria-hidden="true"></i>Tambah Lagu</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold" for="addLaguJudul">Judul Lagu <span class="text-danger">*</span></label>
            <input id="addLaguJudul" name="judul" class="form-control" placeholder="Contoh: Sang Dewi" required autocomplete="off">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" for="addLaguYoutube">Link YouTube (opsional)</label>
            <input id="addLaguYoutube" name="youtube_link" class="form-control" placeholder="https://youtube.com/...">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" for="addLaguIringan">Link Iringan (opsional)</label>
            <input id="addLaguIringan" name="iringan_link" class="form-control" placeholder="https://drive.google.com/...">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" for="addLaguTingkat">Tingkat Kesulitan</label>
            <select id="addLaguTingkat" name="tingkat" class="form-select">
              <option value="mudah">Mudah</option>
              <option value="sedang">Sedang</option>
              <option value="sulit">Sulit</option>
              <option value="festival">Festival</option>
            </select>
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
