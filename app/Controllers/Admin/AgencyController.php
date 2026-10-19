<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Exceptions\BusinessRuleException;
use App\Services\AgencyService;
use App\Validation\AgencyRules;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Master data instansi.
 */
final class AgencyController extends BaseController
{
    public function index(): string
    {
        return view('admin/instansi/index', $this->withLayout([
            'title'    => lang('App.agencies'),
            'agencies' => service('agencyService')->all(),
            'errors'   => session()->getFlashdata('errors') ?? [],
        ]));
    }

    public function store(): RedirectResponse
    {
        $rules = new AgencyRules();

        if (! $this->validate($rules->store, $rules->messages)) {
            return $this->backWithErrors(route_to('admin.agencies.index'));
        }

        /** @var AgencyService $agencies */
        $agencies = service('agencyService');

        try {
            $agencies->create($this->actor(), $this->payload());
        } catch (BusinessRuleException $e) {
            return $this->errorRedirect(route_to('admin.agencies.index'), $e->getMessage());
        }

        return $this->successRedirect(route_to('admin.agencies.index'), 'Instansi berhasil ditambahkan.');
    }

    public function update(int $id): RedirectResponse
    {
        $rules = new AgencyRules();

        if (! $this->validate($rules->store, $rules->messages)) {
            return $this->backWithErrors(route_to('admin.agencies.index'));
        }

        /** @var AgencyService $agencies */
        $agencies = service('agencyService');

        try {
            $agencies->update($this->actor(), $id, $this->payload());
        } catch (BusinessRuleException $e) {
            return $this->errorRedirect(route_to('admin.agencies.index'), $e->getMessage());
        }

        return $this->successRedirect(route_to('admin.agencies.index'), 'Data instansi berhasil diperbarui.');
    }

    public function delete(int $id): RedirectResponse
    {
        /** @var AgencyService $agencies */
        $agencies = service('agencyService');

        try {
            $agencies->delete($this->actor(), $id);
        } catch (BusinessRuleException $e) {
            return $this->errorRedirect(route_to('admin.agencies.index'), $e->getMessage());
        }

        return $this->successRedirect(route_to('admin.agencies.index'), 'Instansi berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'name'        => (string) $this->request->getPost('name'),
            'description' => (string) $this->request->getPost('description'),
            'is_active'   => (string) $this->request->getPost('is_active'),
        ];
    }

    private function actor(): \App\Entities\User
    {
        $actor = $this->currentUser;

        if ($actor === null) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Pengguna tidak ditemukan.');
        }

        return $actor;
    }
}
