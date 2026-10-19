<?php

declare(strict_types=1);

namespace App\Validation;

/**
 * Aturan validasi autentikasi.
 *
 * Dipakai melalui App\Validation\ValidationRunner agar pesan kesalahan
 * konsisten dan terpusat (bukan tersebar di controller).
 */
final class AuthRules
{
    /**
     * Register publik — hanya menghasilkan peran Masyarakat.
     *
     * @var array<string, string>
     */
    public array $register = [
        'name'             => 'required|min_length[3]|max_length[100]',
        'email'            => 'required|valid_email|max_length[191]|is_unique[users.email]',
        'phone'            => 'required|min_length[9]|max_length[25]|regex_match[/^[0-9+\-\s()]+$/]',
        'password'         => 'required|min_length[8]|max_length[72]|regex_match[/^(?=.*[A-Za-z])(?=.*[0-9]).+$/]',
        'password_confirm' => 'required|matches[password]',
        'privacy_accepted' => 'required|in_list[1]',
    ];

    /**
     * @var array<string, string>
     */
    public array $login = [
        'email'    => 'required|valid_email|max_length[191]',
        'password' => 'required|max_length[72]',
    ];

    /**
     * @var array<string, string>
     */
    public array $forgotPassword = [
        'email' => 'required|valid_email|max_length[191]',
    ];

    /**
     * @var array<string, string>
     */
    public array $resetPassword = [
        'token'            => 'required|alpha_numeric_punct|max_length[128]',
        'password'         => 'required|min_length[8]|max_length[72]|regex_match[/^(?=.*[A-Za-z])(?=.*[0-9]).+$/]',
        'password_confirm' => 'required|matches[password]',
    ];

    /**
     * @var array<string, string>
     */
    public array $changePassword = [
        'current_password' => 'required',
        'password'         => 'required|min_length[8]|max_length[72]|regex_match[/^(?=.*[A-Za-z])(?=.*[0-9]).+$/]',
        'password_confirm' => 'required|matches[password]',
    ];

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
            'is_unique'   => 'Email sudah terdaftar. Silakan masuk atau gunakan email lain.',
            'max_length'  => 'Email maksimal 191 karakter.',
        ],
        'phone' => [
            'required'    => 'Nomor HP wajib diisi.',
            'min_length'  => 'Nomor HP minimal 9 digit.',
            'max_length'  => 'Nomor HP maksimal 25 karakter.',
            'regex_match' => 'Nomor HP hanya boleh berisi angka, spasi, tanda +, -, dan tanda kurung.',
        ],
        'password' => [
            'required'    => 'Kata sandi wajib diisi.',
            'min_length'  => 'Kata sandi minimal 8 karakter.',
            'max_length'  => 'Kata sandi maksimal 72 karakter.',
            'regex_match' => 'Kata sandi harus memuat huruf dan angka.',
        ],
        'password_confirm' => [
            'required' => 'Konfirmasi kata sandi wajib diisi.',
            'matches'  => 'Konfirmasi kata sandi tidak sama.',
        ],
        'current_password' => [
            'required' => 'Kata sandi saat ini wajib diisi.',
        ],
        'privacy_accepted' => [
            'required' => 'Anda harus menyetujui kebijakan privasi.',
            'in_list'  => 'Anda harus menyetujui kebijakan privasi.',
        ],
        'token' => [
            'required' => 'Token reset tidak valid.',
        ],
    ];
}
