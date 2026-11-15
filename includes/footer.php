<!-- end content --></div>
</div>

<!-- Mobile Bottom Navigation Dock (Thumb-Zone Optimized for Mobile) -->
<nav class="mobile-bottom-nav d-md-none" aria-label="Navigasi Cepat Mobile">
  <?php if (!empty($isOrtu)): ?>
    <a href="index.php" class="mobile-bottom-nav-item <?= $currentPage === 'index.php' ? 'active' : '' ?>">
      <i class="fa fa-chart-pie" aria-hidden="true"></i>
      <span>Dashboard</span>
    </a>
    <a href="iringan.php" class="mobile-bottom-nav-item <?= $currentPage === 'iringan.php' ? 'active' : '' ?>">
      <i class="fa fa-headphones" aria-hidden="true"></i>
      <span>Iringan</span>
    </a>
    <a href="profil.php" class="mobile-bottom-nav-item <?= $currentPage === 'profil.php' ? 'active' : '' ?>">
      <i class="fa fa-user-cog" aria-hidden="true"></i>
      <span>Akun</span>
    </a>
  <?php else: 
    $isExtendedPage = !in_array($currentPage, ['index.php', 'pertemuan.php', 'rencana_pertemuan.php', 'murid.php'], true);
  ?>
    <a href="index.php" class="mobile-bottom-nav-item <?= $currentPage === 'index.php' ? 'active' : '' ?>">
      <i class="fa fa-chart-pie" aria-hidden="true"></i>
      <span>Dashboard</span>
    </a>
    <a href="pertemuan.php" class="mobile-bottom-nav-item <?= $currentPage === 'pertemuan.php' ? 'active' : '' ?>">
      <i class="fa fa-calendar-check" aria-hidden="true"></i>
      <span>Pertemuan</span>
    </a>
    <a href="rencana_pertemuan.php" class="mobile-bottom-nav-item <?= $currentPage === 'rencana_pertemuan.php' ? 'active' : '' ?>">
      <i class="fa fa-calendar-alt" aria-hidden="true"></i>
      <span>Target</span>
    </a>
    <a href="murid.php" class="mobile-bottom-nav-item <?= $currentPage === 'murid.php' ? 'active' : '' ?>">
      <i class="fa fa-users" aria-hidden="true"></i>
      <span>Murid</span>
    </a>
    <button type="button" class="mobile-bottom-nav-item btn-link border-0 bg-transparent <?= $isExtendedPage ? 'active' : '' ?>" id="bottomNavMenuToggle" aria-label="Buka Menu Lainnya">
      <i class="fa fa-bars" aria-hidden="true"></i>
      <span>Menu</span>
    </button>
  <?php endif; ?>
</nav>

<div class="modal fade" id="modalDeleteConfirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-triangle-exclamation me-2" style="color:var(--danger);"></i><span id="deleteModalTitle">Konfirmasi Hapus</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" id="deleteModalMessage">Data ini akan dihapus. Tindakan ini tidak bisa dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="deleteModalSubmit">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function() {
        const toggle = document.getElementById('themeToggle'),
            icon = document.getElementById('themeIcon'),
            root = document.documentElement;
        let pendingDeleteForm = null;

        function applyTheme(t) {
            root.classList.remove('theme-light', 'theme-dark');
            root.classList.add(t === 'dark' ? 'theme-dark' : 'theme-light');
            if (icon) icon.className = (t === 'dark') ? 'fa fa-sun' : 'fa fa-moon';
        }
        let theme = localStorage.getItem('chorus_theme');
        if (!theme) {
            theme = 'light';
            localStorage.setItem('chorus_theme', 'light');
        }
        applyTheme(theme);
        if (toggle) {
            toggle.addEventListener('click', () => {
                theme = (theme === 'light') ? 'dark' : 'light';
                localStorage.setItem('chorus_theme', theme);
                root.classList.add('theme-transition');
                applyTheme(theme);
                setTimeout(() => root.classList.remove('theme-transition'), 450);
            });
        }
        const hb = document.getElementById('hamburger'),
            sb = document.getElementById('sidebar'),
            bd = document.getElementById('sidebarBackdrop'),
            closeBtn = document.getElementById('closeSidebar');

        function openSidebar() {
            if (!sb) return;
            sb.classList.add('sidebar-open');
            if (bd) bd.classList.add('show');
            document.body.classList.add('sidebar-active');
            if (hb) hb.setAttribute('aria-expanded', 'true');
        }

        function closeSidebar() {
            if (!sb) return;
            sb.classList.remove('sidebar-open');
            if (bd) bd.classList.remove('show');
            document.body.classList.remove('sidebar-active');
            if (hb) hb.setAttribute('aria-expanded', 'false');
        }

        hb?.addEventListener('click', (e) => {
            e.stopPropagation();
            if (sb?.classList.contains('sidebar-open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        const bottomNavMenuToggle = document.getElementById('bottomNavMenuToggle');
        bottomNavMenuToggle?.addEventListener('click', (e) => {
            e.stopPropagation();
            if (sb?.classList.contains('sidebar-open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        bd?.addEventListener('click', closeSidebar);
        closeBtn?.addEventListener('click', closeSidebar);

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && sb?.classList.contains('sidebar-open')) {
                closeSidebar();
            }
        });

        sb?.querySelectorAll('.list-group-item').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) closeSidebar();
            });
        });

        function adapt() {
            if (window.innerWidth > 768) {
                closeSidebar();
                if (sb) sb.style.transform = '';
            }
        }
        window.addEventListener('resize', adapt);
        adapt();
        // ========== TOAST NOTIFICATION ENGINE ==========
        window.showToast = function(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            if (!container) return;

            const toast = document.createElement('div');
            const isSuccess = type === 'success';
            const isDanger = type === 'danger';
            const iconClass = isSuccess ? 'fa-check-circle text-success' : (isDanger ? 'fa-exclamation-circle text-danger' : 'fa-info-circle text-info');

            toast.className = `custom-toast toast-${type} p-3 mb-2 d-flex align-items-center justify-content-between shadow-sm`;
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="toast-accent-bar"></div>
                <div class="d-flex align-items-center me-3 ms-2">
                    <i class="fa ${iconClass} toast-icon me-2"></i>
                    <span style="font-size:0.88rem;font-weight:500;">${message}</span>
                </div>
                <button type="button" class="btn-close btn-close-sm ms-auto" aria-label="Tutup"></button>
            `;

            const closeBtn = toast.querySelector('.btn-close');
            let isDismissed = false;
            const dismiss = () => {
                if (isDismissed) return;
                isDismissed = true;
                toast.classList.add('toast-hiding');
                setTimeout(() => toast.remove(), 350);
            };
            closeBtn.addEventListener('click', dismiss);
            setTimeout(dismiss, 3800);

            container.appendChild(toast);
        };

        // ========== CLIENT-SIDE IMAGE COMPRESSOR ==========
        function formatBytes(bytes) {
            if (!bytes || bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }

        async function compressImageFile(file, maxWidth = 800, maxHeight = 800, quality = 0.82) {
            return new Promise((resolve) => {
                if (!file || !file.type.startsWith('image/')) {
                    resolve({ blob: file, originalSize: file ? file.size : 0, compressedSize: file ? file.size : 0 });
                    return;
                }
                // If file is already tiny (< 120KB), keep as is
                if (file.size <= 120 * 1024) {
                    resolve({ blob: file, originalSize: file.size, compressedSize: file.size });
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = new Image();
                    img.onload = function() {
                        let width = img.width;
                        let height = img.height;

                        if (width > height) {
                            if (width > maxWidth) {
                                height = Math.round((height * maxWidth) / width);
                                width = maxWidth;
                            }
                        } else {
                            if (height > maxHeight) {
                                width = Math.round((width * maxHeight) / height);
                                height = maxHeight;
                            }
                        }

                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        const outType = file.type === 'image/png' ? 'image/jpeg' : (file.type || 'image/jpeg');
                        canvas.toBlob((blob) => {
                            if (!blob || blob.size >= file.size) {
                                resolve({ blob: file, originalSize: file.size, compressedSize: file.size });
                            } else {
                                resolve({ blob: blob, originalSize: file.size, compressedSize: blob.size });
                            }
                        }, outType, quality);
                    };
                    img.onerror = function() {
                        resolve({ blob: file, originalSize: file.size, compressedSize: file.size });
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            });
        }

        // Modal reset & cleanup
        document.querySelectorAll('.modal').forEach(m => m.addEventListener('hidden.bs.modal', function() {
            this.querySelectorAll('form').forEach(f => {
                f.reset();
                f.querySelectorAll('input[type="file"]').forEach(inp => {
                    inp._compressedBlob = null;
                });
            });
            this.querySelectorAll('img.thumb-preview-circle, img.thumb-preview').forEach(img => {
                // If original source is preserved via data-original-src, restore it; otherwise clear
                const orig = img.getAttribute('data-original-src');
                if (orig) {
                    img.src = orig;
                    img.style.display = '';
                } else {
                    img.src = '';
                    img.style.display = 'none';
                }
            });
            this.querySelectorAll('.preview-placeholder-circle').forEach(ph => {
                const imgId = ph.id.replace('placeholder', 'preview');
                const img = document.getElementById(imgId);
                if (img && img.src && img.src !== window.location.href) {
                    ph.style.display = 'none';
                } else {
                    ph.style.display = '';
                }
            });
            this.querySelectorAll('[id^="compressInfo"]').forEach(info => {
                info.style.display = 'none';
                info.innerHTML = '';
            });
        }));

        // Enhanced Image Preview + Instant Canvas Compressor Listener
        document.addEventListener('change', async function(e) {
            const input = e.target;
            if (!input.matches('input[type="file"][data-preview-target]')) return;
            const target = document.querySelector(input.getAttribute('data-preview-target'));
            const placeholder = input.hasAttribute('data-placeholder-target') 
                ? document.querySelector(input.getAttribute('data-placeholder-target')) 
                : null;
            const infoEl = input.hasAttribute('data-compress-info')
                ? document.querySelector(input.getAttribute('data-compress-info'))
                : null;
            const zoomBtn = input.hasAttribute('data-zoom-btn')
                ? document.querySelector(input.getAttribute('data-zoom-btn'))
                : null;

            if (!target) return;
            const file = input.files && input.files[0];
            if (!file) {
                target.src = target.getAttribute('data-original-src') || '';
                if (!target.src) {
                    target.style.display = 'none';
                    target.removeAttribute('data-zoom-src');
                    if (placeholder) placeholder.style.display = '';
                    if (zoomBtn) zoomBtn.style.display = 'none';
                } else {
                    target.setAttribute('data-zoom-src', target.src);
                    if (zoomBtn) zoomBtn.style.display = 'inline-block';
                }
                if (infoEl) infoEl.style.display = 'none';
                input._compressedBlob = null;
                input._compressionPromise = null;
                return;
            }

            // 1. Instant preview
            const reader = new FileReader();
            reader.onload = function(ev) {
                target.src = ev.target.result;
                target.setAttribute('data-zoom-src', ev.target.result);
                target.style.display = 'inline-block';
                if (placeholder) placeholder.style.display = 'none';
                if (zoomBtn) zoomBtn.style.display = 'inline-block';
            };
            reader.readAsDataURL(file);

            // 2. Compress image if data-compress-image is present or image file
            if (input.hasAttribute('data-compress-image') || file.type.startsWith('image/')) {
                if (infoEl) {
                    infoEl.style.display = 'inline-block';
                    infoEl.innerHTML = '<span class="compress-badge"><i class="fa fa-spinner fa-spin me-1"></i> Mengompres…</span>';
                }

                // Store promise so form submit can await it safely without race condition
                input._compressionPromise = compressImageFile(file).then(result => {
                    input._compressedBlob = result.blob;
                    if (infoEl) {
                        const savedPct = Math.max(0, Math.round(((result.originalSize - result.compressedSize) / result.originalSize) * 100));
                        infoEl.innerHTML = `<span class="compress-badge" title="Ukuran asli: ${formatBytes(result.originalSize)}"><i class="fa fa-compress me-1"></i> ${formatBytes(result.originalSize)} &rarr; ${formatBytes(result.compressedSize)} (${savedPct}% lebih hemat)</span>`;
                    }

                    // Also inject into input.files via DataTransfer for standard form POST fallback
                    if (window.DataTransfer && result.blob) {
                        try {
                            const dt = new DataTransfer();
                            const origName = file.name.replace(/\.[^/.]+$/, "") + ".jpg";
                            const newFile = new File([result.blob], origName, { type: result.blob.type || 'image/jpeg' });
                            dt.items.add(newFile);
                            input.files = dt.files;
                        } catch (err) {
                            // Silently ignore if browser restricts DataTransfer
                        }
                    }
                    return result;
                });
            }
        });

        // Universal Image Zoom / Lightbox Trigger
        document.addEventListener('click', function(e) {
            const trigger = e.target.closest('[data-zoom-src]');
            if (!trigger) return;
            e.preventDefault();

            const src = trigger.getAttribute('data-zoom-src') || (trigger.tagName === 'IMG' ? trigger.src : '');
            const title = trigger.getAttribute('data-zoom-title') || 'Foto Murid';
            if (!src || src === window.location.href) return;

            const modalEl = document.getElementById('modalImageZoom');
            const imgEl = document.getElementById('modalImageZoomImg');
            const titleEl = document.getElementById('modalImageZoomTitle');
            const downloadEl = document.getElementById('modalImageZoomDownload');

            if (modalEl && imgEl) {
                imgEl.src = src;
                if (titleEl) titleEl.innerHTML = `<i class="fa fa-user-circle me-2" style="color:var(--accent);"></i>Foto: ${title}`;
                if (downloadEl) downloadEl.href = src;
                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.show();
            }
        });

        // ========== AJAX FORM SUBMISSION ENGINE ==========
        document.addEventListener('submit', async function(e) {
            const form = e.target.closest('form[data-ajax-form]');
            if (!form) return;
            e.preventDefault();

            const submitBtn = form.querySelector('button[type="submit"]') || form.querySelector('.modal-footer .btn-success') || form.querySelector('.modal-footer .btn-primary');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : 'Simpan';

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan…';
            }

            try {
                // Await any pending client-side image compression before packaging FormData
                const fileInputs = form.querySelectorAll('input[type="file"]');
                for (const fileInput of fileInputs) {
                    if (fileInput._compressionPromise) {
                        await fileInput._compressionPromise;
                    } else if (fileInput.files && fileInput.files[0] && (fileInput.hasAttribute('data-compress-image') || fileInput.files[0].type.startsWith('image/')) && !fileInput._compressedBlob) {
                        const res = await compressImageFile(fileInput.files[0]);
                        fileInput._compressedBlob = res.blob;
                    }
                }

                const formData = new FormData(form);
                formData.set('is_ajax', '1');

                // If file input has a compressed blob, replace file in formData
                fileInputs.forEach(fileInput => {
                    if (fileInput._compressedBlob && fileInput.files && fileInput.files[0]) {
                        const originalName = fileInput.files[0].name.replace(/\.[^/.]+$/, "") + ".jpg";
                        formData.set(fileInput.name, fileInput._compressedBlob, originalName);
                    }
                });

                const response = await fetch(form.action || window.location.href, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });

                const contentType = response.headers.get('content-type') || '';
                if (contentType.includes('application/json')) {
                    const res = await response.json();
                    if (res.success) {
                        showToast(res.message, 'success');
                        const modalEl = form.closest('.modal');
                        if (modalEl) {
                            const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                            modalInstance.hide();
                        }
                        form.reset();

                        const refreshRegion = form.getAttribute('data-refresh-region');
                        if (refreshRegion && window.updateAsyncRegion) {
                            try {
                                await window.updateAsyncRegion(window.location.href, refreshRegion, false, false);
                            } catch (e) {
                                window.location.reload();
                            }
                        } else {
                            setTimeout(() => window.location.reload(), 400);
                        }
                    } else {
                        showToast(res.message || 'Gagal menyimpan data.', 'danger');
                    }
                } else {
                    // Fallback normal POST
                    form.removeAttribute('data-ajax-form');
                    form.submit();
                }
            } catch (err) {
                // If fetch is blocked by network or hosting proxy, fallback to standard form submit!
                form.removeAttribute('data-ajax-form');
                form.submit();
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            }
        });

        // ========== ENHANCED AJAX DELETE CONFIRMATION ==========
        const deleteModal = document.getElementById('modalDeleteConfirm');
        const deleteTitle = document.getElementById('deleteModalTitle');
        const deleteMessage = document.getElementById('deleteModalMessage');
        const deleteSubmit = document.getElementById('deleteModalSubmit');
        if (deleteModal && deleteSubmit) {
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const trigger = event.relatedTarget;
                pendingDeleteForm = trigger ? trigger.closest('form') : null;
                deleteTitle.textContent = trigger?.getAttribute('data-delete-title') || 'Konfirmasi Hapus';
                deleteMessage.textContent = trigger?.getAttribute('data-delete-message') || 'Data ini akan dihapus. Tindakan ini tidak bisa dibatalkan.';
                deleteSubmit.textContent = trigger?.getAttribute('data-delete-confirm') || 'Ya, Hapus';
            });
            deleteModal.addEventListener('hidden.bs.modal', function() {
                pendingDeleteForm = null;
                deleteTitle.textContent = 'Konfirmasi Hapus';
                deleteMessage.textContent = 'Data ini akan dihapus. Tindakan ini tidak bisa dibatalkan.';
                deleteSubmit.textContent = 'Ya, Hapus';
            });
            deleteSubmit.addEventListener('click', async function() {
                if (!pendingDeleteForm) return;
                const form = pendingDeleteForm;
                const originalHtml = deleteSubmit.innerHTML;
                deleteSubmit.disabled = true;
                deleteSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Menghapus…';

                try {
                    const formData = new FormData(form);
                    formData.set('is_ajax', '1');
                    const response = await fetch(form.action || window.location.href, {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    const contentType = response.headers.get('content-type') || '';
                    if (contentType.includes('application/json')) {
                        const data = await response.json();
                        const modalInstance = bootstrap.Modal.getInstance(deleteModal);
                        if (modalInstance) modalInstance.hide();

                        if (data.success) {
                            showToast(data.message, 'success');
                            const region = form.closest('[data-async-region]');
                            if (region && region.id && window.updateAsyncRegion) {
                                await window.updateAsyncRegion(window.location.href, '#' + region.id, false, false);
                            } else {
                                setTimeout(() => window.location.reload(), 400);
                            }
                        } else {
                            showToast(data.message || 'Gagal menghapus data.', 'danger');
                        }
                    } else {
                        form.submit();
                    }
                } catch (err) {
                    form.submit();
                } finally {
                    deleteSubmit.disabled = false;
                    deleteSubmit.innerHTML = originalHtml;
                }
            });
        }
        (function() {
            const parser = new DOMParser();
            const asyncControllers = new Map();
            let cardObserver = null;

            function activateCardsWithin(scope) {
                const cards = [];
                if (scope?.classList?.contains('card')) {
                    cards.push(scope);
                }
                if (scope?.querySelectorAll) {
                    cards.push(...scope.querySelectorAll('.card'));
                }
                cards.forEach(card => {
                    card.style.animationPlayState = 'running';
                    if (cardObserver) {
                        cardObserver.unobserve(card);
                    }
                });
            }

            function syncAsyncSearchForm(targetSelector, url) {
                const form = document.querySelector('form[data-async-target="' + targetSelector + '"]');
                if (!form) return;
                const parsedUrl = new URL(url, window.location.href);
                const input = form.querySelector('input[name="q"]');
                if (input && document.activeElement !== input) {
                    input.value = parsedUrl.searchParams.get('q') || '';
                }
                const limitInput = form.querySelector('[name="limit"]');
                if (limitInput) {
                    limitInput.value = parsedUrl.searchParams.get('limit') || limitInput.defaultValue || '10';
                }
            }

            async function updateAsyncRegion(url, targetSelector, pushState, skipSyncForm = false) {
                const target = document.querySelector(targetSelector);
                if (!target) return;

                const previousController = asyncControllers.get(targetSelector);
                if (previousController) previousController.abort();

                const controller = new AbortController();
                asyncControllers.set(targetSelector, controller);
                target.style.opacity = '0.55';
                target.style.pointerEvents = 'none';

                try {
                    const response = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        signal: controller.signal
                    });
                    if (!response.ok) throw new Error('Request failed');

                    const html = await response.text();
                    const doc = parser.parseFromString(html, 'text/html');
                    const replacement = doc.querySelector(targetSelector);
                    if (!replacement) {
                        window.location.href = url;
                        return;
                    }

                    target.replaceWith(replacement);
                    activateCardsWithin(replacement);
                    if (window.initLiveTableFilters) window.initLiveTableFilters();
                    if (!skipSyncForm) {
                        syncAsyncSearchForm(targetSelector, url);
                    }

                    if (pushState) {
                        const historyUrl = new URL(url, window.location.href);
                        history.pushState({ asyncTarget: targetSelector }, '', historyUrl.pathname + historyUrl.search);
                    }
                } catch (error) {
                    if (error.name === 'AbortError') return;
                    throw error;
                } finally {
                    if (asyncControllers.get(targetSelector) === controller) {
                        asyncControllers.delete(targetSelector);
                    }
                }
            }

            window.updateAsyncRegion = updateAsyncRegion;

            document.querySelectorAll('form[data-async-search]').forEach(form => {
                const targetSelector = form.getAttribute('data-async-target');
                if (targetSelector) {
                    history.replaceState({ asyncTarget: targetSelector }, '', window.location.href);
                }
            });

            document.addEventListener('submit', function(e) {
                const form = e.target.closest('form[data-async-search]');
                if (!form) return;
                e.preventDefault();

                const targetSelector = form.getAttribute('data-async-target');
                const requestUrl = new URL(form.getAttribute('action') || window.location.href, window.location.href);
                const formData = new FormData(form);
                const params = new URLSearchParams();

                formData.forEach((value, key) => {
                    const normalized = typeof value === 'string' ? value.trim() : value;
                    if (normalized !== '') params.set(key, normalized);
                });
                params.delete('page');
                requestUrl.search = params.toString();

                updateAsyncRegion(requestUrl.toString(), targetSelector, true, true).catch(() => {
                    form.submit();
                });
            });

            document.addEventListener('input', function(e) {
                const input = e.target.closest('form[data-async-search] input[name="q"]');
                if (!input) return;

                const form = input.form;
                clearTimeout(form._asyncSearchTimer);
                form._asyncSearchTimer = setTimeout(() => {
                    form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
                }, 280);
            });

            document.addEventListener('click', function(e) {
                const resetLink = e.target.closest('a[data-async-reset]');
                if (resetLink) {
                    e.preventDefault();
                    const form = resetLink.closest('form[data-async-search]');
                    const targetSelector = form?.getAttribute('data-async-target');
                    if (!targetSelector) {
                        window.location.href = resetLink.href;
                        return;
                    }
                    const input = form.querySelector('input[name="q"]');
                    if (input) input.value = '';
                    updateAsyncRegion(resetLink.href, targetSelector, true).catch(() => {
                        window.location.href = resetLink.href;
                    });
                    return;
                }

                const asyncLink = e.target.closest('[data-async-region] a[data-async-link]');
                if (!asyncLink) return;
                if (asyncLink.closest('.page-item.disabled')) {
                    e.preventDefault();
                    return;
                }

                const region = asyncLink.closest('[data-async-region]');
                if (!region || !region.id) return;

                e.preventDefault();
                updateAsyncRegion(asyncLink.href, '#' + region.id, true).catch(() => {
                    window.location.href = asyncLink.href;
                });
            });

            window.addEventListener('popstate', function(e) {
                if (!e.state?.asyncTarget) return;
                updateAsyncRegion(window.location.href, e.state.asyncTarget, false).catch(() => {
                    window.location.reload();
                });
            });
        })();
        /* ========== UNIVERSAL CLIENT-SIDE LIVE SEARCH & MULTI-FILTER ========== */
        window.initLiveTableFilters = function() {
            document.querySelectorAll('[data-live-search]').forEach(input => {
                const tableSelector = input.getAttribute('data-live-search');
                const counterSelector = input.getAttribute('data-counter-target');

                const applyFilter = () => {
                    const table = document.querySelector(tableSelector);
                    if (!table) return;
                    const tbody = table.querySelector('tbody');

                    if (tbody) {
                        const query = input.value.trim().toLowerCase();
                        const rows = Array.from(tbody.querySelectorAll('tr:not(.no-result-row)'));
                        const totalCount = rows.length;

                        // Associated column filter dropdowns
                        const filterDropdowns = document.querySelectorAll(`[data-table-target="${tableSelector}"][data-live-filter-col]`);

                        const matchingRows = [];

                        rows.forEach(row => {
                            let matchesSearch = true;
                            if (query !== '') {
                                const rowText = row.textContent.toLowerCase();
                                matchesSearch = rowText.includes(query);
                            }

                            let matchesDropdowns = true;
                            filterDropdowns.forEach(dropdown => {
                                const colIndex = parseInt(dropdown.getAttribute('data-live-filter-col'), 10);
                                const selectedVal = dropdown.value.trim().toLowerCase();
                                if (selectedVal !== '') {
                                    const cell = row.children[colIndex];
                                    if (cell) {
                                        const cellHasVal = cell.getAttribute('data-has-val');
                                        const cellFilterVal = cell.getAttribute('data-filter-val');
                                        const cellText = cell.textContent.trim().toLowerCase();
                                        const hasImg = !!cell.querySelector('img');
                                        const hasLink = !!cell.querySelector('a');
                                        const hasRealText = cellText !== '' && cellText !== '-' && cellText !== '—' && cellText !== 'none';

                                        if (selectedVal === ':has-value' || selectedVal === '1') {
                                            if (cellHasVal !== null) {
                                                if (cellHasVal !== '1') matchesDropdowns = false;
                                            } else if (!hasImg && !hasLink && !hasRealText) {
                                                matchesDropdowns = false;
                                            }
                                        } else if (selectedVal === ':empty' || selectedVal === '0') {
                                            if (cellHasVal !== null) {
                                                if (cellHasVal !== '0') matchesDropdowns = false;
                                            } else if (hasImg || hasLink || hasRealText) {
                                                matchesDropdowns = false;
                                            }
                                        } else {
                                            if (cellFilterVal !== null) {
                                                const normFilterVal = cellFilterVal.trim().toLowerCase();
                                                const cleanFilterVal = normFilterVal.replace(/[_\s-]+/g, '');
                                                const cleanSelectedVal = selectedVal.replace(/[_\s-]+/g, '');
                                                if (normFilterVal !== selectedVal && !normFilterVal.includes(selectedVal) && cleanFilterVal !== cleanSelectedVal) {
                                                    matchesDropdowns = false;
                                                }
                                            } else if (!cellText.includes(selectedVal)) {
                                                matchesDropdowns = false;
                                            }
                                        }
                                    }
                                }
                            });

                            if (matchesSearch && matchesDropdowns) {
                                matchingRows.push(row);
                            }
                        });

                        const visibleCount = matchingRows.length;

                        // Check for client-side pagination container
                        const paginationEl = document.querySelector(`.pagination-container[data-pagination-table="${tableSelector}"]`) 
                                          || document.querySelector(`.pagination-container[data-table-target="${tableSelector}"]`);

                        if (paginationEl) {
                            const pageSize = table._pageSize !== undefined ? table._pageSize : 10;
                            let currentPage = table._currentPage || 1;
                            const totalMatching = matchingRows.length;

                            let totalPages = 1;
                            let startIdx = 0;
                            let endIdx = totalMatching;

                            if (pageSize !== 'all') {
                                const numSize = parseInt(pageSize, 10) || 10;
                                totalPages = Math.max(1, Math.ceil(totalMatching / numSize));
                                if (currentPage > totalPages) currentPage = 1;
                                if (currentPage < 1) currentPage = 1;
                                table._currentPage = currentPage;

                                startIdx = (currentPage - 1) * numSize;
                                endIdx = startIdx + numSize;
                            } else {
                                currentPage = 1;
                                table._currentPage = 1;
                            }

                            // Hide all data rows first
                            rows.forEach(r => r.style.display = 'none');

                            // Show matching rows that fall within the current page window
                            matchingRows.forEach((row, index) => {
                                if (pageSize === 'all' || (index >= startIdx && index < endIdx)) {
                                    row.style.display = '';
                                } else {
                                    row.style.display = 'none';
                                }
                            });

                            // Update pagination controls UI
                            const summaryEl = paginationEl.querySelector('.pagination-summary, small.text-muted');
                            if (summaryEl) {
                                if (totalMatching === 0) {
                                    summaryEl.textContent = 'Menampilkan 0 data';
                                } else {
                                    const displayStart = startIdx + 1;
                                    const displayEnd = Math.min(endIdx, totalMatching);
                                    const isFiltered = query !== '' || Array.from(filterDropdowns).some(d => d.value !== '');
                                    summaryEl.textContent = `Menampilkan ${displayStart}-${displayEnd} dari ${totalMatching} data${isFiltered ? ` (difilter dari ${totalCount} total)` : ''}`;
                                }
                            }

                            const statusPill = paginationEl.querySelector('.page-status-pill');
                            if (statusPill) {
                                statusPill.textContent = `Hal. ${currentPage} / ${totalPages}`;
                            }

                            const prevLi = paginationEl.querySelector('.page-prev, li:has(a[aria-label="Halaman sebelumnya"]), li:has(button[aria-label="Halaman sebelumnya"])');
                            if (prevLi) {
                                prevLi.classList.toggle('disabled', currentPage <= 1);
                            }

                            const nextLi = paginationEl.querySelector('.page-next, li:has(a[aria-label="Halaman berikutnya"]), li:has(button[aria-label="Halaman berikutnya"])');
                            if (nextLi) {
                                nextLi.classList.toggle('disabled', currentPage >= totalPages || pageSize === 'all');
                            }

                            const limitPills = paginationEl.querySelectorAll('.limit-pill');
                            limitPills.forEach(pill => {
                                const pLimit = pill.getAttribute('data-limit');
                                pill.classList.toggle('active', (pLimit === 'all' && pageSize === 'all') || (pLimit !== 'all' && parseInt(pLimit, 10) === pageSize));
                            });

                            const navEl = paginationEl.querySelector('.pagination-nav, nav[aria-label^="Pagination"]');
                            if (navEl) {
                                navEl.style.display = (totalPages <= 1) ? 'none' : '';
                            }

                            if (!paginationEl._boundPagination) {
                                paginationEl._boundPagination = true;

                                paginationEl.addEventListener('click', (e) => {
                                    const prevBtn = e.target.closest('.page-prev button, .page-prev a, [aria-label="Halaman sebelumnya"]');
                                    if (prevBtn) {
                                        e.preventDefault();
                                        if ((table._currentPage || 1) > 1) {
                                            table._currentPage = (table._currentPage || 1) - 1;
                                            applyFilter();
                                        }
                                        return;
                                    }

                                    const nextBtn = e.target.closest('.page-next button, .page-next a, [aria-label="Halaman berikutnya"]');
                                    if (nextBtn) {
                                        e.preventDefault();
                                        const pSize = table._pageSize !== undefined ? table._pageSize : 10;
                                        const totPages = pSize === 'all' ? 1 : Math.max(1, Math.ceil(matchingRows.length / (parseInt(pSize, 10) || 10)));
                                        if ((table._currentPage || 1) < totPages) {
                                            table._currentPage = (table._currentPage || 1) + 1;
                                            applyFilter();
                                        }
                                        return;
                                    }

                                    const pill = e.target.closest('.limit-pill');
                                    if (pill) {
                                        e.preventDefault();
                                        const val = pill.getAttribute('data-limit') || pill.textContent.trim().toLowerCase();
                                        table._pageSize = (val === 'all' || val === 'semua') ? 'all' : parseInt(val, 10);
                                        table._currentPage = 1;
                                        applyFilter();
                                    }
                                });
                            }
                        } else {
                            rows.forEach(r => r.style.display = 'none');
                            matchingRows.forEach(r => r.style.display = '');
                        }

                        // Update active filter badge on Single Filter Button trigger
                        document.querySelectorAll(`.filter-dropdown-container[data-table-target="${tableSelector}"], .filter-dropdown-container`).forEach(container => {
                            const selects = container.querySelectorAll(`select[data-table-target="${tableSelector}"][data-live-filter-col], select[data-live-filter-col]`);
                            if (selects.length > 0) {
                                const activeCount = Array.from(selects).filter(s => s.value.trim() !== '').length;
                                const badge = container.querySelector('.filter-count-badge');
                                const trigger = container.querySelector('.btn-filter-trigger');
                                if (badge) {
                                    badge.textContent = activeCount;
                                    badge.style.display = activeCount > 0 ? 'inline-block' : 'none';
                                }
                                if (trigger) {
                                    if (activeCount > 0) {
                                        trigger.classList.add('active');
                                    } else {
                                        trigger.classList.remove('active');
                                    }
                                }
                            }
                        });

                        // Update counter badge if target provided
                        if (counterSelector) {
                            const counterEl = document.querySelector(counterSelector);
                            if (counterEl) {
                                const isFiltered = query !== '' || Array.from(filterDropdowns).some(d => d.value !== '');
                                if (isFiltered) {
                                    counterEl.textContent = `Menampilkan ${visibleCount} dari ${totalCount} data`;
                                    counterEl.classList.add('bg-warning-subtle');
                                } else {
                                    counterEl.textContent = `Total: ${totalCount} data`;
                                    counterEl.classList.remove('bg-warning-subtle');
                                }
                            }
                        }

                        // Empty state row
                        let noRow = tbody.querySelector('.no-result-row');
                        if (!noRow) {
                            noRow = document.createElement('tr');
                            noRow.className = 'no-result-row';
                            const thCount = table.querySelectorAll('thead th').length || 4;
                            noRow.innerHTML = `<td colspan="${thCount}" class="text-center text-muted py-4"><i class="fa fa-search me-2 opacity-50"></i>Tidak ada data yang cocok dengan pencarian / filter.</td>`;
                            tbody.appendChild(noRow);
                        }
                        noRow.style.display = (visibleCount === 0 && totalCount > 0) ? '' : 'none';
                    } else {
                        // Card Grid Mode
                        const query = input.value.trim().toLowerCase();
                        const cards = Array.from(table.querySelectorAll('[data-card-item]'));
                        const totalCount = cards.length;
                        let visibleCount = 0;

                        const filterDropdowns = document.querySelectorAll(`[data-table-target="${tableSelector}"][data-live-filter-attr], [data-table-target="${tableSelector}"][data-live-filter-col]`);

                        cards.forEach(card => {
                            let matchesSearch = true;
                            if (query !== '') {
                                const cardText = card.textContent.toLowerCase();
                                matchesSearch = cardText.includes(query);
                            }

                            let matchesDropdowns = true;
                            filterDropdowns.forEach(dropdown => {
                                const selectedVal = dropdown.value.trim().toLowerCase();
                                if (selectedVal !== '') {
                                    const attrName = dropdown.getAttribute('data-live-filter-attr');
                                    if (attrName) {
                                        const cardVal = (card.getAttribute(attrName) || '').trim().toLowerCase();
                                        if (cardVal !== selectedVal && !cardVal.includes(selectedVal)) {
                                            matchesDropdowns = false;
                                        }
                                    }
                                }
                            });

                            if (matchesSearch && matchesDropdowns) {
                                card.style.display = '';
                                visibleCount++;
                            } else {
                                card.style.display = 'none';
                            }
                        });

                        // Update active filter badge on Single Filter Button trigger
                        document.querySelectorAll(`.filter-dropdown-container[data-table-target="${tableSelector}"], .filter-dropdown-container`).forEach(container => {
                            const selects = container.querySelectorAll(`select[data-table-target="${tableSelector}"]`);
                            if (selects.length > 0) {
                                const activeCount = Array.from(selects).filter(s => s.value.trim() !== '').length;
                                const badge = container.querySelector('.filter-count-badge');
                                const trigger = container.querySelector('.btn-filter-trigger');
                                if (badge) {
                                    badge.textContent = activeCount;
                                    badge.style.display = activeCount > 0 ? 'inline-block' : 'none';
                                }
                                if (trigger) {
                                    if (activeCount > 0) {
                                        trigger.classList.add('active');
                                    } else {
                                        trigger.classList.remove('active');
                                    }
                                }
                            }
                        });

                        // Update counter badge if target provided
                        if (counterSelector) {
                            const counterEl = document.querySelector(counterSelector);
                            if (counterEl) {
                                const isFiltered = query !== '' || Array.from(filterDropdowns).some(d => d.value !== '');
                                if (isFiltered) {
                                    counterEl.textContent = `Menampilkan ${visibleCount} dari ${totalCount} lagu`;
                                    counterEl.classList.add('bg-warning-subtle');
                                } else {
                                    counterEl.textContent = `Total: ${totalCount} iringan`;
                                    counterEl.classList.remove('bg-warning-subtle');
                                }
                            }
                        }

                        // Empty state card
                        let noCard = table.querySelector('.no-cards-result');
                        if (!noCard) {
                            noCard = document.createElement('div');
                            noCard.className = 'col-12 no-cards-result text-center py-4';
                            noCard.innerHTML = `<div class="card p-4 text-center border-dashed shadow-sm"><i class="fa fa-search fa-2x text-muted mb-2"></i><h6 class="fw-semibold mb-1">Tidak Ada Lagu Iringan yang Cocok</h6><p class="text-muted small mb-0">Coba ubah kata kunci pencarian atau reset filter kategori.</p></div>`;
                            table.appendChild(noCard);
                        }
                        noCard.style.display = (visibleCount === 0 && totalCount > 0) ? '' : 'none';
                    }
                };

                input._applyLiveFilter = applyFilter;

                if (!input._liveFilterBound) {
                    input._liveFilterBound = true;
                    input.addEventListener('input', () => {
                        const tbl = document.querySelector(tableSelector);
                        if (tbl) tbl._currentPage = 1;
                        if (typeof input._applyLiveFilter === 'function') input._applyLiveFilter();
                    });
                }

                // Auto-bind any related dropdowns
                document.querySelectorAll(`[data-table-target="${tableSelector}"]`).forEach(dropdown => {
                    if (dropdown.tagName === 'SELECT' && !dropdown._liveFilterBound) {
                        dropdown._liveFilterBound = true;
                        dropdown.addEventListener('change', () => {
                            const tbl = document.querySelector(tableSelector);
                            if (tbl) tbl._currentPage = 1;
                            if (typeof input._applyLiveFilter === 'function') input._applyLiveFilter();
                        });
                        dropdown.addEventListener('input', () => {
                            const tbl = document.querySelector(tableSelector);
                            if (tbl) tbl._currentPage = 1;
                            if (typeof input._applyLiveFilter === 'function') input._applyLiveFilter();
                        });
                    }
                });

                // Immediately run filter on active table rows or cards
                applyFilter();
            });
        };

        // Reset Filter handler
        document.addEventListener('click', function(e) {
            const resetBtn = e.target.closest('.btn-reset-filters');
            if (!resetBtn) return;
            e.preventDefault();
            const container = resetBtn.closest('.filter-dropdown-container') || resetBtn.closest('.dropdown-menu');
            if (!container) return;
            const targetTableSel = container.getAttribute('data-table-target');
            if (targetTableSel) {
                const tbl = document.querySelector(targetTableSel);
                if (tbl) tbl._currentPage = 1;
            }
            const selects = container.querySelectorAll('select[data-live-filter-col], select[data-live-filter-attr]');
            selects.forEach(s => {
                s.value = '';
                s.dispatchEvent(new Event('input', { bubbles: true }));
                s.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });

        window.initLiveTableFilters();
        document.addEventListener('DOMContentLoaded', window.initLiveTableFilters);

        /* Animate cards on scroll without hiding cards that are already visible on load */
        const cards = document.querySelectorAll('.card');
        if ('IntersectionObserver' in window) {
            const revealCard = (card) => {
                card.style.animationPlayState = 'running';
            };
            cardObserver = new IntersectionObserver((entries) => {
                entries.forEach(e => {
                    if (e.isIntersecting) {
                        revealCard(e.target);
                        cardObserver.unobserve(e.target);
                    }
                });
            }, { threshold: 0 });
            cards.forEach(card => {
                const rect = card.getBoundingClientRect();
                const isInitiallyVisible = rect.bottom > 0 && rect.top < window.innerHeight;
                if (isInitiallyVisible) {
                    revealCard(card);
                    return;
                }
                cardObserver.observe(card);
            });
        } else {
            cards.forEach(card => {
                card.style.animationPlayState = 'running';
            });
        }
    })();
</script>

<!-- Universal Student Photo Zoom / Lightbox Modal -->
<div class="modal fade modal-image-zoom" id="modalImageZoom" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content" style="background: rgba(18, 30, 26, 0.95); border: 1px solid rgba(168,144,98,0.3); backdrop-filter: blur(20px);">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <h6 class="modal-title text-light mb-0" id="modalImageZoomTitle">
                    <i class="fa fa-image me-2 text-warning"></i>Foto Murid
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-3">
                <div class="zoom-img-container position-relative d-inline-block">
                    <img id="modalImageZoomImg" src="" alt="Foto Murid" class="img-fluid rounded-3 shadow-lg" style="max-height: 70vh; max-width: 100%; object-fit: contain; border: 2px solid rgba(255,255,255,0.15);">
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-between">
                <span class="small text-muted" id="modalImageZoomInfo">Klik di luar atau tombol Tutup</span>
                <div class="d-flex gap-2">
                    <a id="modalImageZoomDownload" href="#" target="_blank" download class="btn btn-sm btn-outline-secondary"><i class="fa fa-download me-1"></i>Unduh</a>
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
            <div class="modal-header border-bottom border-light-subtle pb-3">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:40px;height:40px;border-radius:10px;background:rgba(168,144,98,0.15);display:flex;align-items:center;justify-content:center;">
                        <i class="fa fa-calendar-check" style="color:var(--accent);font-size:1.2rem;"></i>
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
                    <i class="fa fa-spinner fa-spin fa-2x mb-3 text-primary"></i>
                    <div class="small fw-semibold">Memuat rincian sesi pertemuan...</div>
                </div>
            </div>
            <div class="modal-footer border-top border-light-subtle pt-2">
                <span class="text-muted small me-auto" id="modalDetailRealisasiCount"></span>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-realisasi-detail');
        if (!btn) return;

        const muridId = btn.getAttribute('data-murid-id');
        if (!muridId) return;

        const muridNama = btn.getAttribute('data-murid-nama') || 'Murid';
        const tahun = btn.getAttribute('data-tahun') || new Date().getFullYear();
        const bulan = btn.getAttribute('data-bulan') || (new Date().getMonth() + 1);
        const coachScope = btn.getAttribute('data-coach-scope') || '0';

        const modalEl = document.getElementById('modalDetailRealisasi');
        if (!modalEl) return;

        const titleEl = document.getElementById('modalDetailRealisasiTitle');
        const subtitleEl = document.getElementById('modalDetailRealisasiSubtitle');
        const bodyEl = document.getElementById('modalDetailRealisasiBody');
        const countEl = document.getElementById('modalDetailRealisasiCount');

        if (titleEl) titleEl.textContent = 'Realisasi Pertemuan';
        if (subtitleEl) subtitleEl.textContent = `${muridNama} • Memuat data...`;
        if (countEl) countEl.textContent = '';

        if (bodyEl) {
            bodyEl.innerHTML = `
                <div class="text-center py-5 text-muted">
                    <i class="fa fa-spinner fa-spin fa-2x mb-3 text-primary"></i>
                    <div class="small fw-semibold">Memuat rincian sesi pertemuan...</div>
                </div>
            `;
        }

        // Determine fetch URL based on current page or fallback to rencana_pertemuan.php
        const currentPath = window.location.pathname.split('/').pop() || 'index.php';
        const baseEndpoint = (currentPath.includes('index.php') || currentPath === '') ? 'index.php' : 'rencana_pertemuan.php';
        const fetchUrl = `${baseEndpoint}?action=get_realisasi_detail&murid_id=${encodeURIComponent(muridId)}&tahun=${encodeURIComponent(tahun)}&bulan=${encodeURIComponent(bulan)}&coach_scope=${encodeURIComponent(coachScope)}`;

        fetch(fetchUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => {
            if (!res.ok) throw new Error('Network error ' + res.status);
            return res.json();
        })
        .then(data => {
            if (!data.success) {
                if (bodyEl) {
                    bodyEl.innerHTML = `
                        <div class="alert alert-danger mb-0">
                            <i class="fa fa-exclamation-circle me-1"></i> ${data.message || 'Gagal memuat rincian pertemuan.'}
                        </div>
                    `;
                }
                return;
            }

            const m = data.murid || {};
            const p = data.periode || {};
            const total = data.total || 0;
            const list = data.pertemuan || [];

            if (subtitleEl) {
                subtitleEl.innerHTML = `<strong>${escapeHtml(m.nama || muridNama)}</strong> • Periode ${escapeHtml(p.label || '')}`;
            }
            if (countEl) {
                countEl.textContent = `Total: ${total} pertemuan terealisasi`;
            }

            if (total === 0) {
                if (bodyEl) {
                    bodyEl.innerHTML = `
                        <div class="text-center py-5 text-muted">
                            <div class="mb-3"><i class="fa fa-calendar-xmark fa-3x opacity-50" style="color:var(--accent);"></i></div>
                            <h6 class="fw-bold mb-1">Belum Ada Sesi Pertemuan yang Terealisasi</h6>
                            <p class="small text-muted mb-0" style="max-width:440px;margin:0 auto;">
                                Belum ada catatan pertemuan untuk <strong>${escapeHtml(m.nama || muridNama)}</strong> pada bulan <strong>${escapeHtml(p.label || '')}</strong>. Sesi yang dicatat di halaman Pertemuan akan otomatis muncul di sini.
                            </p>
                        </div>
                    `;
                }
            } else {
                let rowsHtml = '';
                list.forEach((item, index) => {
                    const laguHtml = item.lagu_judul 
                        ? `<div class="fw-semibold text-truncate" style="max-width:200px;"><i class="fa fa-music me-1 text-accent"></i>${escapeHtml(item.lagu_judul)}</div>`
                        : `<span class="text-muted small">-</span>`;
                    
                    const catatanHtml = item.catatan 
                        ? `<div class="small text-body" style="line-height:1.45;">${escapeHtml(item.catatan)}</div>`
                        : `<span class="text-muted small fst-italic">Tidak ada catatan</span>`;

                    const coachBadge = item.coach_nama 
                        ? `<span class="badge bg-light text-dark border"><i class="fa fa-chalkboard-user me-1 text-primary"></i>${escapeHtml(item.coach_nama)}</span>`
                        : `<span class="text-muted small">-</span>`;

                    rowsHtml += `
                        <tr>
                            <td class="text-muted small">${index + 1}</td>
                            <td>
                                <div class="fw-bold" style="color:var(--primary);font-size:0.88rem;">
                                    <i class="fa fa-calendar-day me-1" style="color:var(--accent);"></i>
                                    ${escapeHtml(item.tanggal_formatted || item.tanggal)}
                                </div>
                                <small class="text-muted">${escapeHtml(item.tanggal_short || '')}</small>
                            </td>
                            <td>${coachBadge}</td>
                            <td>${laguHtml}</td>
                            <td>${catatanHtml}</td>
                        </tr>
                    `;
                });

                if (bodyEl) {
                    bodyEl.innerHTML = `
                        <div class="d-flex align-items-center justify-content-between p-2 p-sm-3 mb-3 rounded-3 flex-wrap gap-2" 
                             style="background:rgba(74,107,93,0.08);border:1px solid rgba(74,107,93,0.15);">
                            <div class="d-flex align-items-center gap-2">
                                ${m.foto ? `<img src="${escapeHtml(m.foto)}" alt="Foto" style="width:36px;height:36px;border-radius:10px;object-fit:cover;">` : `<span class="avatar-empty-badge" style="width:36px;height:36px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;background:rgba(74,107,93,0.1);"><i class="fa fa-user text-muted"></i></span>`}
                                <div>
                                    <h6 class="mb-0 fw-bold" style="color:var(--text);">${escapeHtml(m.nama || muridNama)}</h6>
                                    <span class="small text-muted">Periode: <strong>${escapeHtml(p.label || '')}</strong></span>
                                </div>
                            </div>
                            <span class="badge rounded-pill bg-success px-3 py-2" style="font-size:0.82rem;font-variant-numeric:tabular-nums;">
                                <i class="fa fa-check-circle me-1"></i> ${total} Sesi Terlaksana
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr style="font-size:0.82rem;">
                                        <th style="width:35px;">#</th>
                                        <th style="min-width:180px;">Tanggal Pertemuan</th>
                                        <th style="min-width:130px;">Coach</th>
                                        <th style="min-width:150px;">Lagu</th>
                                        <th style="min-width:180px;">Catatan Latihan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${rowsHtml}
                                </tbody>
                            </table>
                        </div>
                    `;
                }
            }
        })
        .catch(err => {
            if (bodyEl) {
                bodyEl.innerHTML = `
                    <div class="alert alert-danger mb-0">
                        <i class="fa fa-triangle-exclamation me-1"></i> Terjadi kesalahan saat memuat data: ${escapeHtml(err.message)}
                    </div>
                `;
            }
        });
    });

    // Handle stacked modals: ensure body retains modal-open if another modal is still visible
    const modalDetailRealisasiEl = document.getElementById('modalDetailRealisasi');
    if (modalDetailRealisasiEl) {
        modalDetailRealisasiEl.addEventListener('hidden.bs.modal', function () {
            if (document.querySelector('.modal.show')) {
                document.body.classList.add('modal-open');
            }
        });
    }
})();
</script>

</body>
</html>
