<div class="container-fluid">
    <h3><i class="fa fa-spinner fa-spin me-2" id="titleIcon" style="color:var(--primary);"></i>Memproses Laporan...</h3>
    <div class="card p-4 text-center mt-4 mx-auto" style="max-width: 600px; border-top: 4px solid var(--primary);">
        <h5 id="statusText" style="font-family:'Outfit',sans-serif;">Menyiapkan data murid...</h5>
        <div class="progress mt-3 mb-2" style="height: 25px; border-radius: var(--radius-xs);">
            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%; font-weight:600;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
        </div>
        <p id="detailText" class="text-muted small">Mohon jangan tutup atau refresh halaman ini.</p>
        <div class="mt-3">
            <button type="button" class="btn btn-outline-danger btn-sm" id="btnCancel"><i class="fa fa-times me-2"></i>Batal</button>
            <a href="laporan.php" class="btn btn-secondary btn-sm" id="btnBack" style="display:none;"><i class="fa fa-arrow-left me-2"></i>Kembali ke Laporan</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async function() {
    const tahun = <?= $tahun ?>;
    const bulan = <?= $bulan ?>;
    const statusText = document.getElementById('statusText');
    const detailText = document.getElementById('detailText');
    const progressBar = document.getElementById('progressBar');
    const btnCancel = document.getElementById('btnCancel');
    const btnBack = document.getElementById('btnBack');
    const titleIcon = document.getElementById('titleIcon');
    
    let isCancelled = false;

    // Aksi saat tombol batal ditekan
    btnCancel.addEventListener('click', function() {
        if (!confirm('Yakin ingin membatalkan proses pembuatan laporan?')) return;
        isCancelled = true;
        btnCancel.disabled = true;
        btnCancel.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Membatalkan...';
    });

    try {
        let res = await fetch(`generate_bulk_pdf.php?tahun=${tahun}&bulan=${bulan}&action=init`);
        let initData = await res.json();

        if (initData.data.length === 0) {
            statusText.innerText = "Tidak ada data murid di periode ini.";
            detailText.innerText = "Proses dihentikan.";
            progressBar.style.width = '100%';
            progressBar.classList.remove('progress-bar-animated');
            btnCancel.style.display = 'none';
            btnBack.style.display = 'inline-block';
            titleIcon.className = "fa fa-exclamation-circle me-2";
            return;
        }

        const muridList = initData.data;
        const total = muridList.length;

        // Proses looping pembuatan PDF
        for (let i = 0; i < total; i++) {
            // Cek apakah user menekan tombol batal
            if (isCancelled) {
                statusText.innerText = "Proses Dibatalkan!";
                detailText.innerText = "Membersihkan file sementara...";
                progressBar.classList.replace('bg-success', 'bg-warning');
                progressBar.classList.remove('progress-bar-animated');
                titleIcon.className = "fa fa-ban me-2";
                titleIcon.style.color = "var(--danger)";
                
                // Panggil endpoint cleanup untuk menghapus file di server
                await fetch(`generate_bulk_pdf.php?tahun=${tahun}&bulan=${bulan}&action=cleanup`);
                
                detailText.innerText = "Pembersihan selesai. File tidak disimpan.";
                btnCancel.style.display = 'none';
                btnBack.style.display = 'inline-block';
                return; // Hentikan fungsi JS seketika
            }

            let m = muridList[i];
            statusText.innerText = `Membuat PDF (${i+1}/${total})`;
            detailText.innerText = `Sedang memproses laporan: ${m.nama}`;

            let fd = new FormData();
            fd.append('id_murid', m.id);

            await fetch(`generate_bulk_pdf.php?tahun=${tahun}&bulan=${bulan}&action=generate`, {
                method: 'POST',
                body: fd
            });

            let percent = Math.round(((i + 1) / total) * 100);
            progressBar.style.width = `${percent}%`;
            progressBar.innerText = `${percent}%`;
        }

        // Jika sampai sini, berarti tidak dibatalkan. Lanjut kompresi (Zipping)
        btnCancel.style.display = 'none'; // Sembunyikan tombol batal karena sudah masuk tahap akhir
        statusText.innerText = "Menggabungkan file (ZIP)...";
        detailText.innerText = "Sedikit lagi selesai, memadatkan file PDF...";
        progressBar.classList.add('bg-primary');
        progressBar.classList.remove('bg-success');

        let zipRes = await fetch(`generate_bulk_pdf.php?tahun=${tahun}&bulan=${bulan}&action=zip`);
        let zipData = await zipRes.json();

        // Download selesai
        statusText.innerText = "Selesai!";
        detailText.innerText = "File otomatis diunduh. Anda bisa kembali ke halaman sebelumnya.";
        progressBar.classList.remove('progress-bar-animated');
        btnBack.style.display = 'inline-block';
        titleIcon.className = "fa fa-check-circle me-2";
        titleIcon.style.color = "var(--success)";

        window.location.href = zipData.download_url;

    } catch (e) {
        statusText.innerText = "Terjadi Kesalahan Server!";
        detailText.innerText = "Proses terhenti: " + e.message;
        progressBar.classList.replace('bg-success', 'bg-danger');
        progressBar.classList.replace('bg-primary', 'bg-danger');
        progressBar.classList.remove('progress-bar-animated');
        btnCancel.style.display = 'none';
        btnBack.style.display = 'inline-block';
        titleIcon.className = "fa fa-times-circle me-2";
        titleIcon.style.color = "var(--danger)";
    }
});
</script>

