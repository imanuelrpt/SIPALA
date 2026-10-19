<?php

/**
 * Pemilih lokasi aduan (Leaflet + pencarian Nominatim).
 *
 * Dipakai oleh formulir aduan masyarakat. Semua field memakai `old()` agar
 * nilai terisi kembali setelah validasi gagal.
 *
 * @var \Config\Sipala            $sipalaConfig
 * @var array<string, string>|null $errors
 */
$sipalaConfig = $sipalaConfig ?? config(\Config\Sipala::class);
$errors       = $errors ?? [];
?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h2 class="h6 mb-0"><i class="bi bi-geo-alt me-1 text-primary" aria-hidden="true"></i><?= esc(lang('App.location')) ?></h2>
    </div>
    <div class="card-body">
        <p class="text-body-secondary small">
            Klik pada peta untuk menandai titik lokasi, atau cari alamat lalu pilih salah satu hasil.
            Titik lokasi wajib berada di wilayah Indonesia.
        </p>

        <div class="row g-2 mb-3">
            <div class="col-md-9">
                <label class="form-label" for="cari-alamat"><?= esc(lang('App.searchAddress')) ?></label>
                <input type="text" class="form-control" id="cari-alamat" autocomplete="off"
                       placeholder="Contoh: Jalan Asia Afrika Bandung">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="button" class="btn btn-outline-primary w-100" id="cari-alamat-btn">
                    <i class="bi bi-search me-1" aria-hidden="true"></i><?= esc(lang('App.search')) ?>
                </button>
            </div>
        </div>

        <ul class="list-group map-search-results mb-3 d-none" id="hasil-alamat"></ul>

        <div id="peta-pilih" class="map-canvas mb-3"
             data-lat="<?= esc((string) $sipalaConfig->defaultLatitude, 'attr') ?>"
             data-lng="<?= esc((string) $sipalaConfig->defaultLongitude, 'attr') ?>"
             data-zoom="<?= esc((string) $sipalaConfig->defaultZoom, 'attr') ?>"
             role="application" aria-label="Peta pemilihan lokasi aduan"></div>

        <div class="d-flex flex-wrap gap-2 mb-3">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="gunakan-lokasi">
                <i class="bi bi-crosshair me-1" aria-hidden="true"></i><?= esc(lang('App.useMyLocation')) ?>
            </button>
            <span class="small text-body-secondary align-self-center">
                Geser penanda pada peta bila titik kurang tepat.
            </span>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="latitude"><?= esc(lang('App.latitude')) ?></label>
                <input type="text" inputmode="decimal" class="form-control <?= is_invalid($errors, 'latitude') ? 'is-invalid' : '' ?>"
                       id="latitude" name="latitude" value="<?= esc(old('latitude') ?? '', 'attr') ?>" readonly required>
                <?php if (is_invalid($errors, 'latitude')) : ?>
                    <div class="invalid-feedback"><?= esc(field_error($errors, 'latitude')) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="longitude"><?= esc(lang('App.longitude')) ?></label>
                <input type="text" inputmode="decimal" class="form-control <?= is_invalid($errors, 'longitude') ? 'is-invalid' : '' ?>"
                       id="longitude" name="longitude" value="<?= esc(old('longitude') ?? '', 'attr') ?>" readonly required>
                <?php if (is_invalid($errors, 'longitude')) : ?>
                    <div class="invalid-feedback"><?= esc(field_error($errors, 'longitude')) ?></div>
                <?php endif; ?>
            </div>

            <div class="col-12">
                <label class="form-label" for="address_detail"><?= esc(lang('App.addressDetail')) ?></label>
                <input type="text" class="form-control <?= is_invalid($errors, 'address_detail') ? 'is-invalid' : '' ?>"
                       id="address_detail" name="address_detail" maxlength="255"
                       value="<?= esc(old('address_detail') ?? '', 'attr') ?>">
                <?php if (is_invalid($errors, 'address_detail')) : ?>
                    <div class="invalid-feedback"><?= esc(field_error($errors, 'address_detail')) ?></div>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <label class="form-label" for="village"><?= esc(lang('App.village')) ?></label>
                <input type="text" class="form-control" id="village" name="village" maxlength="100"
                       value="<?= esc(old('village') ?? '', 'attr') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="district"><?= esc(lang('App.district')) ?></label>
                <input type="text" class="form-control" id="district" name="district" maxlength="100"
                       value="<?= esc(old('district') ?? '', 'attr') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="city"><?= esc(lang('App.city')) ?></label>
                <input type="text" class="form-control" id="city" name="city" maxlength="100"
                       value="<?= esc(old('city') ?? '', 'attr') ?>">
            </div>
        </div>
    </div>
</div>
