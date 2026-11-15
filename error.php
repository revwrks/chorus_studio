<?php
require_once __DIR__ . '/includes/auth.php';

$code = isset($_GET['code']) ? (int)$_GET['code'] : 403;
$allowedCodes = [400, 401, 403, 404, 405, 500];
if (!in_array($code, $allowedCodes, true)) {
    $code = 403;
}

http_response_code($code);

$errorInfo = [
    400 => [
        'title' => 'Permintaan Tidak Valid (400)',
        'message' => 'Parameter atau permintaan yang dikirimkan tidak sesuai dengan format yang diharapkan.',
        'icon' => 'fa-triangle-exclamation',
        'color' => 'var(--accent)'
    ],
    401 => [
        'title' => 'Sesi Berakhir (401)',
        'message' => 'Silakan masuk (login) kembali untuk melanjutkan aktivitas Anda.',
        'icon' => 'fa-lock',
        'color' => 'var(--gold)'
    ],
    403 => [
        'title' => 'Akses Ditolak (403)',
        'message' => 'Peran akun Anda tidak memiliki hak akses atau izin untuk membuka halaman atau menjalankan tindakan ini.',
        'icon' => 'fa-shield-halved',
        'color' => 'var(--danger)'
    ],
    404 => [
        'title' => 'Halaman Tidak Ditemukan (404)',
        'message' => 'Tautan yang Anda tuju tidak tersedia, telah dipindahkan, atau alamat URL salah ketik.',
        'icon' => 'fa-circle-question',
        'color' => 'var(--gold)'
    ],
    405 => [
        'title' => 'Metode Tidak Diizinkan (405)',
        'message' => 'Metode HTTP yang digunakan tidak diizinkan untuk rute ini.',
        'icon' => 'fa-ban',
        'color' => 'var(--danger)'
    ],
    500 => [
        'title' => 'Kesalahan Sistem (500)',
        'message' => 'Terjadi kendala pada server saat memproses permintaan Anda. Silakan coba beberapa saat lagi.',
        'icon' => 'fa-bug',
        'color' => 'var(--danger)'
    ]
][$code];

$customMsg = trim($_GET['msg'] ?? '');
if ($customMsg !== '') {
    $errorInfo['message'] = htmlspecialchars($customMsg, ENT_QUOTES, 'UTF-8');
}

$styleVersion = @filemtime(__DIR__ . '/style/main.css') ?: time();
$faviconVersion = @filemtime(__DIR__ . '/favicon.ico') ?: $styleVersion;
$logoVersion = @filemtime(__DIR__ . '/assets/img/logo.png') ?: $styleVersion;
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= htmlspecialchars($errorInfo['title']) ?> - Chorus Studio</title>
    <link rel="icon" href="favicon.ico?v=<?= urlencode((string)$faviconVersion) ?>" type="image/x-icon">
    <script>
        (function() {
            try {
                var t = localStorage.getItem('chorus_theme') || 'light';
                document.documentElement.classList.add(t === 'dark' ? 'theme-dark' : 'theme-light');
            } catch (e) {}
        })();
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style/main.css?v=<?= urlencode((string)$styleVersion) ?>" rel="stylesheet">
    <style>
        .error-page-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .error-card {
            max-width: 540px;
            width: 100%;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            padding: 2.5rem;
            text-align: center;
            animation: fadeIn 0.3s ease;
        }
        .error-icon-box {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(139, 63, 63, 0.1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin-bottom: 1.5rem;
        }
        .error-code-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
            background: rgba(74, 107, 93, 0.1);
            color: var(--primary);
        }
        .error-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
            color: var(--text);
        }
        .error-desc {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="error-page-container">
        <div class="error-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <img src="assets/img/logo.png?v=<?= urlencode((string)$logoVersion) ?>" width="28" height="28" alt="Logo" class="me-2">
                    <span class="brand-text" style="font-size:1.1rem;font-weight:700;">Chorus Studio</span>
                </div>
                <button id="themeToggle" class="btn btn-sm btn-outline-secondary" title="Ganti Tema">
                    <i id="themeIcon" class="fa fa-moon"></i>
                </button>
            </div>

            <div class="error-icon-box" style="background:rgba(139,63,63,0.12);color:<?= $errorInfo['color'] ?>;">
                <i class="fa <?= $errorInfo['icon'] ?>"></i>
            </div>

            <div>
                <span class="error-code-badge">Kode HTTP: <?= $code ?></span>
            </div>

            <h2 class="error-title"><?= htmlspecialchars($errorInfo['title']) ?></h2>
            <p class="error-desc"><?= $errorInfo['message'] ?></p>

            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <?php if (is_logged_in()): ?>
                    <a href="index.php" class="btn btn-primary px-4">
                        <i class="fa fa-home me-2"></i>Kembali ke Dashboard
                    </a>
                    <a href="javascript:history.back()" class="btn btn-outline-secondary px-3">
                        <i class="fa fa-arrow-left me-1"></i>Halaman Sebelumnya
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary px-4">
                        <i class="fa fa-sign-in-alt me-2"></i>Ke Halaman Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            const toggle = document.getElementById('themeToggle');
            const icon = document.getElementById('themeIcon');
            const root = document.documentElement;

            function applyTheme(t) {
                root.classList.remove('theme-light', 'theme-dark');
                root.classList.add(t === 'dark' ? 'theme-dark' : 'theme-light');
                if (icon) icon.className = (t === 'dark') ? 'fa fa-sun' : 'fa fa-moon';
            }

            let theme = localStorage.getItem('chorus_theme') || 'light';
            applyTheme(theme);

            if (toggle) {
                toggle.addEventListener('click', () => {
                    theme = (theme === 'light') ? 'dark' : 'light';
                    localStorage.setItem('chorus_theme', theme);
                    applyTheme(theme);
                });
            }
        })();
    </script>
</body>
</html>
