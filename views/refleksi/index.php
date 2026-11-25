<?php
$namaBulanIndo = [
  1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
  5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
  9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
];
$totalRowsCount = $totalRows ?? count($rows);
$canDeleteRefleksi = function_exists('is_admin_or_owner') ? is_admin_or_owner() : false;
?>
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3 form-inline flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
      <h3 class="page-title mb-0"><i class="fa fa-lightbulb me-2" style="color:var(--gold);"></i>Refleksi</h3>
      <span class="filter-counter-badge" id="counterRefleksi">Total: <?= count($rows) ?> refleksi</span>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
      <div class="position-relative">
        <input type="search" id="searchRefleksiLive" data-live-search="#tableRefleksi" data-counter-target="#counterRefleksi" class="form-control form-control-sm" style="max-width:240px;padding-left:32px;border-radius:8px;" placeholder="Cari murid / refleksi / saran…" aria-label="Cari refleksi...">
        <i class="fa fa-search position-absolute top-50 translate-middle-y text-muted" style="left:12px;font-size:0.82rem;" aria-hidden="true"></i>
      </div>
      <div class="dropdown filter-dropdown-container" data-table-target="#tableRefleksi">
        <button class="btn btn-sm btn-filter-trigger dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="border-radius:8px;">
          <i class="fa fa-sliders-h me-1" style="color:var(--accent);" aria-hidden="true"></i> Filter
          <span class="filter-count-badge ms-1" style="display:none;">0</span>
        </button>
        <div class="dropdown-menu filter-dropdown-menu shadow-lg">
          <div class="filter-header">
            <span class="filter-title"><i class="fa fa-sliders-h me-1 text-accent" aria-hidden="true"></i> Filter Data</span>
            <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 btn-reset-filters"><i class="fa fa-undo me-1" aria-hidden="true"></i>Reset</button>
          </div>
          <div>
            <label for="filterTahunRefleksi" class="filter-label">Tahun Periode</label>
            <select id="filterTahunRefleksi" data-table-target="#tableRefleksi" data-live-filter-col="1" class="form-select form-select-sm">
              <option value="">Semua Periode</option>
              <?php foreach ($availableYears as $yr): ?>
                <option value="<?= (int)$yr ?>"><?= (int)$yr ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
      <button type="button" class="btn btn-primary btn-add btn-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalAddRefleksi" style="border-radius:8px;font-weight:600;"><i class="fa fa-plus-circle me-1" aria-hidden="true"></i> Buat Refleksi</button>
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
          foreach ($rows as $r):
            $rId = (int)($r['id'] ?? 0);
            $rMuridId = (int)($r['id_murid'] ?? 0);
            $rMuridNama = (string)($r['murid_nama'] ?? '');
            $rTahun = (int)($r['tahun'] ?? date('Y'));
            $rBulan = (int)($r['bulan'] ?? date('n'));
            $rPeriode = $rTahun . '-' . str_pad((string)$rBulan, 2, '0', STR_PAD_LEFT);
            $rRefleksi = (string)($r['refleksi'] ?? '');
            $rRencana = (string)($r['rencana_perbaikan'] ?? '');
            $rSaran = (string)($r['saran'] ?? '');
          ?>
            <tr>
              <td><strong><?= e($rMuridNama) ?></strong></td>
              <td><span class="badge rounded-pill" style="background-color:var(--primary-soft);color:var(--primary);padding:5px 12px;"><?= e($rPeriode) ?></span></td>
              <td><div class="clamp-2" title="<?= e($rRefleksi) ?>"><?= e($rRefleksi) ?></div></td>
              <td><div class="clamp-2" title="<?= e($rSaran) ?>"><?= e($rSaran) ?></div></td>
              <td>
                <button type="button" class="btn btn-sm btn-edit" data-bs-toggle="modal" data-bs-target="#modalEditRef<?= $rId ?>" aria-label="Edit refleksi <?= e($rMuridNama) ?> periode <?= e($rPeriode) ?>"><i class="fa fa-edit" aria-hidden="true"></i></button>
                <?php if ($canDeleteRefleksi): ?>
                  <form method="post" action="refleksi.php" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $rId ?>">
                    <button class="btn btn-sm btn-delete" type="button" data-bs-toggle="modal" data-bs-target="#modalDeleteConfirm" data-delete-title="Hapus Refleksi" data-delete-message="Refleksi <?= e($rMuridNama . ' ' . $rPeriode) ?> akan dihapus. Tindakan ini tidak bisa dibatalkan." data-delete-confirm="Hapus Refleksi" aria-label="Hapus refleksi <?= e($rMuridNama) ?> periode <?= e($rPeriode) ?>"><i class="fa fa-trash" aria-hidden="true"></i></button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
            <?php ob_start(); ?>
            <div class="modal fade" id="modalEditRef<?= $rId ?>" tabindex="-1" aria-labelledby="modalEditRefLabel<?= $rId ?>" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <form method="post" action="refleksi.php" data-ajax-form data-refresh-region="#refleksiListRegion">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= $rId ?>">
                    <div class="modal-header">
                      <h5 class="modal-title" id="modalEditRefLabel<?= $rId ?>"><i class="fa fa-lightbulb me-2" style="color:var(--accent);" aria-hidden="true"></i>Edit Refleksi</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                      <div class="row">
                        <div class="col-md-6 mb-2">
                          <label for="editRefMurid_<?= $rId ?>">Murid</label>
                          <select id="editRefMurid_<?= $rId ?>" name="id_murid" class="form-select" required>
                            <?php foreach ($allMurid as $m): ?>
                              <option value="<?= (int)$m['id'] ?>" <?= ((int)$m['id'] === $rMuridId ? 'selected' : '') ?>><?= e($m['nama'] ?? '') ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="col-md-3 mb-2">
                          <label for="editRefTahun_<?= $rId ?>">Tahun</label>
                          <input id="editRefTahun_<?= $rId ?>" type="number" name="tahun" class="form-control" min="2020" max="2100" required value="<?= $rTahun ?>">
                        </div>
                        <div class="col-md-3 mb-2">
                          <label for="editRefBulan_<?= $rId ?>">Bulan</label>
                          <select id="editRefBulan_<?= $rId ?>" name="bulan" class="form-select" required>
                            <?php foreach ($namaBulanIndo as $num => $namaBulanItem): ?>
                              <option value="<?= $num ?>" <?= $num === $rBulan ? 'selected' : '' ?>><?= e($namaBulanItem) ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>
                      <div class="mb-2">
                        <label for="editRefText_<?= $rId ?>">Refleksi</label>
                        <textarea id="editRefText_<?= $rId ?>" name="refleksi" class="form-control" rows="4"><?= e($rRefleksi) ?></textarea>
                      </div>
                      <div class="mb-2">
                        <label for="editRefRencana_<?= $rId ?>">Rencana Perbaikan</label>
                        <textarea id="editRefRencana_<?= $rId ?>" name="rencana" class="form-control" rows="3"><?= e($rRencana) ?></textarea>
                      </div>
                      <div class="mb-2">
                        <label for="editRefSaran_<?= $rId ?>">Saran</label>
                        <textarea id="editRefSaran_<?= $rId ?>" name="saran" class="form-control" rows="2"><?= e($rSaran) ?></textarea>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                      <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
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
        <small class="text-muted pagination-summary">Menampilkan 1-<?= min(10, $totalRowsCount) ?> dari <?= $totalRowsCount ?> data</small>
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

<div class="modal fade" id="modalAddRefleksi" tabindex="-1" aria-labelledby="modalAddRefleksiLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="post" action="refleksi.php" data-ajax-form data-refresh-region="#refleksiListRegion">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <div class="modal-header">
          <h5 class="modal-title" id="modalAddRefleksiLabel"><i class="fa fa-lightbulb me-2" style="color:var(--success);" aria-hidden="true"></i>Buat Refleksi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-2">
              <label for="addRefMurid">Murid</label>
              <select id="addRefMurid" name="id_murid" class="form-select" required>
                <?php foreach ($allMurid as $m): ?>
                  <option value="<?= (int)$m['id'] ?>"><?= e($m['nama'] ?? '') ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3 mb-2">
              <label for="addRefTahun">Tahun</label>
              <input id="addRefTahun" type="number" name="tahun" class="form-control" min="2020" max="2100" required value="<?= (int)date('Y') ?>">
            </div>
            <div class="col-md-3 mb-2">
              <label for="addRefBulan">Bulan</label>
              <select id="addRefBulan" name="bulan" class="form-select" required>
                <?php $curBulan = (int)date('n'); ?>
                <?php foreach ($namaBulanIndo as $num => $namaBulanItem): ?>
                  <option value="<?= $num ?>" <?= $num === $curBulan ? 'selected' : '' ?>><?= e($namaBulanItem) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="mb-2">
            <label for="addRefText">Refleksi</label>
            <textarea id="addRefText" name="refleksi" class="form-control" rows="4"></textarea>
          </div>
          <div class="mb-2">
            <label for="addRefRencana">Rencana Perbaikan</label>
            <textarea id="addRefRencana" name="rencana" class="form-control" rows="3"></textarea>
          </div>
          <div class="mb-2">
            <label for="addRefSaran">Saran</label>
            <textarea id="addRefSaran" name="saran" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
