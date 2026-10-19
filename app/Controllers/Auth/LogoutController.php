<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Logout (POST + CSRF). Sesi dihancurkan sepenuhnya.
 */
final class LogoutController extends BaseController
{
    public function logout(): RedirectResponse
    {
        $this->auth->logout();

        return redirect()->to(site_url('/'))->with('success', 'Anda telah keluar dari aplikasi.');
    }
}
