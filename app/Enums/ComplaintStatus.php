<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Siklus hidup sebuah aduan.
 *
 * Alur utama: menunggu -> diverifikasi -> diproses -> selesai.
 * Aduan dapat ditolak (dengan alasan wajib) dari status non-final mana pun.
 */
enum ComplaintStatus: string
{
    case Menunggu     = 'menunggu';
    case Diverifikasi = 'diverifikasi';
    case Diproses     = 'diproses';
    case Selesai      = 'selesai';
    case Ditolak      = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::Menunggu     => 'Menunggu',
            self::Diverifikasi => 'Diverifikasi',
            self::Diproses     => 'Diproses',
            self::Selesai      => 'Selesai',
            self::Ditolak      => 'Ditolak',
        };
    }

    /**
     * Kelas utilitas Bootstrap 5 untuk badge status.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Menunggu     => 'text-bg-secondary',
            self::Diverifikasi => 'text-bg-info',
            self::Diproses     => 'text-bg-warning',
            self::Selesai      => 'text-bg-success',
            self::Ditolak      => 'text-bg-danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Menunggu     => 'bi-hourglass-split',
            self::Diverifikasi => 'bi-patch-check',
            self::Diproses     => 'bi-tools',
            self::Selesai      => 'bi-check2-circle',
            self::Ditolak      => 'bi-x-octagon',
        };
    }

    /**
     * Status final tidak dapat berpindah lagi kecuali oleh Admin.
     */
    public function isFinal(): bool
    {
        return $this === self::Selesai || $this === self::Ditolak;
    }

    /**
     * Status yang boleh dipilih pengguna saat membuat aduan baru — selalu Menunggu.
     */
    public function requiresNote(): bool
    {
        return $this === self::Ditolak;
    }

    /**
     * Transisi status yang diizinkan untuk Pemda.
     *
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Menunggu     => [self::Diverifikasi, self::Diproses, self::Ditolak],
            self::Diverifikasi => [self::Diproses, self::Ditolak],
            self::Diproses     => [self::Selesai, self::Ditolak],
            self::Selesai, self::Ditolak => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        if ($this === $target) {
            return false;
        }

        return in_array($target, $this->allowedTransitions(), true);
    }

    /**
     * Status yang dianggap masih berjalan (belum final).
     *
     * @return list<self>
     */
    public static function openStatuses(): array
    {
        return array_values(array_filter(
            self::cases(),
            static fn (self $status): bool => ! $status->isFinal(),
        ));
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /**
     * Aturan validasi "in_list" untuk kolom status.
     */
    public static function validationRule(): string
    {
        return 'in_list[' . implode(',', self::values()) . ']';
    }
}
