<?php

declare(strict_types=1);

namespace App\Validation;

/**
 * Aturan validasi pelacakan aduan publik (berdasarkan kode tiket).
 */
final class TrackingRules
{
    /**
     * @var array<string, string>
     */
    public array $lookup = [
        'ticket_code' => 'required|max_length[32]|regex_match[/^SIP-[0-9]{8}-[A-Z0-9]{6}$/]',
    ];

    /**
     * @var array<string, array<string, string>>
     */
    public array $messages = [
        'ticket_code' => [
            'required'    => 'Kode tiket wajib diisi.',
            'max_length'  => 'Kode tiket maksimal 32 karakter.',
            'regex_match' => 'Format kode tiket tidak sesuai (contoh: SIP-20260119-AB12CD).',
        ],
    ];
}
