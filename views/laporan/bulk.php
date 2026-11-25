<div class="container-fluid">
    <h1 class="page-title"><i class="fa fa-spinner fa-spin me-2" id="titleIcon" style="color:var(--primary);" aria-hidden="true"></i>Memproses Laporan...</h1>
    <div class="card p-4 text-center mt-4 mx-auto" style="max-width: 600px;">
        <?= csrf_field() ?>
        <h2 class="h5" id="statusText">Menyiapkan data murid...</h2>
        <div class="progress mt-3 mb-2" style="height: 25px; border-radius: var(--radius-xs);">
            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%; font-weight:600;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
        </div>
        <p id="detailText" class="text-muted small">Mohon jangan tutup atau muat ulang halaman ini.</p>
        <div class="mt-3 d-flex justify-content-center gap-2">
            <button type="button" class="btn btn-outline-danger btn-sm" id="btnCancel" data-bs-toggle="modal" data-bs-target="#modalCancelBulk"><i class="fa fa-times me-2" aria-hidden="true"></i>Batal</button>
            <a href="laporan.php" class="btn btn-secondary btn-sm" id="btnBack" style="display:none;"><i class="fa fa-arrow-left me-2" aria-hidden="true"></i>Kembali ke Laporan</a>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCancelBulk" tabindex="-1" aria-labelledby="modalCancelBulkLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger" id="modalCancelBulkLabel"><i class="fa fa-exclamation-triangle me-2" aria-hidden="true"></i>Batalkan Proses Laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Yakin ingin membatalkan proses pembuatan laporan bulanan?</p>
                <p class="small text-muted mb-0">File PDF sementara yang sudah dibuat akan dibersihkan dari server.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Lanjutkan Proses</button>
                <button type="button" class="btn btn-danger btn-sm" id="btnConfirmCancelBulk">Ya, Batalkan</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async function() {
    const tahun = <?= (int)$tahun ?>;
    const bulan = <?= (int)$bulan ?>;
    const CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;
    const statusText = document.getElementById('statusText');
    const detailText = document.getElementById('detailText');
    const progressBar = document.getElementById('progressBar');
    const btnCancel = document.getElementById('btnCancel');
    const btnBack = document.getElementById('btnBack');
    const titleIcon = document.getElementById('titleIcon');
    const cancelModalEl = document.getElementById('modalCancelBulk');
    const btnConfirmCancel = document.getElementById('btnConfirmCancelBulk');

    let isCancelled = false;
    let currentJobId = '';

    function hideCancelModal() {
        if (!cancelModalEl || typeof bootstrap === 'undefined') return;
        const inst = bootstrap.Modal.getInstance(cancelModalEl);
        if (inst) inst.hide();
    }

    function setProgress(percent) {
        const clamped = Math.max(0, Math.min(100, percent));
        progressBar.style.width = `${clamped}%`;
        progressBar.textContent = `${clamped}%`;
        progressBar.setAttribute('aria-valuenow', String(clamped));
    }

    function showErrorState(message) {
        hideCancelModal();
        statusText.textContent = 'Terjadi Kesalahan Server!';
        detailText.textContent = 'Proses terhenti: ' + (message || 'Permintaan gagal diproses.');
        progressBar.classList.remove('bg-success', 'bg-primary', 'progress-bar-animated');
        progressBar.classList.add('bg-danger');
        btnCancel.style.display = 'none';
        btnBack.style.display = 'inline-block';
        titleIcon.className = 'fa fa-times-circle me-2';
        titleIcon.style.color = 'var(--danger)';
    }

    async function postBulkAction(action, extraFields = {}) {
        const fd = new FormData();
        fd.append('csrf_token', CSRF_TOKEN);
        if (currentJobId) {
            fd.append('job_id', currentJobId);
        }
        Object.entries(extraFields).forEach(([key, val]) => {
            fd.append(key, String(val));
        });

        const res = await fetch(`generate_bulk_pdf.php?tahun=${encodeURIComponent(tahun)}&bulan=${encodeURIComponent(bulan)}&action=${encodeURIComponent(action)}`, {
            method: 'POST',
            headers: {
                'X-CSRF-Token': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: fd
        });

        let data = null;
        try {
            data = await res.json();
        } catch (parseErr) {
            throw new Error(`Respons server tidak valid (HTTP ${res.status}).`);
        }

        if (!res.ok || !data || (data.success !== true && data.status !== 'ok')) {
            throw new Error(data?.message || `Permintaan gagal (HTTP ${res.status}).`);
        }

        return data;
    }

    if (btnConfirmCancel) {
        btnConfirmCancel.addEventListener('click', function() {
            isCancelled = true;
            btnCancel.disabled = true;
            btnCancel.innerHTML = '<i class="fa fa-spinner fa-spin me-2" aria-hidden="true"></i>Membatalkan...';
            hideCancelModal();
        });
    }

    try {
        const initData = await postBulkAction('init');
        currentJobId = initData.job_id || '';
        const muridList = Array.isArray(initData.data) ? initData.data : [];

        if (muridList.length === 0) {
            if (currentJobId) {
                try {
                    await postBulkAction('cleanup');
                } catch (cleanupErr) {
                    // Ignore cleanup errors on empty period
                }
            }
            hideCancelModal();
            statusText.textContent = 'Tidak ada data murid di periode ini.';
            detailText.textContent = 'Proses dihentikan.';
            setProgress(100);
            progressBar.classList.remove('progress-bar-animated');
            btnCancel.style.display = 'none';
            btnBack.style.display = 'inline-block';
            titleIcon.className = 'fa fa-exclamation-circle me-2';
            return;
        }

        const total = muridList.length;

        for (let i = 0; i < total; i++) {
            if (isCancelled) {
                statusText.textContent = 'Proses Dibatalkan!';
                detailText.textContent = 'Membersihkan file sementara...';
                progressBar.classList.replace('bg-success', 'bg-warning');
                progressBar.classList.remove('progress-bar-animated');
                titleIcon.className = 'fa fa-ban me-2';
                titleIcon.style.color = 'var(--danger)';

                if (currentJobId) {
                    await postBulkAction('cleanup');
                }

                detailText.textContent = 'Pembersihan selesai. File tidak disimpan.';
                btnCancel.style.display = 'none';
                btnBack.style.display = 'inline-block';
                return;
            }

            const m = muridList[i];
            statusText.textContent = `Membuat PDF (${i + 1}/${total})`;
            detailText.textContent = `Sedang memproses laporan: ${m.nama || '-'}`;

            await postBulkAction('generate', { id_murid: m.id });

            const percent = Math.round(((i + 1) / total) * 100);
            setProgress(percent);
        }

        if (isCancelled) {
            statusText.textContent = 'Proses Dibatalkan!';
            detailText.textContent = 'Membersihkan file sementara...';
            progressBar.classList.replace('bg-success', 'bg-warning');
            progressBar.classList.remove('progress-bar-animated');
            titleIcon.className = 'fa fa-ban me-2';
            titleIcon.style.color = 'var(--danger)';
            if (currentJobId) {
                await postBulkAction('cleanup');
            }
            detailText.textContent = 'Pembersihan selesai. File tidak disimpan.';
            btnCancel.style.display = 'none';
            btnBack.style.display = 'inline-block';
            return;
        }

        hideCancelModal();
        btnCancel.style.display = 'none';
        statusText.textContent = 'Menggabungkan file (ZIP)...';
        detailText.textContent = 'Sedikit lagi selesai, memadatkan file PDF...';
        progressBar.classList.add('bg-primary');
        progressBar.classList.remove('bg-success');

        const zipData = await postBulkAction('zip');
        if (!zipData.download_url) {
            throw new Error('URL unduhan arsip ZIP tidak tersedia.');
        }

        statusText.textContent = 'Selesai!';
        detailText.textContent = 'File otomatis diunduh. Anda bisa kembali ke halaman sebelumnya.';
        progressBar.classList.remove('progress-bar-animated');
        btnBack.style.display = 'inline-block';
        titleIcon.className = 'fa fa-check-circle me-2';
        titleIcon.style.color = 'var(--success)';

        window.location.href = zipData.download_url;
    } catch (err) {
        showErrorState(err && err.message ? err.message : 'Kesalahan tidak diketahui.');
    }
});
</script>
