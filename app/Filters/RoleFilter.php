<?php

declare(strict_types=1);

namespace App\Filters;

use App\Enums\UserRole;
use App\Exceptions\AccessDeniedException;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Membatasi rute berdasarkan peran, contoh: ['filter' => 'role:admin,pemda'].
 */
final class RoleFilter implements FilterInterface
{
    /**
     * @param array<string, string>|null $arguments
     */
    public function before(RequestInterface $request, $arguments = null): ?ResponseInterface
    {
        $user = service('authService')->user();

        if ($user === null) {
            session()->setFlashdata('error', 'Silakan masuk terlebih dahulu.');

            return redirect()->to(site_url('masuk'));
        }

        $allowed = [];

        foreach ((array) $arguments as $argument) {
            $role = UserRole::tryFrom(trim((string) $argument));

            if ($role !== null) {
                $allowed[] = $role;
            }
        }

        if ($allowed === [] || $user->hasRole(...$allowed)) {
            return null;
        }

        throw new AccessDeniedException('Peran akun Anda tidak memiliki akses ke halaman ini.');
    }

    /**
     * @param array<string, string>|null $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
