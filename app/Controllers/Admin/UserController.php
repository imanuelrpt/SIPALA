<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Entities\User;
use App\Exceptions\BusinessRuleException;
use App\Services\UserService;
use App\Validation\UserRules;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Manajemen pengguna oleh Admin.
 */
final class UserController extends BaseController
{
    private const PER_PAGE = 15;

    public function index(): string
    {
        $filters = [
            'role'      => $this->request->getGet('role'),
            'is_active' => $this->request->getGet('is_active'),
            'agency_id' => $this->request->getGet('agency_id'),
            'search'    => trim((string) $this->request->getGet('search')),
        ];

        $page   = max(1, (int) ($this->request->getGet('page') ?? 1));
        $result = service('userService')->paginate($filters, self::PER_PAGE, $page);

        $this->initPager($page, self::PER_PAGE, $result['total']);

        return view('admin/pengguna/index', $this->withLayout([
            'title'    => lang('App.users'),
            'items'    => $result['items'],
            'total'    => $result['total'],
            'filters'  => $filters,
            'agencies' => service('agencyService')->options(false),
            'roles'    => \App\Enums\UserRole::options(),
            'pager'    => service('pager'),
        ]));
    }

    public function create(): string
    {
        return view('admin/pengguna/form', $this->withLayout([
            'title'    => 'Tambah Pengguna',
            'user'     => null,
            'agencies' => service('agencyService')->options(false),
            'roles'    => \App\Enums\UserRole::options(),
            'errors'   => session()->getFlashdata('errors') ?? [],
        ]));
    }

    public function store(): RedirectResponse
    {
        $rules = new UserRules();

        $extraRules    = $this->agencyRules((string) $this->request->getPost('role'));
        $rulesToCheck  = array_merge($rules->store, $extraRules);

        if (! $this->validate($rulesToCheck, $rules->messages)) {
            return $this->backWithErrors(route_to('admin.users.create'));
        }

        /** @var UserService $users */
        $users = service('userService');

        try {
            $users->create($this->actor(), $this->payload());
        } catch (BusinessRuleException $e) {
            return $this->errorRedirect(route_to('admin.users.create'), $e->getMessage());
        }

        return $this->successRedirect(route_to('admin.users.index'), 'Pengguna baru berhasil ditambahkan.');
    }

    public function edit(int $id): string
    {
        $user = $this->findUser($id);

        return view('admin/pengguna/form', $this->withLayout([
            'title'    => 'Ubah Pengguna',
            'user'     => $user,
            'agencies' => service('agencyService')->options(false),
            'roles'    => \App\Enums\UserRole::options(),
            'errors'   => session()->getFlashdata('errors') ?? [],
        ]));
    }

    public function update(int $id): RedirectResponse
    {
        $this->findUser($id);

        $rules       = new UserRules();
        $extraRules  = $this->agencyRules((string) $this->request->getPost('role'));
        $rulesToCheck = array_merge($rules->updateFor($id), $extraRules);

        if (! $this->validate($rulesToCheck, $rules->messages)) {
            return $this->backWithErrors(route_to('admin.users.edit', $id));
        }

        /** @var UserService $users */
        $users = service('userService');

        try {
            $users->update($this->actor(), $id, $this->payload());
        } catch (BusinessRuleException $e) {
            return $this->errorRedirect(route_to('admin.users.edit', $id), $e->getMessage());
        }

        return $this->successRedirect(route_to('admin.users.index'), 'Data pengguna berhasil diperbarui.');
    }

    public function activate(int $id): RedirectResponse
    {
        return $this->setActive($id, true);
    }

    public function deactivate(int $id): RedirectResponse
    {
        return $this->setActive($id, false);
    }

    public function resetPassword(int $id): RedirectResponse
    {
        $user = $this->findUser($id);

        /** @var UserService $users */
        $users = service('userService');

        try {
            $temporary = $users->resetPassword($this->actor(), $id);
        } catch (BusinessRuleException $e) {
            return $this->errorRedirect(route_to('admin.users.index'), $e->getMessage());
        }

        // Kata sandi sementara hanya ditampilkan sekali melalui flash data.
        return redirect()->to(route_to('admin.users.edit', $id))
            ->with('success', 'Kata sandi baru untuk ' . $user->email . ': ' . $temporary)
            ->with('warning', 'Sampaikan kata sandi sementara ini kepada pengguna dan minta segera menggantinya.');
    }

    private function setActive(int $id, bool $active): RedirectResponse
    {
        $this->findUser($id);

        /** @var UserService $users */
        $users = service('userService');

        try {
            $users->setActive($this->actor(), $id, $active);
        } catch (\Throwable $e) {
            return $this->errorRedirect(route_to('admin.users.index'), $e->getMessage());
        }

        return $this->successRedirect(
            route_to('admin.users.index'),
            $active ? 'Akun pengguna diaktifkan.' : 'Akun pengguna dinonaktifkan.',
        );
    }

    private function findUser(int $id): User
    {
        $user = model(\App\Models\UserModel::class)->find($id);

        if (! $user instanceof User) {
            throw new PageNotFoundException('Pengguna tidak ditemukan.');
        }

        return $user;
    }

    private function actor(): User
    {
        $actor = $this->currentUser;

        if ($actor === null) {
            throw new PageNotFoundException('Pengguna tidak ditemukan.');
        }

        return $actor;
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'name'      => (string) $this->request->getPost('name'),
            'email'     => (string) $this->request->getPost('email'),
            'phone'     => (string) $this->request->getPost('phone'),
            'role'      => (string) $this->request->getPost('role'),
            'agency_id' => $this->request->getPost('agency_id'),
            'is_active' => (string) $this->request->getPost('is_active'),
            'password'  => (string) $this->request->getPost('password'),
        ];
    }

    /**
     * Instansi wajib diisi hanya untuk peran Pemda.
     *
     * @return array<string, string>
     */
    private function agencyRules(string $role): array
    {
        return $role === \App\Enums\UserRole::Pemda->value
            ? (new UserRules())->agencyRequired
            : [];
    }
}
