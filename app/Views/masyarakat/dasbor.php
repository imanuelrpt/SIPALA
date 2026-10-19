<?php

/**
 * Dasbor masyarakat.
 *
 * @var array{total: int, per_status: array<string, int>, per_agency: list<array<string, mixed>>, per_category: list<array<string, mixed>>, average_days: float} $summary
 * @var list<\App\Entities\Complaint> $recent
 */
$this->extend('layouts/main');

$perStatus    = $summary['per_status'] ?? [];
$statusCounts = [];

foreach (\App\Enums\ComplaintStatus::values() as $value) {
    $statusCounts[$value] = (int) ($perStatus[$value] ?? 0);
}
?>
<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1"><?= esc(lang('App.dashboard')) ?></h1>
        <p class="text-body-secondary mb-0">
            Halo, <?= esc($currentUser?->displayName() ?? '') ?>. Berikut ringkasan aduan Anda.
        </p>
    </div>
    <a class="btn btn-primary" href="<?= site_url('masyarakat/aduan/baru') ?>">
        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Buat Aduan Baru
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100 p-3">
            <span class="stat-icon mb-2"><i class="bi bi-megaphone" aria-hidden="true"></i></span>
            <div class="stat-value"><?= esc(angka_ringkas((int) ($summary['total'] ?? 0))) ?></div>
            <div class="text-body-secondary small">Total aduan saya</div>
        </div>
    </div>
    <?php foreach (['menunggu' => 'Menunggu', 'diproses' => 'Sedang diproses', 'selesai' => 'Selesai'] as $key => $label) : ?>
        <div class="col-6 col-lg-3">
            <div class="stat-card h-100 p-3">
                <span class="stat-icon mb-2"><i class="bi bi-activity" aria-hidden="true"></i></span>
                <div class="stat-value"><?= esc(angka_ringkas($statusCounts[$key] ?? 0)) ?></div>
                <div class="text-body-secondary small"><?= esc($label) ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h2 class="h6 mb-0">Aduan Terbaru Saya</h2>
        <a class="btn btn-sm btn-outline-primary" href="<?= site_url('masyarakat/aduan') ?>">Lihat semua</a>
    </div>

    <?php if ($recent === []) : ?>
        <div class="empty-state">
            <i class="bi bi-inbox" aria-hidden="true"></i>
            <p class="mb-2 mt-2">Anda belum mengirim aduan.</p>
            <a class="btn btn-primary btn-sm" href="<?= site_url('masyarakat/aduan/baru') ?>">Buat aduan pertama</a>
        </div>
    <?php else : ?>
        <div class="table-responsive">
            <table class="table table-hover table-sipala mb-0">
                <thead>
                <tr>
                    <th scope="col"><?= esc(lang('App.ticketCode')) ?></th>
                    <th scope="col"><?= esc(lang('App.title')) ?></th>
                    <th scope="col"><?= esc(lang('App.status')) ?></th>
                    <th scope="col"><?= esc(lang('App.createdAt')) ?></th>
                    <th scope="col" class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($recent as $complaint) : ?>
                    <tr>
                        <td class="col-ticket"><?= esc((string) $complaint->ticket_code) ?></td>
                        <td>
                            <span class="d-block"><?= esc(kutip_ringkas((string) $complaint->title, 60)) ?></span>
                            <span class="text-body-secondary small"><?= esc((string) ($complaint->category_name ?? '-')) ?></span>
                        </td>
                        <td><?= status_badge($complaint->status()) ?></td>
                        <td class="small text-nowrap"><?= esc(format_tanggal($complaint->created_at)) ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary"
                               href="<?= site_url('masyarakat/aduan/' . (int) $complaint->id) ?>">
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
