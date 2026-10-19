<?php

/**
 * Daftar seluruh aduan (Admin).
 *
 * @var list<\App\Entities\Complaint> $items
 * @var int                           $total
 * @var array<string, mixed>          $filters
 * @var array<int, string>            $agencies
 * @var array<int, string>            $categories
 * @var \CodeIgniter\Pager\Pager      $pager
 */
$this->extend('layouts/main');

$statusFilter   = (string) ($filters['status'] ?? '');
$categoryFilter = (string) ($filters['category_id'] ?? '');
$agencyFilter   = (string) ($filters['agency_id'] ?? '');
$searchFilter   = (string) ($filters['search'] ?? '');
$dateFrom       = (string) ($filters['date_from'] ?? '');
$dateTo         = (string) ($filters['date_to'] ?? '');
?>
<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1"><?= esc(lang('App.allComplaints')) ?></h1>
        <p class="text-body-secondary mb-0">Total <?= esc(angka_ringkas($total)) ?> aduan pada seluruh instansi.</p>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primary" href="<?= site_url('admin/peta') ?>">
            <i class="bi bi-geo-alt me-1" aria-hidden="true"></i><?= esc(lang('App.map')) ?>
        </a>
        <a class="btn btn-primary" href="<?= site_url('admin/laporan') ?>">
            <i class="bi bi-bar-chart me-1" aria-hidden="true"></i><?= esc(lang('App.reports')) ?>
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <?= form_open(site_url('admin/aduan'), ['method' => 'get', 'class' => 'row g-3 align-items-end']) ?>
            <div class="col-md-4">
                <label class="form-label" for="search"><?= esc(lang('App.search')) ?></label>
                <input type="search" class="form-control" id="search" name="search" maxlength="100"
                       value="<?= esc($searchFilter, 'attr') ?>" placeholder="Kode tiket, judul, atau wilayah">
            </div>
            <div class="col-md-4">
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
            <div class="col-md-4">
                <label class="form-label" for="agency_id"><?= esc(lang('App.agency')) ?></label>
                <select class="form-select" id="agency_id" name="agency_id">
                    <option value="">Semua instansi</option>
                    <?php foreach ($agencies as $id => $name) : ?>
                        <option value="<?= esc((string) $id, 'attr') ?>" <?= $agencyFilter === (string) $id ? 'selected' : '' ?>>
                            <?= esc($name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
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
            <div class="col-md-3">
                <label class="form-label" for="date_from"><?= esc(lang('App.dateFrom')) ?></label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?= esc($dateFrom, 'attr') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="date_to"><?= esc(lang('App.dateTo')) ?></label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?= esc($dateTo, 'attr') ?>">
            </div>
            <div class="col-md-1 d-grid">
                <button type="submit" class="btn btn-outline-primary" title="<?= esc(lang('App.filter')) ?>">
                    <i class="bi bi-funnel" aria-hidden="true"></i>
                </button>
            </div>
            <div class="col-md-1 d-grid">
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/aduan') ?>" title="<?= esc(lang('App.reset')) ?>">
                    <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                </a>
            </div>
        <?= form_close() ?>
    </div>
</div>

<?php if ($items === []) : ?>
    <div class="empty-state card border-0 shadow-sm">
        <i class="bi bi-inbox" aria-hidden="true"></i>
        <p class="mb-0 mt-2">Tidak ada aduan yang cocok dengan filter.</p>
    </div>
<?php else : ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover table-sipala mb-0">
                <thead>
                <tr>
                    <th scope="col"><?= esc(lang('App.ticketCode')) ?></th>
                    <th scope="col"><?= esc(lang('App.title')) ?></th>
                    <th scope="col"><?= esc(lang('App.agency')) ?></th>
                    <th scope="col"><?= esc(lang('App.status')) ?></th>
                    <th scope="col"><?= esc(lang('App.createdAt')) ?></th>
                    <th scope="col"><?= esc(lang('App.rating')) ?></th>
                    <th scope="col" class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $complaint) : ?>
                    <tr>
                        <td class="col-ticket"><?= esc((string) $complaint->ticket_code) ?></td>
                        <td>
                            <span class="d-block"><?= esc(kutip_ringkas((string) $complaint->title, 50)) ?></span>
                            <span class="text-body-secondary small"><?= esc((string) ($complaint->category_name ?? '-')) ?></span>
                        </td>
                        <td class="small"><?= esc((string) ($complaint->agency_name ?? '-')) ?></td>
                        <td><?= status_badge($complaint->status()) ?></td>
                        <td class="small text-nowrap"><?= esc(format_tanggal($complaint->created_at)) ?></td>
                        <td class="small"><?= $complaint->hasRating() ? bintang_penilaian((int) $complaint->rating) : '-' ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/aduan/' . (int) $complaint->id) ?>">
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
