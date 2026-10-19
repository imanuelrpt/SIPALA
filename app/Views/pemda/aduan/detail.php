<?php

use App\Enums\ComplaintStatus;

/**
 * Detail & tindak lanjut aduan oleh instansi (Pemda).
 *
 * @var \App\Entities\Complaint                 $complaint
 * @var list<array<string, mixed>>              $thread
 * @var list<\App\Entities\ComplaintAttachment> $attachments
 * @var list<\App\Entities\ComplaintAttachment> $resolutionEvidence
 * @var list<ComplaintStatus>                   $transitions
 * @var array<string, string>                   $errors
 */
$this->extend('layouts/main');

$errors = $errors ?? [];
?>
<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('pemda/aduan') ?>">
        <i class="bi bi-arrow-left me-1" aria-hidden="true"></i><?= esc(lang('App.incomingComplaints')) ?>
    </a>
    <span class="text-body-secondary small"><?= esc(lang('App.ticketCode')) ?>: <strong><?= esc((string) $complaint->ticket_code) ?></strong></span>
</div>

<?= $this->include('partials/complaint_summary', ['complaint' => $complaint, 'showReporter' => true]) ?>

<div class="row g-4">
    <div class="col-lg-7">
        <?php if ($attachments !== []) : ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <?= $this->include('partials/attachments', [
                        'attachments' => $attachments,
                        'title'       => lang('App.evidence'),
                        'emptyText'   => 'Tidak ada foto bukti dari pelapor.',
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
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h6 mb-3"><i class="bi bi-signpost-split me-1 text-primary" aria-hidden="true"></i><?= esc(lang('App.statusTransition')) ?></h2>

                <?php if ($transitions === []) : ?>
                    <p class="text-body-secondary small mb-0">
                        Aduan berstatus <strong><?= esc($complaint->status()->label()) ?></strong> tidak memiliki transisi lanjutan.
                    </p>
                <?php else : ?>
                    <?= form_open(route_to('pemda.complaints.status', (int) $complaint->id), [
                        'novalidate'       => true,
                        'data-status-form' => true,
                    ]) ?>
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label" for="status">Ubah status menjadi</label>
                            <select class="form-select" id="status" name="status" required>
                                <?php foreach ($transitions as $option) : ?>
                                    <option value="<?= esc($option->value, 'attr') ?>"><?= esc($option->label()) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="note">Catatan tindak lanjut</label>
                            <textarea class="form-control" id="note" name="note" rows="3" maxlength="1000"
                                      placeholder="Wajib diisi bila status diubah menjadi “Ditolak”."></textarea>
                            <div class="form-text">Alasan penolakan wajib dicantumkan dan akan terlihat oleh pelapor.</div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-arrow-repeat me-1" aria-hidden="true"></i><?= esc(lang('App.save')) ?>
                        </button>
                    <?= form_close() ?>
                <?php endif; ?>
            </div>
        </div>

        <?= $this->include('partials/respond_form', [
            'action' => route_to('pemda.complaints.respond', (int) $complaint->id),
            'errors' => $errors,
        ]) ?>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h6 mb-3"><i class="bi bi-clipboard2-check me-1 text-primary" aria-hidden="true"></i><?= esc(lang('App.resolutionEvidence')) ?></h2>

                <?php if ($resolutionEvidence !== []) : ?>
                    <div class="mb-3">
                        <?= $this->include('partials/attachments', [
                            'attachments' => $resolutionEvidence,
                            'title'       => 'Sudah diunggah',
                            'emptyText'   => 'Belum ada bukti penyelesaian.',
                        ]) ?>
                    </div>
                <?php endif; ?>

                <?php if ($complaint->status()->value === 'diproses' || $complaint->status()->value === 'selesai') : ?>
                    <?= form_open_multipart(route_to('pemda.complaints.evidence', (int) $complaint->id), ['novalidate' => true]) ?>
                        <?= csrf_field() ?>
                        <label class="form-label" for="evidence">Unggah foto bukti penyelesaian</label>
                        <input type="file" class="form-control <?= is_invalid($errors, 'evidence') ? 'is-invalid' : '' ?>"
                               id="evidence" name="evidence[]" accept="image/jpeg,image/png,image/webp" multiple
                               data-preview-target="#pratinjau-bukti"
                               data-max-files="<?= esc((string) config(\Config\Sipala::class)->uploadMaxFiles, 'attr') ?>"
                               data-max-bytes="<?= esc((string) (config(\Config\Sipala::class)->uploadMaxSizeKB * 1024), 'attr') ?>">
                        <div class="form-text">
                            Format JPG, PNG, atau WEBP. Maksimal
                            <?= esc((string) config(\Config\Sipala::class)->uploadMaxFiles) ?> berkas per unggahan.
                        </div>
                        <div class="alert alert-danger small mt-2 mb-0 d-none" data-upload-feedback="evidence" role="alert"></div>
                        <div class="upload-preview mt-3" id="pratinjau-bukti"></div>
                        <button type="submit" class="btn btn-outline-primary mt-3">
                            <i class="bi bi-cloud-arrow-up me-1" aria-hidden="true"></i>Unggah Bukti
                        </button>
                    <?= form_close() ?>
                <?php else : ?>
                    <div class="alert alert-info small mb-0" role="note">
                        Bukti penyelesaian dapat diunggah setelah aduan berstatus “Diproses”.
                    </div>
                <?php endif; ?>
            </div>
        </div>
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
