<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Menambahkan header keamanan pada setiap response.
 *
 * Content-Security-Policy dikelola terpisah oleh `Config\App::$CSPEnabled`
 * dan `Config\ContentSecurityPolicy`.
 */
final class SecurityHeadersFilter implements FilterInterface
{
    /**
     * @param array<string, string>|null $arguments
     */
    public function before(RequestInterface $request, $arguments = null): ?ResponseInterface
    {
        return null;
    }

    /**
     * @param array<string, string>|null $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
        $response->setHeader('X-Content-Type-Options', 'nosniff');
        $response->setHeader('X-Frame-Options', 'DENY');
        $response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->setHeader(
            'Permissions-Policy',
            'geolocation=(self), camera=(), microphone=(), payment=(), usb=(), magnetometer=(), gyroscope=()',
        );
        $response->setHeader('Cross-Origin-Opener-Policy', 'same-origin');
        $response->setHeader('Cross-Origin-Resource-Policy', 'same-origin');
        $response->setHeader('X-Permitted-Cross-Domain-Policies', 'none');

        if ($request->isSecure()) {
            $response->setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
    }
}
