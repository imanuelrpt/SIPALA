<?php

use App\Enums\UserRole;

/** @var \App\Entities\User|null $currentUser */
$currentUser = $currentUser ?? null;
$role        = $currentUser?->role();
?>
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background:#0d3b66">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-semibold" href="<?= site_url('/') ?>">
            <i class="bi bi-megaphone-fill fs-4" aria-hidden="true"></i>
            <span><?= esc(lang('App.appName')) ?></span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu-utama"
                aria-controls="menu-utama" aria-expanded="false" aria-label="<?= esc(lang('App.openMenu')) ?>">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menu-utama">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if ($currentUser === null) : ?>
                    <li class="nav-item">
                        <a class="nav-link <?= menu_aktif('') ?>" href="<?= site_url('/') ?>"><?= esc(lang('App.home')) ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= menu_aktif('cek-aduan') ?>" href="<?= site_url('cek-aduan') ?>"><?= esc(lang('App.tracking')) ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= menu_aktif('panduan') ?>" href="<?= site_url('panduan') ?>"><?= esc(lang('App.guide')) ?></a>
                    </li>
                <?php elseif ($role === UserRole::Masyarakat) : ?>
                    <li class="nav-item">
                        <a class="nav-link <?= menu_aktif('masyarakat/aduan|masyarakat$') ?>" href="<?= site_url('masyarakat/aduan') ?>">
                            <i class="bi bi-list-ul me-1" aria-hidden="true"></i><?= esc(lang('App.myComplaints')) ?>
                        </a>
                    </li>
                <?php elseif ($role === UserRole::Pemda) : ?>
                    <li class="nav-item">
                        <a class="nav-link <?= menu_aktif('pemda/aduan') ?>" href="<?= site_url('pemda/aduan') ?>">
                            <i class="bi bi-inbox me-1" aria-hidden="true"></i><?= esc(lang('App.incomingComplaints')) ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= menu_aktif('pemda/peta') ?>" href="<?= site_url('pemda/peta') ?>">
                            <i class="bi bi-geo-alt me-1" aria-hidden="true"></i><?= esc(lang('App.map')) ?>
                        </a>
                    </li>
                <?php elseif ($role === UserRole::Admin) : ?>
                    <li class="nav-item">
                        <a class="nav-link <?= menu_aktif('admin/aduan') ?>" href="<?= site_url('admin/aduan') ?>">
                            <i class="bi bi-inbox me-1" aria-hidden="true"></i><?= esc(lang('App.allComplaints')) ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= menu_aktif('admin/peta') ?>" href="<?= site_url('admin/peta') ?>">
                            <i class="bi bi-geo-alt me-1" aria-hidden="true"></i><?= esc(lang('App.map')) ?>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= menu_aktif('admin/pengguna|admin/instansi|admin/kategori') ?>" href="#"
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-sliders me-1" aria-hidden="true"></i>Master Data
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= site_url('admin/pengguna') ?>"><?= esc(lang('App.users')) ?></a></li>
                            <li><a class="dropdown-item" href="<?= site_url('admin/instansi') ?>"><?= esc(lang('App.agencies')) ?></a></li>
                            <li><a class="dropdown-item" href="<?= site_url('admin/kategori') ?>"><?= esc(lang('App.categories')) ?></a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= menu_aktif('admin/laporan') ?>" href="<?= site_url('admin/laporan') ?>">
                            <i class="bi bi-bar-chart me-1" aria-hidden="true"></i><?= esc(lang('App.reports')) ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= menu_aktif('admin/log') ?>" href="<?= site_url('admin/log') ?>">
                            <i class="bi bi-clock-history me-1" aria-hidden="true"></i><?= esc(lang('App.activityLog')) ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav ms-auto align-items-lg-center">
                <?php if ($currentUser === null) : ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('masuk') ?>"><?= esc(lang('App.login')) ?></a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-light btn-sm px-3" href="<?= site_url('daftar') ?>"><?= esc(lang('App.register')) ?></a>
                    </li>
                <?php else : ?>
                    <li class="nav-item">
                        <a class="nav-link <?= menu_aktif('akun') ?> d-flex align-items-center gap-2" href="<?= site_url($role === UserRole::Admin ? 'admin/akun' : ($role === UserRole::Pemda ? 'pemda/akun' : 'masyarakat/akun')) ?>">
                            <span class="avatar-badge" aria-hidden="true"><?= esc(inisial_nama($currentUser->displayName())) ?></span>
                            <span class="d-none d-lg-inline"><?= esc($currentUser->displayName()) ?></span>
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <?= form_open(site_url('keluar'), ['class' => 'd-inline', 'data-confirm' => 'Keluar dari aplikasi?']) ?>
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i><?= esc(lang('App.logout')) ?>
                            </button>
                        <?= form_close() ?>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
