<?php

/**
 * Panduan penggunaan SIPALA.
 */
$this->extend('layouts/main');
?>
<?= $this->section('content') ?>
<h1 class="h3 mb-1"><?= esc(lang('App.guide')) ?></h1>
<p class="text-body-secondary mb-4">Langkah-langkah menggunakan SIPALA, mulai dari pendaftaran sampai aduan selesai.</p>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5 mb-3"><i class="bi bi-1-circle me-1 text-primary" aria-hidden="true"></i>Membuat akun</h2>
                <ol class="mb-0">
                    <li>Buka menu <strong>Daftar</strong> pada bilah navigasi.</li>
                    <li>Isi nama lengkap, email aktif, dan nomor HP yang dapat dihubungi.</li>
                    <li>Buat kata sandi minimal 8 karakter yang memuat huruf dan angka.</li>
                    <li>Centang persetujuan <a href="<?= site_url('kebijakan-privasi') ?>">Kebijakan Privasi</a>, lalu kirim.</li>
                </ol>
                <p class="text-body-secondary small mb-0 mt-3">
                    Pendaftaran publik hanya membuat akun <strong>Masyarakat</strong>. Akun instansi (Pemda) dan
                    administrator dibuat oleh pengelola sistem.
                </p>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5 mb-3"><i class="bi bi-2-circle me-1 text-primary" aria-hidden="true"></i>Mengirim aduan</h2>
                <ol class="mb-0">
                    <li>Masuk ke akun Anda, lalu pilih <strong>Aduan Saya &rarr; Buat Aduan</strong>.</li>
                    <li>Pilih kategori yang paling sesuai; kategori menentukan instansi penanggung jawab.</li>
                    <li>Tuliskan judul dan deskripsi yang jelas: apa, di mana, sejak kapan, dan dampaknya.</li>
                    <li>Tentukan <strong>titik lokasi</strong> dengan mengklik peta, mencari alamat, atau memakai lokasi perangkat.</li>
                    <li>Unggah maksimal 3 foto bukti (JPG, PNG, atau WEBP, maksimal 2 MB per berkas).</li>
                    <li>Kirim aduan, lalu simpan <strong>kode tiket</strong> yang ditampilkan.</li>
                </ol>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5 mb-3"><i class="bi bi-3-circle me-1 text-primary" aria-hidden="true"></i>Memantau status</h2>
                <ol class="mb-0">
                    <li>Buka halaman detail aduan pada menu <strong>Aduan Saya</strong> untuk melihat linimasa lengkap.</li>
                    <li>Gunakan menu <strong>Cek Status Aduan</strong> bila Anda tidak sedang masuk; masukkan kode tiket.</li>
                    <li>Anda dapat menambahkan tanggapan selama aduan belum selesai.</li>
                    <li>Setelah aduan berstatus <em>Selesai</em>, berikan penilaian 1–5 bintang atas pelayanan yang diterima.</li>
                </ol>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 mb-3"><i class="bi bi-signpost-split me-1 text-primary" aria-hidden="true"></i>Arti status aduan</h2>
                <ul class="list-unstyled mb-0">
                    <?php foreach (status_options() as $value => $label) : ?>
                        <li class="mb-2">
                            <span class="badge rounded-pill text-bg-secondary me-2"><?= esc($label) ?></span>
                        </li>
                    <?php endforeach; ?>
                    <li class="small text-body-secondary">
                        Alur utama: <strong>Menunggu &rarr; Diverifikasi &rarr; Diproses &rarr; Selesai</strong>.
                        Aduan juga dapat <strong>Ditolak</strong> dengan alasan yang wajib dicantumkan.
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h6">Ketentuan aduan</h2>
                <ul class="small mb-0">
                    <li>Aduan wajib memuat lokasi yang jelas.</li>
                    <li>Satu aduan untuk satu permasalahan.</li>
                    <li>Gunakan bahasa yang sopan dan tidak menyerang pihak lain.</li>
                    <li>Lampirkan foto yang relevan, bukan dokumen berisi data pribadi orang lain.</li>
                    <li>Aduan palsu atau berulang dapat ditolak.</li>
                </ul>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h6">Butuh bantuan?</h2>
                <p class="small text-body-secondary mb-3">
                    Bila aduan Anda tidak ditindaklanjuti sesuai rentang waktu wajar, sampaikan melalui
                    kanal pengaduan resmi instansi terkait.
                </p>
                <a class="btn btn-primary btn-sm w-100" href="<?= site_url('cek-aduan') ?>">
                    <i class="bi bi-search me-1" aria-hidden="true"></i>Cek status aduan
                </a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
