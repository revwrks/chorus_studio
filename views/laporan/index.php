<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h3 class="mb-1 fw-bold" style="font-family:'Outfit',sans-serif;letter-spacing:-0.02em;"><i class="fa fa-file-pdf me-2" style="color:var(--primary);"></i>Generate Laporan</h3>
      <div class="text-muted small">Cetak atau ekspor dokumen hasil evaluasi dan latihan vokal murid ke format PDF & ZIP.</div>
    </div>
  </div>

  <div class="card bento-hero-card p-4 mb-4" style="border-top:3px solid var(--primary);">
    <div class="d-flex align-items-center gap-2 mb-3">
      <div class="bento-icon-box" style="background:rgba(35,72,59,0.1);color:var(--primary);width:38px;height:38px;border-radius:10px;">
        <i class="fa fa-file-export"></i>
      </div>
      <div>
        <h5 class="mb-0 fw-bold" style="font-family:'Outfit',sans-serif;letter-spacing:-0.01em;">Laporan Individual</h5>
        <small class="text-muted">Cetak rapor perkembangan latihan vokal untuk satu orang murid</small>
      </div>
    </div>
    <form method="get" action="generate_pdf.php" target="_blank" class="row g-3">
      <?php if (!empty($muridList)): ?>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Pilih Murid</label>
          <select name="id_murid" class="form-select form-select-sm" style="border-radius:8px;">
            <?php foreach ($muridList as $mm): ?>
              <option value="<?= $mm['id'] ?>"><?= htmlspecialchars($mm['nama']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php else: ?>
        <div class="col-md-4">
          <label class="form-label small fw-semibold">Pilih Murid</label>
          <div class="text-muted small">Belum ada data murid.</div>
        </div>
      <?php endif; ?>
      <?php if (!empty($tahunList)): ?>
        <div class="col-md-2">
          <label class="form-label small fw-semibold">Tahun</label>
          <select name="tahun" class="form-select form-select-sm" style="border-radius:8px;">
            <?php foreach ($tahunList as $t): ?>
              <option value="<?= $t ?>" <?= $t == $selectedYear ? 'selected' : '' ?>><?= $t ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>
      <?php if (!empty($bulanList)): ?>
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Bulan</label>
          <select name="bulan" class="form-select form-select-sm" style="border-radius:8px;">
            <?php foreach ($bulanList as $num): ?>
              <option value="<?= $num ?>" <?= $num == $selectedMonth ? 'selected' : '' ?>><?= $namaBulan[$num] ?? $num ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>
      <div class="col-md-3 align-self-end">
        <button class="btn btn-primary btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-2" style="height:36px;border-radius:8px;font-weight:600;" <?= $canSingle ? '' : 'disabled' ?>><i class="fa fa-download"></i><span>Generate PDF</span></button>
      </div>
    </form>
  </div>

  <?php if (is_admin_or_owner()): ?>
    <div class="card bento-hero-card p-4 mb-4" style="border-top:3px solid var(--accent);">
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <div class="bento-icon-box" style="background:rgba(197,160,89,0.15);color:var(--accent);width:38px;height:38px;border-radius:10px;">
            <i class="fa fa-file-zipper"></i>
          </div>
          <div>
            <h5 class="mb-0 fw-bold" style="font-family:'Outfit',sans-serif;letter-spacing:-0.01em;">Bulk Export Laporan Bulanan</h5>
            <small class="text-muted">Unduh seluruh laporan murid aktif sekaligus dalam arsip ZIP studio</small>
          </div>
        </div>
        <span class="badge bento-badge" style="background:rgba(197,160,89,0.15);color:var(--accent);"><i class="fa fa-layer-group me-1"></i>Arsip Lengkap</span>
      </div>
      <form method="get" action="generate_bulk_pdf.php" class="row g-3">
        <?php if (!empty($tahunList)): ?>
          <div class="col-md-2">
            <label class="form-label small fw-semibold">Tahun</label>
            <select name="tahun" class="form-select form-select-sm" style="border-radius:8px;">
              <?php foreach ($tahunList as $t): ?>
                <option value="<?= $t ?>" <?= $t == $selectedYear ? 'selected' : '' ?>><?= $t ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endif; ?>
        <?php if (!empty($bulanList)): ?>
          <div class="col-md-3">
            <label class="form-label small fw-semibold">Bulan</label>
            <select name="bulan" class="form-select form-select-sm" style="border-radius:8px;">
              <?php foreach ($bulanList as $num): ?>
                <option value="<?= $num ?>" <?= $num == $selectedMonth ? 'selected' : '' ?>><?= $namaBulan[$num] ?? $num ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endif; ?>
        <div class="col-md-4 align-self-end">
          <button class="btn btn-primary btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-2" style="height:36px;border-radius:8px;font-weight:600;" <?= $canBulk ? '' : 'disabled' ?>><i class="fa fa-file-zipper"></i><span>Download Semua (ZIP)</span></button>
        </div>
      </form>
    </div>
  <?php endif; ?>
</div>
