<footer class="py-4 mt-auto border-top bg-white">
    <div class="container">
        <div class="row gy-3 align-items-center">
            <div class="col-md-6">
                <p class="mb-1 fw-semibold"><i class="bi bi-megaphone-fill me-1" aria-hidden="true"></i><?= esc(lang('App.appLongName')) ?></p>
                <p class="text-body-secondary small mb-0">
                    Data peta &copy; kontributor <a href="https://www.openstreetmap.org/copyright" rel="noopener" target="_blank">OpenStreetMap</a>.
                    Pencarian alamat menggunakan layanan Nominatim.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <ul class="list-inline mb-2">
                    <li class="list-inline-item"><a href="<?= site_url('panduan') ?>"><?= esc(lang('App.guide')) ?></a></li>
                    <li class="list-inline-item">&middot;</li>
                    <li class="list-inline-item"><a href="<?= site_url('kebijakan-privasi') ?>"><?= esc(lang('App.privacy')) ?></a></li>
                    <li class="list-inline-item">&middot;</li>
                    <li class="list-inline-item"><a href="<?= site_url('cek-aduan') ?>"><?= esc(lang('App.tracking')) ?></a></li>
                </ul>
                <p class="text-body-secondary small mb-0">Kanal resmi pengaduan masyarakat daerah.</p>
            </div>
        </div>
    </div>
</footer>
