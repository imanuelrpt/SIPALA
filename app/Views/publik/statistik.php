<?php

/**
 * Statistik publik SIPALA.
 *
 * @var array<string, mixed> $overview
 */
$this->extend('layouts/main');

$statusColors = [
    'menunggu'     => '#6c757d',
    'diverifikasi' => '#0dcaf0',
    'diproses'     => '#ffc107',
    'selesai'      => '#198754',
    'ditolak'      => '#dc3545',
];

$statusCounts = $overview['status_counts'] ?? [];
$byCategory   = $overview['by_category'] ?? [];
$byAgency     = $overview['by_agency'] ?? [];
$byMonth      = $overview['by_month'] ?? [];

$statusLabels = [];
$statusValues = [];
$statusPalette = [];

foreach (status_options() as $value => $label) {
    $statusLabels[]  = $label;
    $statusValues[]  = (int) ($statusCounts[$value] ?? 0);
    $statusPalette[] = $statusColors[$value] ?? '#0d3b66';
}

$categoryLabels = array_map(static fn (array $row): string => $row['name'], $byCategory);
$categoryValues = array_map(static fn (array $row): int => $row['total'], $byCategory);
$agencyLabels   = array_map(static fn (array $row): string => $row['name'], $byAgency);
$agencyValues   = array_map(static fn (array $row): int => $row['total'], $byAgency);
$monthLabels    = array_map(static fn (array $row): string => $row['label'], $byMonth);
$monthValues    = array_map(static fn (array $row): int => $row['total'], $byMonth);

$json = static fn (array $data): string => json_encode($data, JSON_UNESCAPED_UNICODE) ?: '[]';
?>
<?= $this->section('content') ?>
<h1 class="h3 mb-1">Statistik Aduan Masyarakat</h1>
<p class="text-body-secondary">
    Data agregat aduan yang masuk melalui SIPALA. Identitas pelapor tidak ditampilkan pada halaman ini.
</p>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100 p-3">
            <span class="stat-icon mb-2"><i class="bi bi-inboxes" aria-hidden="true"></i></span>
            <div class="stat-value"><?= esc(angka_ringkas((int) ($overview['total'] ?? 0))) ?></div>
            <div class="text-body-secondary small">Total aduan</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100 p-3">
            <span class="stat-icon mb-2"><i class="bi bi-check2-circle" aria-hidden="true"></i></span>
            <div class="stat-value"><?= esc(angka_ringkas((int) ($overview['resolved'] ?? 0))) ?></div>
            <div class="text-body-secondary small">Selesai ditangani</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100 p-3">
            <span class="stat-icon mb-2"><i class="bi bi-hourglass-split" aria-hidden="true"></i></span>
            <div class="stat-value"><?= esc(angka_ringkas((int) ($overview['awaiting'] ?? 0))) ?></div>
            <div class="text-body-secondary small">Menunggu verifikasi</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100 p-3">
            <span class="stat-icon mb-2"><i class="bi bi-stopwatch" aria-hidden="true"></i></span>
            <div class="stat-value"><?= esc(angka_ringkas((float) ($overview['average_days'] ?? 0), 1)) ?></div>
            <div class="text-body-secondary small">Rata-rata hari penyelesaian</div>
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
            <div class="card-header bg-white"><h2 class="h6 mb-0">Kategori Aduan Terbanyak</h2></div>
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
