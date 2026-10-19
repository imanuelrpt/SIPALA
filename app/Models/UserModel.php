<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\User;
use App\Enums\UserRole;
use App\Models\Concerns\GuardsPrivilegedFields;
use CodeIgniter\Model;

class UserModel extends Model
{
    use GuardsPrivilegedFields;

    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $returnType       = User::class;
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = true;
    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';

    /**
     * Hanya kolom ini yang boleh diisi dari data form.
     *
     * @var list<string>
     */
    protected $allowedFields = [
        'name',
        'email',
        'phone',
        'password_hash',
        'email_verified_at',
        'last_login_at',
    ];

    /**
     * Kolom istimewa — hanya lewat updatePrivileged()/insertPrivileged().
     *
     * @var list<string>
     */
    protected array $privilegedFields = [
        'role',
        'agency_id',
        'is_active',
    ];

    /**
     * Jaring pengaman kedua; validasi utama ada di App\Validation\*.
     *
     * @var array<string, string>
     */
    protected $validationRules = [
        'name'  => 'required|min_length[3]|max_length[100]',
        'email' => 'required|valid_email|max_length[191]',
        'phone' => 'permit_empty|max_length[25]|regex_match[/^[0-9+\-\s()]*$/]',
    ];

    /**
     * @var array<string, array<string, string>>
     */
    protected $validationMessages = [
        'name' => [
            'required'   => 'Nama lengkap wajib diisi.',
            'min_length' => 'Nama minimal 3 karakter.',
            'max_length' => 'Nama maksimal 100 karakter.',
        ],
        'email' => [
            'required'    => 'Email wajib diisi.',
            'valid_email' => 'Format email tidak valid.',
        ],
    ];

    public function findActiveByEmail(string $email): ?User
    {
        /** @var User|null $user */
        $user = $this->where('email', strtolower(trim($email)))->first();

        return $user;
    }

    public function findByEmail(string $email): ?User
    {
        /** @var User|null $user */
        $user = $this->withDeleted()->where('email', strtolower(trim($email)))->first();

        return $user;
    }

    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        $builder = $this->withDeleted()->where('email', strtolower(trim($email)));

        if ($exceptId !== null) {
            $builder->where('id !=', $exceptId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * @return list<User>
     */
    public function findByRole(UserRole $role): array
    {
        /** @var list<User> $users */
        $users = $this->where('role', $role->value)->orderBy('name', 'ASC')->findAll();

        return $users;
    }

    public function touchLastLogin(int $id): void
    {
        $this->update($id, ['last_login_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Jumlah akun per peran (tanpa akun terhapus).
     *
     * @return array<string, int>
     */
    public function countsByRole(): array
    {
        $counts = array_fill_keys(UserRole::values(), 0);

        $rows = $this->db->table($this->table)
            ->select('role, COUNT(*) AS total')
            ->where('deleted_at', null)
            ->groupBy('role')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $counts[(string) $row['role']] = (int) $row['total'];
        }

        return $counts;
    }

    public function countTotal(): int
    {
        return $this->db->table($this->table)->where('deleted_at', null)->countAllResults();
    }

    public function countActive(): int
    {
        return $this->db->table($this->table)
            ->where('deleted_at', null)
            ->where('is_active', 1)
            ->countAllResults();
    }

    /**
     * Daftar akun Pemda beserta nama instansinya (read-model panel Admin).
     *
     * @return list<array<string, mixed>>
     */
    public function pemdaWithAgency(): array
    {
        return $this->db->table($this->table)
            ->select('users.*, agencies.name AS agency_name')
            ->join('agencies', 'agencies.id = users.agency_id', 'left')
            ->where('users.role', UserRole::Pemda->value)
            ->where('users.deleted_at', null)
            ->orderBy('users.name', 'ASC')
            ->get()
            ->getResultArray();
    }
}
