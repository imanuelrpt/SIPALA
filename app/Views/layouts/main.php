<!doctype html>
<html lang="id" data-base-url="<?= esc(base_url('/'), 'attr') ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= esc(lang('App.appLongName')) ?> — layanan pengaduan layanan publik dan infrastruktur daerah.">
    <meta name="csrf-token" content="<?= esc(csrf_hash(), 'attr') ?>">
    <title><?= esc($title ?? lang('App.appName')) ?> &middot; <?= esc(lang('App.appName')) ?></title>

    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <?= $this->renderSection('styles') ?>
</head>
<body class="bg-body-tertiary d-flex flex-column min-vh-100">
<a href="#konten-utama" class="visually-hidden-focusable btn btn-primary m-2">Lewati ke konten utama</a>

<?= $this->include('partials/navbar') ?>

<main id="konten-utama" class="flex-grow-1 pb-5">
    <div class="<?= esc($containerClass ?? 'container py-4', 'attr') ?>">
        <?= $this->include('partials/flash') ?>
        <?= $this->renderSection('content') ?>
    </div>
</main>

<?= $this->include('partials/footer') ?>

<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>" defer></script>
<script src="<?= base_url('assets/js/app.js') ?>" defer></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
