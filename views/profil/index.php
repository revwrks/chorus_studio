<?php
$avatarSource = (string)($userData['nama'] ?? $userData['username'] ?? 'U');
$avatarInitial = mb_strtoupper(mb_substr(trim($avatarSource) !== '' ? trim($avatarSource) : 'U', 0, 1));
?>
<div class="container-fluid">
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fa fa-user-cog me-2" style="color:var(--primary);" aria-hidden="true"></i>Manajemen Akun
            </h1>
            <p class="text-muted small mb-0 mt-1">
                Kelola informasi akun login, nama lengkap, dan kata sandi Anda.
            </p>
        </div>
        <div>
            <a href="index.php" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center" style="min-height:38px;">
                <i class="fa fa-arrow-left me-1" aria-hidden="true"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="card p-4 p-md-5 mb-4">
                <!-- User Identity Cardlet -->
                <div class="d-flex align-items-center gap-3 p-3 rounded-3 mb-4" style="background:var(--primary-soft);border:1px solid var(--border);">
                    <div style="width:58px;height:58px;border-radius:16px;background:var(--primary-btn-bg);color:var(--primary-btn-text);display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:700;box-shadow:var(--shadow-sm);border:2px solid var(--accent);flex-shrink:0;" aria-hidden="true">
                        <?= e($avatarInitial) ?>
                    </div>
                    <div class="min-w-0">
                        <h2 class="h5 mb-0 fw-bold text-truncate" style="color:var(--text);letter-spacing:-0.01em;"><?= e($userData['nama'] ?? '') ?></h2>
                        <div class="small text-muted mt-1 d-flex align-items-center flex-wrap gap-2">
                            <span class="bento-badge">
                                <i class="fa fa-shield-alt" aria-hidden="true"></i><?= e($roleLabel ?? '') ?>
                            </span>
                            <span>@<?= e($userData['username'] ?? '') ?></span>
                        </div>
                    </div>
                </div>

                <form method="post" action="profil.php">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="inputNama" class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="inputNama" name="nama" required value="<?= e($userData['nama'] ?? '') ?>" placeholder="Masukkan nama lengkap Anda" style="min-height:44px;">
                    </div>

                    <div class="mb-3">
                        <label for="inputUsername" class="form-label fw-semibold">Username Login <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text" style="background:var(--surface-subtle);border-color:var(--border);color:var(--text-muted);"><i class="fa fa-at" aria-hidden="true"></i></span>
                            <input type="text" class="form-control" id="inputUsername" name="username" required value="<?= e($userData['username'] ?? '') ?>" placeholder="Username login" autocomplete="username" style="min-height:44px;">
                        </div>
                        <div class="form-text small">Username digunakan untuk masuk ke akun Chorus Studio.</div>
                    </div>

                    <div class="mb-3">
                        <label for="inputRole" class="form-label fw-semibold">Peran Akun (Role)</label>
                        <input type="text" class="form-control" id="inputRole" value="<?= e($roleLabel ?? '') ?>" readonly disabled style="min-height:44px;background:var(--surface-subtle);color:var(--text-secondary);cursor:not-allowed;">
                        <div class="form-text small">Peran akun ditentukan oleh pihak manajemen Chorus Studio.</div>
                    </div>

                    <?php if (($roleKey ?? '') === 'orang_tua'): ?>
                        <div class="p-3 rounded-3 mb-4" style="background:var(--primary-soft);border:1px solid var(--border);">
                            <div class="form-label fw-semibold mb-2 d-flex align-items-center" style="color:var(--primary);">
                                <i class="fa fa-child me-2" aria-hidden="true"></i>Data Anak yang Terhubung
                            </div>
                            <?php if (!empty($connectedKids)): ?>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($connectedKids as $kid): ?>
                                        <span class="badge p-2 d-inline-flex align-items-center gap-1 shadow-sm" style="background:var(--surface);color:var(--text);border:1px solid var(--border);border-radius:10px;font-size:0.85rem;">
                                            <i class="fa fa-user-graduate me-1" style="color:var(--success);" aria-hidden="true"></i>
                                            <strong><?= e($kid['nama'] ?? '') ?></strong>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-danger small"><i class="fa fa-exclamation-triangle me-1" aria-hidden="true"></i>Belum ada data anak yang dihubungkan ke akun ini.</div>
                            <?php endif; ?>
                            <small class="text-muted d-block mt-2" style="font-size:0.8rem;line-height:1.4;">
                                <i class="fa fa-lock me-1 text-accent" aria-hidden="true"></i>Hubungan akun dengan murid hanya dapat diubah oleh Admin atau Owner Chorus Studio demi keamanan dan privasi data anak.
                            </small>
                        </div>
                    <?php endif; ?>

                    <hr class="my-4" style="border-color:var(--border);opacity:1;">

                    <div class="mb-3">
                        <h2 class="h6 fw-bold mb-1" style="color:var(--text);">
                            <i class="fa fa-key me-2 text-accent" aria-hidden="true"></i>Ubah Kata Sandi (Opsional)
                        </h2>
                        <small class="text-muted d-block mb-3">Kosongkan kolom di bawah jika Anda tidak ingin mengganti kata sandi akun saat ini.</small>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="inputPasswordBaru" class="form-label fw-semibold">Kata Sandi Baru</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="inputPasswordBaru" name="password_baru" placeholder="Minimal 6 karakter" autocomplete="new-password" style="min-height:44px;">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('inputPasswordBaru', this)" aria-label="Lihat kata sandi" aria-pressed="false">
                                        <i class="fa fa-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <div class="form-text small">Minimal 6 karakter.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="inputKonfirmasiPassword" class="form-label fw-semibold">Konfirmasi Kata Sandi Baru</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="inputKonfirmasiPassword" name="konfirmasi_password" placeholder="Ulangi kata sandi baru" autocomplete="new-password" style="min-height:44px;">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('inputKonfirmasiPassword', this)" aria-label="Lihat kata sandi" aria-pressed="false">
                                        <i class="fa fa-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end flex-wrap gap-2 mt-4 pt-2">
                        <a href="index.php" class="btn btn-secondary d-inline-flex align-items-center" style="min-height:44px;padding:0 20px;">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-2" style="min-height:44px;padding:0 24px;font-weight:600;">
                            <i class="fa fa-check-circle" aria-hidden="true"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
