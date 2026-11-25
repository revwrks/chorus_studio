<?php
require_once __DIR__ . '/auth.php';
$adminNama = $_SESSION['admin_nama'] ?? $_SESSION['admin_username'] ?? 'Admin';
$adminRole = $_SESSION['admin_role'] ?? 'admin';
$styleVersion = @filemtime(__DIR__ . '/../style/main.css') ?: time();
$faviconVersion = @filemtime(__DIR__ . '/../favicon.ico') ?: $styleVersion;
$logoVersion = @filemtime(__DIR__ . '/../assets/img/logo.png') ?: $styleVersion;
$roleLabel = [
  'owner' => 'Owner',
  'admin' => 'Admin',
  'coach' => 'Coach',
  'orang_tua' => 'Orang Tua',
][$adminRole] ?? 'Admin';
$currentPage = basename($_SERVER['PHP_SELF']);
$isOrtu = is_orang_tua();
$anakList = $isOrtu ? get_orang_tua_anak_list() : [];
$activeChild = $isOrtu ? get_active_murid_data() : null;

$childQueryExtra = '';
if ($isOrtu) {
  $extraParams = [];
  if (isset($_GET['bulan']) && $_GET['bulan'] !== '') {
    $extraParams['bulan'] = (int)$_GET['bulan'];
  }
  if (isset($_GET['tahun']) && $_GET['tahun'] !== '') {
    $extraParams['tahun'] = (int)$_GET['tahun'];
  }
  if (!empty($extraParams)) {
    $childQueryExtra = '&' . http_build_query($extraParams);
  }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#F6F4EF">
<title>Chorus Studio</title>
<meta name="description" content="Chorus Studio - Music coaching management system">
<link rel="icon" href="favicon.ico?v=<?= urlencode((string)$faviconVersion) ?>" type="image/x-icon">
<script>(function(){try{var t=localStorage.getItem('chorus_theme')||'light';var r=document.documentElement;r.classList.remove('theme-light','theme-dark');r.classList.add(t==='dark'?'theme-dark':'theme-light');r.setAttribute('data-bs-theme',t==='dark'?'dark':'light');var m=document.querySelector('meta[name="theme-color"]');if(m)m.setAttribute('content',t==='dark'?'#0D1512':'#F6F4EF');if(localStorage.getItem('chorus_sidebar')==='collapsed'&&window.innerWidth>=768){r.classList.add('sidebar-collapsed-init');}}catch(e){}})();</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="style/main.css?v=<?= urlencode((string)$styleVersion) ?>" rel="stylesheet">
</head>
<body>
<a href="#mainContent" class="skip-link">Lewati ke konten utama</a>
<script>try{if(document.documentElement.classList.contains('sidebar-collapsed-init')){document.body.classList.add('sidebar-collapsed');document.documentElement.classList.remove('sidebar-collapsed-init');}}catch(e){}</script>
<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" aria-live="polite"></div>
<header>
  <nav class="navbar navbar-expand-lg" aria-label="Navigasi Atas">
    <div class="container-fluid px-2 px-sm-3">
      <button type="button" class="btn btn-sm d-md-none me-2 btn-hamburger" id="hamburger" aria-expanded="false" aria-controls="sidebar" aria-label="Buka Menu Navigasi"><i class="fa fa-bars" aria-hidden="true"></i></button>
      <button type="button" class="btn btn-sm d-none d-md-inline-flex me-2 btn-icon-touch" id="desktopSidebarToggle" aria-expanded="true" aria-controls="sidebar" aria-label="Sembunyikan atau Tampilkan Menu"><i class="fa fa-bars" aria-hidden="true"></i></button>
      <a class="navbar-brand d-flex align-items-center" href="index.php">
        <img src="assets/img/logo.png?v=<?= urlencode((string)$logoVersion) ?>" class="logo-img me-2" width="34" height="34" alt="Chorus Studio Logo">
        <span class="brand-text">Chorus Studio</span>
      </a>
      <div class="d-flex ms-auto align-items-center gap-1 gap-sm-2">
        <?php if ($isOrtu && count($anakList) > 1): ?>
          <div class="dropdown me-1">
            <button class="btn btn-sm nav-child-btn dropdown-toggle d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Pilih Profil Anak">
              <i class="fa fa-child" style="color:var(--accent);" aria-hidden="true"></i>
              <span class="nav-child-name"><?= e($activeChild['nama'] ?? 'Pilih Anak') ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
              <li class="dropdown-header" style="font-size:0.75rem;">Pilih Profil Anak</li>
              <?php foreach ($anakList as $anak): ?>
                <?php $isActiveAnak = ($activeChild && (int)$activeChild['id'] === (int)$anak['id']); ?>
                <li>
                  <a class="dropdown-item d-flex align-items-center justify-content-between <?= $isActiveAnak ? 'active' : '' ?>" href="?anak_id=<?= (int)$anak['id'] ?><?= e($childQueryExtra) ?>">
                    <span><i class="fa fa-user me-2" aria-hidden="true"></i><?= e($anak['nama']) ?></span>
                    <?php if ($isActiveAnak): ?>
                      <i class="fa fa-check text-success ms-2" aria-hidden="true"></i>
                    <?php endif; ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php elseif ($isOrtu && $activeChild): ?>
          <div class="nav-child-pill me-1 d-inline-flex align-items-center">
            <i class="fa fa-child me-1" style="color:var(--accent);" aria-hidden="true"></i>
            <span class="nav-child-name"><?= e($activeChild['nama']) ?></span>
          </div>
        <?php endif; ?>
        <button type="button" id="themeToggle" class="btn btn-sm btn-icon-touch" aria-label="Ganti Tema Warna"><i id="themeIcon" class="fa fa-moon" aria-hidden="true"></i></button>
        <a href="profil.php" class="nav-user-link me-2 d-none d-md-inline-flex align-items-center text-decoration-none" title="Buka Manajemen Akun Saya">Halo, <strong class="ms-1"><?= e($adminNama) ?></strong> <span class="badge role-badge ms-1"><?= e($roleLabel) ?></span></a>
        <form method="post" action="logout.php" class="d-inline m-0">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-outline-danger btn-sm btn-logout d-inline-flex align-items-center justify-content-center" aria-label="Keluar dari akun">
            <i class="fa fa-sign-out-alt" aria-hidden="true"></i><span class="d-none d-sm-inline ms-1">Keluar</span>
          </button>
        </form>
      </div>
    </div>
  </nav>
</header>
<div class="d-flex position-relative">
  <div id="sidebarBackdrop" class="sidebar-backdrop d-md-none" aria-hidden="true"></div>
  <aside id="sidebar" class="sidebar p-3" aria-label="Menu Utama">
    <div class="d-flex justify-content-between align-items-center mb-3 d-md-none border-bottom pb-2 border-white-subtle">
      <span class="sidebar-brand-mobile fw-bold text-white small"><i class="fa fa-music me-2 text-warning" aria-hidden="true"></i>Chorus Studio</span>
      <button type="button" class="btn btn-sm btn-close-sidebar text-white p-1" id="closeSidebar" aria-label="Tutup Menu"><i class="fa fa-times" aria-hidden="true"></i></button>
    </div>
    <div class="list-group list-group-flush">
      <?php if ($isOrtu): ?>
        <div class="sidebar-section-label">Utama</div>
        <a href="index.php" class="list-group-item list-group-item-action <?= $currentPage === 'index.php' ? 'active' : '' ?>"<?= $currentPage === 'index.php' ? ' aria-current="page"' : '' ?>><i class="fa fa-chart-pie me-2" aria-hidden="true"></i>Dashboard</a>
        <a href="iringan.php" class="list-group-item list-group-item-action <?= $currentPage === 'iringan.php' ? 'active' : '' ?>"<?= $currentPage === 'iringan.php' ? ' aria-current="page"' : '' ?>><i class="fa fa-headphones me-2" aria-hidden="true"></i>Lagu Iringan</a>
        <div class="sidebar-section-label">Sistem</div>
        <a href="profil.php" class="list-group-item list-group-item-action <?= $currentPage === 'profil.php' ? 'active' : '' ?>"<?= $currentPage === 'profil.php' ? ' aria-current="page"' : '' ?>><i class="fa fa-user-cog me-2" aria-hidden="true"></i>Manajemen Akun</a>
      <?php else: ?>
        <div class="sidebar-section-label">Utama</div>
        <a href="index.php" class="list-group-item list-group-item-action <?= $currentPage === 'index.php' ? 'active' : '' ?>"<?= $currentPage === 'index.php' ? ' aria-current="page"' : '' ?>><i class="fa fa-chart-pie me-2" aria-hidden="true"></i>Dashboard</a>
        <div class="sidebar-section-label">Akademi</div>
        <a href="murid.php" class="list-group-item list-group-item-action <?= $currentPage === 'murid.php' ? 'active' : '' ?>"<?= $currentPage === 'murid.php' ? ' aria-current="page"' : '' ?>><i class="fa fa-users me-2" aria-hidden="true"></i>Data Murid</a>
        <a href="pertemuan.php" class="list-group-item list-group-item-action <?= $currentPage === 'pertemuan.php' ? 'active' : '' ?>"<?= $currentPage === 'pertemuan.php' ? ' aria-current="page"' : '' ?>><i class="fa fa-calendar-check me-2" aria-hidden="true"></i>Pertemuan</a>
        <a href="rencana_pertemuan.php" class="list-group-item list-group-item-action <?= $currentPage === 'rencana_pertemuan.php' ? 'active' : '' ?>"<?= $currentPage === 'rencana_pertemuan.php' ? ' aria-current="page"' : '' ?>><i class="fa fa-calendar-alt me-2" aria-hidden="true"></i>Rencana Pertemuan</a>
        <a href="lagu.php" class="list-group-item list-group-item-action <?= $currentPage === 'lagu.php' ? 'active' : '' ?>"<?= $currentPage === 'lagu.php' ? ' aria-current="page"' : '' ?>><i class="fa fa-music me-2" aria-hidden="true"></i>Data Lagu</a>
        <a href="iringan.php" class="list-group-item list-group-item-action <?= $currentPage === 'iringan.php' ? 'active' : '' ?>"<?= $currentPage === 'iringan.php' ? ' aria-current="page"' : '' ?>><i class="fa fa-headphones me-2" aria-hidden="true"></i>Lagu Iringan</a>
        <a href="refleksi.php" class="list-group-item list-group-item-action <?= $currentPage === 'refleksi.php' ? 'active' : '' ?>"<?= $currentPage === 'refleksi.php' ? ' aria-current="page"' : '' ?>><i class="fa fa-lightbulb me-2"></i>Refleksi</a>
        <?php $isLaporanActive = ($currentPage === 'laporan.php' || $currentPage === 'generate_bulk_pdf.php'); ?>
        <a href="laporan.php" class="list-group-item list-group-item-action <?= $isLaporanActive ? 'active' : '' ?>"<?= $isLaporanActive ? ' aria-current="page"' : '' ?>><i class="fa fa-file-pdf me-2" aria-hidden="true"></i>Laporan</a>
        <div class="sidebar-section-label">Sistem</div>
        <?php if (in_array($adminRole, ['owner', 'admin'], true)): ?>
          <a href="admin.php" class="list-group-item list-group-item-action <?= $currentPage === 'admin.php' ? 'active' : '' ?>"<?= $currentPage === 'admin.php' ? ' aria-current="page"' : '' ?>><i class="fa fa-user-shield me-2" aria-hidden="true"></i>Manajemen Akun</a>
          <a href="activity_log.php" class="list-group-item list-group-item-action <?= $currentPage === 'activity_log.php' ? 'active' : '' ?>"<?= $currentPage === 'activity_log.php' ? ' aria-current="page"' : '' ?>><i class="fa fa-history me-2" aria-hidden="true"></i>Activity Log</a>
          <a href="profil.php" class="list-group-item list-group-item-action <?= $currentPage === 'profil.php' ? 'active' : '' ?>"<?= $currentPage === 'profil.php' ? ' aria-current="page"' : '' ?>><i class="fa fa-user-cog me-2" aria-hidden="true"></i>Profil Saya</a>
        <?php else: ?>
          <a href="profil.php" class="list-group-item list-group-item-action <?= $currentPage === 'profil.php' ? 'active' : '' ?>"<?= $currentPage === 'profil.php' ? ' aria-current="page"' : '' ?>><i class="fa fa-user-cog me-2" aria-hidden="true"></i>Manajemen Akun</a>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </aside>
  <main id="mainContent" class="flex-grow-1 main-content p-3 p-sm-4" tabindex="-1"><!-- content -->
<?php if ($flash = get_flash()): ?>
  <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show mb-4 shadow-sm" role="alert" style="border-radius:12px;">
    <i class="fa <?= $flash['type'] === 'success' ? 'fa-check-circle text-success' : ($flash['type'] === 'danger' ? 'fa-exclamation-circle text-danger' : 'fa-info-circle text-info') ?> me-2" aria-hidden="true"></i>
    <span><?= e($flash['message']) ?></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
  </div>
<?php endif; ?>
