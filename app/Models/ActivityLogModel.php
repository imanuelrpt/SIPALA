<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\ActivityLog;
use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table            = 'activity_logs';
    protected $primaryKey       = 'id';
    protected $returnType       = ActivityLog::class;
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = '';

    /** @var list<string> */
    protected $allowedFields = [
        'user_id',
        'action',
        'entity',
        'entity_id',
        'description',
        'ip_address',
        'user_agent',
    ];

    /**
     * Daftar log dengan pencarian dan paginasi.
     *
     * @param array{search?: string|null, action?: string|null, user_id?: int|null} $filters
     *
     * @return list<ActivityLog>
     */
    public function searchLogs(array $filters, int $perPage, int $page): array
    {
        /** @var list<ActivityLog> $logs */
        $logs = $this->applyLogFilters($filters)
            ->orderBy('created_at', 'DESC')
            ->limit($perPage, max(0, ($page - 1) * $perPage))
            ->findAll();

        return $logs;
    }

    /**
     * @param array{search?: string|null, action?: string|null, user_id?: int|null} $filters
     */
    public function countLogs(array $filters): int
    {
        return $this->applyLogFilters($filters)->countAllResults();
    }

    /**
     * @param array{search?: string|null, action?: string|null, user_id?: int|null} $filters
     *
     * @return $this
     */
    private function applyLogFilters(array $filters): self
    {
        if (! empty($filters['action'])) {
            $this->where('action', (string) $filters['action']);
        }

        if (! empty($filters['user_id'])) {
            $this->where('user_id', (int) $filters['user_id']);
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $this->groupStart()
                ->like('action', $search)
                ->orLike('entity', $search)
                ->orLike('description', $search)
                ->orLike('ip_address', $search)
                ->groupEnd();
        }

        return $this;
    }

    /**
     * @return list<string>
     */
    public function distinctActions(): array
    {
        $rows = $this->select('action')->distinct()->orderBy('action', 'ASC')->findAll();

        return array_map(static fn ($row): string => (string) $row->action, $rows);
    }
}
