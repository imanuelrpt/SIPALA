<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

/**
 * Token reset password sekali pakai. Disimpan sebagai hash (bukan token mentah).
 */
class PasswordResetModel extends Model
{
    protected $table            = 'password_resets';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = '';

    /** @var list<string> */
    protected $allowedFields = ['email', 'token_hash', 'expires_at', 'used_at'];

    /**
     * @return array<string, mixed>|null
     */
    public function findValidByHash(string $tokenHash): ?array
    {
        return $this->where('token_hash', $tokenHash)
            ->where('used_at', null)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->first();
    }

    public function invalidateForEmail(string $email): void
    {
        $this->where('email', strtolower($email))->where('used_at', null)->delete();
    }
}
