<?php
/**
 * Formulir pendaftaran masyarakat.
 *
 * @var array<string, string> $errors
 */
$this->extend('layouts/main');

$errors = $errors ?? [];
?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-1"><?= esc(lang('App.register')) ?></h1>
                <p class="text-body-secondary small mb-4">
                    Buat akun masyarakat untuk menyampaikan dan memantau pengaduan layanan publik.
                </p>

                <?= form_open(route_to('auth.register.store'), ['novalidate' => true]) ?>
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="name"><?= esc(lang('App.name')) ?></label>
                            <input type="text" class="form-control <?= is_invalid($errors, 'name') ? 'is-invalid' : '' ?>"
                                   id="name" name="name" required minlength="3" maxlength="100" autocomplete="name"
                                   value="<?= esc(old('name') ?? '', 'attr') ?>">
                            <?php if (is_invalid($errors, 'name')) : ?>
                                <div class="invalid-feedback"><?= esc(field_error($errors, 'name')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="phone"><?= esc(lang('App.phone')) ?></label>
                            <input type="tel" class="form-control <?= is_invalid($errors, 'phone') ? 'is-invalid' : '' ?>"
                                   id="phone" name="phone" required minlength="9" maxlength="25" autocomplete="tel"
                                   value="<?= esc(old('phone') ?? '', 'attr') ?>">
                            <?php if (is_invalid($errors, 'phone')) : ?>
                                <div class="invalid-feedback"><?= esc(field_error($errors, 'phone')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="email"><?= esc(lang('App.email')) ?></label>
                            <input type="email" class="form-control <?= is_invalid($errors, 'email') ? 'is-invalid' : '' ?>"
                                   id="email" name="email" required maxlength="191" autocomplete="email"
                                   value="<?= esc(old('email') ?? '', 'attr') ?>">
                            <?php if (is_invalid($errors, 'email')) : ?>
                                <div class="invalid-feedback"><?= esc(field_error($errors, 'email')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="password"><?= esc(lang('App.password')) ?></label>
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

                    <div class="form-check mt-4">
                        <input class="form-check-input <?= is_invalid($errors, 'privacy_accepted') ? 'is-invalid' : '' ?>"
                               type="checkbox" value="1" id="privacy_accepted" name="privacy_accepted" required>
                        <label class="form-check-label small" for="privacy_accepted">
                            <?= lang('App.privacyConsent', [site_url('kebijakan-privasi')]) ?>
                        </label>
                        <?php if (is_invalid($errors, 'privacy_accepted')) : ?>
                            <div class="invalid-feedback"><?= esc(field_error($errors, 'privacy_accepted')) ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-4">
                        <i class="bi bi-person-plus me-1" aria-hidden="true"></i><?= esc(lang('App.register')) ?>
                    </button>
                <?= form_close() ?>

                <p class="small mb-0 mt-4 text-center">
                    <?= esc(lang('App.haveAccount')) ?> <a href="<?= site_url('masuk') ?>"><?= esc(lang('App.login')) ?></a>
                </p>
            </div>
        </div>

        <p class="small text-body-secondary mt-3 mb-0">
            Akun ini hanya berperan sebagai <strong>Masyarakat</strong>. Akun instansi dan administrator
            dibuat oleh pengelola sistem.
        </p>
    </div>
</div>
<?= $this->endSection() ?>
