<?php

/**
 * Master data kategori aduan (Admin).
 *
 * @var list<\App\Entities\Category> $categories
 * @var array<int, string>           $agencies
 * @var array<string, string>        $errors
 */
$this->extend('layouts/main');

$errors = $errors ?? [];
?>
<?= $this->section('content') ?>
<h1 class="h3 mb-1"><?= esc(lang('App.categories')) ?></h1>
<p class="text-body-secondary mb-4">
    Kategori memetakan aduan masyarakat ke instansi penanggung jawab.
</p>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h2 class="h6 mb-0"><i class="bi bi-plus-circle me-1 text-primary" aria-hidden="true"></i>Tambah Kategori</h2></div>
            <div class="card-body">
                <?= form_open(route_to('admin.categories.store'), ['novalidate' => true]) ?>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="name"><?= esc(lang('App.name')) ?> <span class="text-danger">*</span></label>
                        <input type="text" class="<?= input_class($errors, 'name') ?>" id="name" name="name"
                               required minlength="3" maxlength="100" value="<?= esc(old('name') ?? '', 'attr') ?>">
                        <?php if (is_invalid($errors, 'name')) : ?>
                            <div class="invalid-feedback"><?= esc(field_error($errors, 'name')) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="agency_id"><?= esc(lang('App.agency')) ?> <span class="text-danger">*</span></label>
                        <select class="<?= input_class($errors, 'agency_id', 'form-select') ?>" id="agency_id" name="agency_id" required>
                            <option value="">Pilih instansi…</option>
                            <?php foreach ($agencies as $id => $agencyName) : ?>
                                <option value="<?= esc((string) $id, 'attr') ?>" <?= (string) old('agency_id') === (string) $id ? 'selected' : '' ?>>
                                    <?= esc($agencyName) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (is_invalid($errors, 'agency_id')) : ?>
                            <div class="invalid-feedback"><?= esc(field_error($errors, 'agency_id')) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="is_active"><?= esc(lang('App.isActive')) ?></label>
                        <select class="form-select" id="is_active" name="is_active">
                            <option value="1"><?= esc(lang('App.active')) ?></option>
                            <option value="0"><?= esc(lang('App.inactive')) ?></option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check2 me-1" aria-hidden="true"></i><?= esc(lang('App.save')) ?>
                    </button>
                <?= form_close() ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <?php if ($categories === []) : ?>
            <div class="empty-state card border-0 shadow-sm">
                <i class="bi bi-tags" aria-hidden="true"></i>
                <p class="mb-0 mt-2"><?= esc(lang('App.noData')) ?></p>
            </div>
        <?php else : ?>
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover table-sipala mb-0">
                        <thead>
                        <tr>
                            <th scope="col"><?= esc(lang('App.name')) ?></th>
                            <th scope="col"><?= esc(lang('App.agency')) ?></th>
                            <th scope="col"><?= esc(lang('App.status')) ?></th>
                            <th scope="col" class="text-end">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($categories as $category) : ?>
                            <tr>
                                <td><?= esc((string) $category->name) ?></td>
                                <td class="small">
                                    <?= esc((string) ($category->agency_name ?? ($agencies[$category->agency_id] ?? '-'))) ?>
                                </td>
                                <td>
                                    <?php if ($category->isActive()) : ?>
                                        <span class="badge text-bg-success"><?= esc(lang('App.active')) ?></span>
                                    <?php else : ?>
                                        <span class="badge text-bg-secondary"><?= esc(lang('App.inactive')) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#modal-kategori-<?= esc((string) $category->id, 'attr') ?>">
                                        <i class="bi bi-pencil" aria-hidden="true"></i>
                                        <span class="visually-hidden"><?= esc(lang('App.edit')) ?></span>
                                    </button>
                                    <?= form_open(route_to('admin.categories.delete', (int) $category->id), [
                                        'class'        => 'd-inline',
                                        'data-confirm' => 'Hapus kategori ini? Tindakan ini tidak dapat dibatalkan.',
                                    ]) ?>
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash" aria-hidden="true"></i>
                                            <span class="visually-hidden"><?= esc(lang('App.delete')) ?></span>
                                        </button>
                                    <?= form_close() ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php foreach ($categories as $category) : ?>
                <div class="modal fade" id="modal-kategori-<?= esc((string) $category->id, 'attr') ?>" tabindex="-1"
                     aria-labelledby="modal-kategori-label-<?= esc((string) $category->id, 'attr') ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <?= form_open(route_to('admin.categories.update', (int) $category->id), ['novalidate' => true]) ?>
                                <?= csrf_field() ?>
                                <div class="modal-header">
                                    <h2 class="modal-title h6" id="modal-kategori-label-<?= esc((string) $category->id, 'attr') ?>">Ubah Kategori</h2>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc(lang('App.close')) ?>"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label" for="name-<?= esc((string) $category->id, 'attr') ?>"><?= esc(lang('App.name')) ?></label>
                                        <input type="text" class="form-control" id="name-<?= esc((string) $category->id, 'attr') ?>"
                                               name="name" required minlength="3" maxlength="100"
                                               value="<?= esc((string) $category->name, 'attr') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="agency_id-<?= esc((string) $category->id, 'attr') ?>"><?= esc(lang('App.agency')) ?></label>
                                        <select class="form-select" id="agency_id-<?= esc((string) $category->id, 'attr') ?>" name="agency_id" required>
                                            <?php foreach ($agencies as $id => $agencyName) : ?>
                                                <option value="<?= esc((string) $id, 'attr') ?>" <?= (int) $category->agency_id === (int) $id ? 'selected' : '' ?>>
                                                    <?= esc($agencyName) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label" for="is_active-<?= esc((string) $category->id, 'attr') ?>"><?= esc(lang('App.isActive')) ?></label>
                                        <select class="form-select" id="is_active-<?= esc((string) $category->id, 'attr') ?>" name="is_active">
                                            <option value="1" <?= $category->isActive() ? 'selected' : '' ?>><?= esc(lang('App.active')) ?></option>
                                            <option value="0" <?= ! $category->isActive() ? 'selected' : '' ?>><?= esc(lang('App.inactive')) ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= esc(lang('App.cancel')) ?></button>
                                    <button type="submit" class="btn btn-primary"><?= esc(lang('App.save')) ?></button>
                                </div>
                            <?= form_close() ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
