<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\User;
use App\Enums\ActivityAction;
use App\Enums\UserRole;
use App\Exceptions\AccessDeniedException;
use App\Exceptions\BusinessRuleException;
use App\Models\AgencyModel;
use App\Models\UserModel;

/**
 * Manajemen pengguna oleh Admin.
 *
 * Peran, instansi, dan status aktif TIDAK pernah diambil mentah dari request:
 * nilainya di-whitelist di sini sebelum disimpan melalui insertPrivileged().
 */
final class UserService
{
    public function __construct(
        private readonly UserModel $users,
        private readonly AgencyModel $agencies,
        private readonly AuthService $auth,
        private readonly ActivityLogService $activity,
    ) {
    }

    /**
     * Pendaftaran mandiri masyarakat.
     *
     * Peran SELALU dipaksa menjadi Masyarakat dan instansi dikosongkan —
     * nilai apa pun dari input pengguna diabaikan.
     *
     * @param array<string, mixed> $data
     */
    public function register(array $data): User
    {
        $id = $this->users->insertPrivileged([
            'name'          => trim((string) $data['name']),
            'email'         => strtolower(trim((string) $data['email'])),
            'phone'         => $this->nullable($data['phone'] ?? null),
            'password_hash' => $this->auth->hashPassword((string) $data['password']),
            'role'          => UserRole::Masyarakat->value,
            'agency_id'     => null,
            'is_active'     => 1,
        ]);

        if ((int) $id <= 0) {
            throw new BusinessRuleException('Gagal membuat akun. Silakan coba lagi.');
        }

        $this->activity->log(ActivityAction::Registered, 'user', (int) $id, 'Registrasi akun masyarakat', (int) $id);

        /** @var User $user */
        $user = $this->users->find((int) $id);

        return $user;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(User $actor, array $data): User
    {
        $role     = $this->resolveRole($data['role'] ?? null);
        $agencyId = $this->resolveAgencyId($role, $data['agency_id'] ?? null);

        $id = $this->users->insertPrivileged([
            'name'          => trim((string) $data['name']),
            'email'         => strtolower(trim((string) $data['email'])),
            'phone'         => $this->nullable($data['phone'] ?? null),
            'password_hash' => $this->auth->hashPassword((string) $data['password']),
            'role'          => $role->value,
            'agency_id'     => $agencyId,
            'is_active'     => ((string) ($data['is_active'] ?? '1')) === '1' ? 1 : 0,
        ]);

        if ((int) $id <= 0) {
            throw new BusinessRuleException('Gagal menyimpan pengguna baru.');
        }

        $this->activity->log(ActivityAction::UserCreated, 'user', (int) $id, 'Membuat akun ' . $role->label(), (int) $actor->id);

        /** @var User $user */
        $user = $this->users->find((int) $id);

        return $user;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(User $actor, int $userId, array $data): User
    {
        $user = $this->users->find($userId);

        if ($user === null) {
            throw new BusinessRuleException('Pengguna tidak ditemukan.');
        }

        $role     = $this->resolveRole($data['role'] ?? null);
        $agencyId = $this->resolveAgencyId($role, $data['agency_id'] ?? null);

        // Admin tidak boleh menurunkan perannya sendiri agar tidak terkunci di luar sistem.
        if ((int) $user->id === (int) $actor->id && $role !== UserRole::Admin) {
            throw new BusinessRuleException('Anda tidak dapat mengubah peran akun Anda sendiri.');
        }

        $this->users->update($userId, [
            'name'  => trim((string) $data['name']),
            'email' => strtolower(trim((string) $data['email'])),
            'phone' => $this->nullable($data['phone'] ?? null),
        ]);

        $this->users->updatePrivileged($userId, [
            'role'      => $role->value,
            'agency_id' => $agencyId,
            'is_active' => ((string) ($data['is_active'] ?? '1')) === '1' ? 1 : 0,
        ]);

        $this->activity->log(ActivityAction::UserUpdated, 'user', $userId, 'Memperbarui akun ' . $user->email, (int) $actor->id);

        /** @var User $fresh */
        $fresh = $this->users->find($userId);

        return $fresh;
    }

    public function setActive(User $actor, int $userId, bool $active): void
    {
        $user = $this->users->find($userId);

        if ($user === null) {
            throw new BusinessRuleException('Pengguna tidak ditemukan.');
        }

        if ((int) $user->id === (int) $actor->id) {
            throw new AccessDeniedException('Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $this->users->updatePrivileged($userId, ['is_active' => $active ? 1 : 0]);

        $this->activity->log(
            $active ? ActivityAction::AccountActivated : ActivityAction::AccountDeactivated,
            'user',
            $userId,
            ($active ? 'Mengaktifkan ' : 'Menonaktifkan ') . $user->email,
            (int) $actor->id,
        );
    }

    /**
     * Reset kata sandi oleh Admin. Mengembalikan kata sandi sementara
     * yang hanya ditampilkan satu kali kepada Admin.
     */
    public function resetPassword(User $actor, int $userId): string
    {
        $user = $this->users->find($userId);

        if ($user === null) {
            throw new BusinessRuleException('Pengguna tidak ditemukan.');
        }

        $temporary = $this->randomPassword();

        $this->users->update($userId, ['password_hash' => $this->auth->hashPassword($temporary)]);

        $this->activity->log(ActivityAction::UserPasswordReset, 'user', $userId, 'Reset kata sandi untuk ' . $user->email, (int) $actor->id);

        return $temporary;
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return array{items: list<User>, total: int}
     */
    public function paginate(array $filters, int $perPage, int $page): array
    {
        $builder = $this->users->orderBy('created_at', 'DESC');

        if (! empty($filters['role'])) {
            $builder->where('role', (string) $filters['role']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '' && $filters['is_active'] !== null) {
            $builder->where('is_active', (int) $filters['is_active']);
        }

        if (! empty($filters['agency_id'])) {
            $builder->where('agency_id', (int) $filters['agency_id']);
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $builder->groupStart()
                ->like('name', $search)
                ->orLike('email', $search)
                ->orLike('phone', $search)
                ->groupEnd();
        }

        $total = $builder->countAllResults(false);

        /** @var list<User> $items */
        $items = $builder->limit($perPage, max(0, ($page - 1) * $perPage))->findAll();

        return ['items' => $items, 'total' => $total];
    }

    /**
     * Daftar Pemda beserta nama instansinya (untuk panel admin).
     *
     * @return list<array<string, mixed>>
     */
    public function pemdaWithAgency(): array
    {
        return $this->users->pemdaWithAgency();
    }

    /**
     * Statistik akun untuk dasbor admin.
     *
     * @return array{total: int, active: int, per_role: array<string, int>}
     */
    public function stats(): array
    {
        return [
            'total'    => $this->users->countTotal(),
            'active'   => $this->users->countActive(),
            'per_role' => $this->users->countsByRole(),
        ];
    }

    private function resolveRole(mixed $value): UserRole
    {
        $role = UserRole::tryFrom((string) $value);

        if ($role === null) {
            throw new BusinessRuleException('Peran pengguna tidak dikenal.');
        }

        return $role;
    }

    private function resolveAgencyId(UserRole $role, mixed $value): ?int
    {
        if ($role !== UserRole::Pemda) {
            return null;
        }

        $agencyId = is_numeric($value) ? (int) $value : 0;

        if ($agencyId <= 0 || $this->agencies->find($agencyId) === null) {
            throw new BusinessRuleException('Akun Pemda wajib terhubung ke satu instansi yang valid.');
        }

        return $agencyId;
    }

    private function randomPassword(): string
    {
        $alphabet = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $max      = strlen($alphabet) - 1;
        $password = '';

        for ($i = 0; $i < 12; $i++) {
            $password .= $alphabet[random_int(0, $max)];
        }

        return $password;
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
