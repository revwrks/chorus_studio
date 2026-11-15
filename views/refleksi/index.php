<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3 form-inline flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
      <h3 class="mb-0"><i class="fa fa-lightbulb me-2" style="color:var(--gold);"></i>Refleksi</h3>
      <span class="filter-counter-badge" id="counterRefleksi">Total: <?= count($rows) ?> refleksi</span>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
      <div class="position-relative">
        <input id="searchRefleksiLive" data-live-search="#tableRefleksi" data-counter-target="#counterRefleksi" class="form-control form-control-sm" style="max-width:240px;padding-left:32px;border-radius:8px;" placeholder="Cari murid / refleksi / saran…">
        <i class="fa fa-search position-absolute top-50 translate-middle-y text-muted" style="left:12px;font-size:0.82rem;"></i>
      </div>
      <div class="dropdown filter-dropdown-container" data-table-target="#tableRefleksi">
        <button class="btn btn-sm btn-filter-trigger dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="border-radius:8px;">
          <i class="fa fa-sliders-h me-1" style="color:var(--accent);"></i> Filter
          <span class="filter-count-badge ms-1" style="display:none;">0</span>
        </button>
        <div class="dropdown-menu filter-dropdown-menu shadow-lg">
          <div class="filter-header">
            <span class="filter-title"><i class="fa fa-sliders-h me-1 text-accent"></i> Filter Data</span>
            <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 btn-reset-filters"><i class="fa fa-undo me-1"></i>Reset</button>
          </div>
          <div>
            <label class="filter-label">Tahun Periode</label>
            <select data-table-target="#tableRefleksi" data-live-filter-col="1" class="form-select form-select-sm">
              <option value="">Semua Periode</option>
              <?php foreach ($availableYears as $yr): ?>
                <option value="<?= $yr ?>"><?= $yr ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
      <button class="btn btn-primary btn-add btn-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalAddRefleksi" style="border-radius:8px;font-weight:600;"><i class="fa fa-plus-circle me-1"></i> Buat Refleksi</button>
    </div>
  </div>
  <div id="refleksiListRegion" data-async-region>
  <div class="card bento-hero-card p-3 p-md-4 mb-4">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="tableRefleksi">
        <thead>
          <tr>
            <th>Murid</th>
            <th>Bulan</th>
            <th>Refleksi</th>
            <th>Saran</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $modalsHtml = '';
          foreach ($rows as $r): ?>
            <tr>
              <td><strong><?= htmlspecialchars($r['murid_nama']) ?></strong></td>
              <td><span class="badge" style="background:rgba(74,107,93,0.1);color:var(--primary);font-weight:600;padding:5px 12px;border-radius:20px;"><?= $r['tahun'] . '-' . str_pad($r['bulan'], 2, '0', STR_PAD_LEFT) ?></span></td>
              <td><div class="clamp-2" title="<?= htmlspecialchars($r['refleksi']) ?>"><?= htmlspecialchars($r['refleksi']) ?></div></td>
              <td><div class="clamp-2" title="<?= htmlspecialchars($r['saran']) ?>"><?= htmlspecialchars($r['saran']) ?></div></td>
              <td>
                <button class="btn btn-sm btn-edit" data-bs-toggle="modal" data-bs-target="#modalEditRef<?= $r['id'] ?>"><i class="fa fa-edit"></i></button>
                <form method="post" action="refleksi.php" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $r['id'] ?>">
                  <button class="btn btn-sm btn-delete" type="button" data-bs-toggle="modal" data-bs-target="#modalDeleteConfirm" data-delete-title="Hapus Refleksi" data-delete-message="Refleksi <?= htmlspecialchars(($r['murid_nama'] ?? '') . ' ' . ($r['tahun'] ?? '') . '-' . str_pad((string)($r['bulan'] ?? ''), 2, '0', STR_PAD_LEFT), ENT_QUOTES) ?> akan dihapus. Tindakan ini tidak bisa dibatalkan." data-delete-confirm="Hapus Refleksi"><i class="fa fa-trash"></i></button>
                </form>
              </td>
            </tr>
            <?php ob_start(); ?>
            <div class="modal fade" id="modalEditRef<?= $r['id'] ?>" tabindex="-1">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <form method="post" action="refleksi.php" data-ajax-form data-refresh-region="#refleksiListRegion">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <div class="modal-header">
                      <h5 class="modal-title"><i class="fa fa-lightbulb me-2" style="color:var(--gold);"></i>Edit Refleksi</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="row">
                        <div class="col-md-6 mb-2"><label>Murid</label><select name="id_murid" class="form-select"><?php foreach ($allMurid as $m): ?><option value="<?= $m['id'] ?>" <?= ($m['id'] == $r['id_murid'] ? 'selected' : '') ?>><?= htmlspecialchars($m['nama']) ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-3 mb-2"><label>Tahun</label><input name="tahun" class="form-control" value="<?= $r['tahun'] ?>"></div>
                        <div class="col-md-3 mb-2"><label>Bulan</label><input name="bulan" class="form-control" value="<?= $r['bulan'] ?>"></div>
                      </div>
                      <div class="mb-2"><label>Refleksi</label><textarea name="refleksi" class="form-control" rows="4"><?= htmlspecialchars($r['refleksi']) ?></textarea></div>
                      <div class="mb-2"><label>Rencana Perbaikan</label><textarea name="rencana" class="form-control" rows="3"><?= htmlspecialchars($r['rencana_perbaikan']) ?></textarea></div>
                      <div class="mb-2"><label>Saran</label><textarea name="saran" class="form-control" rows="2"><?= htmlspecialchars($r['saran']) ?></textarea></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-success">Simpan</button></div>
                  </form>
                </div>
              </div>
            </div>
            <?php $modalsHtml .= ob_get_clean(); endforeach; ?>
          <?php if (!$rows): ?>
            <tr>
              <td colspan="5" class="text-muted text-center">Belum ada data refleksi.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="pagination-container d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3" data-pagination-table="#tableRefleksi">
      <div class="d-flex align-items-center gap-3 flex-wrap">
        <small class="text-muted pagination-summary">Menampilkan 1-<?= min(10, $totalRows) ?> dari <?= $totalRows ?> data</small>
        <div class="limit-switch">
          <button type="button" class="limit-pill active" data-limit="10">10</button>
          <button type="button" class="limit-pill" data-limit="20">20</button>
          <button type="button" class="limit-pill" data-limit="100">100</button>
          <button type="button" class="limit-pill" data-limit="all">Semua</button>
        </div>
      </div>
      <nav aria-label="Pagination refleksi" class="pagination-nav">
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

<div class="modal fade" id="modalAddRefleksi" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" action="refleksi.php" data-ajax-form data-refresh-region="#refleksiListRegion">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-lightbulb me-2" style="color:var(--success);"></i>Buat Refleksi</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-2"><label>Murid</label><select name="id_murid" class="form-select"><?php foreach ($allMurid as $m): ?><option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nama']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3 mb-2"><label>Tahun</label><input name="tahun" class="form-control" value="<?= date('Y') ?>"></div>
            <div class="col-md-3 mb-2"><label>Bulan</label><input name="bulan" class="form-control" value="<?= date('n') ?>"></div>
          </div>
          <div class="mb-2"><label>Refleksi</label><textarea name="refleksi" class="form-control" rows="4"></textarea></div>
          <div class="mb-2"><label>Rencana Perbaikan</label><textarea name="rencana" class="form-control" rows="3"></textarea></div>
          <div class="mb-2"><label>Saran</label><textarea name="saran" class="form-control" rows="2"></textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-success">Simpan</button></div>
      </form>
    </div>
  </div>
</div>
