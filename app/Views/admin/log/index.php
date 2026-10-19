<?php

/**
 * Log aktivitas / audit trail (Admin).
 *
 * @var list<\App\Entities\ActivityLog> $items
 * @var int                             $total
 * @var array<string, mixed>            $filters
 * @var list<string>                    $actions
 * @var \CodeIgniter\Pager\Pager        $pager
 */
$this->extend('layouts/main');

$actionFilter = (string) ($filters['action'] ?? '');
$searchFilter = (string) ($filters['search'] ?? '');
$userFilter   = (string) ($filters['user_id'] ?? '');
?>
<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1"><?= esc(lang('App.activityLog')) ?></h1>
        <p class="text-body-secondary mb-0">
            Jejak audit <?= esc(angka_ringkas($total)) ?> aktivitas. Data sensitif seperti kata sandi tidak pernah dicatat.
        </p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <?= form_open(site_url('admin/log'), ['method' => 'get', 'class' => 'row g-3 align-items-end']) ?>
            <div class="col-md-5">
                <label class="form-label" for="search"><?= esc(lang('App.search')) ?></label>
                <input type="search" class="form-control" id="search" name="search" maxlength="100"
                       value="<?= esc($searchFilter, 'attr') ?>" placeholder="Aksi, entitas, keterangan, atau IP">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="action"><?= esc(lang('App.status')) ?></label>
                <select class="form-select" id="action" name="action">
                    <option value="">Semua aksi</option>
                    <?php foreach ($actions as $action) : ?>
                        <option value="<?= esc($action, 'attr') ?>" <?= $actionFilter === $action ? 'selected' : '' ?>>
                            <?= esc(label_aksi($action)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label" for="user_id">ID</label>
                <input type="number" class="form-control" id="user_id" name="user_id" min="1"
                       value="<?= esc($userFilter, 'attr') ?>" placeholder="Pengguna">
            </div>
            <div class="col-md-1 d-grid">
                <button type="submit" class="btn btn-outline-primary" title="<?= esc(lang('App.filter')) ?>">
                    <i class="bi bi-funnel" aria-hidden="true"></i>
                </button>
            </div>
            <div class="col-md-1 d-grid">
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/log') ?>" title="<?= esc(lang('App.reset')) ?>">
                    <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                </a>
            </div>
        <?= form_close() ?>
    </div>
</div>

<?php if ($items === []) : ?>
    <div class="empty-state card border-0 shadow-sm">
        <i class="bi bi-clock-history" aria-hidden="true"></i>
        <p class="mb-0 mt-2"><?= esc(lang('App.noData')) ?></p>
    </div>
<?php else : ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover table-sipala mb-0">
                <thead>
                <tr>
                    <th scope="col">Waktu</th>
                    <th scope="col">Aksi</th>
                    <th scope="col">Keterangan</th>
                    <th scope="col">Entitas</th>
                    <th scope="col">Aktor</th>
                    <th scope="col">IP</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $log) : ?>
                    <tr>
                        <td class="small text-nowrap"><?= esc(format_tanggal_waktu($log->created_at)) ?></td>
                        <td>
                            <span class="badge text-bg-light border"><?= esc(label_aksi((string) $log->action)) ?></span>
                        </td>
                        <td class="small"><?= esc(kutip_ringkas((string) ($log->description ?? '-'), 80)) ?></td>
                        <td class="small text-body-secondary">
                            <?= esc((string) ($log->entity ?? '-')) ?>
                            <?php if ($log->entity_id !== null) : ?>
                                <span class="text-body-tertiary">#<?= esc((string) $log->entity_id) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="small">
                            <?php if ($log->user_id === null) : ?>
                                <span class="text-body-secondary">Sistem</span>
                            <?php else : ?>
                                #<?= esc((string) $log->user_id) ?>
                            <?php endif; ?>
                        </td>
                        <td class="small text-body-secondary"><?= esc((string) ($log->ip_address ?? '-')) ?></td>
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
