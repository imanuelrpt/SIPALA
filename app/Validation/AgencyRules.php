<?php

declare(strict_types=1);

namespace App\Validation;

final class AgencyRules
{
    /**
     * @var array<string, string>
     */
    public array $store = [
        'name'        => 'required|min_length[3]|max_length[150]',
        'description' => 'permit_empty|max_length[500]',
        'is_active'   => 'required|in_list[0,1]',
    ];

    /**
     * @var array<string, array<string, string>>
     */
    public array $messages = [
        'name' => [
            'required'   => 'Nama instansi wajib diisi.',
            'min_length' => 'Nama instansi minimal 3 karakter.',
            'max_length' => 'Nama instansi maksimal 150 karakter.',
        ],
        'description' => [
            'max_length' => 'Deskripsi maksimal 500 karakter.',
        ],
        'is_active' => [
            'required' => 'Status instansi wajib dipilih.',
            'in_list'  => 'Status instansi tidak valid.',
        ],
    ];
}
