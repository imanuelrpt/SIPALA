<?php

/**
 * Detail aduan milik masyarakat.
 *
 * @var \App\Entities\Complaint                      $complaint
 * @var list<array<string, mixed>>                   $thread
 * @var list<\App\Entities\ComplaintAttachment>      $attachments
 * @var list<\App\Entities\ComplaintAttachment>      $resolutionEvidence
 * @var bool                                         $canCancel
 * @var bool                                         $canRate
 * @var array<string, string>                        $errors
 */
$this->extend('layouts/main');

$errors = $errors ?? [];
?>
<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('masyarakat/aduan') ?>">
        <i class="bi bi-arrow-left me-1" aria-hidden="true"></i><?= esc(lang('App.myComplaints')) ?>
    </a>
    <span class="text-body-secondary small"><?= esc(lang('App.ticketCode')) ?>: <strong><?= esc((string) $complaint->ticket_code) ?></strong></span>
</div>

<?= $this->include('partials/complaint_summary', ['complaint' => $complaint]) ?>

<div class="row g-4">
    <div class="col-lg-7">
        <?php if ($attachments !== []) : ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <?= $this->include('partials/attachments', [
                        'attachments' => $attachments,
                        'title'       => lang('App.evidence'),
                        'emptyText'   => 'Tidak ada foto bukti.',
                    ]) ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h2 class="h6 mb-0">Riwayat &amp; Tanggapan</h2></div>
            <div class="card-body">
                <?= $this->include('partials/timeline', ['thread' => $thread]) ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h6 mb-3"><i class="bi bi-geo-alt me-1 text-primary" aria-hidden="true"></i><?= esc(lang('App.location')) ?></h2>
                <?= $this->include('partials/mini_map', [
                    'lat'  => (float) $complaint->latitude,
                    'lng'  => (float) $complaint->longitude,
                    'zoom' => 16,
                ]) ?>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <?php if ($resolutionEvidence !== []) : ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <?= $this->include('partials/attachments', [
                        'attachments' => $resolutionEvidence,
                        'title'       => lang('App.resolutionEvidence'),
                        'emptyText'   => 'Belum ada bukti penyelesaian.',
                    ]) ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($complaint->status()->value !== 'selesai' && $complaint->status()->value !== 'ditolak') : ?>
            <?= $this->include('partials/respond_form', [
                'action' => route_to('masyarakat.complaints.respond', (int) $complaint->id),
                'errors' => $errors,
            ]) ?>
        <?php endif; ?>

        <?php if ($canRate) : ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h6 mb-3"><i class="bi bi-star me-1 text-warning" aria-hidden="true"></i>Beri Penilaian</h2>
                    <?= form_open(route_to('masyarakat.complaints.rate', (int) $complaint->id), ['novalidate' => true]) ?>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label" for="rating"><?= esc(lang('App.rating')) ?></label>
                            <select class="form-select <?= is_invalid($errors, 'rating') ? 'is-invalid' : '' ?>" id="rating" name="rating" required>
                                <option value="">Pilih…</option>
                                <?php for ($i = 1; $i <= 5; $i++) : ?>
                                    <option value="<?= esc((string) $i, 'attr') ?>"><?= esc(str_repeat('★', $i) . str_repeat('☆', 5 - $i) . ' (' . $i . ')') ?></option>
                                <?php endfor; ?>
                            </select>
                            <?php if (is_invalid($errors, 'rating')) : ?>
                                <div class="invalid-feedback"><?= esc(field_error($errors, 'rating')) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="rating_comment"><?= esc(lang('App.comment')) ?></label>
                            <textarea class="form-control" id="rating_comment" name="rating_comment" rows="3" maxlength="1000"><?= esc(old('rating_comment') ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2 me-1" aria-hidden="true"></i><?= esc(lang('App.save')) ?>
                        </button>
                    <?= form_close() ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($canCancel) : ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 mb-2">Batalkan Aduan</h2>
                    <p class="small text-body-secondary">
                        Aduan hanya dapat dibatalkan selama belum diverifikasi oleh instansi.
                    </p>
                    <?= form_open(route_to('masyarakat.complaints.cancel', (int) $complaint->id), [
                        'data-confirm' => 'Batalkan aduan ini? Tindakan ini tidak dapat dibatalkan.',
                    ]) ?>
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-x-circle me-1" aria-hidden="true"></i>Batalkan Aduan
                        </button>
                    <?= form_close() ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/leaflet.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/leaflet.js') ?>" defer></script>
<script src="<?= base_url('assets/js/mini-map.js') ?>" defer></script>
<?= $this->endSection() ?>
