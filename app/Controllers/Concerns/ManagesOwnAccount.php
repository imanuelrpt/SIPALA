<?php

declare(strict_types=1);

namespace App\Controllers\Concerns;

use App\Exceptions\BusinessRuleException;
use App\Validation\AuthRules;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Halaman "Akun Saya" dan penggantian kata sandi — dipakai ketiga peran.
 */
trait ManagesOwnAccount
{
    public function index(): string
    {
        $user = $this->currentUser;

        if ($user === null) {
            throw new PageNotFoundException('Pengguna tidak ditemukan.');
        }

        $agency = null;

        if ($user->hasAgency()) {
            /** @var array<int, string> $agencies */
            $agencies = service('agencyService')->options(false);
            $agency   = $agencies[$user->agencyId()] ?? null;
        }

        return view('akun/index', $this->withLayout([
            'title'      => lang('App.account'),
            'agencyName' => $agency,
            'errors'     => session()->getFlashdata('errors') ?? [],
        ]));
    }

    public function updatePassword(): RedirectResponse
    {
        $user = $this->currentUser;

        if ($user === null) {
            throw new PageNotFoundException('Pengguna tidak ditemukan.');
        }

        $rules  = new AuthRules();
        $target = route_to($this->accountRouteName());

        if (! $this->validate($rules->changePassword, $rules->messages)) {
            return $this->backWithErrors($target);
        }

        try {
            $this->auth->changePassword(
                $user,
                (string) $this->request->getPost('current_password'),
                (string) $this->request->getPost('password'),
            );
        } catch (BusinessRuleException $e) {
            return $this->errorRedirect($target, $e->getMessage());
        }

        return $this->successRedirect($target, 'Kata sandi berhasil diperbarui.');
    }

    /**
     * Nama rute halaman akun sesuai peran yang sedang aktif.
     */
    abstract protected function accountRouteName(): string;
}
