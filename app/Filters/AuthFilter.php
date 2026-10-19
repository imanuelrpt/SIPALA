<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Membatasi akses hanya untuk pengguna yang sudah masuk.
 *
 * Sesi yang menganggur melewati batas waktu akan dianggap berakhir
 * (lihat App\Services\AuthService::sessionExpired()).
 */
final class AuthFilter implements FilterInterface
{
    /**
     * @param array<string, string>|null $arguments
     */
    public function before(RequestInterface $request, $arguments = null): ?ResponseInterface
    {
        if (service('authService')->check()) {
            return null;
        }

        if ($request instanceof IncomingRequest && $request->isAJAX()) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['status' => 'error', 'message' => 'Sesi Anda telah berakhir. Silakan masuk kembali.']);
        }

        session()->setFlashdata('error', 'Silakan masuk terlebih dahulu untuk mengakses halaman tersebut.');

        return redirect()->to(site_url('masuk'));
    }

    /**
     * @param array<string, string>|null $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
