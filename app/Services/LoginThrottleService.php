<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\LoginAttemptModel;
use Config\Sipala;

/**
 * Pembatasan percobaan login gagal per kombinasi email + alamat IP.
 *
 * Identifier disimpan sebagai hash agar tidak menyimpan email mentah.
 */
final class LoginThrottleService
{
    public function __construct(
        private readonly LoginAttemptModel $model,
        private readonly Sipala $config,
    ) {
    }

    public function tooManyAttempts(string $email, string $ipAddress): bool
    {
        return $this->remainingAttempts($email, $ipAddress) <= 0;
    }

    public function remainingAttempts(string $email, string $ipAddress): int
    {
        $failures = $this->model->countFailures(
            $this->identifier($email),
            $ipAddress,
            $this->windowStart(),
        );

        return max(0, $this->config->loginMaxAttempts - $failures);
    }

    /**
     * Sisa waktu tunggu dalam menit.
     */
    public function retryAfterMinutes(string $email, string $ipAddress): int
    {
        return $this->remainingAttempts($email, $ipAddress) > 0
            ? 0
            : max(1, $this->config->loginWindowMinutes);
    }

    public function recordFailure(string $email, string $ipAddress): void
    {
        $this->model->recordFailure($this->identifier($email), $ipAddress);
        $this->model->pruneOlderThan(date('Y-m-d H:i:s', time() - ($this->config->loginWindowMinutes * 60 * 12)));
    }

    public function clear(string $email, string $ipAddress): void
    {
        $this->model->clearFor($this->identifier($email), $ipAddress);
    }

    private function identifier(string $email): string
    {
        return hash('sha256', strtolower(trim($email)));
    }

    private function windowStart(): string
    {
        return date('Y-m-d H:i:s', time() - ($this->config->loginWindowMinutes * 60));
    }
}
