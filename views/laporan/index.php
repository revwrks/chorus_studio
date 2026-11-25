<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h1 class="page-title mb-1"><i class="fa fa-file-pdf me-2" style="color:var(--primary);" aria-hidden="true"></i>Generate Laporan</h1>
      <p class="text-muted small mb-0">Cetak atau ekspor dokumen hasil evaluasi dan latihan vokal murid ke format PDF dan arsip ZIP.</p>
    </div>
  </div>

  <div class="card bento-hero-card p-4 mb-4">
    <div class="d-flex align-items-center gap-2 mb-3">
      <div class="bento-icon-box" style="background:var(--primary-soft);color:var(--primary);width:38px;height:38px;border-radius:10px;">
        <i class="fa fa-file-export" aria-hidden="true"></i>
      </div>
      <div>
        <h2 class="h5 mb-0 fw-bold" style="letter-spacing:-0.01em;">Laporan Individual</h2>
        <small class="text-muted">Cetak rapor perkembangan latihan vokal untuk satu murid</small>
      </div>
    </div>
    <form method="get" action="generate_pdf.php" target="_blank" class="row g-3">
      <?php if (!empty($muridList)): ?>
        <div class="col-md-4">
          <label class="form-label small fw-semibold" for="singleIdMurid">Pilih Murid</label>
          <select id="singleIdMurid" name="id_murid" class="form-select form-select-sm" style="border-radius:8px;">
            <?php foreach ($muridList as $mm): ?>
              <option value="<?= e((string)$mm['id']) ?>"><?= e($mm['nama'] ?? '') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php else: ?>
        <div class="col-md-4">
          <span class="form-label small fw-semibold d-block">Pilih Murid</span>
          <div class="text-muted small">Belum ada data murid.</div>
        </div>
      <?php endif; ?>
      <?php if (!empty($tahunList)): ?>
        <div class="col-md-2">
          <label class="form-label small fw-semibold" for="singleTahun">Tahun</label>
          <select id="singleTahun" name="tahun" class="form-select form-select-sm" style="border-radius:8px;">
            <?php foreach ($tahunList as $t): ?>
              <option value="<?= e((string)$t) ?>" <?= (string)$t === (string)$selectedYear ? 'selected' : '' ?>><?= e((string)$t) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>
      <?php if (!empty($bulanList)): ?>
        <div class="col-md-3">
          <label class="form-label small fw-semibold" for="singleBulan">Bulan</label>
          <select id="singleBulan" name="bulan" class="form-select form-select-sm" style="border-radius:8px;">
            <?php foreach ($bulanList as $num): ?>
              <option value="<?= e((string)$num) ?>" <?= (string)$num === (string)$selectedMonth ? 'selected' : '' ?>><?= e((string)($namaBulan[$num] ?? $num)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>
      <div class="col-md-3 align-self-end">
        <button type="submit" class="btn btn-primary btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-2" style="height:36px;border-radius:8px;font-weight:600;" <?= $canSingle ? '' : 'disabled' ?>><i class="fa fa-download" aria-hidden="true"></i><span>Generate PDF</span></button>
      </div>
    </form>
  </div>

  <?php if (is_admin_or_owner()): ?>
    <div class="card bento-hero-card p-4 mb-4">
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <div class="bento-icon-box" style="background:var(--accent-soft);color:var(--accent);width:38px;height:38px;border-radius:10px;">
            <i class="fa fa-file-zipper" aria-hidden="true"></i>
          </div>
          <div>
            <h2 class="h5 mb-0 fw-bold" style="letter-spacing:-0.01em;">Bulk Export Laporan Bulanan</h2>
            <small class="text-muted">Unduh seluruh laporan murid aktif sekaligus dalam satu arsip ZIP studio</small>
          </div>
        </div>
        <span class="bento-badge"><i class="fa fa-layer-group me-1" aria-hidden="true"></i>Arsip Lengkap</span>
      </div>
      <form method="get" action="generate_bulk_pdf.php" class="row g-3">
        <?php if (!empty($tahunList)): ?>
          <div class="col-md-2">
            <label class="form-label small fw-semibold" for="bulkTahun">Tahun</label>
            <select id="bulkTahun" name="tahun" class="form-select form-select-sm" style="border-radius:8px;">
              <?php foreach ($tahunList as $t): ?>
                <option value="<?= e((string)$t) ?>" <?= (string)$t === (string)$selectedYear ? 'selected' : '' ?>><?= e((string)$t) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endif; ?>
        <?php if (!empty($bulanList)): ?>
          <div class="col-md-3">
            <label class="form-label small fw-semibold" for="bulkBulan">Bulan</label>
            <select id="bulkBulan" name="bulan" class="form-select form-select-sm" style="border-radius:8px;">
              <?php foreach ($bulanList as $num): ?>
                <option value="<?= e((string)$num) ?>" <?= (string)$num === (string)$selectedMonth ? 'selected' : '' ?>><?= e((string)($namaBulan[$num] ?? $num)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endif; ?>
        <div class="col-md-4 align-self-end">
          <button type="submit" class="btn btn-primary btn-sm w-100 d-inline-flex align-items-center justify-content-center gap-2" style="height:36px;border-radius:8px;font-weight:600;" <?= $canBulk ? '' : 'disabled' ?>><i class="fa fa-file-zipper" aria-hidden="true"></i><span>Download Semua (ZIP)</span></button>
        </div>
      </form>
    </div>
  <?php endif; ?>
</div>
