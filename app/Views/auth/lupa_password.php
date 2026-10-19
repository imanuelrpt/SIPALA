<?php
/**
 * Formulir permintaan reset kata sandi.
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
                <h1 class="h4 mb-1">Lupa Kata Sandi</h1>
                <p class="text-body-secondary small mb-4">
                    Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat kata sandi baru.
                </p>

                <?= form_open(route_to('auth.forgot.send'), ['novalidate' => true]) ?>
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

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-envelope me-1" aria-hidden="true"></i>Kirim Tautan Reset
                    </button>
                <?= form_close() ?>

                <p class="small mb-0 mt-4 text-center">
                    <a href="<?= site_url('masuk') ?>">&larr; <?= esc(lang('App.back')) ?> ke halaman masuk</a>
                </p>
            </div>
        </div>

        <p class="small text-body-secondary mt-3 mb-0">
            Untuk alasan keamanan, kami tidak mengungkapkan apakah sebuah email terdaftar atau tidak.
        </p>
    </div>
</div>
<?= $this->endSection() ?>
