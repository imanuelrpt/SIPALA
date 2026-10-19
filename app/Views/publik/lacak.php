<?php

/**
 * Pelacakan aduan publik berdasarkan kode tiket.
 *
 * @var array{complaint: \App\Entities\Complaint, thread: list<array<string, mixed>>}|null $result
 * @var array<string, string>|null                                                          $errors
 */
$this->extend('layouts/main');

$errors = $errors ?? [];
?>
<?= $this->section('content') ?>
<h1 class="h3 mb-1"><?= esc(lang('App.tracking')) ?></h1>
<p class="text-body-secondary mb-4">
    Masukkan kode tiket yang Anda terima saat mengirim aduan (contoh: <code>SIP-20260119-AB12CD</code>).
</p>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <?= form_open(route_to('tracking.lookup'), ['novalidate' => true]) ?>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="ticket_code"><?= esc(lang('App.ticketCode')) ?></label>
                        <input type="text" class="form-control <?= is_invalid($errors, 'ticket_code') ? 'is-invalid' : '' ?>"
                               id="ticket_code" name="ticket_code" required maxlength="32"
                               value="<?= esc(old('ticket_code') ?? '', 'attr') ?>"
                               placeholder="SIP-20260119-AB12CD" autocomplete="off">
                        <?php if (is_invalid($errors, 'ticket_code')) : ?>
                            <div class="invalid-feedback"><?= esc(field_error($errors, 'ticket_code')) ?></div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1" aria-hidden="true"></i><?= esc(lang('App.search')) ?>
                    </button>
                <?= form_close() ?>

                <hr>
                <p class="small text-body-secondary mb-0">
                    Pemilik akun dapat melihat seluruh aduannya dengan masuk ke aplikasi melalui menu
                    <a href="<?= site_url('masuk') ?>">Masuk</a>.
                </p>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <?php if ($result === null) : ?>
            <div class="empty-state card border-0 shadow-sm">
                <i class="bi bi-clipboard2-check" aria-hidden="true"></i>
                <p class="mb-0 mt-2">Hasil pelacakan akan tampil di sini.</p>
            </div>
        <?php else : ?>
            <?php
            /** @var \App\Entities\Complaint $complaint */
            $complaint = $result['complaint'];
            ?>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                        <div>
                            <span class="badge text-bg-light border col-ticket mb-2"><?= esc((string) $complaint->ticket_code) ?></span>
                            <h2 class="h5 mb-1"><?= esc((string) $complaint->title) ?></h2>
                            <p class="text-body-secondary small mb-0">
                                <?= esc((string) ($complaint->category_name ?? '-')) ?>
                                &middot; <?= esc((string) ($complaint->agency_name ?? '-')) ?>
                            </p>
                        </div>
                        <?= status_badge($complaint->status()) ?>
                    </div>

                    <dl class="row small mb-3">
                        <dt class="col-sm-4 col-md-3"><?= esc(lang('App.createdAt')) ?></dt>
                        <dd class="col-sm-8 col-md-9"><?= esc(format_tanggal_waktu($complaint->created_at)) ?></dd>

                        <dt class="col-sm-4 col-md-3">Tanggal kejadian</dt>
                        <dd class="col-sm-8 col-md-9"><?= esc(format_tanggal($complaint->incident_date)) ?></dd>

                        <dt class="col-sm-4 col-md-3">Wilayah</dt>
                        <dd class="col-sm-8 col-md-9">
                            <?= esc(trim(((string) ($complaint->district ?? '')) . ', ' . ((string) ($complaint->city ?? '')), ', ') ?: '-') ?>
                        </dd>

                        <?php if ($complaint->status()->value === 'ditolak' && ! empty($complaint->rejected_reason)) : ?>
                            <dt class="col-sm-4 col-md-3"><?= esc(lang('App.rejectedReason')) ?></dt>
                            <dd class="col-sm-8 col-md-9 text-danger"><?= esc((string) $complaint->rejected_reason) ?></dd>
                        <?php endif; ?>
                    </dl>

                    <h3 class="h6"><?= esc(lang('App.description')) ?></h3>
                    <p class="text-pre-wrap mb-0"><?= esc((string) $complaint->description) ?></p>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h2 class="h6 mb-0">Riwayat &amp; Tanggapan</h2></div>
                <div class="card-body">
                    <?= $this->include('partials/timeline', ['thread' => $result['thread']]) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
