<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ActivityAction;
use App\Models\ActivityLogModel;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * Pencatatan audit trail untuk aksi penting.
 *
 * Tidak pernah mencatat kata sandi, token, atau isi berkas.
 */
final class ActivityLogService
{
    public function __construct(private readonly ActivityLogModel $model)
    {
    }

    public function log(
        ActivityAction $action,
        ?string $entity = null,
        ?int $entityId = null,
        ?string $description = null,
        ?int $userId = null,
    ): void {
        $request = service('request');
        $ip      = null;
        $agent   = null;

        if ($request instanceof IncomingRequest) {
            $ip    = $request->getIPAddress();
            $agent = mb_substr((string) $request->getUserAgent(), 0, 255);
        }

        if ($userId === null) {
            $userId = session()->get('auth_user_id');
            $userId = is_numeric($userId) ? (int) $userId : null;
        }

        $this->model->insert([
            'user_id'     => $userId,
            'action'      => $action->value,
            'entity'      => $entity === null ? null : mb_substr($entity, 0, 60),
            'entity_id'   => $entityId,
            'description' => $description === null ? null : mb_substr($description, 0, 500),
            'ip_address'  => $ip,
            'user_agent'  => $agent,
        ]);
    }

    /**
     * Daftar log untuk panel admin.
     *
     * @param array{search?: string|null, action?: string|null, user_id?: int|null} $filters
     *
     * @return array{items: list<\App\Entities\ActivityLog>, total: int}
     */
    public function paginate(array $filters, int $perPage, int $page): array
    {
        return [
            'items' => $this->model->searchLogs($filters, $perPage, $page),
            'total' => $this->model->countLogs($filters),
        ];
    }

    /**
     * @return list<string>
     */
    public function actions(): array
    {
        return $this->model->distinctActions();
    }
}
