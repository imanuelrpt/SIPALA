<?php

use App\Enums\ComplaintStatus;

/**
 * Ringkasan aduan (dipakai halaman detail masyarakat, Pemda, dan Admin).
 *
 * @var \App\Entities\Complaint $complaint
 * @var bool                    $showReporter Tampilkan nama pelapor (hanya untuk pengguna berwenang)
 */
$complaint    = $complaint ?? null;
$showReporter = $showReporter ?? false;

if ($complaint === null) {
    return;
}

$status = $complaint->status();
?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
            <div>
                <span class="badge text-bg-light border col-ticket mb-2"><?= esc((string) $complaint->ticket_code) ?></span>
                <h1 class="h4 mb-1"><?= esc((string) $complaint->title) ?></h1>
                <p class="text-body-secondary small mb-0">
                    <?= esc((string) ($complaint->category_name ?? '-')) ?>
                    &middot; <?= esc((string) ($complaint->agency_name ?? '-')) ?>
                </p>
            </div>
            <div class="text-end">
                <?= status_badge($status) ?>
                <?php if ($complaint->hasRating()) : ?>
                    <div class="mt-2"><?= bintang_penilaian((int) $complaint->rating) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <dl class="row small mb-0">
            <dt class="col-sm-4 col-lg-3"><?= esc(lang('App.createdAt')) ?></dt>
            <dd class="col-sm-8 col-lg-9"><?= esc(format_tanggal_waktu($complaint->created_at)) ?></dd>

            <dt class="col-sm-4 col-lg-3"><?= esc(lang('App.incidentDate')) ?></dt>
            <dd class="col-sm-8 col-lg-9"><?= esc(format_tanggal($complaint->incident_date)) ?></dd>

            <?php if ($showReporter) : ?>
                <dt class="col-sm-4 col-lg-3"><?= esc(lang('App.reporter')) ?></dt>
                <dd class="col-sm-8 col-lg-9">
                    <?= esc(nama_pelapor((string) ($complaint->reporter_name ?? ''), $complaint->hideIdentity(), true)) ?>
                    <?php if ($complaint->hideIdentity()) : ?>
                        <span class="badge text-bg-secondary ms-1">Disembunyikan di tampilan publik</span>
                    <?php endif; ?>
                </dd>
            <?php endif; ?>

            <dt class="col-sm-4 col-lg-3"><?= esc(lang('App.location')) ?></dt>
            <dd class="col-sm-8 col-lg-9">
                <?= esc($complaint->fullAddress()) ?>
                <div class="text-body-secondary">
                    <?= esc($complaint->coordinatePair()) ?>
                    <a class="ms-2" href="<?= esc(google_maps_url((float) $complaint->latitude, (float) $complaint->longitude), 'attr') ?>"
                       target="_blank" rel="noopener"><?= esc(lang('App.openInGoogleMaps')) ?></a>
                </div>
            </dd>

            <?php if ($status->isFinal() && $complaint->resolutionDays() !== null) : ?>
                <dt class="col-sm-4 col-lg-3">Lama penyelesaian</dt>
                <dd class="col-sm-8 col-lg-9"><?= esc($complaint->resolutionDays()) ?> hari</dd>
            <?php endif; ?>
        </dl>

        <hr>

        <h2 class="h6"><?= esc(lang('App.description')) ?></h2>
        <p class="text-pre-wrap mb-0"><?= esc((string) $complaint->description) ?></p>

        <?php if ($status === ComplaintStatus::Ditolak && ! empty($complaint->rejected_reason)) : ?>
            <div class="alert alert-danger mt-3 mb-0" role="alert">
                <strong><?= esc(lang('App.rejectedReason')) ?>:</strong>
                <?= esc((string) $complaint->rejected_reason) ?>
            </div>
        <?php endif; ?>

        <?php if ($complaint->hasRating() && ! empty($complaint->rating_comment)) : ?>
            <div class="alert alert-success mt-3 mb-0" role="note">
                <strong><?= esc(lang('App.comment')) ?>:</strong>
                <?= esc((string) $complaint->rating_comment) ?>
            </div>
        <?php endif; ?>
    </div>
</div>
