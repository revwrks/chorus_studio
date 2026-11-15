<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3 form-inline flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
      <h3 class="mb-0 fw-bold" style="font-family:'Outfit',sans-serif;letter-spacing:-0.02em;"><i class="fa fa-music me-2" style="color:var(--primary);"></i>Data Lagu</h3>
      <span class="filter-counter-badge" id="counterLagu">Total: <?= count($rows) ?> lagu</span>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
      <div class="position-relative">
        <input id="searchLaguLive" data-live-search="#tableLagu" data-counter-target="#counterLagu" class="form-control form-control-sm" style="max-width:200px;padding-left:32px;border-radius:8px;" placeholder="Cari judul lagu…">
        <i class="fa fa-search position-absolute top-50 translate-middle-y text-muted" style="left:12px;font-size:0.82rem;"></i>
      </div>
      <div class="dropdown filter-dropdown-container" data-table-target="#tableLagu">
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
            <label class="filter-label">Tingkat Kesulitan</label>
            <select data-table-target="#tableLagu" data-live-filter-col="4" class="form-select form-select-sm">
              <option value="">Semua Tingkat</option>
              <option value="mudah">Mudah</option>
              <option value="sedang">Sedang</option>
              <option value="sulit">Sulit</option>
              <option value="festival">Festival</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="filter-label">Link YouTube</label>
            <select data-table-target="#tableLagu" data-live-filter-col="2" class="form-select form-select-sm">
              <option value="">Semua YouTube</option>
              <option value=":has-value">Ada Link YT</option>
              <option value=":empty">Tanpa Link YT</option>
            </select>
          </div>
          <div>
            <label class="filter-label">Musik Iringan</label>
            <select data-table-target="#tableLagu" data-live-filter-col="3" class="form-select form-select-sm">
              <option value="">Semua Iringan</option>
              <option value=":has-value">Ada Iringan</option>
              <option value=":empty">Tanpa Iringan</option>
            </select>
          </div>
        </div>
      </div>
      <button class="btn btn-primary btn-add btn-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalTambahLagu" style="border-radius:8px;font-weight:600;"><i class="fa fa-plus-circle me-1"></i> Tambah Lagu</button>
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
          $no = $offset + 1;
          $modalsHtml = '';
          foreach ($rows as $r):
            $tingkatColors = [
              'mudah' => 'background:rgba(74,107,93,0.12);color:#4a6b5d;',
              'sedang' => 'background:rgba(168,144,98,0.15);color:#8a7548;',
              'sulit' => 'background:rgba(139,63,63,0.12);color:#8b3f3f;',
              'festival' => 'background:linear-gradient(135deg,rgba(74,107,93,0.12),rgba(168,144,98,0.12));color:var(--primary);',
            ];
            $tc = $tingkatColors[$r['tingkat']] ?? 'background:var(--bg-soft);color:var(--text);';
          ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><strong><i class="fa fa-compact-disc me-1" style="color:var(--primary-light);"></i><?= htmlspecialchars($r['judul']) ?></strong></td>
              <td data-has-val="<?= !empty($r['youtube_link']) ? '1' : '0' ?>"><?= $r['youtube_link'] ? '<a target="_blank" rel="noopener" href="' . htmlspecialchars($r['youtube_link']) . '"><i class="fab fa-youtube me-1" style="color:#FF0000;"></i>Link</a>' : '<span class="text-muted">-</span>' ?></td>
              <td data-has-val="<?= !empty($r['iringan_link']) ? '1' : '0' ?>"><?= $r['iringan_link'] ? '<a target="_blank" rel="noopener" href="' . htmlspecialchars($r['iringan_link']) . '"><i class="fa fa-headphones me-1" style="color:var(--accent);"></i>Link</a>' : '<span class="text-muted">-</span>' ?></td>
              <td data-filter-val="<?= strtolower($r['tingkat']) ?>"><span class="badge" style="<?= $tc ?>font-weight:600;padding:5px 12px;border-radius:20px;"><?= htmlspecialchars(ucfirst($r['tingkat'])) ?></span></td>
              <td>
                <button class="btn btn-sm btn-edit" data-bs-toggle="modal" data-bs-target="#modalEditLagu<?= $r['id_lagu'] ?>"><i class="fa fa-edit"></i></button>
                <form method="post" action="lagu.php" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $r['id_lagu'] ?>">
                  <button class="btn btn-sm btn-delete" type="button" data-bs-toggle="modal" data-bs-target="#modalDeleteConfirm" data-delete-title="Hapus Lagu" data-delete-message="Lagu <?= htmlspecialchars($r['judul'] ?? '', ENT_QUOTES) ?> akan dihapus. Tindakan ini tidak bisa dibatalkan." data-delete-confirm="Hapus Lagu"><i class="fa fa-trash"></i></button>
                </form>
              </td>
            </tr>
            <?php ob_start(); ?>
            <div class="modal fade" id="modalEditLagu<?= $r['id_lagu'] ?>" tabindex="-1">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="post" action="lagu.php" data-ajax-form data-refresh-region="#laguListRegion">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id_lagu" value="<?= $r['id_lagu'] ?>">
                    <div class="modal-header">
                      <h5 class="modal-title"><i class="fa fa-edit me-2" style="color:var(--gold);"></i>Edit Lagu</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-3"><label class="form-label fw-semibold">Judul Lagu <span class="text-danger">*</span></label><input name="judul" class="form-control" required value="<?= htmlspecialchars($r['judul']) ?>" autocomplete="off"></div>
                      <div class="mb-3"><label class="form-label fw-semibold">Link YouTube (opsional)</label><input name="youtube_link" class="form-control" placeholder="https://youtube.com/..." value="<?= htmlspecialchars($r['youtube_link'] ?? '') ?>"></div>
                      <div class="mb-3"><label class="form-label fw-semibold">Link Iringan (opsional)</label><input name="iringan_link" class="form-control" placeholder="https://drive.google.com/..." value="<?= htmlspecialchars($r['iringan_link'] ?? '') ?>"></div>
                      <div class="mb-3"><label class="form-label fw-semibold">Tingkat Kesulitan</label><select name="tingkat" class="form-select"><?php foreach (['mudah', 'sedang', 'sulit', 'festival'] as $lvl): ?><option value="<?= $lvl ?>" <?= ($r['tingkat'] == $lvl ? 'selected' : '') ?>><?= ucfirst($lvl) ?></option><?php endforeach; ?></select></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-success"><i class="fa fa-save me-1"></i> Simpan Perubahan</button></div>
                  </form>
                </div>
              </div>
            </div>
            <?php $modalsHtml .= ob_get_clean(); endforeach; ?>
          <?php if (!$rows): ?>
            <tr>
              <td colspan="6" class="text-muted text-center"><?= $search !== '' ? 'Tidak ada lagu yang cocok dengan pencarian.' : 'Belum ada data lagu.' ?></td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pagination-container" data-pagination-table="#tableLagu">
      <div class="d-flex align-items-center gap-3 flex-wrap">
        <small class="text-muted pagination-summary">Menampilkan 1-<?= min(10, $totalRows) ?> dari <?= $totalRows ?> data</small>
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

<div class="modal fade" id="modalTambahLagu" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="lagu.php" data-ajax-form data-refresh-region="#laguListRegion">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-music me-2" style="color:var(--success);"></i>Tambah Lagu</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label fw-semibold">Judul Lagu <span class="text-danger">*</span></label><input name="judul" class="form-control" placeholder="Contoh: Sang Dewi" required autocomplete="off"></div>
          <div class="mb-3"><label class="form-label fw-semibold">Link YouTube (opsional)</label><input name="youtube_link" class="form-control" placeholder="https://youtube.com/..."></div>
          <div class="mb-3"><label class="form-label fw-semibold">Link Iringan (opsional)</label><input name="iringan_link" class="form-control" placeholder="https://drive.google.com/..."></div>
          <div class="mb-3"><label class="form-label fw-semibold">Tingkat Kesulitan</label><select name="tingkat" class="form-select">
              <option value="mudah">Mudah</option>
              <option value="sedang">Sedang</option>
              <option value="sulit">Sulit</option>
              <option value="festival">Festival</option>
            </select></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-success"><i class="fa fa-save me-1"></i> Simpan</button></div>
      </form>
    </div>
  </div>
</div>
