<?php

/**
 * Daftar aduan instansi (Pemda).
 *
 * @var list<\App\Entities\Complaint> $items
 * @var int                           $total
 * @var array<string, mixed>          $filters
 * @var array<int, string>            $categories
 * @var \CodeIgniter\Pager\Pager      $pager
 */
$this->extend('layouts/main');

$statusFilter   = (string) ($filters['status'] ?? '');
$categoryFilter = (string) ($filters['category_id'] ?? '');
$searchFilter   = (string) ($filters['search'] ?? '');
$dateFrom       = (string) ($filters['date_from'] ?? '');
$dateTo         = (string) ($filters['date_to'] ?? '');
?>
<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1"><?= esc(lang('App.incomingComplaints')) ?></h1>
        <p class="text-body-secondary mb-0">Total <?= esc(angka_ringkas($total)) ?> aduan untuk instansi Anda.</p>
    </div>
    <a class="btn btn-outline-primary" href="<?= site_url('pemda/peta') ?>">
        <i class="bi bi-geo-alt me-1" aria-hidden="true"></i><?= esc(lang('App.map')) ?>
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <?= form_open(site_url('pemda/aduan'), ['method' => 'get', 'class' => 'row g-3 align-items-end']) ?>
            <div class="col-md-4">
                <label class="form-label" for="search"><?= esc(lang('App.search')) ?></label>
                <input type="search" class="form-control" id="search" name="search" maxlength="100"
                       value="<?= esc($searchFilter, 'attr') ?>" placeholder="Kode tiket, judul, atau wilayah">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="status"><?= esc(lang('App.status')) ?></label>
                <select class="form-select" id="status" name="status">
                    <option value="">Semua status</option>
                    <?php foreach (status_options() as $value => $label) : ?>
                        <option value="<?= esc($value, 'attr') ?>" <?= $statusFilter === $value ? 'selected' : '' ?>>
                            <?= esc($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="category_id"><?= esc(lang('App.category')) ?></label>
                <select class="form-select" id="category_id" name="category_id">
                    <option value="">Semua kategori</option>
                    <?php foreach ($categories as $id => $name) : ?>
                        <option value="<?= esc((string) $id, 'attr') ?>" <?= $categoryFilter === (string) $id ? 'selected' : '' ?>>
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
            <div class="col-md-2">
                <label class="form-label" for="date_from"><?= esc(lang('App.dateFrom')) ?></label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?= esc($dateFrom, 'attr') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="date_to"><?= esc(lang('App.dateTo')) ?></label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?= esc($dateTo, 'attr') ?>">
            </div>
            <div class="col-md-2 d-grid">
                <a class="btn btn-outline-secondary" href="<?= site_url('pemda/aduan') ?>"><?= esc(lang('App.reset')) ?></a>
            </div>
        <?= form_close() ?>
    </div>
</div>

<?php if ($items === []) : ?>
    <div class="empty-state card border-0 shadow-sm">
        <i class="bi bi-inbox" aria-hidden="true"></i>
        <p class="mb-2 mt-2">Tidak ada aduan yang cocok dengan filter Anda.</p>
        <a class="btn btn-outline-primary btn-sm" href="<?= site_url('pemda/aduan') ?>"><?= esc(lang('App.reset')) ?></a>
    </div>
<?php else : ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover table-sipala mb-0">
                <thead>
                <tr>
                    <th scope="col"><?= esc(lang('App.ticketCode')) ?></th>
                    <th scope="col"><?= esc(lang('App.title')) ?></th>
                    <th scope="col"><?= esc(lang('App.reporter')) ?></th>
                    <th scope="col"><?= esc(lang('App.category')) ?></th>
                    <th scope="col"><?= esc(lang('App.status')) ?></th>
                    <th scope="col"><?= esc(lang('App.createdAt')) ?></th>
                    <th scope="col" class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $complaint) : ?>
                    <tr>
                        <td class="col-ticket"><?= esc((string) $complaint->ticket_code) ?></td>
                        <td><?= esc(kutip_ringkas((string) $complaint->title, 50)) ?></td>
                        <td class="small">
                            <?= esc(nama_pelapor((string) ($complaint->reporter_name ?? ''), $complaint->hideIdentity(), true)) ?>
                        </td>
                        <td class="small"><?= esc((string) ($complaint->category_name ?? '-')) ?></td>
                        <td><?= status_badge($complaint->status()) ?></td>
                        <td class="small text-nowrap"><?= esc(format_tanggal($complaint->created_at)) ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="<?= site_url('pemda/aduan/' . (int) $complaint->id) ?>">
                                <?= esc(lang('App.detail')) ?>
                            </a>
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
