<?php

/**
 * Formulir tambah/ubah pengguna (Admin).
 *
 * @var \App\Entities\User|null $user
 * @var array<int, string>      $agencies
 * @var array<string, string>   $roles
 * @var array<string, string>   $errors
 */
$this->extend('layouts/main');

$errors  = $errors ?? [];
$isEdit  = $user !== null;
$action  = $isEdit
    ? route_to('admin.users.update', (int) $user->id)
    : route_to('admin.users.store');

$nameValue   = (string) (old('name') ?? ($user?->name ?? ''));
$emailValue  = (string) (old('email') ?? ($user?->email ?? ''));
$phoneValue  = (string) (old('phone') ?? ($user?->phone ?? ''));
$roleValue   = (string) (old('role') ?? ($user?->role()->value ?? ''));
$agencyValue = (string) (old('agency_id') ?? ($user?->agencyId() ?? ''));
$activeValue = (string) (old('is_active') ?? ($user === null ? '1' : ($user->isActive() ? '1' : '0')));
?>
<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1"><?= esc($isEdit ? 'Ubah Pengguna' : 'Tambah Pengguna') ?></h1>
        <p class="text-body-secondary mb-0">
            <?= $isEdit ? 'Perbarui data akun dan hak aksesnya.' : 'Buat akun baru untuk petugas instansi atau administrator.' ?>
        </p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= site_url('admin/pengguna') ?>">
        <i class="bi bi-arrow-left me-1" aria-hidden="true"></i><?= esc(lang('App.back')) ?>
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <?= form_open($action, ['novalidate' => true]) ?>
            <?= csrf_field() ?>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h2 class="h6 mb-0">Data Akun</h2></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="name"><?= esc(lang('App.name')) ?> <span class="text-danger">*</span></label>
                            <input type="text" class="<?= input_class($errors, 'name') ?>" id="name" name="name"
                                   required minlength="3" maxlength="100" value="<?= esc($nameValue, 'attr') ?>">
                            <?php if (is_invalid($errors, 'name')) : ?>
                                <div class="invalid-feedback"><?= esc(field_error($errors, 'name')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="email"><?= esc(lang('App.email')) ?> <span class="text-danger">*</span></label>
                            <input type="email" class="<?= input_class($errors, 'email') ?>" id="email" name="email"
                                   required maxlength="191" value="<?= esc($emailValue, 'attr') ?>">
                            <?php if (is_invalid($errors, 'email')) : ?>
                                <div class="invalid-feedback"><?= esc(field_error($errors, 'email')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="phone"><?= esc(lang('App.phone')) ?></label>
                            <input type="tel" class="<?= input_class($errors, 'phone') ?>" id="phone" name="phone"
                                   maxlength="25" value="<?= esc($phoneValue, 'attr') ?>">
                            <?php if (is_invalid($errors, 'phone')) : ?>
                                <div class="invalid-feedback"><?= esc(field_error($errors, 'phone')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="is_active"><?= esc(lang('App.isActive')) ?> <span class="text-danger">*</span></label>
                            <select class="form-select <?= is_invalid($errors, 'is_active') ? 'is-invalid' : '' ?>" id="is_active" name="is_active" required>
                                <option value="1" <?= $activeValue === '1' ? 'selected' : '' ?>><?= esc(lang('App.active')) ?></option>
                                <option value="0" <?= $activeValue === '0' ? 'selected' : '' ?>><?= esc(lang('App.inactive')) ?></option>
                            </select>
                            <?php if (is_invalid($errors, 'is_active')) : ?>
                                <div class="invalid-feedback"><?= esc(field_error($errors, 'is_active')) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h2 class="h6 mb-0">Peran &amp; Hak Akses</h2></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="role"><?= esc(lang('App.role')) ?> <span class="text-danger">*</span></label>
                            <select class="form-select <?= is_invalid($errors, 'role') ? 'is-invalid' : '' ?>" id="role" name="role" required>
                                <option value="">Pilih peran…</option>
                                <?php foreach ($roles as $value => $label) : ?>
                                    <option value="<?= esc($value, 'attr') ?>" <?= $roleValue === $value ? 'selected' : '' ?>>
                                        <?= esc($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (is_invalid($errors, 'role')) : ?>
                                <div class="invalid-feedback"><?= esc(field_error($errors, 'role')) ?></div>
                            <?php endif; ?>
                            <div class="form-text">Masyarakat umumnya mendaftar sendiri melalui halaman publik.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="agency_id"><?= esc(lang('App.agency')) ?></label>
                            <select class="form-select <?= is_invalid($errors, 'agency_id') ? 'is-invalid' : '' ?>" id="agency_id" name="agency_id">
                                <option value="">— Tidak ada / bukan Pemda —</option>
                                <?php foreach ($agencies as $id => $agencyName) : ?>
                                    <option value="<?= esc((string) $id, 'attr') ?>" <?= $agencyValue === (string) $id ? 'selected' : '' ?>>
                                        <?= esc($agencyName) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (is_invalid($errors, 'agency_id')) : ?>
                                <div class="invalid-feedback"><?= esc(field_error($errors, 'agency_id')) ?></div>
                            <?php endif; ?>
                            <div class="form-text">Wajib dipilih untuk akun dengan peran <strong>Pemda</strong>.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h2 class="h6 mb-0">Kata Sandi</h2></div>
                <div class="card-body">
                    <?php if ($isEdit) : ?>
                        <p class="small text-body-secondary mb-0">
                            Kata sandi tidak diubah melalui formulir ini. Gunakan tombol <strong>Reset kata sandi</strong>
                            pada daftar pengguna untuk membuat kata sandi sementara.
                        </p>
                    <?php else : ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="password"><?= esc(lang('App.password')) ?> <span class="text-danger">*</span></label>
                                <input type="password" class="<?= input_class($errors, 'password') ?>" id="password" name="password"
                                       required minlength="8" maxlength="72" autocomplete="new-password">
                                <div class="form-text">Minimal 8 karakter, memuat huruf dan angka.</div>
                                <?php if (is_invalid($errors, 'password')) : ?>
                                    <div class="invalid-feedback"><?= esc(field_error($errors, 'password')) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2 me-1" aria-hidden="true"></i><?= esc(lang('App.save')) ?>
                </button>
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/pengguna') ?>"><?= esc(lang('App.cancel')) ?></a>
            </div>
        <?= form_close() ?>
    </div>
</div>
<?= $this->endSection() ?>
