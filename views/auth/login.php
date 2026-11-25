<?php
if (!function_exists('e')) {
    function e(?string $val): string
    {
        return htmlspecialchars((string)($val ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0F1E18">
    <title>Masuk - Chorus Studio</title>
    <link rel="icon" href="favicon.ico?v=<?= urlencode((string)($faviconVersion ?? '')) ?>" type="image/x-icon">
    <script>(function(){try{var t=localStorage.getItem('chorus_theme')||'light';var r=document.documentElement;r.classList.remove('theme-light','theme-dark');r.classList.add(t==='dark'?'theme-dark':'theme-light');r.setAttribute('data-bs-theme',t==='dark'?'dark':'light');}catch(e){}})();</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style/main.css?v=<?= urlencode((string)($styleVersion ?? '')) ?>" rel="stylesheet">
</head>

<body class="d-flex align-items-center justify-content-center login-page">
    <main class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card p-4 shadow-lg login-card">
                    <div class="text-center mb-4">
                        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:56px;height:56px;background-color:#2B6B54;border:1px solid rgba(255,255,255,0.16);border-radius:14px;">
                            <i class="fa fa-music" style="font-size:24px;color:#FFFFFF;" aria-hidden="true"></i>
                        </div>
                        <h1 class="mb-1 fw-bold" style="font-size:1.75rem;letter-spacing:-0.02em;color:#EDF2EF;">Chorus Studio</h1>
                        <p class="text-muted mb-0" style="font-size:0.9rem;">Masuk ke akun Anda untuk melanjutkan</p>
                    </div>
                    <?php if (!empty($err)): ?>
                        <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
                            <i class="fa fa-exclamation-circle mt-1 flex-shrink-0" aria-hidden="true"></i>
                            <span><?= e($err) ?></span>
                        </div>
                    <?php endif; ?>
                    <form method="post" autocomplete="on">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="loginUsername" class="form-label small fw-semibold mb-1" style="color:#D2DFD8;">Username</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background-color:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.16);border-right:none;color:#C2D1C9;border-radius:10px 0 0 10px;"><i class="fa fa-user" aria-hidden="true"></i></span>
                                <input id="loginUsername" name="username" type="text" class="form-control" placeholder="Masukkan username" autocomplete="username" required style="border-radius:0 10px 10px 0;">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label small fw-semibold mb-1" style="color:#D2DFD8;">Password</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background-color:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.16);border-right:none;color:#C2D1C9;border-radius:10px 0 0 10px;"><i class="fa fa-lock" aria-hidden="true"></i></span>
                                <input id="loginPassword" name="password" type="password" class="form-control" placeholder="Masukkan password" autocomplete="current-password" required style="border-radius:0;">
                                <button type="button" class="btn" id="togglePasswordBtn" aria-label="Tampilkan atau sembunyikan password" aria-pressed="false" style="background-color:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.16);border-left:none;color:#D2DFD8;border-radius:0 10px 10px 0;"><i class="fa fa-eye" id="togglePasswordIcon" aria-hidden="true"></i></button>
                            </div>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary" style="padding:11px;font-size:0.95rem;border-radius:10px;">
                                <i class="fa fa-sign-in-alt me-2" aria-hidden="true"></i>Masuk ke Studio
                            </button>
                        </div>
                    </form>
                </div>
                <p class="text-center small mt-3 mb-0" style="color:#A8BAB2;letter-spacing:0.08em;font-size:0.75rem;">ADMIN &middot; OWNER &middot; COACH &middot; ORANG TUA</p>
            </div>
        </div>
    </main>
    <script>
    (function() {
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const pwInput = document.getElementById('loginPassword');
        const pwIcon = document.getElementById('togglePasswordIcon');
        if (toggleBtn && pwInput && pwIcon) {
            toggleBtn.addEventListener('click', function() {
                const isPassword = pwInput.type === 'password';
                pwInput.type = isPassword ? 'text' : 'password';
                pwIcon.className = isPassword ? 'fa fa-eye-slash' : 'fa fa-eye';
                toggleBtn.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
            });
        }
    })();
    </script>
</body>

</html>
