<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Exceptions\BusinessRuleException;
use App\Services\PasswordResetService;
use App\Validation\AuthRules;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Lupa kata sandi dengan token sekali pakai.
 */
final class PasswordResetController extends BaseController
{
    public function form(): string
    {
        return view('auth/lupa_password', $this->withLayout([
            'title'  => 'Lupa Kata Sandi',
            'errors' => session()->getFlashdata('errors') ?? [],
        ]));
    }

    public function send(): RedirectResponse
    {
        $rules = new AuthRules();

        if (! $this->validate($rules->forgotPassword, $rules->messages)) {
            return $this->backWithErrors(route_to('auth.forgot'));
        }

        /** @var PasswordResetService $passwordResets */
        $passwordResets = service('passwordResetService');

        // Tidak pernah mengungkapkan apakah email terdaftar atau tidak.
        $passwordResets->request((string) $this->request->getPost('email'));

        return redirect()->to(route_to('auth.login'))->with(
            'info',
            'Bila email tersebut terdaftar, kami telah mengirim tautan reset kata sandi. Periksa kotak masuk Anda.',
        );
    }

    public function resetForm(string $token): string
    {
        if (trim($token) === '') {
            throw new PageNotFoundException('Tautan reset tidak valid.');
        }

        return view('auth/reset_password', $this->withLayout([
            'title'  => 'Atur Ulang Kata Sandi',
            'token'  => $token,
            'errors' => session()->getFlashdata('errors') ?? [],
        ]));
    }

    public function reset(): RedirectResponse
    {
        $rules = new AuthRules();

        if (! $this->validate($rules->resetPassword, $rules->messages)) {
            return $this->backWithErrors(route_to('auth.forgot'));
        }

        /** @var PasswordResetService $passwordResets */
        $passwordResets = service('passwordResetService');

        try {
            $passwordResets->reset(
                (string) $this->request->getPost('token'),
                (string) $this->request->getPost('password'),
            );
        } catch (BusinessRuleException $e) {
            return $this->errorRedirect(route_to('auth.forgot'), $e->getMessage());
        }

        return redirect()->to(route_to('auth.login'))
            ->with('success', 'Kata sandi berhasil diperbarui. Silakan masuk dengan kata sandi baru Anda.');
    }
}
