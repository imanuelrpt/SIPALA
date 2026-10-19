<?php

/**
 * Dasbor admin.
 *
 * @var array<string, mixed>        $overview
 * @var array{total: int, active: int, per_role: array<string, int>} $userStats
 * @var list<\App\Entities\Agency>  $agencies
 * @var int                         $categoryCnt
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
$byMonth      = $overview['by_month'] ?? [];
$byAgency     = $overview['by_agency'] ?? [];
$latest       = $overview['latest'] ?? [];
$perRole      = $userStats['per_role'] ?? [];

$statusLabels  = [];
$statusValues  = [];
$statusPalette = [];

foreach (status_options() as $value => $label) {
    $statusLabels[]  = $label;
    $statusValues[]  = (int) ($statusCounts[$value] ?? 0);
    $statusPalette[] = $statusColors[$value] ?? '#0d3b66';
}

$monthLabels  = array_map(static fn (array $row): string => $row['label'], $byMonth);
$monthValues  = array_map(static fn (array $row): int => $row['total'], $byMonth);
$agencyLabels = array_map(static fn (array $row): string => $row['name'], $byAgency);
$agencyValues = array_map(static fn (array $row): int => $row['total'], $byAgency);

$json = static fn (array $data): string => json_encode($data, JSON_UNESCAPED_UNICODE) ?: '[]';
?>
<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1"><?= esc(lang('App.dashboard')) ?></h1>
        <p class="text-body-secondary mb-0">Ringkasan menyeluruh sistem pengaduan.</p>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primary" href="<?= site_url('admin/laporan') ?>">
            <i class="bi bi-bar-chart me-1" aria-hidden="true"></i><?= esc(lang('App.reports')) ?>
        </a>
        <a class="btn btn-primary" href="<?= site_url('admin/laporan/ekspor') ?>">
            <i class="bi bi-download me-1" aria-hidden="true"></i><?= esc(lang('App.export')) ?>
        </a>
    </div>
</div>

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
            <div class="text-body-secondary small">Aduan selesai</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100 p-3">
            <span class="stat-icon mb-2"><i class="bi bi-people" aria-hidden="true"></i></span>
            <div class="stat-value"><?= esc(angka_ringkas((int) ($userStats['total'] ?? 0))) ?></div>
            <div class="text-body-secondary small">Total akun (<?= esc(angka_ringkas((int) ($userStats['active'] ?? 0))) ?> aktif)</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100 p-3">
            <span class="stat-icon mb-2"><i class="bi bi-stopwatch" aria-hidden="true"></i></span>
            <div class="stat-value"><?= esc(angka_ringkas((float) ($overview['average_days'] ?? 0), 1)) ?></div>
            <div class="text-body-secondary small">Rata-rata hari selesai</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
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
</div>

<div class="row g-3">
    <div class="col-lg-7">
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
                                data-colors="<?= esc($json(array_fill(0, count($agencyLabels), '#1d7874')), 'attr') ?>"></canvas>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h2 class="h6 mb-0">Master Data</h2>
                <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/pengguna') ?>">Kelola</a>
            </div>
            <div class="card-body">
                <ul class="list-unstyled small mb-4">
                    <?php foreach ($perRole as $role => $count) : ?>
                        <li class="d-flex justify-content-between border-bottom py-2">
                            <span><?= esc(label_peran((string) $role)) ?></span>
                            <strong><?= esc(angka_ringkas((int) $count)) ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <dl class="row small mb-0">
                    <dt class="col-7">Instansi terdaftar</dt>
                    <dd class="col-5 text-end"><?= esc(angka_ringkas(count($agencies))) ?></dd>
                    <dt class="col-7">Kategori aduan</dt>
                    <dd class="col-5 text-end"><?= esc(angka_ringkas((int) $categoryCnt)) ?></dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h2 class="h6 mb-0">Aduan Terbaru</h2>
        <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/aduan') ?>">Lihat semua</a>
    </div>
    <?php if ($latest === []) : ?>
        <div class="empty-state">
            <i class="bi bi-inbox" aria-hidden="true"></i>
            <p class="mb-0 mt-2"><?= esc(lang('App.noData')) ?></p>
        </div>
    <?php else : ?>
        <div class="table-responsive">
            <table class="table table-hover table-sipala mb-0">
                <thead>
                <tr>
                    <th scope="col"><?= esc(lang('App.ticketCode')) ?></th>
                    <th scope="col"><?= esc(lang('App.title')) ?></th>
                    <th scope="col"><?= esc(lang('App.agency')) ?></th>
                    <th scope="col"><?= esc(lang('App.status')) ?></th>
                    <th scope="col" class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($latest as $complaint) : ?>
                    <tr>
                        <td class="col-ticket"><?= esc((string) $complaint->ticket_code) ?></td>
                        <td><?= esc(kutip_ringkas((string) $complaint->title, 55)) ?></td>
                        <td class="small"><?= esc((string) ($complaint->agency_name ?? '-')) ?></td>
                        <td><?= status_badge($complaint->status()) ?></td>
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
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/chart.umd.min.js') ?>" defer></script>
<script src="<?= base_url('assets/js/dashboard-charts.js') ?>" defer></script>
<?= $this->endSection() ?>
