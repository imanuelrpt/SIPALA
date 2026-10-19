<?php

/**
 * Peta sebaran aduan (Leaflet + MarkerCluster).
 *
 * @var list<array<string, mixed>> $points
 * @var float|int|string           $lat
 * @var float|int|string           $lng
 * @var float|int|string           $zoom
 * @var bool                       $showFilters
 * @var string                     $heightClass
 */
$points      = $points ?? [];
$lat         = $lat ?? -6.9175;
$lng         = $lng ?? 107.6191;
$zoom        = $zoom ?? 12;
$showFilters = $showFilters ?? true;
$heightClass = $heightClass ?? 'map-canvas map-canvas-lg';

/** @var array<string, string> $statusOptions */
$statusOptions = status_options();
?>
<div class="row g-4">
    <?php if ($showFilters) : ?>
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 mb-3"><i class="bi bi-funnel me-1 text-primary" aria-hidden="true"></i>Filter Status</h2>
                    <?php foreach ($statusOptions as $value => $label) : ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="<?= esc($value, 'attr') ?>"
                                   id="filter-status-<?= esc($value, 'attr') ?>" data-map-filter checked>
                            <label class="form-check-label" for="filter-status-<?= esc($value, 'attr') ?>">
                                <?= esc($label) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                    <hr>
                    <p class="small text-body-secondary mb-0">
                        Total titik ditampilkan: <strong><?= esc(angka_ringkas(count($points))) ?></strong>.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-9">
    <?php else : ?>
        <div class="col-12">
    <?php endif; ?>
            <div id="peta-sebaran" class="<?= esc($heightClass, 'attr') ?>"
                 data-lat="<?= esc((string) $lat, 'attr') ?>"
                 data-lng="<?= esc((string) $lng, 'attr') ?>"
                 data-zoom="<?= esc((string) $zoom, 'attr') ?>"
                 data-points="<?= esc(json_encode($points, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]', 'attr') ?>"
                 role="application" aria-label="Peta sebaran aduan"></div>
        </div>
</div>
