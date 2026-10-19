<?php

/**
 * Manajemen pengguna (Admin).
 *
 * @var list<\App\Entities\User> $items
 * @var int                      $total
 * @var array<string, mixed>     $filters
 * @var array<int, string>       $agencies
 * @var array<string, string>    $roles
 * @var \CodeIgniter\Pager\Pager $pager
 */
$this->extend('layouts/main');

$roleFilter   = (string) ($filters['role'] ?? '');
$activeFilter = (string) ($filters['is_active'] ?? '');
$agencyFilter = (string) ($filters['agency_id'] ?? '');
$searchFilter = (string) ($filters['search'] ?? '');
?>
<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1"><?= esc(lang('App.users')) ?></h1>
        <p class="text-body-secondary mb-0">Total <?= esc(angka_ringkas($total)) ?> akun terdaftar.</p>
    </div>
    <a class="btn btn-primary" href="<?= site_url('admin/pengguna/baru') ?>">
        <i class="bi bi-person-plus me-1" aria-hidden="true"></i><?= esc(lang('App.add')) ?>
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <?= form_open(site_url('admin/pengguna'), ['method' => 'get', 'class' => 'row g-3 align-items-end']) ?>
            <div class="col-md-4">
                <label class="form-label" for="search"><?= esc(lang('App.search')) ?></label>
                <input type="search" class="form-control" id="search" name="search" maxlength="100"
                       value="<?= esc($searchFilter, 'attr') ?>" placeholder="Nama, email, atau nomor HP">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="role"><?= esc(lang('App.role')) ?></label>
                <select class="form-select" id="role" name="role">
                    <option value="">Semua</option>
                    <?php foreach ($roles as $value => $label) : ?>
                        <option value="<?= esc($value, 'attr') ?>" <?= $roleFilter === $value ? 'selected' : '' ?>>
                            <?= esc($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="is_active"><?= esc(lang('App.isActive')) ?></label>
                <select class="form-select" id="is_active" name="is_active">
                    <option value="">Semua</option>
                    <option value="1" <?= $activeFilter === '1' ? 'selected' : '' ?>><?= esc(lang('App.active')) ?></option>
                    <option value="0" <?= $activeFilter === '0' ? 'selected' : '' ?>><?= esc(lang('App.inactive')) ?></option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="agency_id"><?= esc(lang('App.agency')) ?></label>
                <select class="form-select" id="agency_id" name="agency_id">
                    <option value="">Semua</option>
                    <?php foreach ($agencies as $id => $name) : ?>
                        <option value="<?= esc((string) $id, 'attr') ?>" <?= $agencyFilter === (string) $id ? 'selected' : '' ?>>
                            <?= esc($name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1 d-grid">
                <button type="submit" class="btn btn-outline-primary" title="<?= esc(lang('App.filter')) ?>">
                    <i class="bi bi-funnel" aria-hidden="true"></i>
                </button>
            </div>
            <div class="col-md-1 d-grid">
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/pengguna') ?>" title="<?= esc(lang('App.reset')) ?>">
                    <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                </a>
            </div>
        <?= form_close() ?>
    </div>
</div>

<?php if ($items === []) : ?>
    <div class="empty-state card border-0 shadow-sm">
        <i class="bi bi-person-x" aria-hidden="true"></i>
        <p class="mb-0 mt-2">Tidak ada pengguna yang cocok dengan filter.</p>
    </div>
<?php else : ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover table-sipala mb-0">
                <thead>
                <tr>
                    <th scope="col"><?= esc(lang('App.name')) ?></th>
                    <th scope="col"><?= esc(lang('App.role')) ?></th>
                    <th scope="col"><?= esc(lang('App.agency')) ?></th>
                    <th scope="col"><?= esc(lang('App.status')) ?></th>
                    <th scope="col">Login terakhir</th>
                    <th scope="col" class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $user) : ?>
                    <tr>
                        <td>
                            <span class="d-block"><?= esc($user->displayName()) ?></span>
                            <span class="text-body-secondary small"><?= esc((string) $user->email) ?></span>
                        </td>
                        <td><?= role_badge($user->role()) ?></td>
                        <td class="small">
                            <?= esc($user->hasAgency() ? ($agencies[$user->agencyId()] ?? '-') : '-') ?>
                        </td>
                        <td>
                            <?php if ($user->isActive()) : ?>
                                <span class="badge text-bg-success"><?= esc(lang('App.active')) ?></span>
                            <?php else : ?>
                                <span class="badge text-bg-secondary"><?= esc(lang('App.inactive')) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-nowrap"><?= esc(format_tanggal_waktu($user->last_login_at)) ?></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/pengguna/' . (int) $user->id . '/ubah') ?>">
                                <i class="bi bi-pencil" aria-hidden="true"></i>
                                <span class="visually-hidden"><?= esc(lang('App.edit')) ?></span>
                            </a>

                            <?php if ($user->isActive()) : ?>
                                <?= form_open(route_to('admin.users.deactivate', (int) $user->id), [
                                    'class'        => 'd-inline',
                                    'data-confirm' => 'Nonaktifkan akun ini?',
                                ]) ?>
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Nonaktifkan">
                                        <i class="bi bi-pause-circle" aria-hidden="true"></i>
                                    </button>
                                <?= form_close() ?>
                            <?php else : ?>
                                <?= form_open(route_to('admin.users.activate', (int) $user->id), ['class' => 'd-inline']) ?>
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Aktifkan">
                                        <i class="bi bi-play-circle" aria-hidden="true"></i>
                                    </button>
                                <?= form_close() ?>
                            <?php endif; ?>

                            <?= form_open(route_to('admin.users.reset', (int) $user->id), [
                                'class'        => 'd-inline',
                                'data-confirm' => 'Reset kata sandi pengguna ini? Kata sandi baru akan ditampilkan sekali.',
                            ]) ?>
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Reset kata sandi">
                                    <i class="bi bi-key" aria-hidden="true"></i>
                                </button>
                            <?= form_close() ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        <?= $pager->links('default', 'bootstrap') ?>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
