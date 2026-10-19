<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\Category;
use App\Entities\User;
use App\Enums\ActivityAction;
use App\Exceptions\BusinessRuleException;
use App\Models\AgencyModel;
use App\Models\CategoryModel;
use App\Models\ComplaintModel;

final class CategoryService
{
    public function __construct(
        private readonly CategoryModel $categories,
        private readonly AgencyModel $agencies,
        private readonly ComplaintModel $complaints,
        private readonly ActivityLogService $activity,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(User $actor, array $data): Category
    {
        $agencyId = $this->resolveAgencyId($data['agency_id'] ?? null);

        $id = $this->categories->insertPrivileged([
            'name'      => trim((string) $data['name']),
            'agency_id' => $agencyId,
            'is_active' => ((string) ($data['is_active'] ?? '1')) === '1' ? 1 : 0,
        ]);

        if ((int) $id <= 0) {
            throw new BusinessRuleException('Gagal menyimpan kategori.');
        }

        $this->activity->log(ActivityAction::CategoryCreated, 'category', (int) $id, 'Kategori: ' . trim((string) $data['name']), (int) $actor->id);

        /** @var Category $category */
        $category = $this->categories->find((int) $id);

        return $category;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(User $actor, int $id, array $data): Category
    {
        $category = $this->categories->find($id);

        if ($category === null) {
            throw new BusinessRuleException('Kategori tidak ditemukan.');
        }

        $agencyId = $this->resolveAgencyId($data['agency_id'] ?? null);

        $this->categories->update($id, ['name' => trim((string) $data['name'])]);
        $this->categories->updatePrivileged($id, [
            'agency_id' => $agencyId,
            'is_active' => ((string) ($data['is_active'] ?? '1')) === '1' ? 1 : 0,
        ]);

        $this->activity->log(ActivityAction::CategoryUpdated, 'category', $id, 'Kategori: ' . trim((string) $data['name']), (int) $actor->id);

        /** @var Category $fresh */
        $fresh = $this->categories->find($id);

        return $fresh;
    }

    public function delete(User $actor, int $id): void
    {
        $category = $this->categories->find($id);

        if ($category === null) {
            throw new BusinessRuleException('Kategori tidak ditemukan.');
        }

        if ($this->complaints->where('category_id', $id)->countAllResults() > 0) {
            throw new BusinessRuleException('Kategori sudah dipakai aduan. Nonaktifkan saja kategori ini.');
        }

        $this->categories->delete($id);
        $this->activity->log(ActivityAction::CategoryUpdated, 'category', $id, 'Menghapus kategori: ' . (string) $category->name, (int) $actor->id);
    }

    private function resolveAgencyId(mixed $value): int
    {
        $agencyId = is_numeric($value) ? (int) $value : 0;

        if ($agencyId <= 0 || $this->agencies->find($agencyId) === null) {
            throw new BusinessRuleException('Instansi penanggung jawab tidak valid.');
        }

        return $agencyId;
    }

    /**
     * Kategori beserta instansi penanggung jawab (untuk panel admin).
     *
     * @return list<\App\Entities\Category>
     */
    public function withAgency(bool $onlyActive = true): array
    {
        return $this->categories->withAgency($onlyActive);
    }

    /**
     * @return array<int, string>
     */
    public function options(bool $onlyActive = true): array
    {
        return $this->categories->optionList($onlyActive);
    }
}
