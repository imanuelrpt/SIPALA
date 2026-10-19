<?php

/**
 * Tampilan galat generik untuk lingkungan produksi.
 *
 * Dipakai saat `display_errors` dimatikan: tidak ada jejak tumpukan,
 * nama berkas, atau pesan basis data yang dibocorkan ke pengguna.
 *
 * @var int $code
 */
$code = $code ?? 500;
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Terjadi Kesalahan &middot; SIPALA</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body class="bg-body-tertiary d-flex flex-column min-vh-100">
<main class="container flex-grow-1 d-flex align-items-center justify-content-center py-5">
    <div class="text-center" style="max-width:34rem">
        <div class="display-1 fw-bold text-primary"><?= esc((string) (int) $code) ?></div>
        <h1 class="h3 mt-3">Terjadi Kesalahan</h1>
        <p class="text-body-secondary">
            Permintaan Anda tidak dapat diproses saat ini. Silakan coba beberapa saat lagi.
        </p>
        <a class="btn btn-primary mt-3" href="<?= base_url('/') ?>">Kembali ke beranda</a>
    </div>
</main>
</body>
</html>
