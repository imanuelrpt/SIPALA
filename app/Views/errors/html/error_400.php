<?php

/**
 * Halaman 400 — permintaan tidak valid.
 *
 * Dirender oleh handler CodeIgniter (bukan lewat layout) sehingga berdiri sendiri.
 *
 * @var int $code
 */
$code = $code ?? 400;
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>400 — Permintaan Tidak Valid &middot; SIPALA</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body class="bg-body-tertiary d-flex flex-column min-vh-100">
<main class="container flex-grow-1 d-flex align-items-center justify-content-center py-5">
    <div class="text-center" style="max-width:34rem">
        <div class="display-1 fw-bold text-primary">400</div>
        <h1 class="h3 mt-3">Permintaan Tidak Valid</h1>
        <p class="text-body-secondary">
            <?php if (ENVIRONMENT !== 'production' && isset($message)) : ?>
                <?= nl2br(esc($message)) ?>
            <?php else : ?>
                Permintaan Anda tidak dapat diproses. Silakan kembali dan periksa kembali data yang
                Anda kirimkan.
            <?php endif; ?>
        </p>
        <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">
            <a class="btn btn-primary" href="<?= base_url('/') ?>">
                <i class="bi bi-house-door me-1" aria-hidden="true"></i>Kembali ke beranda
            </a>
        </div>
    </div>
</main>
</body>
</html>
