<div class="container-fluid">
  <!-- Header & Switcher Anak -->
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h3 class="mb-1" style="font-family:'Outfit',sans-serif;font-weight:700;">
        <i class="fa fa-home me-2" style="color:var(--primary);"></i>Dashboard Orang Tua
      </h3>
      <div class="text-muted" style="font-size:0.9rem;">
        Selamat datang di portal pantau hasil latihan vokal Chorus Studio.
      </div>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <?php if (count($anakList) > 1): ?>
        <div class="d-flex align-items-center gap-2">
          <span class="small text-muted">Pilih Profil Anak:</span>
          <div class="btn-group btn-group-sm" role="group">
            <?php foreach ($anakList as $anak): ?>
              <a href="?anak_id=<?= (int)$anak['id'] ?>&bulan=<?= $bulanDipilih ?>&tahun=<?= $tahunDipilih ?>" class="btn <?= ($activeChildId === (int)$anak['id']) ? 'btn-primary' : 'btn-outline-secondary' ?>">
                <i class="fa fa-child me-1"></i><?= htmlspecialchars($anak['nama']) ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if (!$activeChildId): ?>
    <div class="card p-5 text-center shadow-sm">
      <div class="mb-3">
        <i class="fa fa-user-slash fa-3x text-muted"></i>
      </div>
      <h4>Akun Belum Dihubungkan ke Data Murid</h4>
      <p class="text-muted mb-0" style="max-width:480px;margin:0 auto;">
        Akun orang tua Anda belum dihubungkan dengan data murid manapun oleh admin. Silakan konfirmasi ke pihak studio atau coach agar dapat memantau perkembangan latihan.
      </p>
    </div>
  <?php else: ?>

    <!-- Card Profil Singkat Anak & Filter Bulan -->
    <div class="card bento-hero-card p-3 p-md-4 mb-4" style="border-left: 4px solid var(--accent);">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
          <div style="width:58px;height:58px;border-radius:16px;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;box-shadow:var(--shadow-md);border:2px solid var(--accent);overflow:hidden;">
            <?php if (!empty($activeChild['foto'])): ?>
              <img src="<?= htmlspecialchars($activeChild['foto']) ?>" alt="Foto <?= htmlspecialchars($activeChild['nama']) ?>" style="width:100%;height:100%;object-fit:cover;">
            <?php else: ?>
              <i class="fa fa-user-graduate" style="color:#fff;font-size:1.6rem;"></i>
            <?php endif; ?>
          </div>
          <div>
            <div class="d-flex align-items-center gap-2 mb-1">
              <span class="badge bento-badge" style="background:rgba(197,160,89,0.18);color:var(--accent);">
                <i class="fa fa-child me-1"></i>Data Latihan Ananda
              </span>
              <?php if (!empty($activeChild['status'])): ?>
                <span class="badge <?= ($activeChild['status'] === 'aktif') ? 'bg-success' : 'bg-secondary' ?>" style="font-size:0.7rem;padding:3px 8px;border-radius:12px;">
                  <?= ucfirst($activeChild['status']) ?>
                </span>
              <?php endif; ?>
            </div>
            <h4 class="mb-0 fw-bold" style="font-family:'Outfit',sans-serif;color:var(--text);letter-spacing:-0.02em;"><?= htmlspecialchars($activeChild['nama']) ?></h4>
            <?php if (!empty($activeChild['instagram'])): ?>
              <small class="text-muted"><i class="fab fa-instagram me-1" style="color:var(--accent);"></i><?= htmlspecialchars($activeChild['instagram']) ?></small>
            <?php endif; ?>
          </div>
        </div>

        <!-- Periode Filter Form -->
        <form method="get" class="d-flex align-items-center gap-2 m-0 flex-wrap p-2 rounded-3" style="background:var(--card-hover-bg);border:1px solid var(--border);">
          <?php if (isset($_GET['anak_id'])): ?>
            <input type="hidden" name="anak_id" value="<?= (int)$_GET['anak_id'] ?>">
          <?php endif; ?>
          <span class="small text-muted d-none d-sm-inline fw-semibold"><i class="fa fa-calendar-alt me-1 text-accent"></i>Periode:</span>
          <select name="bulan" id="selectBulanOrtu" class="form-select form-select-sm" style="width:auto;border-radius:8px;" aria-label="Pilih Bulan" onchange="this.form.submit()">
            <?php foreach ($availableMonthsForYear as $num): ?>
              <option value="<?= $num ?>" <?= $bulanDipilih == $num ? 'selected' : '' ?>><?= $namaBulan[$num] ?? $num ?></option>
            <?php endforeach; ?>
          </select>
          <select name="tahun" id="selectTahunOrtu" class="form-select form-select-sm" style="width:auto;border-radius:8px;" aria-label="Pilih Tahun" onchange="this.form.submit()">
            <?php foreach ($availableYears as $t): ?>
              <option value="<?= $t ?>" <?= $tahunDipilih == $t ? 'selected' : '' ?>><?= $t ?></option>
            <?php endforeach; ?>
          </select>
        </form>
      </div>
    </div>

    <!-- 3 KPI Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="card bento-hero-card p-3 h-100" style="border-top:3px solid var(--primary);">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
              <span class="badge bento-badge mb-1" style="background:rgba(35,72,59,0.1);color:var(--primary);">
                <i class="fa fa-calendar-check me-1"></i>Pertemuan / Target
              </span>
              <div class="bento-metric-number" style="color:var(--primary);">
                <?= $totalSesiBulanIni ?> <span style="font-size:1.1rem;font-weight:500;color:var(--text-muted);">/ <?= $targetPertemuanBulanIni > 0 ? $targetPertemuanBulanIni : '-' ?></span>
              </div>
            </div>
            <div class="bento-icon-box" style="background:rgba(35,72,59,0.1);color:var(--primary);">
              <i class="fa fa-calendar-check fa-lg"></i>
            </div>
          </div>
          <div class="text-muted small mb-2">
            Pertemuan Bulan Ini di <?= $namaBulan[$bulanDipilih] . ' ' . $tahunDipilih ?><?= $targetPertemuanBulanIni > 0 ? " (Target: {$targetPertemuanBulanIni} sesi)" : "" ?>
          </div>
          <?php if ($targetPertemuanBulanIni > 0): 
            $pct = min(100, round(($totalSesiBulanIni / $targetPertemuanBulanIni) * 100));
          ?>
            <div class="progress" style="height:6px;border-radius:10px;background:rgba(0,0,0,0.06);">
              <div class="progress-bar" role="progressbar" style="width:<?= $pct ?>%;background:var(--primary);" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="d-flex justify-content-between text-muted" style="font-size:0.75rem;margin-top:4px;">
              <span>Tercapai <?= $pct ?>%</span>
              <span><?= $totalSesiBulanIni ?> dari <?= $targetPertemuanBulanIni ?> sesi</span>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card bento-hero-card p-3 h-100" style="border-top:3px solid var(--accent);">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
              <span class="badge bento-badge mb-1" style="background:rgba(197,160,89,0.15);color:var(--accent);">
                <i class="fa fa-music me-1"></i>Materi Vokal
              </span>
              <div class="bento-metric-number" style="color:var(--accent);">
                <?= $totalLaguBulanIni ?>
              </div>
            </div>
            <div class="bento-icon-box" style="background:rgba(197,160,89,0.15);color:var(--accent);">
              <i class="fa fa-music fa-lg"></i>
            </div>
          </div>
          <div class="text-muted small">
            Materi lagu dipelajari bulan ini di <?= $namaBulan[$bulanDipilih] . ' ' . $tahunDipilih ?>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card bento-hero-card p-3 h-100 d-flex flex-column justify-content-between" style="border-top:3px solid var(--gold);">
          <div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div>
                <span class="badge bento-badge mb-1" style="background:rgba(212,175,55,0.15);color:#8a7548;">
                  <i class="fa fa-headphones me-1"></i>Audio Pendukung
                </span>
                <div class="bento-metric-number" style="color:#8a7548;">
                  <?= $totalIringan ?>
                </div>
              </div>
              <div class="bento-icon-box" style="background:rgba(212,175,55,0.15);color:#8a7548;">
                <i class="fa fa-headphones fa-lg"></i>
              </div>
            </div>
            <div class="text-muted small">
              Lagu iringan tersedia di Google Drive studio
            </div>
          </div>
          <div class="mt-3 pt-2 border-top">
            <a href="iringan.php" class="btn btn-sm w-100 d-flex align-items-center justify-content-between" style="background:var(--card-hover-bg);color:var(--primary);border:1px solid var(--border);border-radius:8px;font-weight:600;">
              <span>Buka Halaman Lagu Iringan</span>
              <i class="fa fa-arrow-right"></i>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Progress Latihan Anak Bulan Ini (Riwayat Pertemuan & Catatan Coach) -->
    <div class="card bento-hero-card p-4 mb-4">
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
          <h5 class="mb-1 fw-bold" style="font-family:'Outfit',sans-serif;color:var(--text);letter-spacing:-0.02em;">
            <i class="fa fa-clipboard-list me-2" style="color:var(--primary);"></i>Progress Latihan Bulan Ini
          </h5>
          <small class="text-muted">Catatan detail materi dan perkembangan ananda dari coach per tanggal sesi</small>
        </div>
        <span class="badge bento-badge" style="background:rgba(35,72,59,0.1);color:var(--primary);">
          <i class="fa fa-calendar-alt me-1"></i><?= $namaBulan[$bulanDipilih] . ' ' . $tahunDipilih ?>
        </span>
      </div>

      <?php if (empty($riwayatPertemuan)): ?>
        <div class="p-4 text-center text-muted rounded-3" style="background:var(--card-hover-bg);border:1px dashed var(--border);">
          <i class="fa fa-calendar-times fa-2x mb-2" style="color:var(--text-muted);opacity:0.6;"></i>
          <p class="mb-0">Belum ada catatan sesi pertemuan di bulan <strong><?= $namaBulan[$bulanDipilih] . ' ' . $tahunDipilih ?></strong>.</p>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr style="border-bottom:2px solid var(--border);">
                <th style="width:140px;color:var(--text-muted);font-weight:600;font-size:0.82rem;text-transform:uppercase;letter-spacing:0.5px;">Tanggal</th>
                <th style="width:160px;color:var(--text-muted);font-weight:600;font-size:0.82rem;text-transform:uppercase;letter-spacing:0.5px;">Coach</th>
                <th style="width:220px;color:var(--text-muted);font-weight:600;font-size:0.82rem;text-transform:uppercase;letter-spacing:0.5px;">Lagu / Materi</th>
                <th style="color:var(--text-muted);font-weight:600;font-size:0.82rem;text-transform:uppercase;letter-spacing:0.5px;">Catatan & Hasil Latihan Coach</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($riwayatPertemuan as $sesi): ?>
                <tr>
                  <td>
                    <div class="fw-semibold text-nowrap"><i class="fa fa-calendar me-1" style="color:var(--accent);"></i><?= htmlspecialchars($sesi['tanggal']) ?></div>
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div style="width:28px;height:28px;border-radius:8px;background:rgba(35,72,59,0.1);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:0.75rem;">
                        <i class="fa fa-chalkboard-user"></i>
                      </div>
                      <span class="fw-medium"><?= htmlspecialchars($sesi['coach_nama'] ?: '-') ?></span>
                    </div>
                  </td>
                  <td>
                    <?php if (!empty($sesi['lagu_judul'])): ?>
                      <div class="fw-semibold text-primary"><i class="fa fa-music me-1"></i><?= htmlspecialchars($sesi['lagu_judul']) ?></div>
                      <?php if (!empty($sesi['tingkat'])): ?>
                        <span class="badge" style="background:rgba(197,160,89,0.18);color:var(--accent);font-size:0.7rem;padding:3px 8px;border-radius:10px;"><?= ucfirst($sesi['tingkat']) ?></span>
                      <?php endif; ?>
                    <?php else: ?>
                      <span class="text-muted"><i class="fa fa-microphone me-1"></i>Latihan Vokal / Teknik</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if (!empty($sesi['catatan'])): ?>
                      <div class="p-2 px-3 rounded-3" style="background:rgba(35,72,59,0.04);border-left:3px solid var(--primary);font-size:0.88rem;line-height:1.5;">
                        <?= nl2br(htmlspecialchars($sesi['catatan'])) ?>
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
      $isPreviousPeriod = ((int)$refleksiBulan['tahun'] !== $tahunDipilih || (int)$refleksiBulan['bulan'] !== $bulanDipilih);
      $refleksiPeriodLabel = ($namaBulan[(int)$refleksiBulan['bulan']] ?? $refleksiBulan['bulan']) . ' ' . $refleksiBulan['tahun'];
    ?>
      <div class="card bento-hero-card p-4 mb-4" style="border-left:4px solid var(--accent);">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
          <h5 class="fw-bold mb-0" style="font-family:'Outfit',sans-serif;color:var(--text);letter-spacing:-0.02em;">
            <i class="fa fa-lightbulb me-2" style="color:var(--accent);"></i>Refleksi & Rekomendasi Coach
          </h5>
          <?php if ($isPreviousPeriod): ?>
            <span class="badge bento-badge" style="background:rgba(197,160,89,0.18);color:#8a7548;border:1px solid rgba(197,160,89,0.3);">
              <i class="fa fa-history me-1"></i>Catatan Terakhir: <?= htmlspecialchars($refleksiPeriodLabel) ?>
            </span>
          <?php else: ?>
            <span class="badge bento-badge" style="background:rgba(35,72,59,0.1);color:var(--primary);">
              <i class="fa fa-calendar-check me-1"></i>Periode <?= htmlspecialchars($refleksiPeriodLabel) ?>
            </span>
          <?php endif; ?>
        </div>
        <?php if ($isPreviousPeriod): ?>
          <div class="small text-muted mb-3">
            <i class="fa fa-info-circle me-1" style="color:var(--accent);"></i>Belum ada catatan baru untuk periode <?= htmlspecialchars(($namaBulan[$bulanDipilih] ?? $bulanDipilih) . ' ' . $tahunDipilih) ?>. Menampilkan arahan dan evaluasi terakhir dari Coach.
          </div>
        <?php endif; ?>
        <div class="row g-3">
          <?php if (!empty($refleksiBulan['refleksi'])): ?>
            <div class="col-md-4">
              <div class="p-3 rounded-3 h-100" style="background:rgba(35,72,59,0.05);border:1px solid rgba(35,72,59,0.15);">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <div style="width:26px;height:26px;border-radius:6px;background:rgba(35,72,59,0.15);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:0.75rem;">
                    <i class="fa fa-check"></i>
                  </div>
                  <h6 class="fw-bold mb-0" style="color:var(--primary);">Perkembangan Siswa</h6>
                </div>
                <p class="small text-muted mb-0" style="line-height:1.55;"><?= nl2br(htmlspecialchars($refleksiBulan['refleksi'])) ?></p>
              </div>
            </div>
          <?php endif; ?>

          <?php if (!empty($refleksiBulan['rencana_perbaikan'])): ?>
            <div class="col-md-4">
              <div class="p-3 rounded-3 h-100" style="background:rgba(197,160,89,0.06);border:1px solid rgba(197,160,89,0.2);">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <div style="width:26px;height:26px;border-radius:6px;background:rgba(197,160,89,0.2);color:var(--accent);display:flex;align-items:center;justify-content:center;font-size:0.75rem;">
                    <i class="fa fa-bullseye"></i>
                  </div>
                  <h6 class="fw-bold mb-0" style="color:var(--accent);">Rencana & Target Selanjutnya</h6>
                </div>
                <p class="small text-muted mb-0" style="line-height:1.55;"><?= nl2br(htmlspecialchars($refleksiBulan['rencana_perbaikan'])) ?></p>
              </div>
            </div>
          <?php endif; ?>

          <?php if (!empty($refleksiBulan['saran'])): ?>
            <div class="col-md-4">
              <div class="p-3 rounded-3 h-100" style="background:rgba(46,134,222,0.06);border:1px solid rgba(46,134,222,0.18);">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <div style="width:26px;height:26px;border-radius:6px;background:rgba(46,134,222,0.18);color:#2e86de;display:flex;align-items:center;justify-content:center;font-size:0.75rem;">
                    <i class="fa fa-heart"></i>
                  </div>
                  <h6 class="fw-bold mb-0" style="color:#2e86de;">Saran Latihan di Rumah</h6>
                </div>
                <p class="small text-muted mb-0" style="line-height:1.55;"><?= nl2br(htmlspecialchars($refleksiBulan['saran'])) ?></p>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Unduh Laporan PDF Anak -->
    <div class="card bento-hero-card p-4 mb-4" style="background:linear-gradient(135deg,rgba(35,72,59,0.1),rgba(197,160,89,0.08));border:1px solid rgba(35,72,59,0.2);">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
          <div class="bento-icon-box" style="background:var(--primary);color:#fff;box-shadow:var(--shadow-sm);">
            <i class="fa fa-file-pdf fa-lg"></i>
          </div>
          <div>
            <h6 class="mb-1 fw-bold" style="font-family:'Outfit',sans-serif;letter-spacing:-0.01em;">Unduh Laporan Kemajuan Belajar</h6>
            <div class="text-muted small">Rangkuman hasil latihan vokal dan evaluasi resmi dalam format dokumen PDF studio.</div>
          </div>
        </div>
        <a href="generate_pdf.php?id_murid=<?= $activeChildId ?>&tahun=<?= $tahunDipilih ?>&bulan=<?= $bulanDipilih ?>" target="_blank" class="btn btn-primary d-inline-flex align-items-center gap-2" style="border-radius:10px;padding:8px 18px;font-weight:600;">
          <i class="fa fa-download"></i>
          <span>Download PDF (<?= $namaBulan[$bulanDipilih] . ' ' . $tahunDipilih ?>)</span>
        </a>
      </div>
    </div>
  <?php endif; ?>
</div>
