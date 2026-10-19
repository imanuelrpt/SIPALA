<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Konfigurasi khusus aplikasi SIPALA.
 *
 * Nilai dapat di-override melalui `.env` dengan prefiks `sipala.`,
 * contoh: `sipala.loginMaxAttempts = 5`.
 */
class Sipala extends BaseConfig
{
    public string $appName = 'SIPALA';

    public string $appLongName = 'Sistem Pengaduan Lapangan';

    public string $appTagline = 'Sampaikan laporan Anda, pantau sampai tuntas.';

    /**
     * Register publik. Jika dimatikan, hanya Admin yang boleh menambah akun.
     */
    public bool $registrationEnabled = true;

    /**
     * Verifikasi email (opsional).
     */
    public bool $emailVerification = false;

    /**
     * Batas percobaan login gagal.
     */
    public int $loginMaxAttempts = 5;

    public int $loginWindowMinutes = 15;

    /**
     * Batas pengiriman aduan per pengguna per jam.
     */
    public int $complaintHourlyLimit = 5;

    /**
     * Batas waktu idle sesi (menit). Sesi kedaluwarsa bila tidak ada aktivitas.
     */
    public int $sessionIdleMinutes = 120;

    /**
     * Pengaturan unggahan berkas.
     */
    public int $uploadMaxSizeKB = 2048;

    public int $uploadMaxFiles = 3;

    /** @var list<string> */
    public array $allowedImageExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    /** @var list<string> */
    public array $allowedImageMimes = ['image/jpeg', 'image/png', 'image/webp'];

    /**
     * Rentang koordinat wilayah Indonesia (validasi server-side).
     */
    public float $minLatitude = -11.0;

    public float $maxLatitude = 6.0;

    public float $minLongitude = 95.0;

    public float $maxLongitude = 141.0;

    /**
     * Titik tengah peta default (Kota Bandung) bila pengguna belum memilih lokasi.
     */
    public float $defaultLatitude = -6.9175;

    public float $defaultLongitude = 107.6191;

    public int $defaultZoom = 13;

    /**
     * Captcha (Cloudflare Turnstile) — diaktifkan lewat konfigurasi.
     */
    public bool $captchaEnabled = false;

    public string $captchaProvider = '';

    public string $captchaSiteKey = '';

    public string $captchaSecretKey = '';

    /**
     * Batas token lupa password (menit).
     */
    public int $passwordResetExpiryMinutes = 60;

    /**
     * Folder penyimpanan berkas unggahan, di luar `public/`.
     */
    public function uploadPath(string $folder = ''): string
    {
        $path = WRITEPATH . 'uploads';

        if ($folder !== '') {
            $path .= DIRECTORY_SEPARATOR . trim($folder, '/\\');
        }

        return $path;
    }
}
