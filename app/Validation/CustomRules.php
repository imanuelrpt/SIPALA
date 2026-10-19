<?php

declare(strict_types=1);

namespace App\Validation;

use DateTimeImmutable;

/**
 * Aturan validasi tambahan khusus SIPALA.
 */
final class CustomRules
{
    /**
     * Memastikan tanggal tidak melewati hari ini (waktu lokal aplikasi).
     */
    public function not_future_date(?string $str): bool
    {
        if ($str === null || trim($str) === '') {
            return false;
        }

        $timestamp = strtotime($str);

        if ($timestamp === false) {
            return false;
        }

        $today = (new DateTimeImmutable('today'))->getTimestamp();

        return $timestamp <= $today + 86399;
    }

    /**
     * Memastikan nilai berada pada rentang koordinat wilayah Indonesia.
     *
     * Format parameter: coordinate_range[-11,6] untuk lintang,
     * coordinate_range[95,141] untuk bujur.
     */
    public function coordinate_range(?string $str, string $parameters): bool
    {
        if ($str === null || trim($str) === '' || ! is_numeric($str)) {
            return false;
        }

        $parts = explode(',', $parameters, 2);

        if (count($parts) !== 2) {
            return false;
        }

        $value = (float) $str;

        return $value >= (float) $parts[0] && $value <= (float) $parts[1];
    }
}
