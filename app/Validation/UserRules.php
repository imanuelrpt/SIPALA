<?php

declare(strict_types=1);

namespace App\Validation;

use App\Enums\UserRole;

/**
 * Aturan validasi manajemen pengguna (panel Admin).
 */
final class UserRules
{
    /**
     * @var array<string, string>
     */
    public array $store;

    /**
     * @var array<string, string>
     */
    public array $update;

    public function __construct()
    {
        $roleRule = 'required|' . UserRole::validationRule();

        $this->store = [
            'name'      => 'required|min_length[3]|max_length[100]',
            'email'     => 'required|valid_email|max_length[191]|is_unique[users.email]',
            'phone'     => 'permit_empty|max_length[25]|regex_match[/^[0-9+\-\s()]*$/]',
            'role'      => $roleRule,
            'agency_id' => 'permit_empty|is_natural_no_zero',
            'is_active' => 'required|in_list[0,1]',
            'password'  => 'required|min_length[8]|max_length[72]|regex_match[/^(?=.*[A-Za-z])(?=.*[0-9]).+$/]',
        ];

        $this->update = [
            'name'      => 'required|min_length[3]|max_length[100]',
            'email'     => 'required|valid_email|max_length[191]',
            'phone'     => 'permit_empty|max_length[25]|regex_match[/^[0-9+\-\s()]*$/]',
            'role'      => $roleRule,
            'agency_id' => 'permit_empty|is_natural_no_zero',
            'is_active' => 'required|in_list[0,1]',
        ];
    }

    /**
     * Validasi khusus saat Pemda wajib punya instansi.
     *
     * @var array<string, string>
     */
    public array $agencyRequired = [
        'agency_id' => 'required|is_natural_no_zero',
    ];

    /**
     * Aturan pembaruan dengan pengecualian email milik pengguna itu sendiri.
     *
     * @return array<string, string>
     */
    public function updateFor(int $userId): array
    {
        $rules         = $this->update;
        $rules['email'] = $this->update['email'] . '|is_unique[users.email,id,' . $userId . ']';

        return $rules;
    }

    /**
     * @var array<string, array<string, string>>
     */
    public array $messages = [
        'name' => [
            'required'   => 'Nama lengkap wajib diisi.',
            'min_length' => 'Nama minimal 3 karakter.',
            'max_length' => 'Nama maksimal 100 karakter.',
        ],
        'email' => [
            'required'    => 'Email wajib diisi.',
            'valid_email' => 'Format email tidak valid.',
            'is_unique'   => 'Email sudah digunakan pengguna lain.',
        ],
        'role' => [
            'required' => 'Peran wajib dipilih.',
            'in_list'  => 'Peran tidak dikenal.',
        ],
        'agency_id' => [
            'required'           => 'Instansi wajib dipilih untuk akun Pemda.',
            'is_natural_no_zero' => 'Instansi tidak valid.',
        ],
        'is_active' => [
            'required' => 'Status akun wajib dipilih.',
            'in_list'  => 'Status akun tidak valid.',
        ],
        'password' => [
            'required'    => 'Kata sandi wajib diisi.',
            'min_length'  => 'Kata sandi minimal 8 karakter.',
            'max_length'  => 'Kata sandi maksimal 72 karakter.',
            'regex_match' => 'Kata sandi harus memuat huruf dan angka.',
        ],
    ];
}
