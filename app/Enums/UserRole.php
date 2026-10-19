<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Peran pengguna. Nilai dipetakan langsung ke kolom `users.role`.
 *
 * Register publik HANYA boleh menghasilkan {@see self::Masyarakat}.
 */
enum UserRole: string
{
    case Masyarakat = 'masyarakat';
    case Pemda      = 'pemda';
    case Admin      = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Masyarakat => 'Masyarakat',
            self::Pemda      => 'Pemda',
            self::Admin      => 'Admin',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Masyarakat => 'Warga yang mengirim dan memantau pengaduan.',
            self::Pemda      => 'Petugas instansi pemerintah daerah penanggung jawab aduan.',
            self::Admin      => 'Pengelola sistem, pengguna, instansi, dan kategori.',
        };
    }

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Masyarakat => 'masyarakat.dashboard',
            self::Pemda      => 'pemda.dashboard',
            self::Admin      => 'admin.dashboard',
        };
    }

    public function isMasyarakat(): bool
    {
        return $this === self::Masyarakat;
    }

    public function isPemda(): bool
    {
        return $this === self::Pemda;
    }

    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }

    /**
     * Peran yang boleh dibuat oleh Admin melalui panel manajemen pengguna.
     *
     * @return list<string>
     */
    public static function assignableValues(): array
    {
        return self::values();
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
     * Aturan validasi "in_list" untuk kolom role.
     */
    public static function validationRule(): string
    {
        return 'in_list[' . implode(',', self::values()) . ']';
    }
}
