<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Entities\User;
use App\Exceptions\BusinessRuleException;
use App\Services\CategoryService;
use App\Validation\CategoryRules;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Master data kategori aduan.
 */
final class CategoryController extends BaseController
{
    public function index(): string
    {
        return view('admin/kategori/index', $this->withLayout([
            'title'      => lang('App.categories'),
            'categories' => service('categoryService')->withAgency(false),
            'agencies'   => service('agencyService')->options(false),
            'errors'     => session()->getFlashdata('errors') ?? [],
        ]));
    }

    public function store(): RedirectResponse
    {
        $rules = new CategoryRules();

        if (! $this->validate($rules->store, $rules->messages)) {
            return $this->backWithErrors(route_to('admin.categories.index'));
        }

        /** @var CategoryService $categories */
        $categories = service('categoryService');

        try {
            $categories->create($this->actor(), $this->payload());
        } catch (BusinessRuleException $e) {
            return $this->errorRedirect(route_to('admin.categories.index'), $e->getMessage());
        }

        return $this->successRedirect(route_to('admin.categories.index'), 'Kategori berhasil ditambahkan.');
    }

    public function update(int $id): RedirectResponse
    {
        $rules = new CategoryRules();

        if (! $this->validate($rules->store, $rules->messages)) {
            return $this->backWithErrors(route_to('admin.categories.index'));
        }

        /** @var CategoryService $categories */
        $categories = service('categoryService');

        try {
            $categories->update($this->actor(), $id, $this->payload());
        } catch (BusinessRuleException $e) {
            return $this->errorRedirect(route_to('admin.categories.index'), $e->getMessage());
        }

        return $this->successRedirect(route_to('admin.categories.index'), 'Data kategori berhasil diperbarui.');
    }

    public function delete(int $id): RedirectResponse
    {
        /** @var CategoryService $categories */
        $categories = service('categoryService');

        try {
            $categories->delete($this->actor(), $id);
        } catch (BusinessRuleException $e) {
            return $this->errorRedirect(route_to('admin.categories.index'), $e->getMessage());
        }

        return $this->successRedirect(route_to('admin.categories.index'), 'Kategori berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'name'      => (string) $this->request->getPost('name'),
            'agency_id' => $this->request->getPost('agency_id'),
            'is_active' => (string) $this->request->getPost('is_active'),
        ];
    }

    private function actor(): User
    {
        $actor = $this->currentUser;

        if ($actor === null) {
            throw new PageNotFoundException('Pengguna tidak ditemukan.');
        }

        return $actor;
    }
}
