<?php

/**
 * Kebijakan privasi (kepatuhan UU No. 27 Tahun 2022 tentang Perlindungan Data Pribadi).
 */
$this->extend('layouts/main');
?>
<?= $this->section('content') ?>
<h1 class="h3 mb-1"><?= esc(lang('App.privacy')) ?></h1>
<p class="text-body-secondary mb-4">
    SIPALA memproses data pribadi terbatas untuk keperluan penanganan pengaduan layanan publik,
    sesuai prinsip perlindungan data pribadi (UU No. 27 Tahun 2022).
</p>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h6">1. Data yang kami kumpulkan</h2>
                <ul class="small mb-0">
                    <li><strong>Data identitas:</strong> nama lengkap, email, dan nomor HP saat mendaftar.</li>
                    <li><strong>Data aduan:</strong> judul, deskripsi, kategori, tanggal kejadian, dan titik koordinat lokasi.</li>
                    <li><strong>Berkas pendukung:</strong> foto bukti yang Anda unggah (maksimal 3 berkas per aduan).</li>
                    <li><strong>Data teknis:</strong> alamat IP dan jenis peramban, dicatat pada log aktivitas untuk keamanan sistem.</li>
                </ul>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h6">2. Tujuan pemrosesan</h2>
                <ul class="small mb-0">
                    <li>Memverifikasi dan menindaklanjuti aduan yang Anda sampaikan.</li>
                    <li>Menyampaikan tanggapan resmi dari instansi penanggung jawab.</li>
                    <li>Menyusun statistik dan laporan kinerja layanan dalam bentuk agregat.</li>
                    <li>Menjaga keamanan sistem serta mencegah penyalahgunaan layanan.</li>
                </ul>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h6">3. Kerahasiaan identitas pelapor</h2>
                <p class="small mb-0">
                    Anda dapat mengaktifkan opsi <strong>sembunyikan identitas</strong> saat mengirim aduan.
                    Identitas pelapor juga tidak ditampilkan pada halaman publik: nama pada daftar aduan
                    terbaru disamarkan, dan detail aduan hanya dapat dibuka oleh pelapor, instansi terkait,
                    atau administrator. Berkas lampiran diakses melalui jalur yang selalu diperiksa hak aksesnya.
                </p>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h6">4. Penyimpanan &amp; retensi</h2>
                <p class="small mb-0">
                    Data disimpan pada server aplikasi selama diperlukan untuk penanganan aduan, audit, dan
                    pelaporan. Berkas unggahan disimpan di luar direktori publik sehingga tidak dapat diakses
                    langsung dari peramban.
                </p>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h6">5. Hak Anda</h2>
                <ul class="small mb-0">
                    <li>Meminta salinan data pribadi yang kami simpan.</li>
                    <li>Meminta perbaikan data yang tidak akurat.</li>
                    <li>Meminta penghapusan data sepanjang tidak bertentangan dengan kewajiban hukum.</li>
                    <li>Menarik persetujuan pemrosesan, dengan konsekuensi aduan tidak dapat ditindaklanjuti.</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h6">Pengendali data</h2>
                <p class="small text-body-secondary mb-2">
                    Data aduan dikelola oleh instansi pemerintah daerah penanggung jawab layanan.
                </p>
                <p class="small text-body-secondary mb-0">
                    Permintaan terkait data pribadi dapat disampaikan melalui kanal pengaduan resmi
                    instansi terkait dengan menyertakan kode tiket aduan Anda.
                </p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
