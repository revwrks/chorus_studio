<?php
$totalRealisasi = (int)($rencanaSummary['total_realisasi'] ?? 0);
$totalTarget = (int)($rencanaSummary['total_target'] ?? 0);
$targetDetailList = $rencanaSummary['detail_list'] ?? [];
$persenBulanIni = $totalTarget > 0 ? (int)round(($totalRealisasi / $totalTarget) * 100) : ($totalRealisasi > 0 ? 100 : 0);
$periodeLabel = ($namaBulan[$bulanDipilih] ?? (string)$bulanDipilih) . ' ' . $tahunDipilih;
?>
<div class="container-fluid">
  <!-- Studio Header -->
  <div class="page-header">
    <div>
      <h1 class="page-title">
        <i class="fa fa-chart-pie me-2" style="color:var(--primary);" aria-hidden="true"></i>Dashboard Studio
      </h1>
      <p class="text-muted small mb-0 mt-1">
        Pantau metrik latihan vokal, produktivitas coach, dan capaian target sesi studio.
      </p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="bento-badge">
        <i class="fa fa-calendar-day text-accent" aria-hidden="true"></i>
        <span><?= e($periodeLabel) ?></span>
      </span>
    </div>
  </div>

  <!-- 3 KPI Cards -->
  <div class="kpi-grid mb-4">
    <div class="metric-card">
      <div class="d-flex align-items-start justify-content-between gap-2">
        <div>
          <h2 class="h6 text-uppercase fw-bold text-secondary mb-2" style="font-size:0.75rem;letter-spacing:0.06em;">
            <i class="fa fa-users me-1" style="color:var(--primary);" aria-hidden="true"></i>Total Murid
          </h2>
          <div class="bento-metric-number" style="color:var(--primary);"><?= (int)$totalMuridCount ?></div>
        </div>
        <div class="bento-icon-box" style="background:var(--primary-soft);color:var(--primary);">
          <i class="fa fa-users" aria-hidden="true"></i>
        </div>
      </div>
      <small class="text-muted mt-auto pt-1">
        <strong style="color:var(--success);font-variant-numeric:tabular-nums;"><?= (int)$activeMuridCount ?></strong> murid aktif terdaftar
      </small>
    </div>

    <div class="metric-card">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h2 class="h6 mb-0 text-uppercase fw-bold text-secondary" style="font-size:0.75rem;letter-spacing:0.06em;">
          <i class="fa fa-calendar-check me-1 text-accent" aria-hidden="true"></i>Pertemuan / Target
        </h2>
        <form method="get" class="d-flex align-items-center gap-1 flex-wrap m-0">
          <select name="bulan" class="form-select form-select-sm" style="width:auto;min-width:76px;font-size:0.78rem;" aria-label="Pilih bulan target" onchange="this.form.submit()">
            <?php foreach ($staffAvailableMonthsForYear as $num): ?>
              <option value="<?= (int)$num ?>" <?= (int)$bulanDipilih === (int)$num ? 'selected' : '' ?>><?= e($namaBulan[$num] ?? (string)$num) ?></option>
            <?php endforeach; ?>
          </select>
          <select name="tahun" class="form-select form-select-sm" style="width:auto;min-width:74px;font-size:0.78rem;" aria-label="Pilih tahun target" onchange="this.form.submit()">
            <?php foreach ($staffAvailableYears as $t): ?>
              <option value="<?= (int)$t ?>" <?= (int)$tahunDipilih === (int)$t ? 'selected' : '' ?>><?= (int)$t ?></option>
            <?php endforeach; ?>
          </select>
        </form>
      </div>

      <div class="bento-metric-number mt-1">
        <button type="button" class="count-link" style="color:var(--accent);" data-bs-toggle="modal" data-bs-target="#modalPertemuanTargetDetail" aria-label="Lihat rincian pertemuan dan target <?= e($periodeLabel) ?>">
          <?= $totalRealisasi ?> / <?= $totalTarget ?>
        </button>
      </div>
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-auto pt-1">
        <small class="text-muted"><?= e($periodeLabel) ?> &middot; Klik untuk rincian</small>
        <span class="bento-badge" style="background:var(--accent-soft);color:var(--accent-ink);">
          <?= $persenBulanIni ?>% tercapai
        </span>
      </div>
    </div>

    <div class="metric-card">
      <div class="d-flex align-items-start justify-content-between gap-2">
        <div>
          <h2 class="h6 text-uppercase fw-bold text-secondary mb-2" style="font-size:0.75rem;letter-spacing:0.06em;">
            <i class="fa fa-chalkboard-user me-1 text-accent" aria-hidden="true"></i>Coach Terdaftar
          </h2>
          <div class="bento-metric-number" style="color:var(--accent-ink);"><?= (int)$coachCount ?></div>
        </div>
        <div class="bento-icon-box" style="background:var(--accent-soft);color:var(--accent-ink);">
          <i class="fa fa-chalkboard-user" aria-hidden="true"></i>
        </div>
      </div>
      <small class="text-muted mt-auto pt-1">
        <span class="d-inline-block rounded-circle me-1" style="width:7px;height:7px;background:var(--success);" aria-hidden="true"></span>Akun coach aktif mengajar
      </small>
    </div>
  </div>

  <!-- Murid Dilatih per Coach Table -->
  <div class="card p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <div>
        <h2 class="h5 mb-1 fw-bold">
          <i class="fa fa-chart-bar me-2" style="color:var(--primary);" aria-hidden="true"></i>Murid Dilatih per Coach
        </h2>
        <small class="text-muted">Distribusi jumlah murid dan sesi pertemuan setiap coach pada periode <?= e($periodeLabel) ?>.</small>
      </div>
      <form method="get" class="d-flex align-items-center gap-2 flex-wrap m-0">
        <select name="bulan" class="form-select form-select-sm" style="width:auto;min-width:86px;font-size:0.82rem;" aria-label="Pilih bulan coach" onchange="this.form.submit()">
          <?php foreach ($staffAvailableMonthsForYear as $num): ?>
            <option value="<?= (int)$num ?>" <?= (int)$bulanDipilih === (int)$num ? 'selected' : '' ?>><?= e($namaBulan[$num] ?? (string)$num) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="tahun" class="form-select form-select-sm" style="width:auto;min-width:80px;font-size:0.82rem;" aria-label="Pilih tahun coach" onchange="this.form.submit()">
          <?php foreach ($staffAvailableYears as $t): ?>
            <option value="<?= (int)$t ?>" <?= (int)$tahunDipilih === (int)$t ? 'selected' : '' ?>><?= (int)$t ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>

    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Coach</th>
            <th>Murid Dilatih</th>
            <th>Total Pertemuan</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($coachRows as $c): ?>
            <tr>
              <td>
                <i class="fa fa-user-circle me-2" style="color:var(--primary);" aria-hidden="true"></i>
                <span class="fw-medium"><?= e($c['nama']) ?></span>
              </td>
              <td style="font-variant-numeric:tabular-nums;">
                <?php if ((int)$c['total_murid'] > 0): ?>
                  <button type="button" class="count-link fw-semibold" style="color:var(--primary);" data-bs-toggle="modal" data-bs-target="#modalCoachMuridDetail<?= (int)$c['id'] ?>">
                    <?= (int)$c['total_murid'] ?>
                  </button>
                <?php else: ?>
                  <span class="text-muted">0</span>
                <?php endif; ?>
              </td>
              <td style="font-variant-numeric:tabular-nums;">
                <?php if ((int)$c['total_pertemuan'] > 0): ?>
                  <button type="button" class="count-link fw-semibold" style="color:var(--primary);" data-bs-toggle="modal" data-bs-target="#modalCoachDetail<?= (int)$c['id'] ?>">
                    <?= (int)$c['total_pertemuan'] ?>
                  </button>
                <?php else: ?>
                  <span class="text-muted">0</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($coachRows)): ?>
            <tr>
              <td colspan="3" class="empty-state">Belum ada data coach pada periode ini.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php
$modalsHtml = '';
foreach ($coachRows as $c):
  $coachId = (int)$c['id'];
  if ((int)$c['total_pertemuan'] > 0):
    $rows = $detailByCoach[$c['id']] ?? [];
    $modalTitleId = 'modalCoachDetailTitle' . $coachId;
    ob_start(); ?>
    <div class="modal fade" id="modalCoachDetail<?= $coachId ?>" tabindex="-1" data-paginated-modal aria-labelledby="<?= e($modalTitleId) ?>" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h2 class="modal-title h5 mb-0" id="<?= e($modalTitleId) ?>">Detail Pertemuan <?= e($c['nama']) ?> (<?= e($periodeLabel) ?>)</h2>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
          </div>
          <div class="modal-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover modal-paginated-table align-middle mb-0" data-rows-per-page="<?= (int)$detailRowsPerPage ?>">
                <thead>
                  <tr>
                    <th>Murid</th>
                    <th>Tanggal</th>
                    <th>Latihan</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($rows as $row): ?>
                    <tr>
                      <td><?= e($row['murid_nama'] ?? '') ?></td>
                      <td style="font-variant-numeric:tabular-nums;"><?= e($row['tanggal'] ?? '') ?></td>
                      <td><?= e(($row['lagu_judul'] ?? '') !== '' ? $row['lagu_judul'] : '-') ?></td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (!$rows): ?>
                    <tr class="modal-empty-row"><td colspan="3" class="text-muted text-center py-4">Belum ada pertemuan di periode ini.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3 d-none" data-pagination-controls>
              <button type="button" class="btn btn-sm pager-btn" data-page-action="prev" aria-label="Halaman sebelumnya" title="Halaman sebelumnya"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
              <small class="text-muted" data-page-status></small>
              <button type="button" class="btn btn-sm pager-btn" data-page-action="next" aria-label="Halaman berikutnya" title="Halaman berikutnya"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          </div>
        </div>
      </div>
    </div>
    <?php
    $modalsHtml .= ob_get_clean();
  endif;

  if ((int)$c['total_murid'] > 0):
    $cMuridList = $muridByCoach[$c['id']] ?? [];
    $muridModalTitleId = 'modalCoachMuridDetailTitle' . $coachId;
    ob_start(); ?>
    <div class="modal fade" id="modalCoachMuridDetail<?= $coachId ?>" tabindex="-1" data-paginated-modal aria-labelledby="<?= e($muridModalTitleId) ?>" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h2 class="modal-title h5 mb-0" id="<?= e($muridModalTitleId) ?>">Daftar Murid Dilatih oleh <?= e($c['nama']) ?> (<?= e($periodeLabel) ?>)</h2>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
          </div>
          <div class="modal-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover modal-paginated-table align-middle mb-0" data-rows-per-page="<?= (int)$detailRowsPerPage ?>">
                <thead>
                  <tr>
                    <th>Nama Murid</th>
                    <th>Total Pertemuan</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($cMuridList as $row): ?>
                    <tr>
                      <td><?= e($row['nama'] ?? '') ?></td>
                      <td>
                        <span class="bento-badge" style="font-variant-numeric:tabular-nums;">
                          <?= (int)$row['total_pertemuan'] ?>
                        </span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (!$cMuridList): ?>
                    <tr class="modal-empty-row"><td colspan="2" class="text-muted text-center py-4">Belum ada murid yang dilatih di periode ini.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3 d-none" data-pagination-controls>
              <button type="button" class="btn btn-sm pager-btn" data-page-action="prev" aria-label="Halaman sebelumnya" title="Halaman sebelumnya"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
              <small class="text-muted" data-page-status></small>
              <button type="button" class="btn btn-sm pager-btn" data-page-action="next" aria-label="Halaman berikutnya" title="Halaman berikutnya"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          </div>
        </div>
      </div>
    </div>
    <?php
    $modalsHtml .= ob_get_clean();
  endif;
endforeach;
echo $modalsHtml;
?>

<!-- Modal Detail Pertemuan / Target untuk Admin, Owner, dan Coach -->
<div class="modal fade" id="modalPertemuanTargetDetail" tabindex="-1" data-paginated-modal aria-labelledby="modalPertemuanTargetTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h2 class="modal-title h5 mb-0" id="modalPertemuanTargetTitle">
            <i class="fa fa-calendar-alt me-2" style="color:var(--primary);" aria-hidden="true"></i>Rincian Pertemuan / Target (<?= e($periodeLabel) ?>)
          </h2>
          <small class="text-muted">Daftar murid dengan rencana pertemuan atau realisasi latihan pada periode ini.</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center mb-3 p-2 px-3 rounded-3 flex-wrap gap-2" style="background:var(--surface-subtle);border:1px solid var(--border);">
          <div>
            <span class="small text-muted">Total Realisasi:</span>
            <strong class="ms-1" style="color:var(--accent);font-variant-numeric:tabular-nums;"><?= $totalRealisasi ?> sesi</strong>
          </div>
          <div>
            <span class="small text-muted">Total Target:</span>
            <strong class="ms-1" style="color:var(--primary);font-variant-numeric:tabular-nums;"><?= $totalTarget ?> sesi</strong>
          </div>
          <div>
            <span class="small text-muted">Murid Terdaftar:</span>
            <strong class="ms-1" style="font-variant-numeric:tabular-nums;"><?= count($targetDetailList) ?> murid</strong>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
          <div class="position-relative">
            <label for="searchTargetMurid" class="visually-hidden">Cari nama murid</label>
            <input id="searchTargetMurid" type="text" class="form-control form-control-sm modal-search-input" placeholder="Cari nama murid..." aria-label="Cari nama murid" style="max-width:240px;padding-left:32px;">
            <i class="fa fa-search position-absolute top-50 translate-middle-y text-muted" style="left:11px;font-size:0.8rem;" aria-hidden="true"></i>
          </div>
          <div class="limit-switch">
            <button type="button" class="limit-pill active" data-limit="10">10</button>
            <button type="button" class="limit-pill" data-limit="20">20</button>
            <button type="button" class="limit-pill" data-limit="all">Semua</button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-striped table-hover modal-paginated-table align-middle mb-0" id="tablePertemuanTargetDetail" data-rows-per-page="<?= (int)$detailRowsPerPage ?>">
            <thead>
              <tr>
                <th style="width:40px;">No</th>
                <th>Murid</th>
                <th style="width:110px;">Realisasi</th>
                <th style="width:110px;">Target</th>
                <th style="width:140px;">Capaian</th>
                <th>Coach Pembina</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($targetDetailList as $idx => $row): ?>
                <?php
                  $t = (int)($row['target_pertemuan'] ?? 0);
                  $r = (int)($row['realisasi'] ?? 0);
                  $persen = (int)($row['persentase'] ?? 0);
                  $isTercapai = !empty($row['is_tercapai']);
                  $muridNama = (string)($row['nama'] ?? '');
                ?>
                <tr>
                  <td class="text-muted small" style="font-variant-numeric:tabular-nums;"><?= (int)$idx + 1 ?></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <?php if (!empty($row['foto'])): ?>
                        <img src="<?= e($row['foto']) ?>" alt="Foto <?= e($muridNama) ?>" style="width:30px;height:30px;border-radius:8px;object-fit:cover;">
                      <?php else: ?>
                        <span class="avatar-empty-badge" style="width:30px;height:30px;border-radius:8px;"><i class="fa fa-user text-muted" style="font-size:0.75rem;" aria-hidden="true"></i></span>
                      <?php endif; ?>
                      <strong><?= e($muridNama) ?></strong>
                    </div>
                  </td>
                  <td>
                    <button type="button"
                            class="btn btn-sm btn-realisasi-detail d-inline-flex align-items-center gap-1 <?= $r > 0 ? 'has-meetings' : 'zero-meetings' ?>"
                            data-murid-id="<?= (int)$row['murid_id'] ?>"
                            data-murid-nama="<?= e($muridNama) ?>"
                            data-tahun="<?= (int)$tahunDipilih ?>"
                            data-bulan="<?= (int)$bulanDipilih ?>"
                            data-realisasi-count="<?= $r ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#modalDetailRealisasi"
                            aria-label="Lihat tanggal sesi pertemuan <?= e($muridNama) ?>"
                            title="Lihat tanggal sesi pertemuan <?= e($muridNama) ?>">
                      <i class="fa fa-calendar-check" style="color:var(--accent);" aria-hidden="true"></i>
                      <strong style="font-variant-numeric:tabular-nums;"><?= $r ?></strong> sesi
                      <?php if ($r > 0): ?>
                        <i class="fa fa-chevron-right ms-1 opacity-50" style="font-size:0.65rem;" aria-hidden="true"></i>
                      <?php endif; ?>
                    </button>
                  </td>
                  <td>
                    <?php if ($t > 0): ?>
                      <span class="bento-badge" style="background:var(--surface-subtle);color:var(--text);font-variant-numeric:tabular-nums;">
                        <i class="fa fa-bullseye" style="color:var(--primary);" aria-hidden="true"></i><?= $t ?> sesi
                      </span>
                    <?php else: ?>
                      <span class="text-muted small">-</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($t > 0): ?>
                      <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1" style="height:6px;min-width:60px;background:var(--surface-subtle);">
                          <div class="progress-bar" role="progressbar" style="width:<?= min(100, $persen) ?>%;background-color:<?= $isTercapai ? 'var(--success)' : 'var(--primary)' ?>;" aria-valuenow="<?= min(100, $persen) ?>" aria-valuemin="0" aria-valuemax="100" aria-label="Capaian <?= e($muridNama) ?> <?= $persen ?> persen"></div>
                        </div>
                        <span class="small fw-semibold" style="font-size:0.75rem;min-width:34px;font-variant-numeric:tabular-nums;color:<?= $isTercapai ? 'var(--success)' : 'var(--text-muted)' ?>;">
                          <?= $persen ?>%
                        </span>
                      </div>
                    <?php else: ?>
                      <span class="badge-status-nonaktif">Tanpa Target</span>
                    <?php endif; ?>
                  </td>
                  <td class="small text-muted"><?= e(($row['coach_nama'] ?? '') !== '' ? $row['coach_nama'] : '-') ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($targetDetailList)): ?>
                <tr class="modal-empty-row">
                  <td colspan="6" class="empty-state">
                    Belum ada rencana pertemuan atau sesi latihan untuk periode <?= e($periodeLabel) ?>.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 d-none pagination-container" data-pagination-controls data-pagination-table="#tablePertemuanTargetDetail">
          <small class="text-muted" data-page-summary></small>
          <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm pager-btn" data-page-action="prev" aria-label="Halaman sebelumnya" title="Halaman sebelumnya"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
            <small class="text-muted" data-page-status></small>
            <button type="button" class="btn btn-sm pager-btn" data-page-action="next" aria-label="Halaman berikutnya" title="Halaman berikutnya"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
          </div>
        </div>
      </div>
      <div class="modal-footer d-flex justify-content-between flex-wrap gap-2">
        <a href="rencana_pertemuan.php?tahun=<?= (int)$tahunDipilih ?>&bulan=<?= (int)$bulanDipilih ?>" class="btn btn-sm btn-secondary">
          <i class="fa fa-sliders-h me-1" aria-hidden="true"></i> Atur Rencana Pertemuan
        </a>
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('[data-paginated-modal]').forEach(function(modal) {
    const table = modal.querySelector('.modal-paginated-table');
    const pagination = modal.querySelector('[data-pagination-controls]');
    if (!table || !pagination) return;

    const tbody = table.tBodies[0] || table.querySelector('tbody');
    if (!tbody) return;

    const emptyRow = tbody.querySelector('.modal-empty-row');
    const allRows = Array.from(tbody.querySelectorAll('tr')).filter(function(row) {
      return row !== emptyRow && !row.classList.contains('modal-no-match-row');
    });

    const searchInput = modal.querySelector('.modal-search-input');
    const prevButton = pagination.querySelector('[data-page-action="prev"]');
    const nextButton = pagination.querySelector('[data-page-action="next"]');
    const status = pagination.querySelector('[data-page-status]');
    const summaryEl = pagination.querySelector('[data-page-summary]');
    const limitPills = modal.querySelectorAll('.limit-pill');

    const defaultPerPage = parseInt(table.getAttribute('data-rows-per-page'), 10) || 10;
    let perPage = defaultPerPage;
    let currentPage = 0;
    let filterQuery = '';

    function getMatchingRows() {
      if (!filterQuery) return allRows;
      return allRows.filter(function(row) {
        return row.textContent.toLowerCase().includes(filterQuery);
      });
    }

    function renderPage(page) {
      const matchingRows = getMatchingRows();
      const effectivePerPage = perPage === 'all' ? Math.max(1, matchingRows.length) : perPage;
      const totalPages = Math.max(1, Math.ceil(matchingRows.length / effectivePerPage));
      currentPage = Math.max(0, Math.min(page, totalPages - 1));

      allRows.forEach(function(r) { r.style.display = 'none'; });

      let noMatchRow = tbody.querySelector('.modal-no-match-row');
      if (matchingRows.length === 0) {
        pagination.classList.add('d-none');
        if (emptyRow) emptyRow.style.display = allRows.length === 0 ? '' : 'none';
        if (!noMatchRow && allRows.length > 0) {
          noMatchRow = document.createElement('tr');
          noMatchRow.className = 'modal-no-match-row';
          const colCount = table.querySelectorAll('thead th').length || 5;
          noMatchRow.innerHTML = `<td colspan="${colCount}" class="text-muted text-center py-4"><i class="fa fa-search me-2 opacity-50" aria-hidden="true"></i>Tidak ada data murid yang cocok.</td>`;
          tbody.appendChild(noMatchRow);
        }
        if (noMatchRow) noMatchRow.style.display = allRows.length > 0 ? '' : 'none';
        return;
      }

      if (noMatchRow) noMatchRow.style.display = 'none';
      if (emptyRow) emptyRow.style.display = 'none';

      const startIdx = perPage === 'all' ? 0 : currentPage * effectivePerPage;
      const endIdx = perPage === 'all' ? matchingRows.length : startIdx + effectivePerPage;

      matchingRows.forEach(function(row, index) {
        row.style.display = (index >= startIdx && index < endIdx) ? '' : 'none';
      });

      if (summaryEl) {
        const dStart = startIdx + 1;
        const dEnd = Math.min(endIdx, matchingRows.length);
        summaryEl.textContent = `Menampilkan ${dStart}-${dEnd} dari ${matchingRows.length} data${filterQuery ? ' (difilter)' : ''}`;
      }

      if (limitPills.length > 0) {
        limitPills.forEach(function(pill) {
          const l = pill.getAttribute('data-limit');
          pill.classList.toggle('active', (l === 'all' && perPage === 'all') || (l !== 'all' && parseInt(l, 10) === perPage));
        });
      }

      if (totalPages > 1 || limitPills.length > 0) {
        pagination.classList.remove('d-none');
        if (status) status.textContent = 'Hal. ' + (currentPage + 1) + ' / ' + totalPages;
      } else if (matchingRows.length > 0 && summaryEl) {
        pagination.classList.remove('d-none');
        if (status) status.textContent = '';
      } else {
        pagination.classList.add('d-none');
        if (status) status.textContent = '';
      }

      if (prevButton) prevButton.disabled = currentPage === 0;
      if (nextButton) nextButton.disabled = currentPage >= totalPages - 1 || perPage === 'all';
    }

    if (prevButton) {
      prevButton.addEventListener('click', function() {
        renderPage(currentPage - 1);
      });
    }

    if (nextButton) {
      nextButton.addEventListener('click', function() {
        renderPage(currentPage + 1);
      });
    }

    if (searchInput) {
      searchInput.addEventListener('input', function() {
        filterQuery = this.value.trim().toLowerCase();
        renderPage(0);
      });
    }

    limitPills.forEach(function(pill) {
      pill.addEventListener('click', function(e) {
        e.preventDefault();
        const l = this.getAttribute('data-limit');
        perPage = l === 'all' ? 'all' : parseInt(l, 10);
        renderPage(0);
      });
    });

    modal.addEventListener('show.bs.modal', function() {
      if (searchInput) {
        searchInput.value = '';
        filterQuery = '';
      }
      perPage = defaultPerPage;
      renderPage(0);
    });

    renderPage(0);
  });

  // Stacked modal handling between #modalPertemuanTargetDetail and #modalDetailRealisasi
  const targetModalEl = document.getElementById('modalPertemuanTargetDetail');
  const realisasiModalEl = document.getElementById('modalDetailRealisasi');
  let lastRealisasiTrigger = null;

  if (targetModalEl && realisasiModalEl) {
    targetModalEl.addEventListener('click', function(e) {
      const btn = e.target.closest('.btn-realisasi-detail');
      if (btn) {
        lastRealisasiTrigger = btn;
      }
    });

    realisasiModalEl.addEventListener('show.bs.modal', function() {
      const openModals = document.querySelectorAll('.modal.show').length;
      if (openModals > 0) {
        setTimeout(function() {
          const backdrops = document.querySelectorAll('.modal-backdrop');
          if (backdrops.length > 1) {
            backdrops[backdrops.length - 1].style.zIndex = '1058';
          }
        }, 0);
      }
    });

    realisasiModalEl.addEventListener('hidden.bs.modal', function() {
      if (targetModalEl.classList.contains('show')) {
        document.body.classList.add('modal-open');
        document.body.style.overflow = 'hidden';
        if (lastRealisasiTrigger && document.body.contains(lastRealisasiTrigger)) {
          lastRealisasiTrigger.focus();
        }
      }
    });
  }
});
</script>
