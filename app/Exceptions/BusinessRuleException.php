<?php

declare(strict_types=1);

namespace App\Exceptions;

use CodeIgniter\Exceptions\HTTPExceptionInterface;
use RuntimeException;

/**
 * Dilempar ketika sebuah aksi melanggar aturan bisnis, misalnya transisi
 * status yang tidak valid. Biasanya ditangkap controller untuk menampilkan
 * pesan kesalahan yang ramah pengguna.
 */
final class BusinessRuleException extends RuntimeException implements HTTPExceptionInterface
{
    public function __construct(string $message, private readonly int $statusCode = 422)
    {
        parent::__construct($message, $statusCode);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
