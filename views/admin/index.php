
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 form-inline flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <h3 class="mb-0 fw-bold" style="font-family:'Outfit',sans-serif;letter-spacing:-0.02em;"><i class="fa fa-user-shield me-2" style="color:var(--primary);"></i>Manajemen Akun</h3>
            <span class="filter-counter-badge" id="counterAdmin">Total: <?= count($rows) ?> akun</span>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <div class="position-relative">
                <input id="searchAdminLive" data-live-search="#tableAdmin" data-counter-target="#counterAdmin" class="form-control form-control-sm" style="max-width:240px;padding-left:32px;border-radius:8px;" placeholder="Cari username / nama / role…">
                <i class="fa fa-search position-absolute top-50 translate-middle-y text-muted" style="left:12px;font-size:0.82rem;"></i>
            </div>
            <div class="dropdown filter-dropdown-container" data-table-target="#tableAdmin">
                <button class="btn btn-sm btn-filter-trigger dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="border-radius:8px;">
                    <i class="fa fa-sliders-h me-1" style="color:var(--accent);"></i> Filter
                    <span class="filter-count-badge ms-1" style="display:none;">0</span>
                </button>
                <div class="dropdown-menu filter-dropdown-menu shadow-lg">
                    <div class="filter-header">
                        <span class="filter-title"><i class="fa fa-sliders-h me-1 text-accent"></i> Filter Data</span>
                        <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 btn-reset-filters"><i class="fa fa-undo me-1"></i>Reset</button>
                    </div>
                    <div>
                        <label class="filter-label">Role Pengguna</label>
                        <select data-table-target="#tableAdmin" data-live-filter-col="3" class="form-select form-select-sm">
                            <option value="">Semua Role</option>
                            <option value="owner">Owner</option>
                            <option value="admin">Admin</option>
                            <option value="coach">Coach</option>
                            <option value="orang_tua">Orang Tua</option>
                        </select>
                    </div>
                </div>
            </div>
            <button class="btn btn-primary btn-add btn-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalAddAdmin" style="border-radius:8px;font-weight:600;">
                <i class="fa fa-plus-circle me-1"></i> Tambah Akun
            </button>
        </div>
    </div>

    <div id="adminListRegion" data-async-region>
    <div class="card bento-hero-card p-3 p-md-4 mb-4">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tableAdmin">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Nama</th>
                    <th>Role</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $roleStyles = [
                  'owner' => 'background:linear-gradient(135deg,rgba(168,144,98,0.18),rgba(168,144,98,0.08));color:#8a7548;',
                  'admin' => 'background:rgba(74,107,93,0.12);color:var(--primary);',
                  'coach' => 'background:rgba(26,54,45,0.10);color:var(--primary-dark);',
                  'orang_tua' => 'background:rgba(41,128,185,0.14);color:#2980b9;',
                ];
                $roleLabels = [
                  'owner' => 'Owner',
                  'admin' => 'Admin',
                  'coach' => 'Coach',
                  'orang_tua' => 'Orang Tua',
                ];
                $modalsHtml = '';
                foreach ($rows as $r):
                  $roleKey = $r['role'] ?? 'admin';
                  $rs = $roleStyles[$roleKey] ?? $roleStyles['admin'];
                  $rLabel = $roleLabels[$roleKey] ?? ucfirst($roleKey);
                  $kids = $ortuMuridMap[$r['id']] ?? [];
                  $selectedKidIds = array_map(fn($k) => (int)$k['murid_id'], $kids);
                ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><strong><?= htmlspecialchars($r['username']) ?></strong></td>
                        <td>
                            <div><?= htmlspecialchars($r['nama']) ?></div>
                            <?php if ($roleKey === 'orang_tua'): ?>
                                <?php if (!empty($kids)): ?>
                                    <div class="small text-muted mt-1" style="font-size:0.8rem;">
                                        <i class="fa fa-child me-1" style="color:var(--primary);"></i>Anak: 
                                        <strong><?= htmlspecialchars(implode(', ', array_column($kids, 'nama'))) ?></strong>
                                    </div>
                                <?php else: ?>
                                    <div class="small text-danger mt-1" style="font-size:0.8rem;">
                                        <i class="fa fa-exclamation-triangle me-1"></i>Belum dihubungkan ke anak
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td data-filter-val="<?= strtolower($roleKey) ?>"><span class="badge" style="<?= $rs ?>font-weight:600;padding:5px 12px;border-radius:20px;"><?= htmlspecialchars($rLabel) ?></span></td>
                        <td><span class="text-muted"><?= $r['created_at'] ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-edit" data-bs-toggle="modal" data-bs-target="#modalEditAdmin<?= $r['id'] ?>">
                                <i class="fa fa-edit"></i>
                            </button>

                            <?php
                            $isSelf = ($r['id'] == $currentId);
                            $isLastOwner = (($r['role'] ?? '') === 'owner' && $ownerCount <= 1);
                            ?>
                            <form method="post" action="admin.php" style="display:inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <button class="btn btn-sm btn-delete" type="button" <?= ($isSelf || $isLastOwner) ? 'disabled' : '' ?> data-bs-toggle="modal" data-bs-target="#modalDeleteConfirm" data-delete-title="Hapus Akun" data-delete-message="Akun <?= htmlspecialchars($r['username'] ?? '', ENT_QUOTES) ?> akan dihapus. Tindakan ini tidak bisa dibatalkan." data-delete-confirm="Hapus Akun">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    <?php ob_start(); ?>
                    <div class="modal fade modal-admin-form" id="modalEditAdmin<?= $r['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="post" action="admin.php" data-ajax-form data-refresh-region="#adminListRegion" onsubmit="return validateAdminForm(this)">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><i class="fa fa-user-edit me-2" style="color:var(--gold);"></i>Edit Akun</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-2"><label>Username</label>
                                            <input name="username" class="form-control" required value="<?= htmlspecialchars($r['username']) ?>">
                                        </div>
                                        <div class="mb-2"><label>Nama Lengkap</label>
                                            <input name="nama" class="form-control" required value="<?= htmlspecialchars($r['nama']) ?>">
                                        </div>
                                        <div class="mb-2"><label>Role</label>
                                            <select name="role" class="form-select role-select" onchange="handleRoleChange(this)">
                                                <?php foreach ($roleLabels as $val => $label): ?>
                                                    <option value="<?= $val ?>" <?= ($roleKey === $val ? 'selected' : '') ?>><?= $label ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <!-- Container Pilih Anak (Searchable) -->
                                        <div class="mb-3 p-2 rounded border child-picker-container" style="background:rgba(255,255,255,0.03); <?= ($roleKey === 'orang_tua' ? '' : 'display:none;') ?>">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label class="mb-0 fw-semibold" style="font-size:0.88rem;"><i class="fa fa-child me-1 text-primary"></i>Pilih Anak (Murid)</label>
                                                <span class="badge bg-secondary selected-badge" style="font-size:0.75rem;"><?= count($selectedKidIds) ?> dipilih</span>
                                            </div>
                                            <div class="input-group input-group-sm mb-2 mt-1">
                                                <span class="input-group-text"><i class="fa fa-search"></i></span>
                                                <input type="text" class="form-control child-search-input" placeholder="Ketik nama anak untuk mencari…" onkeyup="filterChildList(this)" autocomplete="off">
                                            </div>
                                            <div class="child-checkbox-list border rounded p-2" style="max-height:160px; overflow-y:auto; background:var(--bg-card);">
                                                <?php foreach ($allMurid as $m): 
                                                    $isKidSelected = in_array((int)$m['id'], $selectedKidIds, true);
                                                ?>
                                                    <div class="form-check child-item py-1">
                                                        <input class="form-check-input child-checkbox" type="checkbox" name="murid_ids[]" value="<?= $m['id'] ?>" id="edit_kid_<?= $r['id'] ?>_<?= $m['id'] ?>" <?= $isKidSelected ? 'checked' : '' ?> onchange="updateSelectedCount(this)">
                                                        <label class="form-check-label w-100" for="edit_kid_<?= $r['id'] ?>_<?= $m['id'] ?>" style="cursor:pointer;">
                                                            <?= htmlspecialchars($m['nama']) ?>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                                <?php if (empty($allMurid)): ?>
                                                    <div class="text-muted small py-2 text-center">Belum ada murid terdaftar.</div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-danger small mt-1 child-error-msg d-none"><i class="fa fa-exclamation-circle me-1"></i>Pilih minimal satu anak untuk akun orang tua.</div>
                                            <small class="text-muted d-block mt-1" style="font-size:0.75rem;">* Dapat memilih lebih dari 1 anak jika orang tua memiliki beberapa anak les di studio.</small>
                                        </div>

                                        <div class="mb-2"><label>Password Baru (opsional)</label>
                                            <input name="password" type="password" class="form-control" placeholder="Isi jika ingin ganti password">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button class="btn btn-success">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php $modalsHtml .= ob_get_clean(); endforeach; ?>
            </tbody>
        </table>
        </div>
        <div class="pagination-container d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3" data-pagination-table="#tableAdmin">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <small class="text-muted pagination-summary">Menampilkan 1-<?= min(10, count($rows)) ?> dari <?= count($rows) ?> data</small>
                <div class="limit-switch">
                    <button type="button" class="limit-pill active" data-limit="10">10</button>
                    <button type="button" class="limit-pill" data-limit="20">20</button>
                    <button type="button" class="limit-pill" data-limit="100">100</button>
                    <button type="button" class="limit-pill" data-limit="all">Semua</button>
                </div>
            </div>
            <nav aria-label="Pagination akun admin" class="pagination-nav">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item page-prev disabled">
                        <button type="button" class="page-link page-arrow" aria-label="Halaman sebelumnya" title="Halaman sebelumnya"><i class="fa fa-chevron-left"></i></button>
                    </li>
                    <li class="page-item disabled"><span class="page-link page-status-pill">Hal. 1 / <?= max(1, (int)ceil(count($rows) / 10)) ?></span></li>
                    <li class="page-item page-next <?= count($rows) <= 10 ? 'disabled' : '' ?>">
                        <button type="button" class="page-link page-arrow" aria-label="Halaman berikutnya" title="Halaman berikutnya"><i class="fa fa-chevron-right"></i></button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
    </div>
</div>

<?= $modalsHtml ?>

<div class="modal fade modal-admin-form" id="modalAddAdmin" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="admin.php" data-ajax-form data-refresh-region="#adminListRegion" onsubmit="return validateAdminForm(this)">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-user-plus me-2" style="color:var(--success);"></i>Tambah Akun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><label>Username</label><input name="username" class="form-control" required autocomplete="username"></div>
                    <div class="mb-2"><label>Nama Lengkap</label><input name="nama" class="form-control" required></div>
                    <div class="mb-2"><label>Role</label>
                        <select name="role" class="form-select role-select" onchange="handleRoleChange(this)">
                            <option value="coach">Coach</option>
                            <option value="admin">Admin</option>
                            <option value="owner">Owner</option>
                            <option value="orang_tua">Orang Tua</option>
                        </select>
                    </div>

                    <!-- Container Pilih Anak (Searchable) -->
                    <div class="mb-3 p-2 rounded border child-picker-container" style="background:rgba(255,255,255,0.03); display:none;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="mb-0 fw-semibold" style="font-size:0.88rem;"><i class="fa fa-child me-1 text-primary"></i>Pilih Anak (Murid)</label>
                            <span class="badge bg-secondary selected-badge" style="font-size:0.75rem;">0 dipilih</span>
                        </div>
                        <div class="input-group input-group-sm mb-2 mt-1">
                            <span class="input-group-text"><i class="fa fa-search"></i></span>
                            <input type="text" class="form-control child-search-input" placeholder="Ketik nama anak untuk mencari…" onkeyup="filterChildList(this)" autocomplete="off">
                        </div>
                        <div class="child-checkbox-list border rounded p-2" style="max-height:160px; overflow-y:auto; background:var(--bg-card);">
                            <?php foreach ($allMurid as $m): ?>
                                <div class="form-check child-item py-1">
                                    <input class="form-check-input child-checkbox" type="checkbox" name="murid_ids[]" value="<?= $m['id'] ?>" id="add_kid_<?= $m['id'] ?>" onchange="updateSelectedCount(this)">
                                    <label class="form-check-label w-100" for="add_kid_<?= $m['id'] ?>" style="cursor:pointer;">
                                        <?= htmlspecialchars($m['nama']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($allMurid)): ?>
                                <div class="text-muted small py-2 text-center">Belum ada murid terdaftar.</div>
                            <?php endif; ?>
                        </div>
                        <div class="text-danger small mt-1 child-error-msg d-none"><i class="fa fa-exclamation-circle me-1"></i>Pilih minimal satu anak untuk akun orang tua.</div>
                        <small class="text-muted d-block mt-1" style="font-size:0.75rem;">* Dapat memilih lebih dari 1 anak jika orang tua memiliki beberapa anak les di studio.</small>
                    </div>

                    <div class="mb-2"><label>Password</label><input name="password" type="password" class="form-control" required autocomplete="new-password"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function handleRoleChange(selectElem) {
    const form = selectElem.closest('form');
    const container = form.querySelector('.child-picker-container');
    if (!container) return;
    if (selectElem.value === 'orang_tua') {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
        const errorMsg = container.querySelector('.child-error-msg');
        if (errorMsg) errorMsg.classList.add('d-none');
    }
}

function filterChildList(inputElem) {
    const query = inputElem.value.toLowerCase().trim();
    const container = inputElem.closest('.child-picker-container');
    const items = container.querySelectorAll('.child-item');
    items.forEach(function(item) {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(query) ? '' : 'none';
    });
}

function updateSelectedCount(checkboxElem) {
    const container = checkboxElem.closest('.child-picker-container');
    const checked = container.querySelectorAll('.child-checkbox:checked').length;
    const badge = container.querySelector('.selected-badge');
    if (badge) {
        badge.textContent = checked + ' dipilih';
        badge.className = checked > 0 ? 'badge bg-success selected-badge' : 'badge bg-secondary selected-badge';
    }
    const errorMsg = container.querySelector('.child-error-msg');
    if (errorMsg && checked > 0) {
        errorMsg.classList.add('d-none');
    }
}

function validateAdminForm(form) {
    const roleSelect = form.querySelector('.role-select');
    if (roleSelect && roleSelect.value === 'orang_tua') {
        const checked = form.querySelectorAll('.child-checkbox:checked').length;
        const errorMsg = form.querySelector('.child-error-msg');
        if (checked === 0) {
            if (errorMsg) errorMsg.classList.remove('d-none');
            return false;
        }
    }
    return true;
}
</script>

