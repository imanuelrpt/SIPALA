<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Exceptions\BusinessRuleException;
use App\Services\UserService;
use App\Validation\AuthRules;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Pendaftaran mandiri. Hanya menghasilkan akun dengan peran Masyarakat.
 */
final class RegisterController extends BaseController
{
    public function form(): string
    {
        if (! $this->sipalaConfig->registrationEnabled) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Pendaftaran mandiri sedang ditutup.');
        }

        return view('auth/daftar', $this->withLayout([
            'title'  => lang('App.register'),
            'errors' => session()->getFlashdata('errors') ?? [],
        ]));
    }

    public function store(): RedirectResponse
    {
        if (! $this->sipalaConfig->registrationEnabled) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Pendaftaran mandiri sedang ditutup.');
        }

        $rules = new AuthRules();

        if (! $this->validate($rules->register, $rules->messages)) {
            return $this->backWithErrors(route_to('auth.register'));
        }

        /** @var UserService $users */
        $users = service('userService');

        try {
            // register() memaksa peran masyarakat; peran dari input tidak pernah dibaca.
            $user = $users->register([
                'name'     => (string) $this->request->getPost('name'),
                'email'    => (string) $this->request->getPost('email'),
                'phone'    => (string) $this->request->getPost('phone'),
                'password' => (string) $this->request->getPost('password'),
            ]);
        } catch (BusinessRuleException $e) {
            return $this->errorRedirect(route_to('auth.register'), $e->getMessage());
        }

        $this->auth->login($user);

        return redirect()
            ->to(url_to('masyarakat.dashboard'))
            ->with('success', 'Akun Anda berhasil dibuat. Selamat datang di SIPALA!');
    }
}
