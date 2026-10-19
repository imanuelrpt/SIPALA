<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Jenis lampiran aduan. Dipetakan ke kolom `complaint_attachments.type`.
 */
enum AttachmentType: string
{
    case BuktiAduan       = 'bukti_aduan';
    case BuktiPenyelesaian = 'bukti_penyelesaian';

    public function label(): string
    {
        return match ($this) {
            self::BuktiAduan        => 'Bukti Aduan',
            self::BuktiPenyelesaian => 'Bukti Penyelesaian',
        };
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
}
