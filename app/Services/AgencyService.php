<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\Agency;
use App\Entities\User;
use App\Enums\ActivityAction;
use App\Exceptions\BusinessRuleException;
use App\Models\AgencyModel;
use App\Models\CategoryModel;

final class AgencyService
{
    public function __construct(
        private readonly AgencyModel $agencies,
        private readonly CategoryModel $categories,
        private readonly ActivityLogService $activity,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(User $actor, array $data): Agency
    {
        $id = $this->agencies->insertPrivileged([
            'name'        => trim((string) $data['name']),
            'description' => $this->nullable($data['description'] ?? null),
            'is_active'   => ((string) ($data['is_active'] ?? '1')) === '1' ? 1 : 0,
        ]);

        if ((int) $id <= 0) {
            throw new BusinessRuleException('Gagal menyimpan instansi.');
        }

        $this->activity->log(ActivityAction::AgencyCreated, 'agency', (int) $id, 'Instansi: ' . trim((string) $data['name']), (int) $actor->id);

        /** @var Agency $agency */
        $agency = $this->agencies->find((int) $id);

        return $agency;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(User $actor, int $id, array $data): Agency
    {
        $agency = $this->agencies->find($id);

        if ($agency === null) {
            throw new BusinessRuleException('Instansi tidak ditemukan.');
        }

        $this->agencies->update($id, [
            'name'        => trim((string) $data['name']),
            'description' => $this->nullable($data['description'] ?? null),
        ]);

        $this->agencies->updatePrivileged($id, [
            'is_active' => ((string) ($data['is_active'] ?? '1')) === '1' ? 1 : 0,
        ]);

        $this->activity->log(ActivityAction::AgencyUpdated, 'agency', $id, 'Instansi: ' . trim((string) $data['name']), (int) $actor->id);

        /** @var Agency $fresh */
        $fresh = $this->agencies->find($id);

        return $fresh;
    }

    public function delete(User $actor, int $id): void
    {
        $agency = $this->agencies->find($id);

        if ($agency === null) {
            throw new BusinessRuleException('Instansi tidak ditemukan.');
        }

        if ($this->categories->where('agency_id', $id)->countAllResults() > 0) {
            throw new BusinessRuleException('Instansi masih dipakai oleh kategori. Nonaktifkan saja instansi ini.');
        }

        $this->agencies->delete($id);
        $this->activity->log(ActivityAction::AgencyUpdated, 'agency', $id, 'Menghapus instansi: ' . (string) $agency->name, (int) $actor->id);
    }

    /**
     * @return list<Agency>
     */
    public function all(): array
    {
        /** @var list<Agency> $agencies */
        $agencies = $this->agencies->orderBy('name', 'ASC')->findAll();

        return $agencies;
    }

    /**
     * @return list<Agency>
     */
    public function active(): array
    {
        return $this->agencies->activeOptions();
    }

    /**
     * @return array<int, string>
     */
    public function options(bool $onlyActive = true): array
    {
        return $this->agencies->optionList($onlyActive);
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}