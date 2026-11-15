
<div class="container-fluid">
  <!-- Page Header -->
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
      <h3 class="mb-1 fw-bold" style="font-family:'Outfit',sans-serif;color:var(--text);">
        <i class="fa fa-calendar-alt me-2" style="color:var(--primary);"></i>Rencana Pertemuan
      </h3>
      <div class="text-muted small">
        Rencanakan target jumlah sesi latihan per bulan khusus untuk murid berstatus <span class="badge badge-status-aktif py-0 px-2">Aktif</span>.
      </div>
    </div>

    <!-- Quick Period Switcher & Dropdown -->
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <div class="period-pill-group">
        <a href="?tahun=<?= $curPeriod['tahun'] ?>&bulan=<?= $curPeriod['bulan'] ?>" 
           class="period-pill <?= $isCurrentSelected ? 'active' : '' ?>"
           title="Pilih Bulan Ini">
          <i class="fa fa-calendar-day"></i>
          <span>Bulan Ini</span>
        </a>
        <a href="?tahun=<?= $nextPeriod['tahun'] ?>&bulan=<?= $nextPeriod['bulan'] ?>" 
           class="period-pill <?= $isNextSelected ? 'active' : '' ?>"
           title="Pilih Bulan Depan">
          <i class="fa fa-forward"></i>
          <span>Bulan Depan</span>
        </a>
      </div>

      <div class="position-relative">
        <select id="selectPeriodDropdown" class="form-select form-select-sm" style="min-width:190px;font-weight:500;" aria-label="Pilih Periode Bulan dan Tahun" onchange="window.location.href=this.value;">
          <?php foreach ($periodData['options'] as $opt): ?>
            <?php $url = "?tahun={$opt['tahun']}&bulan={$opt['bulan']}"; ?>
            <option value="<?= $url ?>" <?= $opt['is_selected'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($opt['label']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </div>

  <!-- 4 Bento KPI Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card bento-hero-card p-3 h-100" style="border-top:3px solid var(--primary);">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <div>
            <span class="badge bento-badge mb-1" style="background:rgba(35,72,59,0.1);color:var(--primary);">Target Total</span>
            <div class="bento-metric-number" style="color:var(--primary);font-size:1.65rem;"><?= $summary['total_target'] ?></div>
            <small class="text-muted" style="font-size:0.75rem;">Sesi direncanakan</small>
          </div>
          <div class="bento-icon-box" style="background:rgba(35,72,59,0.1);color:var(--primary);width:40px;height:40px;border-radius:10px;">
            <i class="fa fa-bullseye"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="card bento-hero-card p-3 h-100" style="border-top:3px solid var(--accent);">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <div>
            <span class="badge bento-badge mb-1" style="background:rgba(197,160,89,0.15);color:var(--accent);">Terlaksana</span>
            <div class="bento-metric-number" style="color:var(--accent);font-size:1.65rem;"><?= $summary['total_realisasi'] ?></div>
            <small class="text-muted" style="font-size:0.75rem;">Realisasi bulan ini</small>
          </div>
          <div class="bento-icon-box" style="background:rgba(197,160,89,0.15);color:var(--accent);width:40px;height:40px;border-radius:10px;">
            <i class="fa fa-check-double"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="card bento-hero-card p-3 h-100" style="border-top:3px solid var(--gold);">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <div>
            <span class="badge bento-badge mb-1" style="background:rgba(212,175,55,0.15);color:#8a7548;">Murid Aktif</span>
            <div class="bento-metric-number" style="color:#8a7548;font-size:1.65rem;"><?= $totalStudents ?></div>
            <small class="text-muted" style="font-size:0.75rem;">Murid terencana</small>
          </div>
          <div class="bento-icon-box" style="background:rgba(212,175,55,0.15);color:#8a7548;width:40px;height:40px;border-radius:10px;">
            <i class="fa fa-users"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="card bento-hero-card p-3 h-100" style="border-top:3px solid #2e7d32;">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <div>
            <?php 
              $persenGlobal = $summary['total_target'] > 0 
                ? (int)round(($summary['total_realisasi'] / $summary['total_target']) * 100) 
                : ($summary['total_realisasi'] > 0 ? 100 : 0);
            ?>
            <span class="badge bento-badge mb-1" style="background:rgba(46,125,50,0.12);color:#2e7d32;">Capaian</span>
            <div class="bento-metric-number" style="color:#2e7d32;font-size:1.65rem;"><?= $persenGlobal ?>%</div>
            <small class="text-muted" style="font-size:0.75rem;"><?= $summary['total_realisasi'] ?>/<?= $summary['total_target'] ?> sesi</small>
          </div>
          <div class="bento-icon-box" style="background:rgba(46,125,50,0.12);color:#2e7d32;width:40px;height:40px;border-radius:10px;">
            <i class="fa fa-chart-line"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Action Bar & Filter Toolbar -->
  <form id="formRencanaBatch" method="post" action="rencana_pertemuan.php">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_batch">
    <input type="hidden" name="tahun" value="<?= $tahunDipilih ?>">
    <input type="hidden" name="bulan" value="<?= $bulanDipilih ?>">

    <div class="card bento-hero-card p-3 p-md-4 mb-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <div class="position-relative">
            <input id="searchRencanaLive" data-live-search="#tableRencana" data-counter-target="#counterRencana" class="form-control form-control-sm" style="max-width:240px;padding-left:32px;border-radius:8px;" placeholder="Cari nama murid / catatan…">
            <i class="fa fa-search position-absolute top-50 translate-middle-y text-muted" style="left:12px;font-size:0.82rem;"></i>
          </div>

          <div class="dropdown filter-dropdown-container" data-table-target="#tableRencana">
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
                <label class="filter-label">Status Capaian Target</label>
                <select data-table-target="#tableRencana" data-live-filter-col="5" class="form-select form-select-sm">
                  <option value="">Semua Capaian</option>
                  <option value="tercapai">Target Tercapai (≥ 100%)</option>
                  <option value="dalam-proses">Sedang Berjalan (&lt; 100%)</option>
                  <option value="belum-mulai">Belum Ada Sesi (0%)</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="filter-label">Realisasi Sesi</label>
                <select data-table-target="#tableRencana" data-live-filter-col="4" class="form-select form-select-sm">
                  <option value="">Semua Realisasi</option>
                  <option value=":has-value">Sudah Ada Sesi (&gt; 0)</option>
                  <option value=":empty">Belum Ada Sesi (0 sesi)</option>
                </select>
              </div>
              <?php if (!$isCoach): ?>
                <div class="mb-3">
                  <label class="filter-label">Coach Pembina</label>
                  <select data-table-target="#tableRencana" data-live-filter-col="6" class="form-select form-select-sm">
                    <option value="">Semua Coach</option>
                    <?php foreach ($coachesList as $c): ?>
                      <option value="<?= strtolower(htmlspecialchars($c['nama'])) ?>"><?= htmlspecialchars($c['nama']) ?></option>
                    <?php endforeach; ?>
                    <option value="belum-ditentukan">Belum Ditentukan</option>
                  </select>
                </div>
              <?php endif; ?>
              <div>
                <label class="filter-label">Catatan Rencana</label>
                <select data-table-target="#tableRencana" data-live-filter-col="<?= $isCoach ? 6 : 7 ?>" class="form-select form-select-sm">
                  <option value="">Semua Catatan</option>
                  <option value=":has-value">Ada Catatan</option>
                  <option value=":empty">Tanpa Catatan</option>
                </select>
              </div>
            </div>
          </div>

          <span class="filter-counter-badge" id="counterRencana">Total: <?= $totalStudents ?> murid</span>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
          <button type="button" id="btnApplyDefaultTarget" class="btn btn-outline-secondary btn-sm" title="Ubah semua target input menjadi 4 sesi">
            <i class="fa fa-magic me-1 text-accent"></i> Terapkan Default 4 Sesi
          </button>

          <?php if (!empty($unplannedActiveStudents)): ?>
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahMuridRencana">
              <i class="fa fa-user-plus me-1"></i> Tambah Murid
            </button>
          <?php endif; ?>

          <button type="submit" id="btnSaveBatch" class="btn btn-success btn-sm px-3">
            <i class="fa fa-save me-1"></i> Simpan Semua Rencana
          </button>
        </div>
      </div>

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
                  <div class="mb-2"><i class="fa fa-calendar-times fa-3x opacity-50"></i></div>
                  <h6 class="fw-bold">Belum Ada Murid Aktif dalam Rencana Periode Ini</h6>
                  <p class="small text-muted mb-3" style="max-width:440px;margin:0 auto;">
                    <?= $isCoach 
                        ? 'Anda belum memiliki murid yang direncanakan untuk bulan ini. Klik tombol "Tambah Murid" di atas untuk memilih murid aktif.' 
                        : 'Pastikan status murid telah diatur "Aktif" di halaman Data Murid untuk muncul dalam rencana pertemuan.' ?>
                  </p>
                  <?php if (!empty($unplannedActiveStudents)): ?>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahMuridRencana">
                      <i class="fa fa-user-plus me-1"></i> Tambah Murid ke Rencana
                    </button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($studentPlans as $idx => $s): ?>
                <?php 
                  $mId = (int)$s['murid_id'];
                  $targetVal = (int)$s['target_pertemuan'];
                  $realisasiVal = (int)$s['realisasi_pertemuan'];
                  $persen = (int)$s['persentase'];
                  $isTercapai = (bool)$s['is_tercapai'];
                ?>
                <tr data-murid-row="<?= $mId ?>">
                  <td class="text-muted small"><?= $idx + 1 ?></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <?php if (!empty($s['foto'])): ?>
                        <div class="thumb-zoom-wrapper" data-zoom-src="<?= htmlspecialchars($s['foto']) ?>" data-zoom-title="<?= htmlspecialchars($s['nama']) ?>" style="cursor:zoom-in;">
                          <img src="<?= htmlspecialchars($s['foto']) ?>" class="thumb" alt="Foto <?= htmlspecialchars($s['nama']) ?>" style="width:36px;height:36px;border-radius:10px;object-fit:cover;">
                        </div>
                      <?php else: ?>
                        <span class="avatar-empty-badge" style="width:36px;height:36px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;background:rgba(74,107,93,0.1);"><i class="fa fa-user text-muted opacity-50"></i></span>
                      <?php endif; ?>
                      <div>
                        <strong><?= htmlspecialchars($s['nama']) ?></strong>
                        <?php if (!empty($s['instagram'])): ?>
                          <div class="text-muted" style="font-size:0.75rem;"><i class="fab fa-instagram me-1 text-accent"></i><?= htmlspecialchars($s['instagram']) ?></div>
                        <?php endif; ?>
                      </div>
                    </div>
                  </td>
                  <td class="d-none d-sm-table-cell">
                    <span class="badge badge-status-aktif"><i class="fa fa-check-circle me-1"></i>Aktif</span>
                  </td>
                  <td>
                    <div class="target-stepper" data-stepper-murid="<?= $mId ?>">
                      <button type="button" class="stepper-btn btn-stepper-minus" aria-label="Kurangi target murid <?= htmlspecialchars($s['nama']) ?>">&minus;</button>
                      <input type="number" 
                             name="targets[<?= $mId ?>]" 
                             value="<?= $targetVal ?>" 
                             min="0" 
                             max="50" 
                             class="stepper-input target-input-field" 
                             data-original-val="<?= $targetVal ?>" 
                             data-realisasi="<?= $realisasiVal ?>"
                             data-target-row="<?= $mId ?>"
                             aria-label="Target pertemuan <?= htmlspecialchars($s['nama']) ?>">
                      <button type="button" class="stepper-btn btn-stepper-plus" aria-label="Tambah target murid <?= htmlspecialchars($s['nama']) ?>">&plus;</button>
                    </div>
                  </td>
                  <td data-has-val="<?= $realisasiVal > 0 ? '1' : '0' ?>" data-filter-val="<?= $realisasiVal > 0 ? 'ada-realisasi' : 'nol-realisasi' ?>">
                    <button type="button" 
                            class="btn btn-sm btn-realisasi-detail d-inline-flex align-items-center gap-1 <?= $realisasiVal > 0 ? 'has-meetings' : 'zero-meetings' ?>" 
                            data-murid-id="<?= $mId ?>" 
                            data-murid-nama="<?= htmlspecialchars($s['nama']) ?>" 
                            data-tahun="<?= $tahunDipilih ?>" 
                            data-bulan="<?= $bulanDipilih ?>"
                            data-realisasi-count="<?= $realisasiVal ?>"
                            data-bs-toggle="modal" 
                            data-bs-target="#modalDetailRealisasi"
                            title="Klik untuk melihat tanggal sesi pertemuan <?= htmlspecialchars($s['nama']) ?>">
                      <i class="fa fa-calendar-check" style="color:var(--accent);"></i>
                      <strong style="font-variant-numeric:tabular-nums;"><?= $realisasiVal ?></strong> sesi
                      <?php if ($realisasiVal > 0): ?>
                        <i class="fa fa-chevron-right ms-1 opacity-50" style="font-size:0.65rem;"></i>
                      <?php endif; ?>
                    </button>
                  </td>
                  <?php $capaianStatus = $isTercapai ? 'tercapai' : ($realisasiVal === 0 ? 'belum-mulai' : 'dalam-proses'); ?>
                  <td data-filter-val="<?= $capaianStatus ?>">
                    <div class="progress-mini-wrapper" id="progressWrapper<?= $mId ?>">
                      <div class="d-flex justify-content-between align-items-center mb-1 small" style="font-size:0.75rem;">
                        <span class="fw-semibold text-label-status" style="font-variant-numeric:tabular-nums;color:<?= $isTercapai ? '#2e7d32' : 'var(--text-secondary)' ?>;">
                          <?= $realisasiVal ?> / <span class="val-target-label"><?= $targetVal ?></span>
                        </span>
                        <span class="fw-bold val-persen-label" style="font-variant-numeric:tabular-nums;color:<?= $isTercapai ? '#2e7d32' : 'var(--accent)' ?>;">
                          <?= $persen ?>%
                        </span>
                      </div>
                      <div class="progress-mini">
                        <div class="progress-mini-bar <?= $isTercapai ? 'is-complete' : '' ?>" style="width:<?= min(100, $persen) ?>%;"></div>
                      </div>
                    </div>
                  </td>
                  <?php if (!$isCoach): ?>
                    <td data-filter-val="<?= !empty($s['coach_nama']) ? strtolower(htmlspecialchars($s['coach_nama'])) : 'belum-ditentukan' ?>">
                      <select name="coaches[<?= $mId ?>]" class="form-select form-select-sm" style="font-size:0.82rem;" onchange="this.closest('td').setAttribute('data-filter-val', this.value ? this.options[this.selectedIndex].text.toLowerCase() : 'belum-ditentukan');">
                        <option value="">(Belum ditentukan)</option>
                        <?php foreach ($coachesList as $c): ?>
                          <option value="<?= $c['id'] ?>" <?= ((int)($s['coach_id'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nama']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </td>
                  <?php endif; ?>
                  <td data-has-val="<?= (!empty($s['catatan']) && trim($s['catatan']) !== '') ? '1' : '0' ?>">
                    <input type="text" 
                           name="catatans[<?= $mId ?>]" 
                           value="<?= htmlspecialchars($s['catatan'] ?? '') ?>" 
                           class="form-control form-control-sm catatan-input-field" 
                           placeholder="Catatan latihan…">
                  </td>
                  <td class="text-center">
                    <button type="button" 
                            class="btn btn-sm btn-outline-success btn-save-row" 
                            data-murid-id="<?= $mId ?>" 
                            title="Simpan baris murid ini">
                      <i class="fa fa-save"></i>
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
          <small class="text-muted pagination-summary">Menampilkan 1-<?= min(10, $totalStudents) ?> dari <?= $totalStudents ?> data</small>
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
              <button type="button" class="page-link page-arrow" aria-label="Halaman sebelumnya" title="Halaman sebelumnya"><i class="fa fa-chevron-left"></i></button>
            </li>
            <li class="page-item disabled"><span class="page-link page-status-pill">Hal. 1 / <?= max(1, (int)ceil($totalStudents / 10)) ?></span></li>
            <li class="page-item page-next <?= $totalStudents <= 10 ? 'disabled' : '' ?>">
              <button type="button" class="page-link page-arrow" aria-label="Halaman berikutnya" title="Halaman berikutnya"><i class="fa fa-chevron-right"></i></button>
            </li>
          </ul>
        </nav>
      </div>

      <?php if (!empty($studentPlans)): ?>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pt-3 border-top">
          <small class="text-muted">
            <i class="fa fa-info-circle me-1"></i> Klik <strong>Simpan Semua Rencana</strong> untuk menyimpan seluruh baris tabel sekaligus, atau gunakan tombol ikon simpan di setiap baris.
          </small>
          <button type="submit" class="btn btn-success btn-sm px-4">
            <i class="fa fa-save me-1"></i> Simpan Semua Rencana
          </button>
        </div>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- Modal Tambah Murid ke Rencana Bulan Ini -->
<?php if (!empty($unplannedActiveStudents)): ?>
<div class="modal fade" id="modalTambahMuridRencana" tabindex="-1" aria-labelledby="modalTambahMuridLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="rencana_pertemuan.php">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_single">
        <input type="hidden" name="tahun" value="<?= $tahunDipilih ?>">
        <input type="hidden" name="bulan" value="<?= $bulanDipilih ?>">
        <?php if ($isCoach): ?>
          <input type="hidden" name="coach_id" value="<?= $currentUserId ?>">
        <?php endif; ?>

        <div class="modal-header">
          <h5 class="modal-title" id="modalTambahMuridLabel">
            <i class="fa fa-user-plus me-2" style="color:var(--primary);"></i>Tambah Murid ke Rencana Bulan Ini
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Pilih Murid Aktif <span class="text-danger">*</span></label>
            <select name="id_murid" class="form-select" required>
              <option value="">-- Pilih Murid --</option>
              <?php foreach ($unplannedActiveStudents as $uMurid): ?>
                <option value="<?= $uMurid['id'] ?>"><?= htmlspecialchars($uMurid['nama']) ?></option>
              <?php endforeach; ?>
            </select>
            <div class="form-text small text-muted">Hanya murid dengan status "Aktif" yang dapat dipilih.</div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Target Pertemuan <span class="text-danger">*</span></label>
            <input type="number" name="target_pertemuan" value="4" min="0" max="50" class="form-control" required>
            <div class="form-text small text-muted">Berapa kali target sesi latihan untuk murid ini di bulan yang dipilih.</div>
          </div>

          <?php if (!$isCoach): ?>
            <div class="mb-3">
              <label class="form-label fw-semibold">Coach Pembina (opsional)</label>
              <select name="coach_id" class="form-select">
                <option value="">(Belum ditentukan)</option>
                <?php foreach ($coachesList as $c): ?>
                  <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nama']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-label fw-semibold">Catatan Rencana (opsional)</label>
            <input type="text" name="catatan" class="form-control" placeholder="Contoh: Persiapan lomba vokal / paket reguler">
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary"><i class="fa fa-check me-1"></i> Tambahkan ke Rencana</button>
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
                progressBar.style.width = Math.min(100, persen) + '%';
                if (targetVal > 0 && realisasiVal >= targetVal) {
                    progressBar.classList.add('is-complete');
                    if (statusLabel) statusLabel.style.color = '#2e7d32';
                    if (persenLabel) persenLabel.style.color = '#2e7d32';
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
            this.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

            const formData = new FormData();
            formData.append('action', 'save_single');
            formData.append('id_murid', mId);
            formData.append('tahun', '<?= $tahunDipilih ?>');
            formData.append('bulan', '<?= $bulanDipilih ?>');
            formData.append('target_pertemuan', targetVal);
            formData.append('catatan', catatanVal);
            formData.append('coach_id', coachVal);
            formData.append('is_ajax', '1');
            formData.append('csrf_token', '<?= csrf_token() ?>');

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

