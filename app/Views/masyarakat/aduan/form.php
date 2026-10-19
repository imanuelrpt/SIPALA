<?php

/**
 * Formulir pembuatan aduan (masyarakat).
 *
 * @var list<\App\Entities\Category> $categories
 * @var array<string, string>        $errors
 */
$this->extend('layouts/main');

$errors = $errors ?? [];

/** @var \Config\Sipala $sipalaConfig */
$sipalaConfig = config(\Config\Sipala::class);
?>
<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Buat Aduan Baru</h1>
        <p class="text-body-secondary mb-0">Lengkapi data aduan; kolom bertanda <span class="text-danger">*</span> wajib diisi.</p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= site_url('masyarakat/aduan') ?>">
        <i class="bi bi-arrow-left me-1" aria-hidden="true"></i><?= esc(lang('App.back')) ?>
    </a>
</div>

<?= form_open_multipart(route_to('masyarakat.complaints.store'), ['novalidate' => true]) ?>
    <?= csrf_field() ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h2 class="h6 mb-0"><i class="bi bi-card-text me-1 text-primary" aria-hidden="true"></i>Informasi Aduan</h2>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label" for="title">
                        <?= esc(lang('App.title')) ?> <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="<?= input_class($errors, 'title') ?>" id="title" name="title"
                           required minlength="10" maxlength="150" value="<?= esc(old('title') ?? '', 'attr') ?>"
                           placeholder="Contoh: Lampu jalan mati di Jalan Asia Afrika">
                    <?php if (is_invalid($errors, 'title')) : ?>
                        <div class="invalid-feedback"><?= esc(field_error($errors, 'title')) ?></div>
                    <?php else : ?>
                        <div class="form-text">Minimal 10 karakter, jelaskan masalah secara singkat dan spesifik.</div>
                    <?php endif; ?>
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="category_id">
                        <?= esc(lang('App.category')) ?> <span class="text-danger">*</span>
                    </label>
                    <select class="<?= input_class($errors, 'category_id', 'form-select') ?>" id="category_id" name="category_id" required>
                        <option value="">Pilih kategori…</option>
                        <?php foreach ($categories as $category) : ?>
                            <?php
                            $label = (string) $category->name;
                            if (! empty($category->agency_name)) {
                                $label .= ' — ' . (string) $category->agency_name;
                            }
                            ?>
                            <option value="<?= esc((string) $category->id, 'attr') ?>"
                                <?= (string) old('category_id') === (string) $category->id ? 'selected' : '' ?>>
                                <?= esc($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (is_invalid($errors, 'category_id')) : ?>
                        <div class="invalid-feedback"><?= esc(field_error($errors, 'category_id')) ?></div>
                    <?php else : ?>
                        <div class="form-text">Kategori menentukan instansi penanggung jawab.</div>
                    <?php endif; ?>
                </div>

                <div class="col-12">
                    <label class="form-label" for="description">
                        <?= esc(lang('App.description')) ?> <span class="text-danger">*</span>
                    </label>
                    <textarea class="<?= input_class($errors, 'description') ?>" id="description" name="description"
                              rows="6" required minlength="20" maxlength="5000"
                              placeholder="Jelaskan apa yang terjadi, sejak kapan, dan dampaknya bagi warga."><?= esc(old('description') ?? '') ?></textarea>
                    <?php if (is_invalid($errors, 'description')) : ?>
                        <div class="invalid-feedback"><?= esc(field_error($errors, 'description')) ?></div>
                    <?php else : ?>
                        <div class="form-text">Minimal 20 karakter. Jangan mencantumkan data pribadi orang lain.</div>
                    <?php endif; ?>
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="incident_date">
                        <?= esc(lang('App.incidentDate')) ?> <span class="text-danger">*</span>
                    </label>
                    <input type="date" class="<?= input_class($errors, 'incident_date') ?>" id="incident_date"
                           name="incident_date" required max="<?= esc(date('Y-m-d'), 'attr') ?>"
                           value="<?= esc(old('incident_date') ?? '', 'attr') ?>">
                    <?php if (is_invalid($errors, 'incident_date')) : ?>
                        <div class="invalid-feedback"><?= esc(field_error($errors, 'incident_date')) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include('partials/map_picker', ['errors' => $errors]) ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h2 class="h6 mb-0"><i class="bi bi-camera me-1 text-primary" aria-hidden="true"></i><?= esc(lang('App.evidence')) ?></h2>
        </div>
        <div class="card-body">
            <label class="form-label" for="photos">Pilih foto (opsional)</label>
            <input type="file" class="form-control" id="photos" name="photos[]"
                   accept="image/jpeg,image/png,image/webp" multiple
                   data-preview-target="#pratinjau-foto"
                   data-max-files="<?= esc((string) $sipalaConfig->uploadMaxFiles, 'attr') ?>"
                   data-max-bytes="<?= esc((string) ($sipalaConfig->uploadMaxSizeKB * 1024), 'attr') ?>">
            <div class="form-text">
                Format JPG, PNG, atau WEBP. Maksimal <?= esc((string) $sipalaConfig->uploadMaxFiles) ?> berkas,
                ukuran maksimal <?= esc((string) $sipalaConfig->uploadMaxSizeKB) ?> KB per berkas.
                Metadata EXIF dibersihkan otomatis saat berkas disimpan.
            </div>
            <div class="alert alert-danger small mt-2 mb-0 d-none" data-upload-feedback="photos" role="alert"></div>
            <div class="upload-preview mt-3" id="pratinjau-foto"></div>

            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" value="1" id="hide_identity" name="hide_identity"
                    <?= old('hide_identity') === '1' ? 'checked' : '' ?>>
                <label class="form-check-label small" for="hide_identity">
                    <?= esc(lang('App.hideIdentity')) ?>
                </label>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-send me-1" aria-hidden="true"></i>Kirim Aduan
        </button>
        <a class="btn btn-outline-secondary" href="<?= site_url('masyarakat/aduan') ?>"><?= esc(lang('App.cancel')) ?></a>
    </div>
<?= form_close() ?>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/leaflet.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/leaflet.js') ?>" defer></script>
<script src="<?= base_url('assets/js/map-picker.js') ?>" defer></script>
<?= $this->endSection() ?>
