<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Hanya untuk tamu (belum masuk). Pengguna yang sudah masuk diarahkan
 * ke dashboard sesuai perannya.
 */
final class GuestFilter implements FilterInterface
{
    /**
     * @param array<string, string>|null $arguments
     */
    public function before(RequestInterface $request, $arguments = null): ?ResponseInterface
    {
        $auth = service('authService');
        $user = $auth->user();

        if ($user === null) {
            return null;
        }

        return redirect()->to(url_to($user->role()->dashboardRoute()));
    }

    /**
     * @param array<string, string>|null $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
