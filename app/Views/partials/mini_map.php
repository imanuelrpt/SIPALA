<?php

/**
 * Peta kecil penunjuk satu titik aduan (Leaflet, tanpa cluster).
 *
 * @var float|int|string $lat
 * @var float|int|string $lng
 * @var float|int|string $zoom
 * @var string           $heightClass
 */
$lat         = $lat ?? -6.9175;
$lng         = $lng ?? 107.6191;
$zoom        = $zoom ?? 16;
$heightClass = $heightClass ?? 'map-canvas map-canvas-sm';
?>
<div id="peta-mini" class="<?= esc($heightClass, 'attr') ?>"
     data-lat="<?= esc((string) $lat, 'attr') ?>"
     data-lng="<?= esc((string) $lng, 'attr') ?>"
     data-zoom="<?= esc((string) $zoom, 'attr') ?>"
     role="application" aria-label="Peta lokasi aduan"></div>
