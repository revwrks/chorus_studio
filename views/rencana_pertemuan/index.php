<?php
$currentUserIdSafe = (int)($currentUserId ?? $coachId ?? 0);
$totalTarget = (int)($summary['total_target'] ?? 0);
$totalRealisasi = (int)($summary['total_realisasi'] ?? 0);
$persenGlobal = $totalTarget > 0
  ? (int)round(($totalRealisasi / $totalTarget) * 100)
  : ($totalRealisasi > 0 ? 100 : 0);
?>
<div class="container-fluid">
  <!-- Page Header -->
  <div class="page-header">
    <div>
      <h1 class="page-title mb-1">
        <i class="fa fa-calendar-alt me-2" style="color:var(--primary);" aria-hidden="true"></i>Rencana Pertemuan
      </h1>
      <div class="text-muted small">
        Rencanakan target jumlah sesi latihan per bulan khusus untuk murid berstatus <span class="badge badge-status-aktif py-0 px-2">Aktif</span>.
      </div>
    </div>

    <!-- Quick Period Switcher & Dropdown -->
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <div class="period-pill-group">
        <a href="?tahun=<?= (int)$curPeriod['tahun'] ?>&bulan=<?= (int)$curPeriod['bulan'] ?>"
           class="period-pill <?= $isCurrentSelected ? 'active' : '' ?>"
           title="Pilih Bulan Ini">
          <i class="fa fa-calendar-day" aria-hidden="true"></i>
          <span>Bulan Ini</span>
        </a>
        <a href="?tahun=<?= (int)$nextPeriod['tahun'] ?>&bulan=<?= (int)$nextPeriod['bulan'] ?>"
           class="period-pill <?= $isNextSelected ? 'active' : '' ?>"
           title="Pilih Bulan Depan">
          <i class="fa fa-forward" aria-hidden="true"></i>
          <span>Bulan Depan</span>
        </a>
      </div>

      <div class="position-relative">
        <select id="selectPeriodDropdown" class="form-select form-select-sm" style="min-width:190px;font-weight:500;" aria-label="Pilih Periode Bulan dan Tahun" onchange="window.location.href=this.value;">
          <?php foreach ($periodData['options'] as $opt): ?>
            <?php $url = '?tahun=' . (int)$opt['tahun'] . '&bulan=' . (int)$opt['bulan']; ?>
            <option value="<?= e($url) ?>" <?= $opt['is_selected'] ? 'selected' : '' ?>>
              <?= e($opt['label'] ?? '') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </div>

  <!-- 4 KPI Summary Cards -->
  <div class="kpi-grid mb-4">
    <div class="metric-card">
      <div class="d-flex justify-content-between align-items-center gap-2">
        <div>
          <span class="bento-badge mb-1">Target Total</span>
          <div class="bento-metric-number" style="color:var(--primary);font-size:1.65rem;"><?= $totalTarget ?></div>
          <small class="text-muted">Sesi direncanakan</small>
        </div>
        <div class="bento-icon-box" style="background-color:var(--primary-soft);color:var(--primary);">
          <i class="fa fa-bullseye" aria-hidden="true"></i>
        </div>
      </div>
    </div>

    <div class="metric-card">
      <div class="d-flex justify-content-between align-items-center gap-2">
        <div>
          <span class="bento-badge mb-1" style="background-color:var(--accent-soft);color:var(--accent-ink);">Terlaksana</span>
          <div class="bento-metric-number" style="color:var(--accent);font-size:1.65rem;"><?= $totalRealisasi ?></div>
          <small class="text-muted">Realisasi bulan ini</small>
        </div>
        <div class="bento-icon-box" style="background-color:var(--accent-soft);color:var(--accent);">
          <i class="fa fa-check-double" aria-hidden="true"></i>
        </div>
      </div>
    </div>

    <div class="metric-card">
      <div class="d-flex justify-content-between align-items-center gap-2">
        <div>
          <span class="bento-badge mb-1" style="background-color:var(--warning-soft);color:var(--warning);">Murid Aktif</span>
          <div class="bento-metric-number" style="color:var(--warning);font-size:1.65rem;"><?= (int)$totalStudents ?></div>
          <small class="text-muted">Murid terencana</small>
        </div>
        <div class="bento-icon-box" style="background-color:var(--warning-soft);color:var(--warning);">
          <i class="fa fa-users" aria-hidden="true"></i>
        </div>
      </div>
    </div>

    <div class="metric-card">
      <div class="d-flex justify-content-between align-items-center gap-2">
        <div>
          <span class="bento-badge mb-1" style="background-color:var(--success-soft);color:var(--success);">Capaian</span>
          <div class="bento-metric-number" style="color:var(--success);font-size:1.65rem;"><?= $persenGlobal ?>%</div>
          <small class="text-muted"><?= $totalRealisasi ?>/<?= $totalTarget ?> sesi</small>
        </div>
        <div class="bento-icon-box" style="background-color:var(--success-soft);color:var(--success);">
          <i class="fa fa-chart-line" aria-hidden="true"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- Action Bar, Filter Toolbar & Batch Table -->
  <div class="card bento-hero-card p-3 p-md-4 mb-4">
    <div class="toolbar-row mb-3">
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <div class="position-relative">
          <input type="search" id="searchRencanaLive" data-live-search="#tableRencana" data-counter-target="#counterRencana" class="form-control form-control-sm" style="max-width:240px;padding-left:32px;border-radius:8px;" placeholder="Cari nama murid / catatan…" aria-label="Cari murid atau catatan rencana..." onkeydown="if(event.key==='Enter'){event.preventDefault();}">
          <i class="fa fa-search position-absolute top-50 translate-middle-y text-muted" style="left:12px;font-size:0.82rem;" aria-hidden="true"></i>
        </div>

        <div class="dropdown filter-dropdown-container" data-table-target="#tableRencana">
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
              <label for="filterCapaianRencana" class="filter-label">Status Capaian Target</label>
              <select id="filterCapaianRencana" data-table-target="#tableRencana" data-live-filter-col="5" class="form-select form-select-sm">
                <option value="">Semua Capaian</option>
                <option value="tercapai">Target Tercapai (≥ 100%)</option>
                <option value="dalam-proses">Sedang Berjalan (&lt; 100%)</option>
                <option value="belum-mulai">Belum Ada Sesi (0%)</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="filterRealisasiRencana" class="filter-label">Realisasi Sesi</label>
              <select id="filterRealisasiRencana" data-table-target="#tableRencana" data-live-filter-col="4" class="form-select form-select-sm">
                <option value="">Semua Realisasi</option>
                <option value=":has-value">Sudah Ada Sesi (&gt; 0)</option>
                <option value=":empty">Belum Ada Sesi (0 sesi)</option>
              </select>
            </div>
            <?php if (!$isCoach): ?>
              <div class="mb-3">
                <label for="filterCoachRencana" class="filter-label">Coach Pembina</label>
                <select id="filterCoachRencana" data-table-target="#tableRencana" data-live-filter-col="6" class="form-select form-select-sm">
                  <option value="">Semua Coach</option>
                  <?php foreach ($coachesList as $c): ?>
                    <option value="<?= e(strtolower((string)($c['nama'] ?? ''))) ?>"><?= e($c['nama'] ?? '') ?></option>
                  <?php endforeach; ?>
                  <option value="belum-ditentukan">Belum Ditentukan</option>
                </select>
              </div>
            <?php endif; ?>
            <div>
              <label for="filterCatatanRencana" class="filter-label">Catatan Rencana</label>
              <select id="filterCatatanRencana" data-table-target="#tableRencana" data-live-filter-col="<?= $isCoach ? 6 : 7 ?>" class="form-select form-select-sm">
                <option value="">Semua Catatan</option>
                <option value=":has-value">Ada Catatan</option>
                <option value=":empty">Tanpa Catatan</option>
              </select>
            </div>
          </div>
        </div>

        <span class="filter-counter-badge" id="counterRencana">Total: <?= (int)$totalStudents ?> murid</span>
      </div>

      <div class="d-flex align-items-center gap-2 flex-wrap">
        <button type="button" id="btnApplyDefaultTarget" class="btn btn-outline-secondary btn-sm" title="Ubah semua target input menjadi 4 sesi">
          <i class="fa fa-magic me-1 text-accent" aria-hidden="true"></i> Terapkan Default 4 Sesi
        </button>

        <?php if (!empty($unplannedActiveStudents)): ?>
          <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahMuridRencana">
            <i class="fa fa-user-plus me-1" aria-hidden="true"></i> Tambah Murid
          </button>
        <?php endif; ?>

        <button type="submit" id="btnSaveBatch" form="formRencanaBatch" class="btn btn-success btn-sm px-3">
          <i class="fa fa-save me-1" aria-hidden="true"></i> Simpan Semua Rencana
        </button>
      </div>
    </div>

    <form id="formRencanaBatch" method="post" action="rencana_pertemuan.php">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save_batch">
      <input type="hidden" name="tahun" value="<?= (int)$tahunDipilih ?>">
      <input type="hidden" name="bulan" value="<?= (int)$bulanDipilih ?>">

      <!-- Table of Students & Meeting Targets -->
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle" id="tableRencana">
          <thead>
            <tr>
              <th style="width:40px;">No</th>
              <th>Murid</th>
              <th class="d-none d-sm-table-cell">Status</th>
              <th style="width:160px;">Target Pertemuan</th>
              <th style="width:140px;">Realisasi</th>
              <th style="min-width:140px;">Capaian</th>
              <?php if (!$isCoach): ?>
                <th style="width:170px;">Coach Pembina</th>
              <?php endif; ?>
              <th>Catatan Rencana</th>
              <th style="width:90px;" class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($studentPlans)): ?>
              <tr>
                <td colspan="<?= $isCoach ? '8' : '9' ?>" class="text-center text-muted py-5">
                  <div class="mb-2"><i class="fa fa-calendar-times fa-3x opacity-50" aria-hidden="true"></i></div>
                  <h6 class="fw-bold">Belum Ada Murid Aktif dalam Rencana Periode Ini</h6>
                  <p class="small text-muted mb-3" style="max-width:440px;margin:0 auto;">
                    <?= $isCoach
                        ? 'Anda belum memiliki murid yang direncanakan untuk bulan ini. Klik tombol "Tambah Murid" di atas untuk memilih murid aktif.'
                        : 'Pastikan status murid telah diatur "Aktif" di halaman Data Murid untuk muncul dalam rencana pertemuan.' ?>
                  </p>
                  <?php if (!empty($unplannedActiveStudents)): ?>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahMuridRencana">
                      <i class="fa fa-user-plus me-1" aria-hidden="true"></i> Tambah Murid ke Rencana
                    </button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($studentPlans as $idx => $s): ?>
                <?php
                  $mId = (int)$s['murid_id'];
                  $mNama = (string)($s['nama'] ?? '');
                  $mFoto = (string)($s['foto'] ?? '');
                  $mIg = (string)($s['instagram'] ?? '');
                  $targetVal = (int)$s['target_pertemuan'];
                  $realisasiVal = (int)$s['realisasi_pertemuan'];
                  $persen = (int)$s['persentase'];
                  $isTercapai = (bool)$s['is_tercapai'];
                ?>
                <tr data-murid-row="<?= $mId ?>">
                  <td class="text-muted small"><?= $idx + 1 ?></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <?php if ($mFoto !== ''): ?>
                        <button type="button" class="thumb-zoom-wrapper border-0 bg-transparent p-0" data-zoom-src="<?= e($mFoto) ?>" data-zoom-title="<?= e($mNama) ?>" aria-label="Perbesar foto <?= e($mNama) ?>">
                          <img src="<?= e($mFoto) ?>" class="thumb" alt="Foto <?= e($mNama) ?>" style="width:36px;height:36px;border-radius:10px;object-fit:cover;">
                        </button>
                      <?php else: ?>
                        <span class="avatar-empty-badge" style="width:36px;height:36px;border-radius:10px;"><i class="fa fa-user text-muted opacity-50" aria-hidden="true"></i></span>
                      <?php endif; ?>
                      <div>
                        <strong><?= e($mNama) ?></strong>
                        <?php if ($mIg !== ''): ?>
                          <div class="text-muted" style="font-size:0.75rem;"><i class="fab fa-instagram me-1 text-accent" aria-hidden="true"></i><?= e($mIg) ?></div>
                        <?php endif; ?>
                      </div>
                    </div>
                  </td>
                  <td class="d-none d-sm-table-cell">
                    <span class="badge badge-status-aktif"><i class="fa fa-check-circle me-1" aria-hidden="true"></i>Aktif</span>
                  </td>
                  <td>
                    <div class="target-stepper" data-stepper-murid="<?= $mId ?>">
                      <button type="button" class="stepper-btn btn-stepper-minus" aria-label="Kurangi target murid <?= e($mNama) ?>">&minus;</button>
                      <input type="number"
                             name="targets[<?= $mId ?>]"
                             value="<?= $targetVal ?>"
                             min="0"
                             max="50"
                             class="stepper-input target-input-field"
                             data-original-val="<?= $targetVal ?>"
                             data-realisasi="<?= $realisasiVal ?>"
                             data-target-row="<?= $mId ?>"
                             aria-label="Target pertemuan <?= e($mNama) ?>">
                      <button type="button" class="stepper-btn btn-stepper-plus" aria-label="Tambah target murid <?= e($mNama) ?>">&plus;</button>
                    </div>
                  </td>
                  <td data-has-val="<?= $realisasiVal > 0 ? '1' : '0' ?>" data-filter-val="<?= $realisasiVal > 0 ? 'ada-realisasi' : 'nol-realisasi' ?>">
                    <button type="button"
                            class="btn btn-sm btn-realisasi-detail d-inline-flex align-items-center gap-1 <?= $realisasiVal > 0 ? 'has-meetings' : 'zero-meetings' ?>"
                            data-murid-id="<?= $mId ?>"
                            data-murid-nama="<?= e($mNama) ?>"
                            data-tahun="<?= (int)$tahunDipilih ?>"
                            data-bulan="<?= (int)$bulanDipilih ?>"
                            data-realisasi-count="<?= $realisasiVal ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#modalDetailRealisasi"
                            title="Klik untuk melihat tanggal sesi pertemuan <?= e($mNama) ?>">
                      <i class="fa fa-calendar-check" style="color:var(--accent);" aria-hidden="true"></i>
                      <strong style="font-variant-numeric:tabular-nums;"><?= $realisasiVal ?></strong> sesi
                      <?php if ($realisasiVal > 0): ?>
                        <i class="fa fa-chevron-right ms-1 opacity-50" style="font-size:0.65rem;" aria-hidden="true"></i>
                      <?php endif; ?>
                    </button>
                  </td>
                  <?php $capaianStatus = $isTercapai ? 'tercapai' : ($realisasiVal === 0 ? 'belum-mulai' : 'dalam-proses'); ?>
                  <td data-filter-val="<?= $capaianStatus ?>">
                    <div class="progress-mini-wrapper" id="progressWrapper<?= $mId ?>">
                      <div class="d-flex justify-content-between align-items-center mb-1 small" style="font-size:0.75rem;">
                        <span class="fw-semibold text-label-status" style="font-variant-numeric:tabular-nums;color:<?= $isTercapai ? 'var(--success)' : 'var(--text-secondary)' ?>;">
                          <?= $realisasiVal ?> / <span class="val-target-label"><?= $targetVal ?></span>
                        </span>
                        <span class="fw-bold val-persen-label" style="font-variant-numeric:tabular-nums;color:<?= $isTercapai ? 'var(--success)' : 'var(--accent)' ?>;">
                          <?= $persen ?>%
                        </span>
                      </div>
                      <div class="progress-mini">
                        <div class="progress-mini-bar <?= $isTercapai ? 'is-complete' : '' ?>" role="progressbar" aria-valuenow="<?= min(100, $persen) ?>" aria-valuemin="0" aria-valuemax="100" aria-label="Capaian target <?= e($mNama) ?>" style="width:<?= min(100, $persen) ?>%;"></div>
                      </div>
                    </div>
                  </td>
                  <?php if (!$isCoach): ?>
                    <td data-filter-val="<?= !empty($s['coach_nama']) ? e(strtolower((string)$s['coach_nama'])) : 'belum-ditentukan' ?>">
                      <select name="coaches[<?= $mId ?>]" class="form-select form-select-sm" style="font-size:0.82rem;" aria-label="Coach pembina untuk <?= e($mNama) ?>" onchange="this.closest('td').setAttribute('data-filter-val', this.value ? this.options[this.selectedIndex].text.toLowerCase() : 'belum-ditentukan');">
                        <option value="">(Belum ditentukan)</option>
                        <?php foreach ($coachesList as $c): ?>
                          <option value="<?= (int)$c['id'] ?>" <?= ((int)($s['coach_id'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>>
                            <?= e($c['nama'] ?? '') ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </td>
                  <?php endif; ?>
                  <td data-has-val="<?= (!empty($s['catatan']) && trim((string)$s['catatan']) !== '') ? '1' : '0' ?>">
                    <input type="text"
                           name="catatans[<?= $mId ?>]"
                           value="<?= e($s['catatan'] ?? '') ?>"
                           class="form-control form-control-sm catatan-input-field input-catatan-rencana"
                           style="min-width:150px;"
                           aria-label="Catatan rencana untuk <?= e($mNama) ?>"
                           placeholder="Catatan latihan…">
                  </td>
                  <td class="text-center">
                    <button type="button"
                            class="btn btn-sm btn-outline-success btn-save-row"
                            data-murid-id="<?= $mId ?>"
                            title="Simpan baris murid ini"
                            aria-label="Simpan rencana <?= e($mNama) ?>">
                      <i class="fa fa-save" aria-hidden="true"></i>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Client-Side Pagination Container -->
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pagination-container" data-pagination-table="#tableRencana">
        <div class="d-flex align-items-center gap-3 flex-wrap">
          <small class="text-muted pagination-summary">Menampilkan 1-<?= min(10, (int)$totalStudents) ?> dari <?= (int)$totalStudents ?> data</small>
          <div class="limit-switch">
            <button type="button" class="limit-pill active" data-limit="10">10</button>
            <button type="button" class="limit-pill" data-limit="20">20</button>
            <button type="button" class="limit-pill" data-limit="100">100</button>
            <button type="button" class="limit-pill" data-limit="all">Semua</button>
          </div>
        </div>
        <nav aria-label="Pagination rencana" class="pagination-nav">
          <ul class="pagination pagination-sm mb-0">
            <li class="page-item page-prev disabled">
              <button type="button" class="page-link page-arrow" aria-label="Halaman sebelumnya" title="Halaman sebelumnya"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
            </li>
            <li class="page-item disabled"><span class="page-link page-status-pill">Hal. 1 / <?= max(1, (int)ceil($totalStudents / 10)) ?></span></li>
            <li class="page-item page-next <?= $totalStudents <= 10 ? 'disabled' : '' ?>">
              <button type="button" class="page-link page-arrow" aria-label="Halaman berikutnya" title="Halaman berikutnya"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
            </li>
          </ul>
        </nav>
      </div>

      <?php if (!empty($studentPlans)): ?>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pt-3 border-top">
          <small class="text-muted">
            <i class="fa fa-info-circle me-1" aria-hidden="true"></i> Klik <strong>Simpan Semua Rencana</strong> untuk menyimpan seluruh baris tabel sekaligus, atau gunakan tombol ikon simpan di setiap baris.
          </small>
          <button type="submit" class="btn btn-success btn-sm px-4">
            <i class="fa fa-save me-1" aria-hidden="true"></i> Simpan Semua Rencana
          </button>
        </div>
      <?php endif; ?>
    </form>
  </div>
</div>

<!-- Modal Tambah Murid ke Rencana Bulan Ini -->
<?php if (!empty($unplannedActiveStudents)): ?>
<div class="modal fade" id="modalTambahMuridRencana" tabindex="-1" aria-labelledby="modalTambahMuridLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="rencana_pertemuan.php">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_single">
        <input type="hidden" name="tahun" value="<?= (int)$tahunDipilih ?>">
        <input type="hidden" name="bulan" value="<?= (int)$bulanDipilih ?>">
        <?php if ($isCoach): ?>
          <input type="hidden" name="coach_id" value="<?= $currentUserIdSafe ?>">
        <?php endif; ?>

        <div class="modal-header">
          <h5 class="modal-title" id="modalTambahMuridLabel">
            <i class="fa fa-user-plus me-2" style="color:var(--primary);" aria-hidden="true"></i>Tambah Murid ke Rencana Bulan Ini
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label for="addRencanaMurid" class="form-label fw-semibold">Pilih Murid Aktif <span class="text-danger">*</span></label>
            <select id="addRencanaMurid" name="id_murid" class="form-select" required>
              <option value="">-- Pilih Murid --</option>
              <?php foreach ($unplannedActiveStudents as $uMurid): ?>
                <option value="<?= (int)$uMurid['id'] ?>"><?= e($uMurid['nama'] ?? '') ?></option>
              <?php endforeach; ?>
            </select>
            <div class="form-text small text-muted">Hanya murid dengan status "Aktif" yang dapat dipilih.</div>
          </div>

          <div class="mb-3">
            <label for="addRencanaTarget" class="form-label fw-semibold">Target Pertemuan <span class="text-danger">*</span></label>
            <input id="addRencanaTarget" type="number" name="target_pertemuan" value="4" min="0" max="50" class="form-control" required>
            <div class="form-text small text-muted">Berapa kali target sesi latihan untuk murid ini di bulan yang dipilih.</div>
          </div>

          <?php if (!$isCoach): ?>
            <div class="mb-3">
              <label for="addRencanaCoach" class="form-label fw-semibold">Coach Pembina (opsional)</label>
              <select id="addRencanaCoach" name="coach_id" class="form-select">
                <option value="">(Belum ditentukan)</option>
                <?php foreach ($coachesList as $c): ?>
                  <option value="<?= (int)$c['id'] ?>"><?= e($c['nama'] ?? '') ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endif; ?>

          <div class="mb-3">
            <label for="addRencanaCatatan" class="form-label fw-semibold">Catatan Rencana (opsional)</label>
            <input id="addRencanaCatatan" type="text" name="catatan" class="form-control" placeholder="Contoh: Persiapan lomba vokal / paket reguler">
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary"><i class="fa fa-check me-1" aria-hidden="true"></i> Tambahkan ke Rencana</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Stepper Plus & Minus Buttons
    document.querySelectorAll('.btn-stepper-minus').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.closest('.target-stepper').querySelector('.stepper-input');
            let val = parseInt(input.value, 10) || 0;
            if (val > 0) {
                input.value = val - 1;
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    });

    document.querySelectorAll('.btn-stepper-plus').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.closest('.target-stepper').querySelector('.stepper-input');
            let val = parseInt(input.value, 10) || 0;
            input.value = val + 1;
            input.dispatchEvent(new Event('input', { bubbles: true }));
        });
    });

    // Update mini progress bar dynamically when target input changes
    document.querySelectorAll('.target-input-field').forEach(input => {
        input.addEventListener('input', function() {
            const rowId = this.getAttribute('data-target-row');
            const targetVal = Math.max(0, parseInt(this.value, 10) || 0);
            const realisasiVal = parseInt(this.getAttribute('data-realisasi'), 10) || 0;
            const wrapper = document.getElementById('progressWrapper' + rowId);
            if (!wrapper) return;

            const targetLabel = wrapper.querySelector('.val-target-label');
            const persenLabel = wrapper.querySelector('.val-persen-label');
            const progressBar = wrapper.querySelector('.progress-mini-bar');
            const statusLabel = wrapper.querySelector('.text-label-status');

            if (targetLabel) targetLabel.textContent = targetVal;

            const persen = targetVal > 0 ? Math.round((realisasiVal / targetVal) * 100) : (realisasiVal > 0 ? 100 : 0);
            if (persenLabel) persenLabel.textContent = persen + '%';

            if (progressBar) {
                const clampedPersen = Math.min(100, persen);
                progressBar.style.width = clampedPersen + '%';
                progressBar.setAttribute('aria-valuenow', String(clampedPersen));
                if (targetVal > 0 && realisasiVal >= targetVal) {
                    progressBar.classList.add('is-complete');
                    if (statusLabel) statusLabel.style.color = 'var(--success)';
                    if (persenLabel) persenLabel.style.color = 'var(--success)';
                } else {
                    progressBar.classList.remove('is-complete');
                    if (statusLabel) statusLabel.style.color = 'var(--text-secondary)';
                    if (persenLabel) persenLabel.style.color = 'var(--accent)';
                }
            }
        });
    });

    // "Terapkan Default 4 Sesi" to all rows
    const btnDefault = document.getElementById('btnApplyDefaultTarget');
    if (btnDefault) {
        btnDefault.addEventListener('click', function() {
            document.querySelectorAll('.target-input-field').forEach(input => {
                input.value = 4;
                input.dispatchEvent(new Event('input', { bubbles: true }));
            });
            if (typeof window.showToast === 'function') {
                window.showToast('Target seluruh murid diatur ke 4 sesi. Klik "Simpan Semua Rencana" untuk menyimpan.', 'info');
            }
        });
    }

    // Save individual row via AJAX
    document.querySelectorAll('.btn-save-row').forEach(btn => {
        btn.addEventListener('click', async function() {
            const mId = this.getAttribute('data-murid-id');
            const row = document.querySelector(`tr[data-murid-row="${mId}"]`);
            if (!row) return;

            const targetInput = row.querySelector('.target-input-field');
            const catatanInput = row.querySelector('.catatan-input-field');
            const coachSelect = row.querySelector('select[name^="coaches"]');

            const targetVal = targetInput ? parseInt(targetInput.value, 10) : 4;
            const catatanVal = catatanInput ? catatanInput.value : '';
            const coachVal = coachSelect ? coachSelect.value : '';

            const origHtml = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>';

            const formData = new FormData();
            formData.append('action', 'save_single');
            formData.append('id_murid', mId);
            formData.append('tahun', '<?= (int)$tahunDipilih ?>');
            formData.append('bulan', '<?= (int)$bulanDipilih ?>');
            formData.append('target_pertemuan', targetVal);
            formData.append('catatan', catatanVal);
            formData.append('coach_id', coachVal);
            formData.append('is_ajax', '1');
            formData.append('csrf_token', '<?= e(csrf_token()) ?>');

            try {
                const resp = await fetch('rencana_pertemuan.php', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const res = await resp.json();
                if (res.success) {
                    if (typeof window.showToast === 'function') {
                        window.showToast(res.message || 'Target murid berhasil disimpan.', 'success');
                    }
                    this.classList.remove('btn-outline-success');
                    this.classList.add('btn-success');
                    setTimeout(() => {
                        this.classList.remove('btn-success');
                        this.classList.add('btn-outline-success');
                    }, 1500);
                } else {
                    if (typeof window.showToast === 'function') {
                        window.showToast(res.message || 'Gagal menyimpan target.', 'danger');
                    }
                }
            } catch (err) {
                if (typeof window.showToast === 'function') {
                    window.showToast('Gagal menghubungi server.', 'danger');
                }
            } finally {
                this.disabled = false;
                this.innerHTML = origHtml;
            }
        });
    });
});
</script>
