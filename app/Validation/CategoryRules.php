<?php

declare(strict_types=1);

namespace App\Validation;

final class CategoryRules
{
    /**
     * @var array<string, string>
     */
    public array $store = [
        'name'      => 'required|min_length[3]|max_length[100]',
        'agency_id' => 'required|is_natural_no_zero',
        'is_active' => 'required|in_list[0,1]',
    ];

    /**
     * @var array<string, array<string, string>>
     */
    public array $messages = [
        'name' => [
            'required'   => 'Nama kategori wajib diisi.',
            'min_length' => 'Nama kategori minimal 3 karakter.',
            'max_length' => 'Nama kategori maksimal 100 karakter.',
        ],
        'agency_id' => [
            'required'           => 'Instansi penanggung jawab wajib dipilih.',
            'is_natural_no_zero' => 'Instansi tidak valid.',
        ],
        'is_active' => [
            'required' => 'Status kategori wajib dipilih.',
            'in_list'  => 'Status kategori tidak valid.',
        ],
    ];
}
