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
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#121e1a">
<title>Chorus Studio</title>
<meta name="description" content="Chorus Studio - Music coaching management system">
<link rel="icon" href="favicon.ico?v=<?= urlencode((string)$faviconVersion) ?>" type="image/x-icon" />
<script>(function(){try{var t=localStorage.getItem('chorus_theme')||'light';document.documentElement.classList.add(t==='dark'?'theme-dark':'theme-light');}catch(e){} })();</script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="style/main.css?v=<?= urlencode((string)$styleVersion) ?>" rel="stylesheet"></head><body>
<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" aria-live="polite"></div>
<nav class="navbar navbar-expand-lg"><div class="container-fluid px-2 px-sm-3">
<button class="btn btn-sm d-md-none me-2 btn-hamburger" id="hamburger" aria-label="Buka Menu Navigasi"><i class="fa fa-bars" aria-hidden="true"></i></button>
<a class="navbar-brand d-flex align-items-center" href="index.php">
<img src="assets/img/logo.png?v=<?= urlencode((string)$logoVersion) ?>" class="logo-img me-2" width="34" height="34" alt="Chorus Studio Logo">
<span class="brand-text">Chorus Studio</span></a>
<div class="d-flex ms-auto align-items-center gap-1 gap-sm-2">
<?php if ($isOrtu && count($anakList) > 1): ?>
  <div class="dropdown me-1">
    <button class="btn btn-sm nav-child-btn dropdown-toggle d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Pilih Profil Anak">
      <i class="fa fa-child" style="color:var(--accent);" aria-hidden="true"></i>
      <span class="nav-child-name"><?= htmlspecialchars($activeChild['nama'] ?? 'Pilih Anak') ?></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
      <li class="dropdown-header" style="font-size:0.75rem;">Pilih Profil Anak</li>
      <?php foreach ($anakList as $anak): ?>
        <li>
          <a class="dropdown-item d-flex align-items-center justify-content-between <?= ($activeChild && (int)$activeChild['id'] === (int)$anak['id']) ? 'active' : '' ?>" href="?anak_id=<?= (int)$anak['id'] ?>">
            <span><i class="fa fa-user me-2" aria-hidden="true"></i><?= htmlspecialchars($anak['nama']) ?></span>
            <?php if ($activeChild && (int)$activeChild['id'] === (int)$anak['id']): ?>
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
    <span class="nav-child-name"><?= htmlspecialchars($activeChild['nama']) ?></span>
  </div>
<?php endif; ?>
<button id="themeToggle" class="btn btn-sm btn-icon-touch" aria-label="Ganti Tema Warna"><i id="themeIcon" class="fa fa-moon" aria-hidden="true"></i></button>
<a href="profil.php" class="me-2 d-none d-md-inline-flex align-items-center text-decoration-none text-body" style="font-size:0.88rem" title="Buka Manajemen Akun Saya">Halo, <strong class="ms-1"><?= htmlspecialchars($adminNama) ?></strong> <span class="badge role-badge ms-1"><?= htmlspecialchars($roleLabel) ?></span></a>
<a class="btn btn-outline-danger btn-sm btn-logout d-inline-flex align-items-center justify-content-center" href="logout.php" aria-label="Keluar dari akun"><i class="fa fa-sign-out-alt" aria-hidden="true"></i><span class="d-none d-sm-inline ms-1">Keluar</span></a>
</div></div></nav>
<div class="d-flex position-relative">
<div id="sidebarBackdrop" class="sidebar-backdrop d-md-none" aria-hidden="true"></div>
<div id="sidebar" class="sidebar p-3">
<div class="d-flex justify-content-between align-items-center mb-3 d-md-none border-bottom pb-2 border-white-subtle">
  <span class="sidebar-brand-mobile fw-bold text-white small"><i class="fa fa-music me-2 text-warning" aria-hidden="true"></i>Chorus Studio</span>
  <button type="button" class="btn btn-sm btn-close-sidebar text-white p-1" id="closeSidebar" aria-label="Tutup Menu"><i class="fa fa-times" aria-hidden="true"></i></button>
</div>
<h6>Menu</h6>
<div class="list-group list-group-flush">
<?php if ($isOrtu): ?>
  <a href="index.php" class="list-group-item list-group-item-action <?= $currentPage === 'index.php' ? 'active' : '' ?>"><i class="fa fa-chart-pie me-2"></i>Dashboard</a>
  <a href="iringan.php" class="list-group-item list-group-item-action <?= $currentPage === 'iringan.php' ? 'active' : '' ?>"><i class="fa fa-headphones me-2"></i>Lagu Iringan</a>
  <a href="profil.php" class="list-group-item list-group-item-action <?= $currentPage === 'profil.php' ? 'active' : '' ?>"><i class="fa fa-user-cog me-2"></i>Manajemen Akun</a>
<?php else: ?>
  <a href="index.php" class="list-group-item list-group-item-action <?= $currentPage === 'index.php' ? 'active' : '' ?>"><i class="fa fa-chart-pie me-2"></i>Dashboard</a>
  <a href="murid.php" class="list-group-item list-group-item-action <?= $currentPage === 'murid.php' ? 'active' : '' ?>"><i class="fa fa-users me-2"></i>Data Murid</a>
  <a href="pertemuan.php" class="list-group-item list-group-item-action <?= $currentPage === 'pertemuan.php' ? 'active' : '' ?>"><i class="fa fa-calendar-check me-2"></i>Pertemuan</a>
  <a href="rencana_pertemuan.php" class="list-group-item list-group-item-action <?= $currentPage === 'rencana_pertemuan.php' ? 'active' : '' ?>"><i class="fa fa-calendar-alt me-2"></i>Rencana Pertemuan</a>
  <a href="lagu.php" class="list-group-item list-group-item-action <?= $currentPage === 'lagu.php' ? 'active' : '' ?>"><i class="fa fa-music me-2"></i>Data Lagu</a>
  <a href="iringan.php" class="list-group-item list-group-item-action <?= $currentPage === 'iringan.php' ? 'active' : '' ?>"><i class="fa fa-headphones me-2"></i>Lagu Iringan</a>
  <a href="refleksi.php" class="list-group-item list-group-item-action <?= $currentPage === 'refleksi.php' ? 'active' : '' ?>"><i class="fa fa-lightbulb me-2"></i>Refleksi</a>
  <a href="laporan.php" class="list-group-item list-group-item-action <?= $currentPage === 'laporan.php' ? 'active' : '' ?>"><i class="fa fa-file-pdf me-2"></i>Laporan</a>
  <?php if (in_array($adminRole, ['owner', 'admin'], true)): ?>
    <a href="admin.php" class="list-group-item list-group-item-action <?= $currentPage === 'admin.php' ? 'active' : '' ?>"><i class="fa fa-user-shield me-2"></i>Manajemen Akun</a>
    <a href="activity_log.php" class="list-group-item list-group-item-action <?= $currentPage === 'activity_log.php' ? 'active' : '' ?>"><i class="fa fa-history me-2"></i>Activity Log</a>
  <?php else: ?>
    <a href="profil.php" class="list-group-item list-group-item-action <?= $currentPage === 'profil.php' ? 'active' : '' ?>"><i class="fa fa-user-cog me-2"></i>Manajemen Akun</a>
  <?php endif; ?>
<?php endif; ?>
</div></div><div class="flex-grow-1 main-content p-3 p-sm-4"><!-- content -->
<?php if ($flash = get_flash()): ?>
  <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show mb-4 shadow-sm" role="alert" style="border-radius:12px;">
    <i class="fa <?= $flash['type'] === 'success' ? 'fa-check-circle text-success' : ($flash['type'] === 'danger' ? 'fa-exclamation-circle text-danger' : 'fa-info-circle text-info') ?> me-2"></i>
    <span><?= htmlspecialchars($flash['message']) ?></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
  </div>
<?php endif; ?>

