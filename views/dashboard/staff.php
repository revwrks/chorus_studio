
<div class="container-fluid">
  <!-- Studio Hero Header -->
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
      <h3 class="mb-1 fw-bold" style="font-family:'Outfit',sans-serif;letter-spacing:-0.5px;">
        <i class="fa fa-chart-pie me-2" style="color:var(--primary);"></i>Dashboard Studio
      </h3>
      <div class="text-muted small">
        Pantau metrik latihan vokal, produktivitas coach, dan capaian target sesi studio secara real-time.
      </div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="badge" style="background:rgba(35,72,59,0.08);color:var(--primary);font-weight:600;padding:7px 14px;border-radius:20px;border:1px solid rgba(35,72,59,0.12);">
        <i class="fa fa-calendar-day me-1 text-accent"></i> <?= ($namaBulan[$bulanDipilih] ?? $bulanDipilih) . ' ' . $tahunDipilih ?>
      </span>
    </div>
  </div>

  <!-- 3 Bento KPI Cards -->
  <div class="row mb-4 g-3">
    <div class="col-md-4">
      <div class="card p-3 p-md-4 h-100 shadow-sm" style="border-left:4px solid var(--primary);">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <h6 class="text-muted mb-1" style="font-size:0.8rem;letter-spacing:0.5px;"><i class="fa fa-users me-1 text-primary"></i> Total Murid</h6>
            <h2 class="mb-0 fw-bold" style="font-variant-numeric:tabular-nums;font-family:'Outfit',sans-serif;color:var(--primary);"><?= $totalMuridCount ?></h2>
            <small class="text-muted"><strong class="text-success"><?= $activeMuridCount ?></strong> murid aktif terdaftar</small>
          </div>
          <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,var(--primary),var(--primary-light));display:flex;align-items:center;justify-content:center;box-shadow:0 6px 16px var(--primary-glow);flex-shrink:0;">
            <i class="fa fa-users" style="color:#fff;font-size:1.15rem;"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card p-3 p-md-4 h-100 shadow-sm" style="border-left:4px solid var(--accent);">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <h6 class="mb-0 text-muted" style="font-size:0.8rem;letter-spacing:0.5px;"><i class="fa fa-calendar-check me-1 text-accent"></i> Pertemuan / Target</h6>
          <form method="get" style="display:flex; gap:5px; align-items:center;">
            <select name="bulan" class="form-select form-select-sm" style="font-size:0.75rem;padding:3px 20px 3px 8px;min-width:70px;" onchange="this.form.submit()">
              <?php foreach ($staffAvailableMonthsForYear as $num): ?>
                <option value="<?= $num ?>" <?= $bulanDipilih == $num ? 'selected' : '' ?>><?= $namaBulan[$num] ?? $num ?></option>
              <?php endforeach; ?>
            </select>

            <select name="tahun" class="form-select form-select-sm" style="font-size:0.75rem;padding:3px 20px 3px 8px;min-width:65px;" onchange="this.form.submit()">
              <?php foreach ($staffAvailableYears as $t): ?>
                <option value="<?= $t ?>" <?= $tahunDipilih == $t ? 'selected' : '' ?>><?= $t ?></option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>

        <?php
        $rencanaSummary = $rencanaService->getSummary($tahunDipilih, $bulanDipilih, null);
        $totalRealisasi = (int)$rencanaSummary['total_realisasi'];
        $totalTarget = (int)$rencanaSummary['total_target'];
        $targetDetailList = $rencanaSummary['detail_list'];
        $persenBulanIni = $totalTarget > 0 ? (int)round(($totalRealisasi / $totalTarget) * 100) : ($totalRealisasi > 0 ? 100 : 0);
        ?>

        <h2 class="mt-1 mb-0" style="font-variant-numeric:tabular-nums;font-family:'Outfit',sans-serif;">
          <button type="button" class="count-link" style="color:var(--accent);font-weight:800;font-size:2rem;" data-bs-toggle="modal" data-bs-target="#modalPertemuanTargetDetail" title="Klik untuk melihat rincian murid dan target pertemuan">
            <?= $totalRealisasi ?> / <?= $totalTarget ?>
          </button>
        </h2>
        <div class="d-flex align-items-center justify-content-between mt-1">
          <small class="text-muted"><?= $namaBulan[$bulanDipilih] . ' ' . $tahunDipilih ?> (Klik rincian)</small>
          <span class="badge" style="background:rgba(197,160,89,0.15);color:var(--accent);font-size:0.75rem;font-weight:600;padding:3px 8px;border-radius:10px;">
            <?= $persenBulanIni ?>% tercapai
          </span>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card p-3 p-md-4 h-100 shadow-sm" style="border-left:4px solid var(--gold);">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <h6 class="mb-2"><i class="fa fa-chalkboard-user me-1"></i> Coach Terdaftar</h6>
            <?php $coachCount = $pdo->query("SELECT COUNT(*) FROM admin WHERE role IN ('coach','owner')")->fetchColumn(); ?>
            <h2 class="mb-0 fw-bold" style="font-variant-numeric:tabular-nums;font-family:'Outfit',sans-serif;color:#8a7548;"><?= $coachCount ?></h2>
            <small class="text-muted"><span class="d-inline-block rounded-circle bg-success me-1" style="width:7px;height:7px;"></span>Akun coach aktif mengajar</small>
          </div>
          <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,var(--gold),#8a7548);display:flex;align-items:center;justify-content:center;box-shadow:0 6px 16px var(--accent-glow);flex-shrink:0;">
            <i class="fa fa-chalkboard-user" style="color:#2D3436;font-size:1.15rem;"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Murid Dilatih per Coach Bento Table -->
  <div class="card p-3 p-md-4 mb-4 shadow-sm">
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
          <h5 class="mb-0 fw-bold" style="font-family:'Outfit',sans-serif;"><i class="fa fa-chart-bar me-2" style="color:var(--primary);"></i>Murid Dilatih per Coach</h5>
          <small class="text-muted">Distribusi jumlah murid dan sesi pertemuan yang diampu setiap coach pada periode ini</small>
        </div>
        <form method="get" style="display:flex; gap:5px; align-items:center;">
          <select name="bulan" class="form-select form-select-sm" style="font-size:0.8rem;" onchange="this.form.submit()">
            <?php foreach ($namaBulan as $num => $nama): ?>
              <option value="<?= $num ?>" <?= $bulanDipilih == $num ? 'selected' : '' ?>><?= $nama ?></option>
            <?php endforeach; ?>
          </select>
          <select name="tahun" class="form-select form-select-sm" style="font-size:0.8rem;" onchange="this.form.submit()">
            <?php for ($t = $tahunMulai; $t <= $tahunSekarang; $t++): ?>
              <option value="<?= $t ?>" <?= $tahunDipilih == $t ? 'selected' : '' ?>><?= $t ?></option>
            <?php endfor; ?>
          </select>
        </form>
      </div>
      <small class="text-muted">Periode: <?= $namaBulan[$bulanDipilih] . ' ' . $tahunDipilih ?></small>
      <div class="table-responsive">
        <table class="table table-striped table-hover">
          <thead>
            <tr>
              <th>Coach</th>
              <th>Murid Dilatih</th>
              <th>Total Pertemuan</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $stmtCoach = $pdo->prepare("SELECT a.id, COALESCE(NULLIF(a.nama,''), a.username) as nama,
                                      COUNT(DISTINCT p.id_murid) as total_murid,
                                      COUNT(p.id) as total_pertemuan
                                      FROM admin a
                                      LEFT JOIN pertemuan p ON p.coach_id=a.id AND YEAR(p.tanggal)=? AND MONTH(p.tanggal)=?
                                      WHERE a.role IN ('coach','owner')
                                      GROUP BY a.id
                                      ORDER BY total_murid DESC, nama ASC");
            $stmtCoach->execute([$tahunDipilih, $bulanDipilih]);
            $coachRows = $stmtCoach->fetchAll();

            $detailStmt = $pdo->prepare('SELECT p.coach_id, p.tanggal, m.nama AS murid_nama, l.judul AS lagu_judul
                                        FROM pertemuan p
                                        LEFT JOIN murid m ON p.id_murid=m.id
                                        LEFT JOIN lagu l ON p.id_lagu=l.id_lagu
                                        WHERE p.coach_id IS NOT NULL AND YEAR(p.tanggal)=? AND MONTH(p.tanggal)=?
                                        ORDER BY p.tanggal DESC');
            $detailStmt->execute([$tahunDipilih, $bulanDipilih]);
            $detailByCoach = [];
            foreach ($detailStmt->fetchAll() as $row) {
              $detailByCoach[$row['coach_id']][] = $row;
            }

            $stmtCoachMurid = $pdo->prepare('SELECT p.coach_id, m.nama, COUNT(p.id) as total_pertemuan
                                             FROM pertemuan p
                                             JOIN murid m ON p.id_murid = m.id
                                             WHERE p.coach_id IS NOT NULL AND YEAR(p.tanggal)=? AND MONTH(p.tanggal)=?
                                             GROUP BY p.coach_id, m.id
                                             ORDER BY total_pertemuan DESC, m.nama ASC');
            $stmtCoachMurid->execute([$tahunDipilih, $bulanDipilih]);
            $coachMuridRows = $stmtCoachMurid->fetchAll();
            $muridByCoach = [];
            foreach ($coachMuridRows as $row) {
              $muridByCoach[$row['coach_id']][] = $row;
            }
            foreach ($coachRows as $c): ?>
              <tr>
                <td><i class="fa fa-user-circle me-1" style="color:var(--primary-light);"></i><?= htmlspecialchars($c['nama']) ?></td>
                <td>
                  <?php if ((int)$c['total_murid'] > 0): ?>
                    <button type="button" class="count-link" data-bs-toggle="modal" data-bs-target="#modalCoachMuridDetail<?= $c['id'] ?>">
                      <?= $c['total_murid'] ?>
                    </button>
                  <?php else: ?>
                    0
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ((int)$c['total_pertemuan'] > 0): ?>
                    <button type="button" class="count-link" data-bs-toggle="modal" data-bs-target="#modalCoachDetail<?= $c['id'] ?>">
                      <?= $c['total_pertemuan'] ?>
                    </button>
                  <?php else: ?>
                    0
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php
      $modalsHtml = '';
      foreach ($coachRows as $c):
        if ((int)$c['total_pertemuan'] > 0):
          $rows = $detailByCoach[$c['id']] ?? [];
          ob_start(); ?>
          <div class="modal fade" id="modalCoachDetail<?= $c['id'] ?>" tabindex="-1" data-paginated-modal>
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Detail Pertemuan <?= htmlspecialchars($c['nama']) ?> (<?= $namaBulan[$bulanDipilih] . ' ' . $tahunDipilih ?>)</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="table-responsive">
                    <table class="table table-striped table-hover modal-paginated-table" data-rows-per-page="<?= $detailRowsPerPage ?>">
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
                            <td><?= htmlspecialchars($row['murid_nama']) ?></td>
                            <td><?= htmlspecialchars($row['tanggal']) ?></td>
                            <td><?= htmlspecialchars($row['lagu_judul'] ?: '-') ?></td>
                          </tr>
                        <?php endforeach; ?>
                        <?php if (!$rows): ?>
                          <tr class="modal-empty-row"><td colspan="3" class="text-muted text-center">Belum ada pertemuan di periode ini.</td></tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mt-3 d-none" data-pagination-controls>
                    <button type="button" class="btn btn-sm pager-btn" data-page-action="prev" aria-label="Halaman sebelumnya" title="Halaman sebelumnya"><i class="fa fa-chevron-left"></i></button>
                    <small class="text-muted" data-page-status></small>
                    <button type="button" class="btn btn-sm pager-btn" data-page-action="next" aria-label="Halaman berikutnya" title="Halaman berikutnya"><i class="fa fa-chevron-right"></i></button>
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
          ob_start(); ?>
          <div class="modal fade" id="modalCoachMuridDetail<?= $c['id'] ?>" tabindex="-1" data-paginated-modal>
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Daftar Murid Dilatih oleh <?= htmlspecialchars($c['nama']) ?> (<?= $namaBulan[$bulanDipilih] . ' ' . $tahunDipilih ?>)</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="table-responsive">
                    <table class="table table-striped table-hover modal-paginated-table" data-rows-per-page="<?= $detailRowsPerPage ?>">
                      <thead>
                        <tr>
                          <th>Nama Murid</th>
                          <th>Total Pertemuan</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($cMuridList as $row): ?>
                          <tr>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><span class="badge" style="background:rgba(74,107,93,0.12);color:var(--primary);font-size:0.85rem;"><?= $row['total_pertemuan'] ?></span></td>
                          </tr>
                        <?php endforeach; ?>
                        <?php if (!$cMuridList): ?>
                          <tr class="modal-empty-row"><td colspan="2" class="text-muted text-center">Belum ada murid yang dilatih di periode ini.</td></tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mt-3 d-none" data-pagination-controls>
                    <button type="button" class="btn btn-sm pager-btn" data-page-action="prev" aria-label="Halaman sebelumnya" title="Halaman sebelumnya"><i class="fa fa-chevron-left"></i></button>
                    <small class="text-muted" data-page-status></small>
                    <button type="button" class="btn btn-sm pager-btn" data-page-action="next" aria-label="Halaman berikutnya" title="Halaman berikutnya"><i class="fa fa-chevron-right"></i></button>
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
      endforeach; ?>
    </div>
</div>

<?= $modalsHtml ?>

<!-- Modal Detail Pertemuan / Target untuk Admin, Owner, dan Coach -->
<div class="modal fade" id="modalPertemuanTargetDetail" tabindex="-1" data-paginated-modal aria-labelledby="modalPertemuanTargetTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-0" id="modalPertemuanTargetTitle">
            <i class="fa fa-calendar-alt me-2" style="color:var(--primary);"></i>Rincian Pertemuan / Target (<?= $namaBulan[$bulanDipilih] . ' ' . $tahunDipilih ?>)
          </h5>
          <small class="text-muted">Daftar murid yang memiliki rencana pertemuan atau realisasi latihan di bulan ini.</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded flex-wrap gap-2" style="background:rgba(74,107,93,0.06);border:1px solid rgba(74,107,93,0.15);">
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
            <input type="text" class="form-control form-control-sm modal-search-input" placeholder="Cari nama murid…" style="max-width:240px;padding-left:30px;">
            <i class="fa fa-search position-absolute top-50 translate-middle-y text-muted" style="left:10px;font-size:0.8rem;"></i>
          </div>
          <div class="limit-switch">
            <button type="button" class="limit-pill active" data-limit="10">10</button>
            <button type="button" class="limit-pill" data-limit="20">20</button>
            <button type="button" class="limit-pill" data-limit="all">Semua</button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-striped table-hover modal-paginated-table align-middle" data-rows-per-page="<?= $detailRowsPerPage ?>">
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
                  $t = (int)$row['target_pertemuan'];
                  $r = (int)$row['realisasi'];
                  $persen = (int)$row['persentase'];
                  $isTercapai = (bool)$row['is_tercapai'];
                ?>
                <tr>
                  <td class="text-muted small"><?= $idx + 1 ?></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <?php if (!empty($row['foto'])): ?>
                        <img src="<?= htmlspecialchars($row['foto']) ?>" alt="Foto <?= htmlspecialchars($row['nama']) ?>" style="width:30px;height:30px;border-radius:8px;object-fit:cover;">
                      <?php else: ?>
                        <span class="avatar-empty-badge" style="width:30px;height:30px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;background:rgba(74,107,93,0.1);"><i class="fa fa-user text-muted" style="font-size:0.75rem;"></i></span>
                      <?php endif; ?>
                      <strong><?= htmlspecialchars($row['nama']) ?></strong>
                    </div>
                  </td>
                  <td>
                    <button type="button" 
                            class="btn btn-sm btn-realisasi-detail d-inline-flex align-items-center gap-1 <?= $r > 0 ? 'has-meetings' : 'zero-meetings' ?>" 
                            data-murid-id="<?= (int)$row['murid_id'] ?>" 
                            data-murid-nama="<?= htmlspecialchars($row['nama']) ?>" 
                            data-tahun="<?= $tahunDipilih ?>" 
                            data-bulan="<?= $bulanDipilih ?>"
                            data-realisasi-count="<?= $r ?>"
                            data-bs-toggle="modal" 
                            data-bs-target="#modalDetailRealisasi"
                            title="Klik untuk melihat tanggal sesi pertemuan <?= htmlspecialchars($row['nama']) ?>">
                      <i class="fa fa-calendar-check" style="color:var(--accent);"></i>
                      <strong style="font-variant-numeric:tabular-nums;"><?= $r ?></strong> sesi
                      <?php if ($r > 0): ?>
                        <i class="fa fa-chevron-right ms-1 opacity-50" style="font-size:0.65rem;"></i>
                      <?php endif; ?>
                    </button>
                  </td>
                  <td>
                    <?php if ($t > 0): ?>
                      <span class="badge rounded-pill bg-light text-dark border px-2 py-1" style="font-size:0.82rem;font-variant-numeric:tabular-nums;">
                        <i class="fa fa-bullseye me-1" style="color:var(--primary);"></i><?= $t ?> sesi
                      </span>
                    <?php else: ?>
                      <span class="text-muted small">-</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($t > 0): ?>
                      <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1" style="height:6px;min-width:60px;">
                          <div class="progress-bar <?= $isTercapai ? 'bg-success' : 'bg-primary' ?>" style="width:<?= min(100, $persen) ?>%;"></div>
                        </div>
                        <span class="small fw-semibold <?= $isTercapai ? 'text-success' : 'text-muted' ?>" style="font-size:0.75rem;min-width:32px;font-variant-numeric:tabular-nums;">
                          <?= $persen ?>%
                        </span>
                      </div>
                    <?php else: ?>
                      <span class="badge bg-secondary-subtle text-secondary small">Tanpa Target</span>
                    <?php endif; ?>
                  </td>
                  <td class="small text-muted"><?= htmlspecialchars($row['coach_nama'] ?: '-') ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($targetDetailList)): ?>
                <tr class="modal-empty-row">
                  <td colspan="6" class="text-muted text-center py-4">
                    Belum ada rencana pertemuan atau sesi latihan untuk periode <?= $namaBulan[$bulanDipilih] . ' ' . $tahunDipilih ?>.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 d-none" data-pagination-controls>
          <small class="text-muted" data-page-summary></small>
          <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm pager-btn" data-page-action="prev" aria-label="Halaman sebelumnya" title="Halaman sebelumnya"><i class="fa fa-chevron-left"></i></button>
            <small class="text-muted" data-page-status></small>
            <button type="button" class="btn btn-sm pager-btn" data-page-action="next" aria-label="Halaman berikutnya" title="Halaman berikutnya"><i class="fa fa-chevron-right"></i></button>
          </div>
        </div>
      </div>
      <div class="modal-footer d-flex justify-content-between">
        <a href="rencana_pertemuan.php?tahun=<?= $tahunDipilih ?>&bulan=<?= $bulanDipilih ?>" class="btn btn-sm btn-outline-primary">
          <i class="fa fa-sliders-h me-1"></i> Atur Rencana Pertemuan
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

    let defaultPerPage = parseInt(table.getAttribute('data-rows-per-page'), 10) || 10;
    let perPage = defaultPerPage;
    let currentPage = 0;
    let filterQuery = '';

    function getMatchingRows() {
      if (!filterQuery) return allRows;
      return allRows.filter(row => {
        return row.textContent.toLowerCase().includes(filterQuery);
      });
    }

    function renderPage(page) {
      const matchingRows = getMatchingRows();
      const effectivePerPage = perPage === 'all' ? Math.max(1, matchingRows.length) : perPage;
      const totalPages = Math.max(1, Math.ceil(matchingRows.length / effectivePerPage));
      currentPage = Math.max(0, Math.min(page, totalPages - 1));

      allRows.forEach(r => r.style.display = 'none');

      let noMatchRow = tbody.querySelector('.modal-no-match-row');
      if (matchingRows.length === 0) {
        pagination.classList.add('d-none');
        if (emptyRow) emptyRow.style.display = allRows.length === 0 ? '' : 'none';
        if (!noMatchRow && allRows.length > 0) {
          noMatchRow = document.createElement('tr');
          noMatchRow.className = 'modal-no-match-row';
          const colCount = table.querySelectorAll('thead th').length || 5;
          noMatchRow.innerHTML = `<td colspan="${colCount}" class="text-muted text-center py-4"><i class="fa fa-search me-2 opacity-50"></i>Tidak ada data murid yang cocok.</td>`;
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
        if (index >= startIdx && index < endIdx) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });

      if (summaryEl) {
        const dStart = startIdx + 1;
        const dEnd = Math.min(endIdx, matchingRows.length);
        summaryEl.textContent = `Menampilkan ${dStart}-${dEnd} dari ${matchingRows.length} data${filterQuery ? ' (difilter)' : ''}`;
      }

      if (limitPills.length > 0) {
        limitPills.forEach(pill => {
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

    limitPills.forEach(pill => {
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
});
</script>
