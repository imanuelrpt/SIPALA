<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\User;
use App\Enums\ActivityAction;
use App\Enums\UserRole;
use App\Exceptions\BusinessRuleException;
use App\Models\UserModel;
use Config\Sipala;

/**
 * Autentikasi berbasis sesi.
 *
 * - Password disimpan dengan password_hash(PASSWORD_DEFAULT).
 * - ID sesi diregenerasi saat login (mencegah session fixation).
 * - Sesi dihancurkan sepenuhnya saat logout.
 * - Akun nonaktif tidak dapat login.
 */
final class AuthService
{
    private const SESSION_USER_ID   = 'auth_user_id';
    private const SESSION_LAST_SEEN = 'auth_last_seen';

    private ?User $cachedUser = null;

    private bool $resolved = false;

    public function __construct(
        private readonly UserModel $users,
        private readonly LoginThrottleService $throttle,
        private readonly ActivityLogService $activity,
        private readonly Sipala $config,
    ) {
    }

    /**
     * Mencoba login. Mengembalikan null bila gagal (pesan tetap generik).
     */
    public function attempt(string $email, string $password): ?User
    {
        $email = strtolower(trim($email));
        $ip    = $this->clientIp();

        if ($this->throttle->tooManyAttempts($email, $ip)) {
            $this->activity->log(ActivityAction::LoginFailed, 'user', null, 'Diblokir sementara (terlalu banyak percobaan)');

            return null;
        }

        $user = $this->users->findActiveByEmail($email);

        if ($user === null || ! $this->verifyPassword($user, $password)) {
            $this->throttle->recordFailure($email, $ip);
            $this->activity->log(ActivityAction::LoginFailed, 'user', null, 'Kredensial tidak cocok', $user?->id);

            return null;
        }

        if (! $user->isActive()) {
            $this->activity->log(ActivityAction::LoginFailed, 'user', $user->id, 'Akun nonaktif');

            return null;
        }

        if ($this->passwordNeedsRehash($user, $password)) {
            $this->users->update($user->id, ['password_hash' => $this->hashPassword($password)]);
        }

        $this->throttle->clear($email, $ip);
        $this->login($user);

        return $user;
    }

    public function login(User $user): void
    {
        $session = session();

        // Mencegah session fixation.
        $session->regenerate(true);

        $session->set([
            self::SESSION_USER_ID   => (int) $user->id,
            self::SESSION_LAST_SEEN => time(),
            'auth_role'             => $user->role()->value,
        ]);

        $this->cachedUser = $user;
        $this->resolved   = true;

        $this->users->touchLastLogin((int) $user->id);
        $this->activity->log(ActivityAction::LoginSucceeded, 'user', (int) $user->id, 'Login berhasil', (int) $user->id);
    }

    public function logout(): void
    {
        $user = $this->user();

        if ($user !== null) {
            $this->activity->log(ActivityAction::Logout, 'user', (int) $user->id, 'Logout', (int) $user->id);
        }

        $session = session();
        $session->remove([self::SESSION_USER_ID, self::SESSION_LAST_SEEN, 'auth_role']);
        $session->destroy();

        $this->cachedUser = null;
        $this->resolved   = true;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function user(): ?User
    {
        if ($this->resolved) {
            return $this->cachedUser;
        }

        $this->resolved = true;

        $session = session();
        $userId  = $session->get(self::SESSION_USER_ID);

        if (! is_numeric($userId)) {
            return null;
        }

        if ($this->sessionExpired()) {
            $this->logout();

            return null;
        }

        $user = $this->users->find((int) $userId);

        if ($user === null || ! $user->isActive()) {
            $session->remove([self::SESSION_USER_ID, self::SESSION_LAST_SEEN, 'auth_role']);

            return null;
        }

        $session->set(self::SESSION_LAST_SEEN, time());
        $this->cachedUser = $user;

        return $user;
    }

    public function id(): ?int
    {
        $id = $this->user()?->id;

        return $id === null ? null : (int) $id;
    }

    public function role(): ?UserRole
    {
        return $this->user()?->role();
    }

    /**
     * @param list<UserRole> $roles
     */
    public function hasRole(UserRole ...$roles): bool
    {
        $user = $this->user();

        return $user !== null && $user->hasRole(...$roles);
    }

    public function hashPassword(string $plain): string
    {
        $hash = password_hash($plain, PASSWORD_DEFAULT);

        if ($hash === false) {
            throw new BusinessRuleException('Gagal memproses kata sandi.');
        }

        return $hash;
    }

    public function verifyPassword(User $user, string $plain): bool
    {
        $hash = (string) ($user->password_hash ?? '');

        return $hash !== '' && password_verify($plain, $hash);
    }

    private function passwordNeedsRehash(User $user, string $plain): bool
    {
        $hash = (string) ($user->password_hash ?? '');

        return $hash !== '' && password_needs_rehash($hash, PASSWORD_DEFAULT);
    }

    /**
     * Ganti kata sandi dengan verifikasi kata sandi lama.
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (! $this->verifyPassword($user, $currentPassword)) {
            throw new BusinessRuleException('Kata sandi saat ini tidak sesuai.');
        }

        if ($currentPassword === $newPassword) {
            throw new BusinessRuleException('Kata sandi baru harus berbeda dari kata sandi saat ini.');
        }

        $this->users->update((int) $user->id, ['password_hash' => $this->hashPassword($newPassword)]);

        session()->regenerate(true);

        $this->activity->log(
            ActivityAction::PasswordChanged,
            'user',
            (int) $user->id,
            'Pengguna mengubah kata sandi sendiri',
            (int) $user->id,
        );
    }

    private function sessionExpired(): bool
    {
        $lastSeen = session()->get(self::SESSION_LAST_SEEN);

        if (! is_numeric($lastSeen)) {
            return false;
        }

        $idleSeconds = $this->config->sessionIdleMinutes * 60;

        return (time() - (int) $lastSeen) > $idleSeconds;
    }

    private function clientIp(): string
    {
        $request = service('request');

        return $request instanceof \CodeIgniter\HTTP\IncomingRequest ? $request->getIPAddress() : '0.0.0.0';
    }
}
