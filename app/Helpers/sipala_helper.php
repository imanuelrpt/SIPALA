<?php

declare(strict_types=1);

use App\Enums\ComplaintStatus;

/**
 * Helper umum SIPALA.
 *
 * Semua fungsi di sini hanya membentuk string yang aman (di-escape) atau
 * nilai primitif, sehingga tidak boleh dipakai untuk menampilkan input mentah.
 */

if (! function_exists('format_tanggal')) {
    /**
     * Format tanggal ke gaya Indonesia, contoh: 19 Oktober 2026.
     */
    function format_tanggal(\DateTimeInterface|int|string|null $datetime, bool $withTime = false): string
    {
        if ($datetime === null || $datetime === '') {
            return '-';
        }

        if ($datetime instanceof \DateTimeInterface) {
            $timestamp = $datetime->getTimestamp();
        } else {
            $timestamp = strtotime((string) $datetime);
        }

        if ($timestamp === false || $timestamp <= 0) {
            return '-';
        }

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $label = sprintf(
            '%d %s %d',
            (int) date('j', $timestamp),
            $months[(int) date('n', $timestamp)],
            (int) date('Y', $timestamp),
        );

        if ($withTime) {
            $label .= ', ' . date('H:i', $timestamp) . ' WIB';
        }

        return $label;
    }
}

if (! function_exists('format_tanggal_waktu')) {
    /**
     * Format tanggal + jam dalam gaya Indonesia.
     */
    function format_tanggal_waktu(\DateTimeInterface|int|string|null $datetime): string
    {
        return format_tanggal($datetime, true);
    }
}

if (! function_exists('status_badge')) {
    /**
     * Badge Bootstrap untuk sebuah status aduan.
     */
    function status_badge(ComplaintStatus $status): string
    {
        return sprintf(
            '<span class="badge rounded-pill %s"><i class="bi %s me-1"></i>%s</span>',
            esc($status->badgeClass(), 'attr'),
            esc($status->icon(), 'attr'),
            esc($status->label()),
        );
    }
}

if (! function_exists('role_badge')) {
    /**
     * Badge Bootstrap untuk sebuah peran.
     */
    function role_badge(\App\Enums\UserRole $role): string
    {
        $class = match ($role) {
            \App\Enums\UserRole::Masyarakat => 'text-bg-light border',
            \App\Enums\UserRole::Pemda      => 'text-bg-primary',
            \App\Enums\UserRole::Admin      => 'text-bg-dark',
        };

        return sprintf(
            '<span class="badge rounded-pill %s">%s</span>',
            esc($class, 'attr'),
            esc($role->label()),
        );
    }
}

if (! function_exists('mask_nama')) {
    /**
     * Menyamarkan nama pelapor untuk tampilan publik (UU PDP).
     *
     * "Budi Santoso" -> "Budi S."
     */
    function mask_nama(string $name): string
    {
        $name = trim($name);

        if ($name === '') {
            return 'Anonim';
        }

        $parts = preg_split('/\s+/', $name) ?: [$name];

        if (count($parts) === 1) {
            $first = $parts[0];
            $visible = mb_substr($first, 0, 1);

            return $visible . str_repeat('*', max(mb_strlen($first) - 1, 1));
        }

        $first = array_shift($parts);
        $initials = array_map(
            static fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)) . '.',
            $parts,
        );

        return trim($first . ' ' . implode(' ', $initials));
    }
}

if (! function_exists('inisial_nama')) {
    /**
     * Inisial untuk avatar, maksimal dua huruf.
     */
    function inisial_nama(?string $name): string
    {
        $name = trim((string) $name);

        if ($name === '') {
            return '?';
        }

        $parts = preg_split('/\s+/', $name) ?: [$name];
        $initials = '';

        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return $initials === '' ? '?' : $initials;
    }
}

if (! function_exists('menu_aktif')) {
    /**
     * Mengembalikan kelas "active" bila segmen URL saat ini cocok.
     */
    function menu_aktif(string $pattern, string $class = 'active'): string
    {
        $uri = service('request')->getUri()->getPath();

        foreach (explode('|', $pattern) as $candidate) {
            $candidate = trim($candidate, '/');

            if ($candidate === '') {
                continue;
            }

            if ($uri === '/' . $candidate || str_starts_with(ltrim($uri, '/'), $candidate)) {
                return $class;
            }
        }

        return '';
    }
}

if (! function_exists('angka_ringkas')) {
    /**
     * Format angka dengan pemisah ribuan gaya Indonesia.
     */
    function angka_ringkas(int|float $value, int $decimals = 0): string
    {
        return number_format((float) $value, $decimals, ',', '.');
    }
}

if (! function_exists('ukuran_berkas')) {
    /**
     * Konversi byte ke satuan yang mudah dibaca.
     */
    function ukuran_berkas(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1, ',', '.') . ' KB';
        }

        return number_format($bytes / (1024 * 1024), 1, ',', '.') . ' MB';
    }
}

if (! function_exists('google_maps_url')) {
    /**
     * URL navigasi Google Maps dari koordinat.
     */
    function google_maps_url(float|string $latitude, float|string $longitude): string
    {
        return sprintf(
            'https://www.google.com/maps/dir/?api=1&destination=%s,%s',
            rawurlencode((string) $latitude),
            rawurlencode((string) $longitude),
        );
    }
}

if (! function_exists('nama_pelapor')) {
    /**
     * Nama pelapor sesuai pengaturan sembunyikan identitas.
     */
    function nama_pelapor(?string $name, bool $hideIdentity, bool $viewerIsPrivileged = false): string
    {
        $name = (string) $name;

        if ($hideIdentity && ! $viewerIsPrivileged) {
            return 'Warga Anonim';
        }

        return $name === '' ? 'Warga Anonim' : $name;
    }
}

if (! function_exists('status_options')) {
    /**
     * Daftar opsi status untuk dropdown filter.
     *
     * @return array<string, string>
     */
    function status_options(): array
    {
        return ComplaintStatus::options();
    }
}

if (! function_exists('field_error')) {
    /**
     * Pesan kesalahan validasi untuk satu nama kolom.
     *
     * @param array<string, string>|null $errors
     */
    function field_error(?array $errors, string $field): string
    {
        if (! is_array($errors) || ! isset($errors[$field])) {
            return '';
        }

        return (string) $errors[$field];
    }
}

if (! function_exists('is_invalid')) {
    /**
     * True bila kolom tertentu memiliki kesalahan validasi.
     *
     * @param array<string, string>|null $errors
     */
    function is_invalid(?array $errors, string $field): bool
    {
        return is_array($errors) && isset($errors[$field]);
    }
}

if (! function_exists('input_class')) {
    /**
     * Kelas form-control dengan penanda kesalahan.
     *
     * @param array<string, string>|null $errors
     */
    function input_class(?array $errors, string $field, string $base = 'form-control'): string
    {
        return is_invalid($errors, $field) ? $base . ' is-invalid' : $base;
    }
}

if (! function_exists('bintang_penilaian')) {
    /**
     * Bintang untuk menampilkan penilaian 1–5.
     */
    function bintang_penilaian(?int $rating): string
    {
        if ($rating === null || $rating <= 0) {
            return '<span class="text-body-secondary small">Belum dinilai</span>';
        }

        $html = '<span class="rating-stars" title="' . esc((string) $rating) . ' dari 5">';

        for ($i = 1; $i <= 5; $i++) {
            $icon = $i <= $rating ? 'bi-star-fill' : 'bi-star';
            $html .= '<i class="bi ' . $icon . '" aria-hidden="true"></i>';
        }

        return $html . '<span class="visually-hidden">' . esc((string) $rating) . ' dari 5</span></span>';
    }
}

if (! function_exists('inisial_peran')) {
    /**
     * Label peran dari nilai mentah (read-model tanpa Entity).
     */
    function label_peran(string $role): string
    {
        $peran = \App\Enums\UserRole::tryFrom($role);

        return $peran === null ? $role : $peran->label();
    }
}

if (! function_exists('label_aksi')) {
    /**
     * Label aksi log dari nilai mentah.
     */
    function label_aksi(string $action): string
    {
        $aksi = \App\Enums\ActivityAction::tryFrom($action);

        return $aksi === null ? $action : $aksi->label();
    }
}

if (! function_exists('kutip_ringkas')) {
    /**
     * Memotong teks panjang untuk tampilan tabel.
     */
    function kutip_ringkas(?string $text, int $length = 70): string
    {
        $text = trim((string) $text);

        if ($text === '') {
            return '-';
        }

        return mb_strlen($text) <= $length ? $text : mb_substr($text, 0, $length - 1) . '…';
    }
}
