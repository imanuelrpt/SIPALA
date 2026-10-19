<?php

/**
 * Surel tautan reset kata sandi.
 *
 * @var \App\Entities\User $user
 * @var string             $link
 */
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Reset kata sandi SIPALA</title>
</head>
<body style="margin:0;padding:24px;background:#f5f7fa;font-family:Arial,Helvetica,sans-serif;color:#212529">
<div style="max-width:560px;margin:0 auto;background:#ffffff;border:1px solid #dbe4ee;border-radius:12px;padding:28px">
    <h1 style="margin:0 0 8px;font-size:20px;color:#0d3b66">Reset Kata Sandi SIPALA</h1>
    <p style="margin:0 0 16px;font-size:14px">Halo <?= esc($user->displayName()) ?>,</p>
    <p style="margin:0 0 16px;font-size:14px;line-height:1.6">
        Kami menerima permintaan untuk mengatur ulang kata sandi akun SIPALA Anda
        (<?= esc((string) $user->email) ?>). Klik tombol di bawah untuk membuat kata sandi baru.
    </p>
    <p style="margin:0 0 24px">
        <a href="<?= esc($link, 'attr') ?>"
           style="display:inline-block;padding:12px 20px;background:#0d3b66;color:#ffffff;text-decoration:none;border-radius:8px;font-size:14px">
            Atur ulang kata sandi
        </a>
    </p>
    <p style="margin:0 0 8px;font-size:13px;color:#6c757d">
        Tautan ini berlaku terbatas dan hanya dapat dipakai satu kali. Bila Anda tidak meminta
        reset kata sandi, abaikan surel ini — kata sandi Anda tidak akan berubah.
    </p>
    <hr style="border:none;border-top:1px solid #dbe4ee;margin:20px 0">
    <p style="margin:0;font-size:12px;color:#6c757d;word-break:break-all">
        Bila tombol tidak dapat diklik, salin tautan berikut ke peramban:<br>
        <?= esc($link) ?>
    </p>
</div>
</body>
</html>
