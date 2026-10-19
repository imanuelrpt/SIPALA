<?php

declare(strict_types=1);

namespace App\Exceptions;

use CodeIgniter\Exceptions\HTTPExceptionInterface;
use RuntimeException;

/**
 * Dilempar ketika pengguna yang sudah login tidak berhak mengakses
 * sumber daya tertentu (IDOR / salah instansi / salah peran).
 *
 * Menghasilkan halaman 403.
 */
final class AccessDeniedException extends RuntimeException implements HTTPExceptionInterface
{
    public function __construct(string $message = 'Anda tidak memiliki hak akses untuk halaman ini.')
    {
        parent::__construct($message, 403);
    }
}
