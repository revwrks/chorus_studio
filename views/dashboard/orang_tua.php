<?php
$periodeLabel = ($namaBulan[$bulanDipilih] ?? (string)$bulanDipilih) . ' ' . $tahunDipilih;
?>
<div class="container-fluid">
  <!-- Header & Switcher Anak -->
  <div class="page-header">
    <div>
      <h1 class="page-title">
        <i class="fa fa-home me-2" style="color:var(--primary);" aria-hidden="true"></i>Dashboard Orang Tua
      </h1>
      <p class="text-muted small mb-0 mt-1">
        Pantau perkembangan latihan vokal ananda di Chorus Studio.
      </p>
    </div>
    <?php if (count($anakList) > 1): ?>
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="small text-muted fw-semibold">Pilih Profil Anak:</span>
        <div class="d-flex flex-wrap gap-2" role="group" aria-label="Pilih Profil Anak">
          <?php foreach ($anakList as $anak):
            $isActiveAnak = ($activeChildId === (int)$anak['id']);
          ?>
            <a href="?anak_id=<?= (int)$anak['id'] ?>&bulan=<?= (int)$bulanDipilih ?>&tahun=<?= (int)$tahunDipilih ?>"
               class="btn btn-sm <?= $isActiveAnak ? 'btn-primary' : 'btn-outline-secondary' ?>"
               <?= $isActiveAnak ? 'aria-current="true"' : '' ?>>
              <i class="fa fa-child me-1" aria-hidden="true"></i><?= e($anak['nama'] ?? '') ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <?php if (!$activeChildId): ?>
    <div class="card empty-state shadow-sm">
      <div class="mb-3">
        <i class="fa fa-user-slash fa-3x text-muted" aria-hidden="true"></i>
      </div>
      <h2 class="h4">Akun Belum Dihubungkan ke Data Murid</h2>
      <p class="text-muted mb-0 mx-auto" style="max-width:480px;">
        Akun orang tua Anda belum dihubungkan dengan data murid manapun oleh admin. Silakan konfirmasi ke pihak studio atau coach agar dapat memantau perkembangan latihan.
      </p>
    </div>
  <?php else: ?>

    <!-- Card Profil Singkat Anak & Filter Bulan -->
    <div class="card p-3 p-md-4 mb-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
          <div style="width:58px;height:58px;border-radius:16px;background:var(--primary);display:flex;align-items:center;justify-content:center;box-shadow:var(--shadow);border:2px solid var(--accent);overflow:hidden;flex-shrink:0;">
            <?php if (!empty($activeChild['foto'])): ?>
              <img src="<?= e($activeChild['foto']) ?>" alt="Foto <?= e($activeChild['nama'] ?? '') ?>" style="width:100%;height:100%;object-fit:cover;">
            <?php else: ?>
              <i class="fa fa-user-graduate" style="color:#FFFFFF;font-size:1.5rem;" aria-hidden="true"></i>
            <?php endif; ?>
          </div>
          <div>
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
              <span class="bento-badge" style="background:var(--accent-soft);color:var(--accent-ink);">
                <i class="fa fa-child" aria-hidden="true"></i>Data Latihan Ananda
              </span>
              <?php if (!empty($activeChild['status'])):
                $isAktifChild = strtolower((string)$activeChild['status']) === 'aktif';
              ?>
                <span class="badge <?= $isAktifChild ? 'badge-status-aktif' : 'badge-status-nonaktif' ?>">
                  <?= e($isAktifChild ? 'Aktif' : 'Non-Aktif') ?>
                </span>
              <?php endif; ?>
            </div>
            <h2 class="h4 mb-0 fw-bold" style="color:var(--text);letter-spacing:-0.02em;"><?= e($activeChild['nama'] ?? '') ?></h2>
            <?php if (!empty($activeChild['instagram'])): ?>
              <small class="text-muted"><i class="fab fa-instagram me-1 text-accent" aria-hidden="true"></i><?= e($activeChild['instagram']) ?></small>
            <?php endif; ?>
          </div>
        </div>

        <!-- Periode Filter Form -->
        <form method="get" class="d-flex align-items-center gap-2 m-0 flex-wrap p-2 rounded-3" style="background:var(--surface-hover);border:1px solid var(--border);">
          <?php if (isset($_GET['anak_id'])): ?>
            <input type="hidden" name="anak_id" value="<?= (int)$_GET['anak_id'] ?>">
          <?php endif; ?>
          <span class="small text-muted d-none d-sm-inline fw-semibold">
            <i class="fa fa-calendar-alt me-1 text-accent" aria-hidden="true"></i>Periode:
          </span>
          <label for="selectBulanOrtu" class="visually-hidden">Pilih Bulan</label>
          <select name="bulan" id="selectBulanOrtu" class="form-select form-select-sm" style="width:auto;" aria-label="Pilih Bulan" onchange="this.form.submit()">
            <?php foreach ($availableMonthsForYear as $num): ?>
              <option value="<?= (int)$num ?>" <?= (int)$bulanDipilih === (int)$num ? 'selected' : '' ?>><?= e($namaBulan[$num] ?? (string)$num) ?></option>
            <?php endforeach; ?>
          </select>
          <label for="selectTahunOrtu" class="visually-hidden">Pilih Tahun</label>
          <select name="tahun" id="selectTahunOrtu" class="form-select form-select-sm" style="width:auto;" aria-label="Pilih Tahun" onchange="this.form.submit()">
            <?php foreach ($availableYears as $t): ?>
              <option value="<?= (int)$t ?>" <?= (int)$tahunDipilih === (int)$t ? 'selected' : '' ?>><?= (int)$t ?></option>
            <?php endforeach; ?>
          </select>
        </form>
      </div>
    </div>

    <!-- 3 KPI Cards -->
    <div class="kpi-grid mb-4">
      <div class="metric-card">
        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
          <div>
            <span class="bento-badge mb-2">
              <i class="fa fa-calendar-check" aria-hidden="true"></i>Pertemuan / Target
            </span>
            <div class="bento-metric-number" style="color:var(--primary);">
              <?= (int)$totalSesiBulanIni ?> <span style="font-size:1.1rem;font-weight:500;color:var(--text-muted);">/ <?= (int)$targetPertemuanBulanIni > 0 ? (int)$targetPertemuanBulanIni : '-' ?></span>
            </div>
          </div>
          <div class="bento-icon-box" style="background:var(--primary-soft);color:var(--primary);">
            <i class="fa fa-calendar-check" aria-hidden="true"></i>
          </div>
        </div>
        <div class="text-muted small">
          Pertemuan Bulan Ini di <?= e($periodeLabel) ?><?= (int)$targetPertemuanBulanIni > 0 ? ' (Target: ' . (int)$targetPertemuanBulanIni . ' sesi)' : '' ?>
        </div>
        <?php if ((int)$targetPertemuanBulanIni > 0):
          $pct = min(100, (int)round(((int)$totalSesiBulanIni / (int)$targetPertemuanBulanIni) * 100));
        ?>
          <div class="progress mt-2" style="height:6px;border-radius:10px;background:var(--surface-subtle);">
            <div class="progress-bar" role="progressbar" style="width:<?= $pct ?>%;background-color:var(--primary);" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100" aria-label="Capaian pertemuan bulan ini"></div>
          </div>
          <div class="d-flex justify-content-between text-muted" style="font-size:0.75rem;margin-top:4px;font-variant-numeric:tabular-nums;">
            <span>Tercapai <?= $pct ?>%</span>
            <span><?= (int)$totalSesiBulanIni ?> dari <?= (int)$targetPertemuanBulanIni ?> sesi</span>
          </div>
        <?php endif; ?>
      </div>

      <div class="metric-card">
        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
          <div>
            <span class="bento-badge mb-2" style="background:var(--accent-soft);color:var(--accent-ink);">
              <i class="fa fa-music" aria-hidden="true"></i>Materi Vokal
            </span>
            <div class="bento-metric-number" style="color:var(--accent);">
              <?= (int)$totalLaguBulanIni ?>
            </div>
          </div>
          <div class="bento-icon-box" style="background:var(--accent-soft);color:var(--accent-ink);">
            <i class="fa fa-music" aria-hidden="true"></i>
          </div>
        </div>
        <div class="text-muted small mt-auto">
          Materi lagu dipelajari pada <?= e($periodeLabel) ?>
        </div>
      </div>

      <div class="metric-card justify-content-between">
        <div>
          <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
            <div>
              <span class="bento-badge mb-2" style="background:var(--accent-soft);color:var(--accent-ink);">
                <i class="fa fa-headphones" aria-hidden="true"></i>Audio Pendukung
              </span>
              <div class="bento-metric-number" style="color:var(--accent-ink);">
                <?= (int)$totalIringan ?>
              </div>
            </div>
            <div class="bento-icon-box" style="background:var(--accent-soft);color:var(--accent-ink);">
              <i class="fa fa-headphones" aria-hidden="true"></i>
            </div>
          </div>
          <div class="text-muted small">
            Lagu iringan tersedia untuk latihan mandiri
          </div>
        </div>
        <div class="mt-3 pt-2 border-top">
          <a href="iringan.php" class="btn btn-sm w-100 d-flex align-items-center justify-content-between" style="background:var(--surface-hover);color:var(--primary);border:1px solid var(--border);">
            <span>Buka Halaman Lagu Iringan</span>
            <i class="fa fa-arrow-right" aria-hidden="true"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Progress Latihan Anak Bulan Ini (Riwayat Pertemuan & Catatan Coach) -->
    <div class="card p-3 p-md-4 mb-4">
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
          <h2 class="h5 mb-1 fw-bold" style="color:var(--text);letter-spacing:-0.02em;">
            <i class="fa fa-clipboard-list me-2" style="color:var(--primary);" aria-hidden="true"></i>Progress Latihan Bulan Ini
          </h2>
          <small class="text-muted">Catatan materi dan perkembangan ananda dari coach per tanggal sesi</small>
        </div>
        <span class="bento-badge">
          <i class="fa fa-calendar-alt" aria-hidden="true"></i><?= e($periodeLabel) ?>
        </span>
      </div>

      <?php if (empty($riwayatPertemuan)): ?>
        <div class="empty-state rounded-3" style="background:var(--surface-hover);border:1px dashed var(--border);">
          <i class="fa fa-calendar-times fa-2x mb-2 text-muted" aria-hidden="true"></i>
          <p class="mb-0">Belum ada catatan sesi pertemuan di bulan <strong><?= e($periodeLabel) ?></strong>.</p>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Coach</th>
                <th>Lagu / Materi</th>
                <th>Catatan &amp; Hasil Latihan Coach</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($riwayatPertemuan as $sesi): ?>
                <tr>
                  <td style="font-variant-numeric:tabular-nums;">
                    <div class="fw-semibold text-nowrap">
                      <i class="fa fa-calendar me-1 text-accent" aria-hidden="true"></i><?= e($sesi['tanggal'] ?? '') ?>
                    </div>
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div style="width:28px;height:28px;border-radius:8px;background:var(--primary-soft);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:0.75rem;flex-shrink:0;">
                        <i class="fa fa-chalkboard-user" aria-hidden="true"></i>
                      </div>
                      <span class="fw-medium"><?= e(($sesi['coach_nama'] ?? '') !== '' ? $sesi['coach_nama'] : '-') ?></span>
                    </div>
                  </td>
                  <td>
                    <?php if (!empty($sesi['lagu_judul'])): ?>
                      <div class="fw-semibold" style="color:var(--primary);">
                        <i class="fa fa-music me-1" aria-hidden="true"></i><?= e($sesi['lagu_judul']) ?>
                      </div>
                      <?php if (!empty($sesi['tingkat'])):
                        $tingkatSlug = preg_replace('/[^a-z]/', '', strtolower((string)$sesi['tingkat']));
                        $levelClass = in_array($tingkatSlug, ['mudah', 'menengah', 'sedang', 'sulit', 'festival'], true)
                          ? 'badge-level-' . $tingkatSlug
                          : 'bento-badge';
                      ?>
                        <span class="badge <?= e($levelClass) ?> mt-1"><?= e(ucfirst((string)$sesi['tingkat'])) ?></span>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class="text-muted"><i class="fa fa-microphone me-1" aria-hidden="true"></i>Latihan Vokal / Teknik</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if (!empty($sesi['catatan'])): ?>
                      <div class="p-2 px-3 rounded-3" style="background:var(--surface-subtle);border:1px solid var(--border);font-size:0.88rem;line-height:1.5;">
                        <?= nl2br(e($sesi['catatan'])) ?>
                      </div>
                    <?php else: ?>
                      <span class="text-muted small">Tidak ada catatan tertulis.</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- Catatan Refleksi & Evaluasi (Jika Ada) -->
    <?php if ($refleksiBulan):
      $isPreviousPeriod = ((int)$refleksiBulan['tahun'] !== (int)$tahunDipilih || (int)$refleksiBulan['bulan'] !== (int)$bulanDipilih);
      $refleksiPeriodLabel = ($namaBulan[(int)$refleksiBulan['bulan']] ?? (string)$refleksiBulan['bulan']) . ' ' . $refleksiBulan['tahun'];
    ?>
      <div class="card p-3 p-md-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
          <h2 class="h5 fw-bold mb-0" style="color:var(--text);letter-spacing:-0.02em;">
            <i class="fa fa-lightbulb me-2 text-accent" aria-hidden="true"></i>Refleksi &amp; Rekomendasi Coach
          </h2>
          <?php if ($isPreviousPeriod): ?>
            <span class="bento-badge" style="background:var(--accent-soft);color:var(--accent-ink);">
              <i class="fa fa-history" aria-hidden="true"></i>Catatan Terakhir: <?= e($refleksiPeriodLabel) ?>
            </span>
          <?php else: ?>
            <span class="bento-badge">
              <i class="fa fa-calendar-check" aria-hidden="true"></i>Periode <?= e($refleksiPeriodLabel) ?>
            </span>
          <?php endif; ?>
        </div>
        <?php if ($isPreviousPeriod): ?>
          <div class="small text-muted mb-3">
            <i class="fa fa-info-circle me-1 text-accent" aria-hidden="true"></i>Belum ada catatan baru untuk periode <?= e($periodeLabel) ?>. Menampilkan arahan dan evaluasi terakhir dari Coach.
          </div>
        <?php endif; ?>
        <div class="row g-3">
          <?php if (!empty($refleksiBulan['refleksi'])): ?>
            <div class="col-md-4">
              <div class="p-3 rounded-3 h-100" style="background:var(--primary-soft);border:1px solid var(--border);">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <div style="width:26px;height:26px;border-radius:6px;background:var(--surface);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:0.75rem;">
                    <i class="fa fa-check" aria-hidden="true"></i>
                  </div>
                  <h3 class="h6 fw-bold mb-0" style="color:var(--primary);">Perkembangan Siswa</h3>
                </div>
                <p class="small text-muted mb-0" style="line-height:1.55;"><?= nl2br(e($refleksiBulan['refleksi'])) ?></p>
              </div>
            </div>
          <?php endif; ?>

          <?php if (!empty($refleksiBulan['rencana_perbaikan'])): ?>
            <div class="col-md-4">
              <div class="p-3 rounded-3 h-100" style="background:var(--accent-soft);border:1px solid var(--border);">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <div style="width:26px;height:26px;border-radius:6px;background:var(--surface);color:var(--accent-ink);display:flex;align-items:center;justify-content:center;font-size:0.75rem;">
                    <i class="fa fa-bullseye" aria-hidden="true"></i>
                  </div>
                  <h3 class="h6 fw-bold mb-0" style="color:var(--accent-ink);">Rencana &amp; Target Selanjutnya</h3>
                </div>
                <p class="small text-muted mb-0" style="line-height:1.55;"><?= nl2br(e($refleksiBulan['rencana_perbaikan'])) ?></p>
              </div>
            </div>
          <?php endif; ?>

          <?php if (!empty($refleksiBulan['saran'])): ?>
            <div class="col-md-4">
              <div class="p-3 rounded-3 h-100" style="background:var(--info-soft);border:1px solid var(--border);">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <div style="width:26px;height:26px;border-radius:6px;background:var(--surface);color:var(--info);display:flex;align-items:center;justify-content:center;font-size:0.75rem;">
                    <i class="fa fa-heart" aria-hidden="true"></i>
                  </div>
                  <h3 class="h6 fw-bold mb-0" style="color:var(--info);">Saran Latihan di Rumah</h3>
                </div>
                <p class="small text-muted mb-0" style="line-height:1.55;"><?= nl2br(e($refleksiBulan['saran'])) ?></p>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Unduh Laporan PDF Anak -->
    <div class="card p-3 p-md-4 mb-4" style="background:var(--primary-soft);">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
          <div class="bento-icon-box" style="background:var(--primary-btn-bg);color:var(--primary-btn-text);box-shadow:var(--shadow-sm);">
            <i class="fa fa-file-pdf" aria-hidden="true"></i>
          </div>
          <div>
            <h2 class="h6 mb-1 fw-bold">Unduh Laporan Kemajuan Belajar</h2>
            <div class="text-muted small">Rangkuman hasil latihan vokal dan evaluasi resmi dalam format dokumen PDF studio.</div>
          </div>
        </div>
        <a href="generate_pdf.php?id_murid=<?= (int)$activeChildId ?>&tahun=<?= (int)$tahunDipilih ?>&bulan=<?= (int)$bulanDipilih ?>" target="_blank" class="btn btn-primary d-inline-flex align-items-center gap-2">
          <i class="fa fa-download" aria-hidden="true"></i>
          <span>Download PDF (<?= e($periodeLabel) ?>)</span>
        </a>
      </div>
    </div>
  <?php endif; ?>
</div>
