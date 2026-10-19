<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

/**
 * Riwayat percobaan login untuk throttling per email + per IP.
 */
class LoginAttemptModel extends Model
{
    protected $table            = 'login_attempts';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;

    /** @var list<string> */
    protected $allowedFields = ['identifier', 'ip_address', 'attempted_at'];

    /**
     * Jumlah percobaan gagal dalam jendela waktu tertentu.
     */
    public function countFailures(string $identifier, string $ipAddress, string $since): int
    {
        return $this->groupStart()
            ->where('identifier', $identifier)
            ->orWhere('ip_address', $ipAddress)
            ->groupEnd()
            ->where('attempted_at >=', $since)
            ->countAllResults();
    }

    public function recordFailure(string $identifier, string $ipAddress): void
    {
        $this->insert([
            'identifier'   => $identifier,
            'ip_address'   => $ipAddress,
            'attempted_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function clearFor(string $identifier, string $ipAddress): void
    {
        $this->groupStart()
            ->where('identifier', $identifier)
            ->orWhere('ip_address', $ipAddress)
            ->groupEnd()
            ->delete();
    }

    /**
     * Menghapus riwayat lama agar tabel tidak tumbuh tanpa batas.
     */
    public function pruneOlderThan(string $before): void
    {
        $this->where('attempted_at <', $before)->delete();
    }
}
