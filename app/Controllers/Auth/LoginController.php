<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Validation\AuthRules;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Formulir dan pemrosesan login.
 *
 * Pembatasan percobaan login ditangani App\Services\LoginThrottleService;
 * pesan kesalahan selalu generik agar tidak membocorkan keberadaan akun.
 */
final class LoginController extends BaseController
{
    public function form(): string
    {
        return view('auth/masuk', $this->withLayout([
            'title'  => lang('App.login'),
            'errors' => session()->getFlashdata('errors') ?? [],
        ]));
    }

    public function attempt(): RedirectResponse
    {
        $rules = new AuthRules();

        if (! $this->validate($rules->login, $rules->messages)) {
            return $this->backWithErrors(route_to('auth.login'));
        }

        $email    = (string) $this->request->getPost('email');
        $password = (string) $this->request->getPost('password');

        $user = $this->auth->attempt($email, $password);

        if ($user === null) {
            return $this->errorRedirect(
                route_to('auth.login'),
                'Email atau kata sandi tidak sesuai, atau akun Anda sementara dibatasi. Silakan coba lagi.',
            );
        }

        return redirect()
            ->to(url_to($user->role()->dashboardRoute()))
            ->with('success', 'Selamat datang kembali, ' . $user->displayName() . '.');
    }
}
