<?php
/**
 * Formulir masuk.
 *
 * @var array<string, string> $errors
 */
$this->extend('layouts/main');

$errors = $errors ?? [];
?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-1"><?= esc(lang('App.login')) ?></h1>
                <p class="text-body-secondary small mb-4">Masuk untuk mengirim dan memantau aduan Anda.</p>

                <?= form_open(route_to('auth.login.attempt'), ['novalidate' => true]) ?>
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label" for="email"><?= esc(lang('App.email')) ?></label>
                        <input type="email" class="form-control <?= is_invalid($errors, 'email') ? 'is-invalid' : '' ?>"
                               id="email" name="email" required maxlength="191" autocomplete="email"
                               value="<?= esc(old('email') ?? '', 'attr') ?>">
                        <?php if (is_invalid($errors, 'email')) : ?>
                            <div class="invalid-feedback"><?= esc(field_error($errors, 'email')) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password"><?= esc(lang('App.password')) ?></label>
                        <input type="password" class="form-control <?= is_invalid($errors, 'password') ? 'is-invalid' : '' ?>"
                               id="password" name="password" required maxlength="72" autocomplete="current-password">
                        <?php if (is_invalid($errors, 'password')) : ?>
                            <div class="invalid-feedback"><?= esc(field_error($errors, 'password')) ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i><?= esc(lang('App.login')) ?>
                    </button>
                <?= form_close() ?>

                <div class="d-flex flex-wrap justify-content-between gap-2 mt-4 small">
                    <a href="<?= site_url('lupa-password') ?>"><?= esc(lang('App.forgotPassword')) ?></a>
                    <span>
                        <?= esc(lang('App.noAccount')) ?>
                        <a href="<?= site_url('daftar') ?>"><?= esc(lang('App.register')) ?></a>
                    </span>
                </div>
            </div>
        </div>

        <div class="alert alert-info small mt-3" role="note">
            <strong>Akun demo:</strong> gunakan email seperti <code>admin@sipala.test</code>,
            <code>pupr1@sipala.test</code>, atau <code>warga1@sipala.test</code> — kata sandi lihat README.
        </div>
    </div>
</div>
<?= $this->endSection() ?>
