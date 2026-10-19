<?php

/**
 * Detail & koreksi aduan (Admin).
 *
 * @var \App\Entities\Complaint                 $complaint
 * @var list<array<string, mixed>>              $thread
 * @var list<\App\Entities\ComplaintAttachment> $attachments
 * @var list<\App\Entities\ComplaintAttachment> $resolutionEvidence
 * @var array<string, string>                   $statusOptions
 * @var array<int, string>                      $agencies
 * @var array<string, string>                   $errors
 */
$this->extend('layouts/main');

$errors = $errors ?? [];
?>
<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('admin/aduan') ?>">
        <i class="bi bi-arrow-left me-1" aria-hidden="true"></i><?= esc(lang('App.allComplaints')) ?>
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
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h6 mb-3"><i class="bi bi-signpost-split me-1 text-primary" aria-hidden="true"></i><?= esc(lang('App.statusTransition')) ?></h2>
                <div class="alert alert-secondary small" role="note">
                    Admin dapat mengoreksi status ke nilai mana pun. Perubahan dicatat pada linimasa.
                </div>
                <?= form_open(route_to('admin.complaints.status', (int) $complaint->id), ['novalidate' => true]) ?>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="status">Status baru</label>
                        <select class="form-select" id="status" name="status" required>
                            <?php foreach ($statusOptions as $value => $label) : ?>
                                <option value="<?= esc($value, 'attr') ?>" <?= $complaint->status()->value === $value ? 'selected' : '' ?>>
                                    <?= esc($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="note">Catatan</label>
                        <textarea class="form-control" id="note" name="note" rows="3" maxlength="1000"
                                  placeholder="Wajib diisi bila status diubah menjadi “Ditolak”."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-arrow-repeat me-1" aria-hidden="true"></i><?= esc(lang('App.save')) ?>
                    </button>
                <?= form_close() ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h6 mb-3"><i class="bi bi-signpost-2 me-1 text-primary" aria-hidden="true"></i>Disposisi</h2>
                <p class="small text-body-secondary">
                    Saat ini ditangani: <strong><?= esc((string) ($complaint->agency_name ?? '-')) ?></strong>.
                </p>
                <?= form_open(route_to('admin.complaints.dispatch', (int) $complaint->id), ['novalidate' => true]) ?>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="agency_id">Instansi tujuan <span class="text-danger">*</span></label>
                        <select class="form-select <?= is_invalid($errors, 'agency_id') ? 'is-invalid' : '' ?>" id="agency_id" name="agency_id" required>
                            <option value="">Pilih instansi…</option>
                            <?php foreach ($agencies as $id => $name) : ?>
                                <option value="<?= esc((string) $id, 'attr') ?>" <?= (int) $complaint->agency_id === (int) $id ? 'disabled' : '' ?>>
                                    <?= esc($name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (is_invalid($errors, 'agency_id')) : ?>
                            <div class="invalid-feedback"><?= esc(field_error($errors, 'agency_id')) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="dispatch_note">Catatan disposisi</label>
                        <textarea class="form-control" id="dispatch_note" name="note" rows="2" maxlength="1000"
                                  placeholder="Alasan pemindahan tanggung jawab (opsional)."></textarea>
                    </div>
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-send me-1" aria-hidden="true"></i>Disposisikan
                    </button>
                <?= form_close() ?>
            </div>
        </div>

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

        <?= $this->include('partials/respond_form', [
            'action' => route_to('admin.complaints.respond', (int) $complaint->id),
            'errors' => $errors,
        ]) ?>
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
