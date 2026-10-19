<?php

/**
 * Laporan & rekap aduan (Admin).
 *
 * @var array<string, mixed> $filters
 * @var array<string, mixed> $summary
 * @var list<array<string, mixed>> $monthly
 * @var array<int, string>   $agencies
 * @var array<int, string>   $categories
 */
$this->extend('layouts/main');

$statusColors = [
    'menunggu'     => '#6c757d',
    'diverifikasi' => '#0dcaf0',
    'diproses'     => '#ffc107',
    'selesai'      => '#198754',
    'ditolak'      => '#dc3545',
];

$statusFilter   = (string) ($filters['status'] ?? '');
$categoryFilter = (string) ($filters['category_id'] ?? '');
$agencyFilter   = (string) ($filters['agency_id'] ?? '');
$searchFilter   = (string) ($filters['search'] ?? '');
$dateFrom       = (string) ($filters['date_from'] ?? '');
$dateTo         = (string) ($filters['date_to'] ?? '');

$perStatus = $summary['per_status'] ?? [];

$statusLabels  = [];
$statusValues  = [];
$statusPalette = [];

foreach (status_options() as $value => $label) {
    $statusLabels[]  = $label;
    $statusValues[]  = (int) ($perStatus[$value] ?? 0);
    $statusPalette[] = $statusColors[$value] ?? '#0d3b66';
}

$byCategory = $summary['per_category'] ?? [];
$byAgency   = $summary['per_agency'] ?? [];

$categoryLabels = array_map(static fn (array $row): string => $row['name'], $byCategory);
$categoryValues = array_map(static fn (array $row): int => $row['total'], $byCategory);
$agencyLabels   = array_map(static fn (array $row): string => $row['name'], $byAgency);
$agencyValues   = array_map(static fn (array $row): int => $row['total'], $byAgency);
$monthLabels    = array_map(static fn (array $row): string => $row['label'], $monthly);
$monthValues    = array_map(static fn (array $row): int => $row['total'], $monthly);

$json        = static fn (array $data): string => json_encode($data, JSON_UNESCAPED_UNICODE) ?: '[]';
$exportQuery = array_filter($filters, static fn ($value): bool => $value !== '' && $value !== null);
$exportUrl   = site_url('admin/laporan/ekspor') . ($exportQuery === [] ? '' : '?' . http_build_query($exportQuery));
?>
<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1"><?= esc(lang('App.reports')) ?></h1>
        <p class="text-body-secondary mb-0">Rekapitulasi aduan sesuai filter yang dipilih.</p>
    </div>
    <a class="btn btn-primary" href="<?= esc($exportUrl, 'attr') ?>">
        <i class="bi bi-download me-1" aria-hidden="true"></i><?= esc(lang('App.export')) ?>
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <?= form_open(site_url('admin/laporan'), ['method' => 'get', 'class' => 'row g-3 align-items-end']) ?>
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
                <a class="btn btn-outline-secondary" href="<?= site_url('admin/laporan') ?>" title="<?= esc(lang('App.reset')) ?>">
                    <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                </a>
            </div>
        <?= form_close() ?>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100 p-3">
            <span class="stat-icon mb-2"><i class="bi bi-inboxes" aria-hidden="true"></i></span>
            <div class="stat-value"><?= esc(angka_ringkas((int) ($summary['total'] ?? 0))) ?></div>
            <div class="text-body-secondary small">Aduan sesuai filter</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100 p-3">
            <span class="stat-icon mb-2"><i class="bi bi-check2-circle" aria-hidden="true"></i></span>
            <div class="stat-value"><?= esc(angka_ringkas((int) ($perStatus['selesai'] ?? 0))) ?></div>
            <div class="text-body-secondary small">Selesai</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100 p-3">
            <span class="stat-icon mb-2"><i class="bi bi-hourglass-split" aria-hidden="true"></i></span>
            <div class="stat-value"><?= esc(angka_ringkas((int) ($perStatus['menunggu'] ?? 0))) ?></div>
            <div class="text-body-secondary small">Menunggu verifikasi</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100 p-3">
            <span class="stat-icon mb-2"><i class="bi bi-stopwatch" aria-hidden="true"></i></span>
            <div class="stat-value"><?= esc(angka_ringkas((float) ($summary['average_days'] ?? 0), 1)) ?></div>
            <div class="text-body-secondary small">Rata-rata hari selesai</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white"><h2 class="h6 mb-0">Komposisi Status</h2></div>
            <div class="card-body">
                <div style="height:18rem">
                    <canvas data-chart="doughnut" data-label="Aduan"
                            data-labels="<?= esc($json($statusLabels), 'attr') ?>"
                            data-values="<?= esc($json($statusValues), 'attr') ?>"
                            data-colors="<?= esc($json($statusPalette), 'attr') ?>"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white"><h2 class="h6 mb-0">Aduan per Bulan (12 bulan terakhir)</h2></div>
            <div class="card-body">
                <div style="height:18rem">
                    <canvas data-chart="line" data-label="Aduan masuk"
                            data-labels="<?= esc($json($monthLabels), 'attr') ?>"
                            data-values="<?= esc($json($monthValues), 'attr') ?>"
                            data-colors="<?= esc($json(array_fill(0, max(1, count($monthLabels)), '#0d3b66')), 'attr') ?>"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white"><h2 class="h6 mb-0">Kategori Terbanyak</h2></div>
            <div class="card-body">
                <?php if ($categoryLabels === []) : ?>
                    <p class="text-body-secondary small mb-0"><?= esc(lang('App.noData')) ?></p>
                <?php else : ?>
                    <div style="height:20rem">
                        <canvas data-chart="bar" data-label="Aduan"
                                data-labels="<?= esc($json($categoryLabels), 'attr') ?>"
                                data-values="<?= esc($json($categoryValues), 'attr') ?>"
                                data-colors="<?= esc($json(array_fill(0, count($categoryLabels), '#1d7874')), 'attr') ?>"></canvas>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white"><h2 class="h6 mb-0">Aduan per Instansi</h2></div>
            <div class="card-body">
                <?php if ($agencyLabels === []) : ?>
                    <p class="text-body-secondary small mb-0"><?= esc(lang('App.noData')) ?></p>
                <?php else : ?>
                    <div style="height:20rem">
                        <canvas data-chart="bar" data-label="Aduan"
                                data-labels="<?= esc($json($agencyLabels), 'attr') ?>"
                                data-values="<?= esc($json($agencyValues), 'attr') ?>"
                                data-colors="<?= esc($json(array_fill(0, count($agencyLabels), '#0d3b66')), 'attr') ?>"></canvas>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/chart.umd.min.js') ?>" defer></script>
<script src="<?= base_url('assets/js/dashboard-charts.js') ?>" defer></script>
<?= $this->endSection() ?>
