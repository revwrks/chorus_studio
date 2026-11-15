
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="mb-1" style="font-family:'Outfit',sans-serif;font-weight:700;">
                <i class="fa fa-user-cog me-2" style="color:var(--primary);"></i>Manajemen Akun
            </h3>
            <div class="text-muted" style="font-size:0.9rem;">
                Kelola informasi akun login, nama lengkap, dan kata sandi Anda.
            </div>
        </div>
        <div>
            <a href="index.php" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center" style="min-height:38px;">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="card bento-hero-card p-4 p-md-5 mb-4" style="border-top:3px solid var(--accent);">
                <!-- User Identity Cardlet -->
                <div class="d-flex align-items-center gap-3 p-3 rounded-3 mb-4" style="background:linear-gradient(135deg,rgba(35,72,59,0.08),rgba(197,160,89,0.08));border:1px solid rgba(35,72,59,0.15);">
                    <div style="width:58px;height:58px;border-radius:16px;background:linear-gradient(135deg,var(--primary),var(--accent));color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.6rem;font-weight:700;box-shadow:var(--shadow-sm);border:2px solid var(--accent);">
                        <?= strtoupper(substr($userData['nama'] ?? $userData['username'], 0, 1)) ?>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold" style="font-family:'Outfit',sans-serif;color:var(--text);letter-spacing:-0.01em;"><?= htmlspecialchars($userData['nama']) ?></h5>
                        <div class="small text-muted mt-1">
                            <span class="badge bento-badge" style="background:rgba(35,72,59,0.1);color:var(--primary);">
                                <i class="fa fa-shield-alt me-1"></i><?= htmlspecialchars($roleLabel) ?>
                            </span>
                            <span class="ms-2">@<?= htmlspecialchars($userData['username']) ?></span>
                        </div>
                    </div>
                </div>

                <form method="post" action="profil.php">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="inputNama" class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="inputNama" name="nama" required value="<?= htmlspecialchars($userData['nama']) ?>" placeholder="Masukkan nama lengkap Anda" style="min-height:44px;border-radius:10px;">
                    </div>

                    <div class="mb-3">
                        <label for="inputUsername" class="form-label fw-semibold">Username Login <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text" style="border-radius:10px 0 0 10px;background:var(--bg-subtle);"><i class="fa fa-at text-muted"></i></span>
                            <input type="text" class="form-control" id="inputUsername" name="username" required value="<?= htmlspecialchars($userData['username']) ?>" placeholder="Username login" autocomplete="username" style="min-height:44px;border-radius:0 10px 10px 0;">
                        </div>
                        <div class="form-text small">Username digunakan untuk masuk ke akun Chorus Studio.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Peran Akun (Role)</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($roleLabel) ?>" disabled style="min-height:44px;border-radius:10px;background:rgba(0,0,0,0.04);cursor:not-allowed;">
                        <div class="form-text small">Peran akun ditentukan oleh pihak manajemen Chorus Studio.</div>
                    </div>

                    <?php if ($roleKey === 'orang_tua'): ?>
                        <div class="p-3 rounded mb-4" style="background:rgba(74,107,93,0.06);border:1px solid rgba(74,107,93,0.18);border-radius:12px;">
                            <label class="fw-semibold text-primary mb-2 d-flex align-items-center">
                                <i class="fa fa-child me-2"></i>Data Anak yang Terhubung
                            </label>
                            <?php if (!empty($connectedKids)): ?>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($connectedKids as $kid): ?>
                                        <div class="badge bg-white text-dark p-2 d-inline-flex align-items-center gap-1 shadow-sm" style="border:1px solid rgba(74,107,93,0.25);border-radius:10px;font-size:0.85rem;">
                                            <i class="fa fa-user-graduate text-success me-1"></i>
                                            <strong><?= htmlspecialchars($kid['nama']) ?></strong>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-danger small"><i class="fa fa-exclamation-triangle me-1"></i>Belum ada data anak yang dihubungkan ke akun ini.</div>
                            <?php endif; ?>
                            <small class="text-muted d-block mt-2" style="font-size:0.8rem;line-height:1.4;">
                                <i class="fa fa-lock me-1 text-accent"></i>Hubungan akun dengan murid hanya dapat diubah oleh Admin atau Owner Chorus Studio demi keamanan dan privasi data anak.
                            </small>
                        </div>
                    <?php endif; ?>

                    <hr class="my-4" style="opacity:0.15;">

                    <div class="mb-3">
                        <h6 class="fw-bold mb-1" style="font-family:'Outfit',sans-serif;color:var(--text);">
                            <i class="fa fa-key me-2" style="color:var(--gold);"></i>Ubah Kata Sandi (Opsional)
                        </h6>
                        <small class="text-muted d-block mb-3">Kosongkan kolom di bawah jika Anda tidak ingin mengganti kata sandi akun saat ini.</small>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="inputPasswordBaru" class="form-label fw-semibold">Kata Sandi Baru</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="inputPasswordBaru" name="password_baru" placeholder="Minimal 4 karakter" autocomplete="new-password" style="min-height:44px;border-radius:10px 0 0 10px;">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('inputPasswordBaru', this)" aria-label="Lihat kata sandi" style="border-radius:0 10px 10px 0;">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="inputKonfirmasiPassword" class="form-label fw-semibold">Konfirmasi Kata Sandi Baru</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="inputKonfirmasiPassword" name="konfirmasi_password" placeholder="Ulangi kata sandi baru" autocomplete="new-password" style="min-height:44px;border-radius:10px 0 0 10px;">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('inputKonfirmasiPassword', this)" aria-label="Lihat kata sandi" style="border-radius:0 10px 10px 0;">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-2">
                        <a href="index.php" class="btn btn-secondary d-inline-flex align-items-center" style="min-height:44px;padding:0 20px;border-radius:10px;">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-2" style="min-height:44px;padding:0 24px;border-radius:10px;font-weight:600;">
                            <i class="fa fa-check-circle"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    } else {
        input.type = 'password';
        if (icon) {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
}
</script>

