<?php
/**
 * Formulir pengaturan ulang kata sandi (token dari email).
 *
 * @var string                $token
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
                <h1 class="h4 mb-1">Atur Ulang Kata Sandi</h1>
                <p class="text-body-secondary small mb-4">Buat kata sandi baru untuk akun Anda.</p>

                <?= form_open(route_to('auth.reset.save'), ['novalidate' => true]) ?>
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= esc($token, 'attr') ?>">

                    <div class="mb-3">
                        <label class="form-label" for="password"><?= esc(lang('App.newPassword')) ?></label>
                        <input type="password" class="form-control <?= is_invalid($errors, 'password') ? 'is-invalid' : '' ?>"
                               id="password" name="password" required minlength="8" maxlength="72" autocomplete="new-password">
                        <div class="form-text">Minimal 8 karakter, memuat huruf dan angka.</div>
                        <?php if (is_invalid($errors, 'password')) : ?>
                            <div class="invalid-feedback"><?= esc(field_error($errors, 'password')) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password_confirm"><?= esc(lang('App.passwordConfirm')) ?></label>
                        <input type="password" class="form-control <?= is_invalid($errors, 'password_confirm') ? 'is-invalid' : '' ?>"
                               id="password_confirm" name="password_confirm" required maxlength="72" autocomplete="new-password">
                        <?php if (is_invalid($errors, 'password_confirm')) : ?>
                            <div class="invalid-feedback"><?= esc(field_error($errors, 'password_confirm')) ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-shield-check me-1" aria-hidden="true"></i>Simpan Kata Sandi Baru
                    </button>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
