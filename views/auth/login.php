<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#121e1a">
    <link rel="icon" href="favicon.ico?v=<?= urlencode((string)$faviconVersion) ?>" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="style/main.css?v=<?= urlencode((string)$styleVersion) ?>" rel="stylesheet">
    <title>Login - Chorus Studio</title>
</head>

<body class="d-flex align-items-center justify-content-center login-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card p-4 shadow-lg login-card">
                    <div class="text-center mb-4">
                        <div style="width:64px;height:64px;margin:0 auto 16px;background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:16px;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 25px rgba(74,107,93,0.35);">
                            <i class="fa fa-music" style="font-size:28px;color:#fff;"></i>
                        </div>
                        <h3 class="mb-1">Chorus Studio</h3>
                        <div class="text-muted" style="font-size:0.9rem;">Masuk untuk melanjutkan</div>
                    </div>
                    <?php if ($err): ?>
                        <div class="alert alert-danger"><i class="fa fa-exclamation-circle me-2"></i><?= htmlspecialchars($err) ?></div>
                    <?php endif; ?>
                    <form method="post" autocomplete="on">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);color:rgba(255,255,255,0.5);border-radius:10px 0 0 10px;"><i class="fa fa-user" aria-hidden="true"></i></span>
                                <input name="username" class="form-control" placeholder="Username" autocomplete="username" aria-label="Username" required style="border-radius:0 10px 10px 0;">
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);color:rgba(255,255,255,0.5);border-radius:10px 0 0 10px;"><i class="fa fa-lock" aria-hidden="true"></i></span>
                                <input id="loginPassword" name="password" type="password" class="form-control" placeholder="Password" autocomplete="current-password" aria-label="Password" required style="border-radius:0;">
                                <button type="button" class="btn" id="togglePasswordBtn" aria-label="Lihat Password" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-left:none;color:rgba(255,255,255,0.6);border-radius:0 10px 10px 0;"><i class="fa fa-eye" id="togglePasswordIcon"></i></button>
                            </div>
                        </div>
                        <div class="d-grid mt-4">
                            <button class="btn btn-primary" style="padding:11px;font-size:0.95rem;border-radius:10px;"><i class="fa fa-sign-in-alt me-2"></i>Masuk ke Studio</button>
                        </div>
                    </form>
                </div>
                <div class="text-center small mt-3" style="color:rgba(255,255,255,0.35);letter-spacing:1px;font-size:0.75rem;">ADMIN • OWNER • COACH • ORANG TUA</div>
            </div>
        </div>
    </div>
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
            });
        }
    })();
    </script>
</body>

</html>
