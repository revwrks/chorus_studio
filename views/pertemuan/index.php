<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3 form-inline flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
      <h1 class="page-title mb-0"><i class="fa fa-calendar-check me-2" style="color:var(--primary);" aria-hidden="true"></i>Pertemuan</h1>
      <span class="filter-counter-badge" id="counterPertemuan">Total: <?= count($rows) ?> pertemuan</span>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
      <div class="position-relative">
        <input type="search" id="searchPertemuanLive" data-live-search="#tablePertemuan" data-counter-target="#counterPertemuan" class="form-control form-control-sm" style="max-width:240px;padding-left:32px;border-radius:8px;" placeholder="Cari tanggal / murid / coach…" aria-label="Cari pertemuan...">
        <i class="fa fa-search position-absolute top-50 translate-middle-y text-muted" style="left:12px;font-size:0.82rem;" aria-hidden="true"></i>
      </div>
      <div class="dropdown filter-dropdown-container" data-table-target="#tablePertemuan">
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
            <label for="filterPeriodePertemuan" class="filter-label">Periode Bulan</label>
            <select id="filterPeriodePertemuan" data-table-target="#tablePertemuan" data-live-filter-col="0" class="form-select form-select-sm">
              <option value="">Semua Periode</option>
              <?php foreach ($periodeList as $ymOpt => $label): ?>
                <option value="<?= e((string)$ymOpt) ?>"><?= e((string)$label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="filterMuridPertemuan" class="filter-label">Nama Murid</label>
            <select id="filterMuridPertemuan" data-table-target="#tablePertemuan" data-live-filter-col="1" class="form-select form-select-sm">
              <option value="">Semua Murid</option>
              <?php foreach ($allMurid as $m): ?>
                <option value="<?= e($m['nama'] ?? '') ?>"><?= e($m['nama'] ?? '') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="filterLaguPertemuan" class="filter-label">Status Materi Lagu</label>
            <select id="filterLaguPertemuan" data-table-target="#tablePertemuan" data-live-filter-col="2" class="form-select form-select-sm">
              <option value="">Semua Lagu</option>
              <option value=":has-value">Ada Lagu</option>
              <option value=":empty">Tanpa Lagu</option>
            </select>
          </div>
          <div>
            <label for="filterCoachPertemuan" class="filter-label">Coach</label>
            <select id="filterCoachPertemuan" data-table-target="#tablePertemuan" data-live-filter-col="3" class="form-select form-select-sm">
              <option value="">Semua Coach</option>
              <?php foreach ($coachList as $c): ?>
                <option value="<?= e($c['nama'] ?? '') ?>"><?= e($c['nama'] ?? '') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
      <button type="button" class="btn btn-primary btn-add btn-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalAddPertemuan" style="border-radius:8px;font-weight:600;"><i class="fa fa-plus-circle me-1" aria-hidden="true"></i> Tambah Pertemuan</button>
    </div>
  </div>
  <?php
  $searchVal = $search ?? '';
  $totalRowsCount = $totalRows ?? count($rows);
  ?>
  <div id="pertemuanListRegion" data-async-region>
  <div class="card bento-hero-card p-3 p-md-4 mb-4">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="tablePertemuan">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Murid</th>
            <th>Lagu</th>
            <th>Coach</th>
            <th>Catatan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r):
            $pId = (int)($r['id'] ?? 0);
            $pTanggal = (string)($r['tanggal'] ?? '');
            $pMuridNama = (string)($r['murid_nama'] ?? '');
            $pCoachNama = (string)($r['coach_nama'] ?? '');
            $meetingPayload = htmlspecialchars(json_encode([
              'id' => (string)$pId,
              'id_murid' => (string)($r['id_murid'] ?? ''),
              'tanggal' => $pTanggal,
              'id_lagu' => isset($r['id_lagu']) ? (string)$r['id_lagu'] : '',
              'catatan' => (string)($r['catatan'] ?? ''),
              'murid_nama' => $pMuridNama,
            ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE), ENT_QUOTES, 'UTF-8');
            $hasLagu = (!empty($r['id_lagu']) || (!empty($r['lagu_judul']) && trim((string)$r['lagu_judul']) !== '-'));
            $ym = $pTanggal !== '' ? e(substr($pTanggal, 0, 7)) : '';
          ?>
            <tr>
              <td data-filter-val="<?= $ym ?>"><i class="fa fa-calendar me-1" style="color:var(--accent);font-size:0.8rem;" aria-hidden="true"></i><?= e($pTanggal) ?></td>
              <td data-filter-val="<?= htmlspecialchars($r['murid_nama'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><strong><?= e($pMuridNama) ?></strong></td>
              <td data-has-val="<?= $hasLagu ? '1' : '0' ?>"><i class="fa fa-music me-1" style="color:var(--primary);font-size:0.8rem;" aria-hidden="true"></i><?= e($r['lagu_judul'] ?? '-') ?></td>
              <td data-filter-val="<?= htmlspecialchars($r['coach_nama'] ?: '-', ENT_QUOTES, 'UTF-8') ?>"><i class="fa fa-user-circle me-1 text-muted" style="font-size:0.8rem;" aria-hidden="true"></i><?= e($pCoachNama !== '' ? $pCoachNama : '-') ?></td>
              <td><span class="text-muted"><?= e($r['catatan'] ?? '') ?></span></td>
              <td>
                <button
                  class="btn btn-sm btn-edit"
                  type="button"
                  data-bs-toggle="modal"
                  data-bs-target="#modalEditPertemuan"
                  data-pertemuan="<?= $meetingPayload ?>"
                  aria-label="Edit pertemuan <?= e($pMuridNama !== '' ? $pMuridNama : 'tanpa nama') ?> tanggal <?= e($pTanggal) ?>">
                  <i class="fa fa-edit" aria-hidden="true"></i>
                </button>
                <form method="post" action="pertemuan.php" class="d-inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $pId ?>">
                  <button class="btn btn-sm btn-delete" type="button" data-bs-toggle="modal" data-bs-target="#modalDeleteConfirm" data-delete-title="Hapus Pertemuan" data-delete-message="Pertemuan <?= e(($pMuridNama !== '' ? $pMuridNama : 'tanpa nama') . ' pada ' . $pTanggal) ?> akan dihapus. Tindakan ini tidak bisa dibatalkan." data-delete-confirm="Hapus Pertemuan" aria-label="Hapus pertemuan <?= e($pMuridNama !== '' ? $pMuridNama : 'tanpa nama') ?> tanggal <?= e($pTanggal) ?>"><i class="fa fa-trash" aria-hidden="true"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$rows): ?>
            <tr>
              <td colspan="6" class="text-muted text-center"><?= $searchVal !== '' ? 'Tidak ada pertemuan yang cocok dengan pencarian.' : 'Belum ada data pertemuan.' ?></td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pagination-container" data-pagination-table="#tablePertemuan">
      <div class="d-flex align-items-center gap-3 flex-wrap">
        <small class="text-muted pagination-summary">Menampilkan 1-<?= min(10, $totalRowsCount) ?> dari <?= $totalRowsCount ?> data</small>
        <div class="limit-switch">
          <button type="button" class="limit-pill active" data-limit="10">10</button>
          <button type="button" class="limit-pill" data-limit="20">20</button>
          <button type="button" class="limit-pill" data-limit="100">100</button>
          <button type="button" class="limit-pill" data-limit="all">Semua</button>
        </div>
      </div>
      <nav aria-label="Pagination pertemuan" class="pagination-nav">
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
  </div>
</div>

<div class="modal fade" id="modalEditPertemuan" tabindex="-1" aria-labelledby="modalEditPertemuanTitle" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="pertemuan.php" data-ajax-form data-refresh-region="#pertemuanListRegion">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" id="editPertemuanId">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fa fa-edit me-2" style="color:var(--accent);" aria-hidden="true"></i><span id="modalEditPertemuanTitle">Edit Pertemuan</span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label for="editPertemuanMurid">Murid</label>
            <select name="id_murid" id="editPertemuanMurid" class="form-select" required>
              <?php foreach ($allMurid as $m): ?>
                <option value="<?= (int)$m['id'] ?>"><?= e($m['nama'] ?? '') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2">
            <label for="editPertemuanTanggal">Tanggal</label>
            <input name="tanggal" id="editPertemuanTanggal" type="date" class="form-control" required>
          </div>
          <div class="mb-2">
            <label for="editPertemuanLagu">Lagu</label>
            <select name="id_lagu" id="editPertemuanLagu" class="form-select">
              <option value="">Pilih lagu (opsional)</option>
              <?php foreach ($allLagu as $l): ?>
                <option value="<?= (int)$l['id_lagu'] ?>"><?= e($l['judul'] ?? '') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2">
            <label for="editPertemuanCatatan">Catatan</label>
            <textarea name="catatan" id="editPertemuanCatatan" class="form-control"></textarea>
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

<div class="modal fade" id="modalAddPertemuan" tabindex="-1" aria-labelledby="modalAddPertemuanTitle" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="pertemuan.php" data-ajax-form data-refresh-region="#pertemuanListRegion">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <div class="modal-header">
          <h5 class="modal-title" id="modalAddPertemuanTitle"><i class="fa fa-calendar-plus me-2" style="color:var(--success);" aria-hidden="true"></i>Tambah Pertemuan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label for="addPertemuanMurid">Murid</label>
            <select id="addPertemuanMurid" name="id_murid" class="form-select" required>
              <?php foreach ($allMurid as $m): ?>
                <option value="<?= (int)$m['id'] ?>"><?= e($m['nama'] ?? '') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2">
            <label for="addPertemuanTanggal">Tanggal</label>
            <input id="addPertemuanTanggal" name="tanggal" type="date" class="form-control" value="<?= e(date('Y-m-d')) ?>" required>
          </div>
          <div class="mb-2">
            <label for="addPertemuanLagu">Lagu</label>
            <select id="addPertemuanLagu" name="id_lagu" class="form-select">
              <option value="">Pilih lagu (opsional)</option>
              <?php foreach ($allLagu as $l): ?>
                <option value="<?= (int)$l['id_lagu'] ?>"><?= e($l['judul'] ?? '') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2">
            <label for="addPertemuanCatatan">Catatan</label>
            <textarea id="addPertemuanCatatan" name="catatan" class="form-control"></textarea>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
  const editModal = document.getElementById('modalEditPertemuan');
  if (!editModal) return;

  editModal.addEventListener('show.bs.modal', function(event) {
    const trigger = event.relatedTarget;
    if (!trigger) return;

    const payload = trigger.getAttribute('data-pertemuan');
    if (!payload) return;

    let data;
    try {
      data = JSON.parse(payload);
    } catch (error) {
      return;
    }

    document.getElementById('editPertemuanId').value = data.id || '';
    document.getElementById('editPertemuanMurid').value = data.id_murid || '';
    document.getElementById('editPertemuanTanggal').value = data.tanggal || '';
    document.getElementById('editPertemuanLagu').value = data.id_lagu || '';
    document.getElementById('editPertemuanCatatan').value = data.catatan || '';

    const title = document.getElementById('modalEditPertemuanTitle');
    title.textContent = data.murid_nama ? 'Edit Pertemuan: ' + data.murid_nama : 'Edit Pertemuan';
  });
});
</script>
