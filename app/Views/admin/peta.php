<?php

/**
 * Peta sebaran seluruh aduan (Admin).
 *
 * @var list<array<string, mixed>> $points
 * @var bool                       $showFilters
 */
$this->extend('layouts/main');
?>
<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1"><?= esc(lang('App.map')) ?></h1>
        <p class="text-body-secondary mb-0">Sebaran seluruh aduan yang masuk ke sistem.</p>
    </div>
    <a class="btn btn-outline-primary" href="<?= site_url('admin/aduan') ?>">
        <i class="bi bi-list-ul me-1" aria-hidden="true"></i><?= esc(lang('App.allComplaints')) ?>
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?= $this->include('partials/map_view', [
            'points'      => $points,
            'showFilters' => $showFilters ?? true,
        ]) ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/leaflet.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/MarkerCluster.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/MarkerCluster.Default.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/leaflet.js') ?>" defer></script>
<script src="<?= base_url('assets/js/leaflet.markercluster.js') ?>" defer></script>
<script src="<?= base_url('assets/js/map-view.js') ?>" defer></script>
<?= $this->endSection() ?>
