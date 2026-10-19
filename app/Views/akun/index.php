<?php

use App\Enums\UserRole;

/**
 * Halaman akun (dipakai seluruh peran).
 *
 * @var \App\Entities\User|null $currentUser
 * @var string|null             $agencyName
 * @var array<string, string>   $errors
 */
$this->extend('layouts/main');

$errors   = $errors ?? [];
$agencyName = $agencyName ?? null;

$passwordRoute = match ($currentUser?->role()) {
    UserRole::Admin => 'admin.password.update',
    UserRole::Pemda => 'pemda.password.update',
    default         => 'masyarakat.password.update',
};
?>
<?= $this->section('content') ?>
<h1 class="h3 mb-1"><?= esc(lang('App.account')) ?></h1>
<p class="text-body-secondary mb-4">Informasi akun dan pengaturan kata sandi.</p>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <span class="avatar-badge avatar-badge-lg" aria-hidden="true"><?= esc(inisial_nama($currentUser?->displayName())) ?></span>
                    <div>
                        <h2 class="h6 mb-0"><?= esc($currentUser?->displayName() ?? '-') ?></h2>
                        <?php if ($currentUser !== null) : ?>
                            <?= role_badge($currentUser->role()) ?>
                        <?php endif; ?>
                    </div>
                </div>

                <dl class="row small mb-0">
                    <dt class="col-4"><?= esc(lang('App.email')) ?></dt>
                    <dd class="col-8 text-break"><?= esc((string) ($currentUser?->email ?? '-')) ?></dd>

                    <dt class="col-4"><?= esc(lang('App.phone')) ?></dt>
                    <dd class="col-8"><?= esc((string) ($currentUser?->phone ?? '-')) ?></dd>

                    <?php if ($agencyName !== null) : ?>
                        <dt class="col-4"><?= esc(lang('App.agency')) ?></dt>
                        <dd class="col-8"><?= esc($agencyName) ?></dd>
                    <?php endif; ?>

                    <dt class="col-4">Status akun</dt>
                    <dd class="col-8">
                        <?php if ($currentUser?->isActive()) : ?>
                            <span class="badge text-bg-success"><?= esc(lang('App.active')) ?></span>
                        <?php else : ?>
                            <span class="badge text-bg-secondary"><?= esc(lang('App.inactive')) ?></span>
                        <?php endif; ?>
                    </dd>

                    <dt class="col-4">Login terakhir</dt>
                    <dd class="col-8"><?= esc(format_tanggal_waktu($currentUser?->last_login_at)) ?></dd>

                    <dt class="col-4">Terdaftar sejak</dt>
                    <dd class="col-8"><?= esc(format_tanggal($currentUser?->created_at)) ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6 mb-3"><i class="bi bi-shield-lock me-1 text-primary" aria-hidden="true"></i>Ganti Kata Sandi</h2>

                <?= form_open(route_to($passwordRoute), ['novalidate' => true]) ?>
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label" for="current_password"><?= esc(lang('App.currentPassword')) ?></label>
                        <input type="password" class="form-control <?= is_invalid($errors, 'current_password') ? 'is-invalid' : '' ?>"
                               id="current_password" name="current_password" required autocomplete="current-password">
                        <?php if (is_invalid($errors, 'current_password')) : ?>
                            <div class="invalid-feedback"><?= esc(field_error($errors, 'current_password')) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="password"><?= esc(lang('App.newPassword')) ?></label>
                            <input type="password" class="form-control <?= is_invalid($errors, 'password') ? 'is-invalid' : '' ?>"
                                   id="password" name="password" required minlength="8" maxlength="72" autocomplete="new-password">
                            <div class="form-text">Minimal 8 karakter, memuat huruf dan angka.</div>
                            <?php if (is_invalid($errors, 'password')) : ?>
                                <div class="invalid-feedback"><?= esc(field_error($errors, 'password')) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="password_confirm"><?= esc(lang('App.passwordConfirm')) ?></label>
                            <input type="password" class="form-control <?= is_invalid($errors, 'password_confirm') ? 'is-invalid' : '' ?>"
                                   id="password_confirm" name="password_confirm" required maxlength="72" autocomplete="new-password">
                            <?php if (is_invalid($errors, 'password_confirm')) : ?>
                                <div class="invalid-feedback"><?= esc(field_error($errors, 'password_confirm')) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-4">
                        <i class="bi bi-check2 me-1" aria-hidden="true"></i><?= esc(lang('App.save')) ?>
                    </button>
                <?= form_close() ?>

                <hr>
                <p class="small text-body-secondary mb-0">
                    <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
                    Setelah kata sandi diganti, sesi Anda diperbarui. Gunakan kata sandi baru saat masuk berikutnya.
                </p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
