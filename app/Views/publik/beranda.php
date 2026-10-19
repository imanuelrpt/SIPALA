<?php

use App\Enums\ComplaintStatus;

/**
 * Beranda publik SIPALA.
 *
 * @var array<string, mixed> $overview
 * @var array<string, int>   $statusList
 */
$this->extend('layouts/main');

$statusList = $statusList ?? [];
$latest     = $overview['latest'] ?? [];
?>
<?= $this->section('content') ?>
<section class="hero p-4 p-md-5 mb-4">
    <div class="row align-items-center g-4">
        <div class="col-lg-7">
            <span class="badge rounded-pill text-bg-light mb-3">
                <i class="bi bi-shield-check me-1" aria-hidden="true"></i>Kanal Resmi Pengaduan Masyarakat
            </span>
            <h1 class="display-5 fw-bold mb-3"><?= esc(lang('App.appLongName')) ?></h1>
            <p class="lead mb-4"><?= esc(lang('App.appTagline')) ?></p>
            <div class="d-flex flex-wrap gap-2">
                <?php if ($currentUser === null) : ?>
                    <a class="btn btn-light btn-lg text-primary" href="<?= site_url('daftar') ?>">
                        <i class="bi bi-pencil-square me-1" aria-hidden="true"></i>Buat Aduan Sekarang
                    </a>
                    <a class="btn btn-outline-light btn-lg" href="<?= site_url('cek-aduan') ?>">
                        <i class="bi bi-search me-1" aria-hidden="true"></i><?= esc(lang('App.tracking')) ?>
                    </a>
                <?php else : ?>
                    <a class="btn btn-light btn-lg text-primary" href="<?= site_url('masyarakat/aduan/baru') ?>">
                        <i class="bi bi-pencil-square me-1" aria-hidden="true"></i>Buat Aduan Baru
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="row g-3">
                <div class="col-6">
                    <div class="p-3 rounded-3 bg-white bg-opacity-10 border border-light border-opacity-25">
                        <div class="fs-3 fw-bold"><?= esc(angka_ringkas((int) ($overview['total'] ?? 0))) ?></div>
                        <div class="small">Total aduan masuk</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 bg-white bg-opacity-10 border border-light border-opacity-25">
                        <div class="fs-3 fw-bold"><?= esc(angka_ringkas((int) ($overview['resolved'] ?? 0))) ?></div>
                        <div class="small">Aduan selesai</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 bg-white bg-opacity-10 border border-light border-opacity-25">
                        <div class="fs-3 fw-bold"><?= esc(angka_ringkas((int) ($overview['awaiting'] ?? 0))) ?></div>
                        <div class="small">Menunggu verifikasi</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 bg-white bg-opacity-10 border border-light border-opacity-25">
                        <div class="fs-3 fw-bold"><?= esc(angka_ringkas((float) ($overview['average_days'] ?? 0), 1)) ?></div>
                        <div class="small">Rata-rata hari selesai</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mb-5">
    <h2 class="h4 mb-3">Bagaimana SIPALA bekerja?</h2>
    <div class="row g-3">
        <?php
        $steps = [
            ['bi-person-plus', '1. Daftar & masuk', 'Buat akun dengan email dan nomor HP Anda. Hanya warga yang dapat mendaftar sendiri.'],
            ['bi-geo-alt', '2. Laporkan lokasi', 'Tandai titik kejadian pada peta, lengkapi deskripsi dan foto bukti.'],
            ['bi-arrow-repeat', '3. Pantau tindak lanjut', 'Simpan kode tiket untuk memantau status, tanggapan, hingga penyelesaian.'],
        ];
        ?>
        <?php foreach ($steps as [$icon, $title, $text]) : ?>
            <div class="col-md-4">
                <div class="stat-card h-100 p-4">
                    <span class="stat-icon mb-3"><i class="bi <?= esc($icon, 'attr') ?>" aria-hidden="true"></i></span>
                    <h3 class="h6"><?= esc($title) ?></h3>
                    <p class="text-body-secondary small mb-0"><?= esc($text) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h2 class="h4 mb-0">Aduan Terbaru</h2>
        <a class="btn btn-sm btn-outline-primary" href="<?= site_url('statistik') ?>">
            <i class="bi bi-bar-chart me-1" aria-hidden="true"></i>Lihat statistik lengkap
        </a>
    </div>

    <?php if ($latest === []) : ?>
        <div class="empty-state card border-0 shadow-sm">
            <i class="bi bi-inbox" aria-hidden="true"></i>
            <p class="mb-0 mt-2"><?= esc(lang('App.noData')) ?></p>
        </div>
    <?php else : ?>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover table-sipala mb-0">
                    <thead>
                    <tr>
                        <th scope="col">Aduan</th>
                        <th scope="col">Kategori</th>
                        <th scope="col">Wilayah</th>
                        <th scope="col"><?= esc(lang('App.status')) ?></th>
                        <th scope="col"><?= esc(lang('App.createdAt')) ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($latest as $complaint) : ?>
                        <tr>
                            <td>
                                <span class="d-block fw-semibold"><?= esc(kutip_ringkas((string) $complaint->title, 60)) ?></span>
                                <span class="text-body-secondary small">
                                    <?= esc(nama_pelapor((string) ($complaint->reporter_name ?? ''), $complaint->hideIdentity())) ?>
                                </span>
                            </td>
                            <td class="small"><?= esc((string) ($complaint->category_name ?? '-')) ?></td>
                            <td class="small"><?= esc((string) ($complaint->district ?? '-')) ?>, <?= esc((string) ($complaint->city ?? '-')) ?></td>
                            <td><?= status_badge($complaint->status()) ?></td>
                            <td class="small text-nowrap"><?= esc(format_tanggal($complaint->created_at)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <p class="text-body-secondary small mt-2 mb-0">
            Identitas pelapor yang memilih opsi kerahasiaan tidak ditampilkan (sesuai UU Perlindungan Data Pribadi).
        </p>
    <?php endif; ?>
</section>

<section class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-md-8">
                <h2 class="h5 mb-1">Aduan Anda ditangani instansi yang berwenang</h2>
                <p class="text-body-secondary mb-0">
                    Setiap aduan diverifikasi dan diteruskan ke dinas terkait. Anda akan menerima tanggapan
                    resmi melalui halaman detail aduan.
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <a class="btn btn-primary" href="<?= $currentUser === null ? site_url('daftar') : site_url('masyarakat/aduan/baru') ?>">
                    <i class="bi bi-megaphone me-1" aria-hidden="true"></i>Sampaikan Aduan
                </a>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
