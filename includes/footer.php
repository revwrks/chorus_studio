<!-- end content --></main>
</div>

<!-- Mobile Bottom Navigation Dock (Thumb-Zone Optimized for Mobile) -->
<nav class="mobile-bottom-nav d-md-none" aria-label="Navigasi Cepat Mobile">
  <?php if (!empty($isOrtu)): ?>
    <a href="index.php" class="mobile-bottom-nav-item <?= $currentPage === 'index.php' ? 'active' : '' ?>"<?= $currentPage === 'index.php' ? ' aria-current="page"' : '' ?>>
      <i class="fa fa-chart-pie" aria-hidden="true"></i>
      <span>Dashboard</span>
    </a>
    <a href="iringan.php" class="mobile-bottom-nav-item <?= $currentPage === 'iringan.php' ? 'active' : '' ?>"<?= $currentPage === 'iringan.php' ? ' aria-current="page"' : '' ?>>
      <i class="fa fa-headphones" aria-hidden="true"></i>
      <span>Iringan</span>
    </a>
    <a href="profil.php" class="mobile-bottom-nav-item <?= $currentPage === 'profil.php' ? 'active' : '' ?>"<?= $currentPage === 'profil.php' ? ' aria-current="page"' : '' ?>>
      <i class="fa fa-user-cog" aria-hidden="true"></i>
      <span>Akun</span>
    </a>
  <?php else:
    $isExtendedPage = !in_array($currentPage, ['index.php', 'pertemuan.php', 'rencana_pertemuan.php', 'murid.php'], true);
  ?>
    <a href="index.php" class="mobile-bottom-nav-item <?= $currentPage === 'index.php' ? 'active' : '' ?>"<?= $currentPage === 'index.php' ? ' aria-current="page"' : '' ?>>
      <i class="fa fa-chart-pie" aria-hidden="true"></i>
      <span>Dashboard</span>
    </a>
    <a href="pertemuan.php" class="mobile-bottom-nav-item <?= $currentPage === 'pertemuan.php' ? 'active' : '' ?>"<?= $currentPage === 'pertemuan.php' ? ' aria-current="page"' : '' ?>>
      <i class="fa fa-calendar-check" aria-hidden="true"></i>
      <span>Pertemuan</span>
    </a>
    <a href="rencana_pertemuan.php" class="mobile-bottom-nav-item <?= $currentPage === 'rencana_pertemuan.php' ? 'active' : '' ?>"<?= $currentPage === 'rencana_pertemuan.php' ? ' aria-current="page"' : '' ?>>
      <i class="fa fa-calendar-alt" aria-hidden="true"></i>
      <span>Target</span>
    </a>
    <a href="murid.php" class="mobile-bottom-nav-item <?= $currentPage === 'murid.php' ? 'active' : '' ?>"<?= $currentPage === 'murid.php' ? ' aria-current="page"' : '' ?>>
      <i class="fa fa-users" aria-hidden="true"></i>
      <span>Murid</span>
    </a>
    <button type="button" class="mobile-bottom-nav-item btn-link border-0 bg-transparent <?= $isExtendedPage ? 'active' : '' ?>" id="bottomNavMenuToggle" aria-expanded="false" aria-controls="sidebar" aria-label="Buka Menu Lainnya">
      <i class="fa fa-bars" aria-hidden="true"></i>
      <span>Menu</span>
    </button>
  <?php endif; ?>
</nav>

<div class="modal fade" id="modalDeleteConfirm" tabindex="-1" aria-labelledby="deleteModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--radius);background:var(--modal-bg);">
            <div class="modal-header" style="border-color:var(--border);">
                <h5 class="modal-title"><i class="fa fa-triangle-exclamation me-2" style="color:var(--danger);" aria-hidden="true"></i><span id="deleteModalTitle">Konfirmasi Hapus</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" id="deleteModalMessage">Data ini akan dihapus. Tindakan ini tidak bisa dibatalkan.</p>
            </div>
            <div class="modal-footer" style="border-color:var(--border);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="deleteModalSubmit">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>

<!-- Universal Student Photo Zoom / Lightbox Modal -->
<div class="modal fade modal-image-zoom" id="modalImageZoom" tabindex="-1" aria-labelledby="modalImageZoomTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content" style="background:var(--modal-bg);border:1px solid var(--card-border);backdrop-filter:blur(20px);">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <h6 class="modal-title mb-0" id="modalImageZoomTitle" style="color:var(--text);">
                    <i class="fa fa-image me-2" style="color:var(--accent);" aria-hidden="true"></i>Foto Murid
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body text-center p-3">
                <div class="zoom-img-container position-relative d-inline-block">
                    <img id="modalImageZoomImg" src="" alt="Foto Murid" class="img-fluid rounded-3 shadow-lg" style="max-height:70vh;max-width:100%;object-fit:contain;border:2px solid var(--card-border);">
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-between">
                <span class="small text-muted" id="modalImageZoomInfo">Klik di luar atau tombol Tutup</span>
                <div class="d-flex gap-2">
                    <a id="modalImageZoomDownload" href="#" target="_blank" download class="btn btn-sm btn-outline-secondary"><i class="fa fa-download me-1" aria-hidden="true"></i>Unduh</a>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Universal Realisasi Pertemuan Detail Modal -->
<div class="modal fade" id="modalDetailRealisasi" tabindex="-1" aria-labelledby="modalDetailRealisasiTitle" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius:var(--radius);background:var(--modal-bg);">
            <div class="modal-header border-bottom pb-3" style="border-color:var(--border) !important;">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:40px;height:40px;border-radius:10px;background:rgba(168,144,98,0.15);display:flex;align-items:center;justify-content:center;">
                        <i class="fa fa-calendar-check" style="color:var(--accent);font-size:1.2rem;" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalDetailRealisasiTitle">
                            Realisasi Pertemuan
                        </h5>
                        <small class="text-muted" id="modalDetailRealisasiSubtitle">
                            Memuat data...
                        </small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-3 p-sm-4" id="modalDetailRealisasiBody">
                <div class="text-center py-5 text-muted">
                    <i class="fa fa-spinner fa-spin fa-2x mb-3 text-primary" aria-hidden="true"></i>
                    <div class="small fw-semibold">Memuat rincian sesi pertemuan...</div>
                </div>
            </div>
            <div class="modal-footer border-top pt-2" style="border-color:var(--border) !important;">
                <span class="text-muted small me-auto" id="modalDetailRealisasiCount"></span>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php $appJsVersion = @filemtime(__DIR__ . '/../assets/js/app.js') ?: time(); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js?v=<?= urlencode((string)$appJsVersion) ?>" defer></script>
</body>
</html>
