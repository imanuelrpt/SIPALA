<?php

declare(strict_types=1);

namespace App\Entities;

use App\Enums\UserRole;
use CodeIgniter\Entity\Entity;

/**
 * @property int         $id
 * @property string      $name
 * @property string      $email
 * @property string|null $phone
 * @property string      $password_hash
 * @property string      $role
 * @property int|null    $agency_id
 * @property bool        $is_active
 * @property string|null $email_verified_at
 * @property string|null $last_login_at
 */
class User extends Entity
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id'         => 'integer',
        'agency_id'  => '?integer',
        'is_active'  => 'boolean',
    ];

    /**
     * @var list<string>
     */
    protected $dates = [
        'email_verified_at',
        'last_login_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Jangan pernah bocorkan hash password ke tampilan/JSON.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
        'deleted_at',
    ];

    public function role(): UserRole
    {
        return UserRole::tryFrom((string) $this->attributes['role']) ?? UserRole::Masyarakat;
    }

    public function roleLabel(): string
    {
        return $this->role()->label();
    }

    public function isActive(): bool
    {
        return (bool) ($this->attributes['is_active'] ?? false);
    }

    public function isMasyarakat(): bool
    {
        return $this->role() === UserRole::Masyarakat;
    }

    public function isPemda(): bool
    {
        return $this->role() === UserRole::Pemda;
    }

    public function isAdmin(): bool
    {
        return $this->role() === UserRole::Admin;
    }

    /**
     * Benar bila pengguna memiliki salah satu peran yang diberikan.
     */
    public function hasRole(UserRole ...$roles): bool
    {
        return in_array($this->role(), $roles, true);
    }

    public function agencyId(): ?int
    {
        $value = $this->attributes['agency_id'] ?? null;

        return $value === null ? null : (int) $value;
    }

    public function hasAgency(): bool
    {
        return $this->agencyId() !== null;
    }

    public function emailVerified(): bool
    {
        return isset($this->attributes['email_verified_at']) && $this->attributes['email_verified_at'] !== null;
    }

    /**
     * Nama yang aman ditampilkan (fallback bila kosong).
     */
    public function displayName(): string
    {
        $name = trim((string) ($this->attributes['name'] ?? ''));

        return $name === '' ? 'Pengguna' : $name;
    }
}
