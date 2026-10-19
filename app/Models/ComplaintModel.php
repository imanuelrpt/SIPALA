<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\Complaint;
use App\Enums\ComplaintStatus;
use App\Models\Concerns\GuardsPrivilegedFields;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Model;

class ComplaintModel extends Model
{
    use GuardsPrivilegedFields;

    public const REPORT_COLUMNS = 'complaints.*, categories.name AS category_name, '
        . 'agencies.name AS agency_name, users.name AS reporter_name';

    protected $table            = 'complaints';
    protected $primaryKey       = 'id';
    protected $returnType       = Complaint::class;
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = true;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';

    /**
     * Kolom yang boleh diisi dari form aduan.
     *
     * @var list<string>
     */
    protected $allowedFields = [
        'category_id',
        'title',
        'description',
        'incident_date',
        'latitude',
        'longitude',
        'address_detail',
        'village',
        'district',
        'city',
        'hide_identity',
    ];

    /**
     * Kolom istimewa — hanya lewat updatePrivileged()/insertPrivileged().
     *
     * @var list<string>
     */
    protected array $privilegedFields = [
        'ticket_code',
        'user_id',
        'agency_id',
        'status',
        'rejected_reason',
        'rating',
        'rating_comment',
    ];

    /** @var array<string, string> */
    protected $validationRules = [
        'title'       => 'required|min_length[10]|max_length[150]',
        'description' => 'required|min_length[20]',
        'category_id' => 'required|is_natural_no_zero',
    ];

    /** @var array<string, array<string, string>> */
    protected $validationMessages = [
        'title' => [
            'required'   => 'Judul aduan wajib diisi.',
            'min_length' => 'Judul aduan minimal 10 karakter.',
            'max_length' => 'Judul aduan maksimal 150 karakter.',
        ],
        'description' => [
            'required'   => 'Deskripsi aduan wajib diisi.',
            'min_length' => 'Deskripsi aduan minimal 20 karakter.',
        ],
        'category_id' => [
            'required'           => 'Kategori wajib dipilih.',
            'is_natural_no_zero' => 'Kategori tidak valid.',
        ],
    ];

    /**
     * Builder baru (tidak memakai builder bersama milik Model) berisi
     * join relasi dan filter soft delete.
     */
    public function newReportQuery(): BaseBuilder
    {
        /** @var BaseConnection $db */
        $db = $this->db;

        return $db->table($this->table)
            ->select(self::REPORT_COLUMNS)
            ->join('categories', 'categories.id = complaints.category_id', 'left')
            ->join('agencies', 'agencies.id = complaints.agency_id', 'left')
            ->join('users', 'users.id = complaints.user_id', 'left')
            ->where('complaints.deleted_at', null);
    }

    /**
     * Menerapkan filter daftar aduan. Nilai harus sudah divalidasi pemanggil.
     *
     * @param array<string, mixed> $filters
     */
    public function applyFilters(BaseBuilder $builder, array $filters): BaseBuilder
    {
        if (! empty($filters['status'])) {
            $builder->where('complaints.status', (string) $filters['status']);
        }

        if (! empty($filters['category_id'])) {
            $builder->where('complaints.category_id', (int) $filters['category_id']);
        }

        if (! empty($filters['agency_id'])) {
            $builder->where('complaints.agency_id', (int) $filters['agency_id']);
        }

        if (! empty($filters['user_id'])) {
            $builder->where('complaints.user_id', (int) $filters['user_id']);
        }

        if (! empty($filters['date_from'])) {
            $builder->where('complaints.created_at >=', (string) $filters['date_from'] . ' 00:00:00');
        }

        if (! empty($filters['date_to'])) {
            $builder->where('complaints.created_at <=', (string) $filters['date_to'] . ' 23:59:59');
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $builder->groupStart()
                ->like('complaints.ticket_code', $search)
                ->orLike('complaints.title', $search)
                ->orLike('complaints.description', $search)
                ->orLike('complaints.city', $search)
                ->orLike('complaints.district', $search)
                ->groupEnd();
        }

        return $builder;
    }

    public function findReport(int $id): ?Complaint
    {
        $row = $this->newReportQuery()->where('complaints.id', $id)->get()->getRowArray();

        return $row === null ? null : new Complaint($row);
    }

    public function findByTicket(string $ticketCode): ?Complaint
    {
        $row = $this->newReportQuery()
            ->where('complaints.ticket_code', $ticketCode)
            ->get()
            ->getRowArray();

        return $row === null ? null : new Complaint($row);
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return list<Complaint>
     */
    public function findWithRelations(array $filters = [], int $limit = 1000): array
    {
        $rows = $this->applyFilters($this->newReportQuery(), $filters)
            ->orderBy('complaints.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        return array_map(static fn (array $row): Complaint => new Complaint($row), $rows);
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return list<Complaint>
     */
    public function paginateReport(array $filters, int $perPage, int $page): array
    {
        $page  = max(1, $page);
        $rows  = $this->applyFilters($this->newReportQuery(), $filters)
            ->orderBy('complaints.created_at', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        return array_map(static fn (array $row): Complaint => new Complaint($row), $rows);
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function countReport(array $filters): int
    {
        return $this->applyFilters($this->newReportQuery(), $filters)->countAllResults();
    }

    /**
     * @return array<string, int>
     */
    public function statusCounts(?int $agencyId = null): array
    {
        /** @var BaseConnection $db */
        $db = $this->db;

        $builder = $db->table($this->table)
            ->select('status, COUNT(*) AS total')
            ->where('deleted_at', null)
            ->groupBy('status');

        if ($agencyId !== null) {
            $builder->where('agency_id', $agencyId);
        }

        $counts = array_fill_keys(ComplaintStatus::values(), 0);

        foreach ($builder->get()->getResultArray() as $row) {
            $counts[(string) $row['status']] = (int) $row['total'];
        }

        return $counts;
    }

    public function countAllComplaints(?int $agencyId = null): int
    {
        /** @var BaseConnection $db */
        $db = $this->db;

        $builder = $db->table($this->table)->where('deleted_at', null);

        if ($agencyId !== null) {
            $builder->where('agency_id', $agencyId);
        }

        return $builder->countAllResults();
    }

    /**
     * Rata-rata waktu penyelesaian dalam hari.
     */
    public function averageResolutionDays(?int $agencyId = null): float
    {
        /** @var BaseConnection $db */
        $db = $this->db;

        $builder = $db->table($this->table)
            ->select('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) AS hours')
            ->where('deleted_at', null)
            ->where('status', ComplaintStatus::Selesai->value);

        if ($agencyId !== null) {
            $builder->where('agency_id', $agencyId);
        }

        $row   = $builder->get()->getRowArray();
        $hours = $row === null ? null : $row['hours'];

        return $hours === null ? 0.0 : round(((float) $hours) / 24, 1);
    }

    /**
     * @return list<array{name: string, total: int}>
     */
    public function countsByCategory(?int $agencyId = null, int $limit = 10): array
    {
        /** @var BaseConnection $db */
        $db = $this->db;

        $builder = $db->table($this->table)
            ->select('categories.name AS name, COUNT(complaints.id) AS total')
            ->join('categories', 'categories.id = complaints.category_id', 'left')
            ->where('complaints.deleted_at', null)
            ->groupBy('categories.id, categories.name')
            ->orderBy('total', 'DESC')
            ->limit($limit);

        if ($agencyId !== null) {
            $builder->where('complaints.agency_id', $agencyId);
        }

        return array_map(
            static fn (array $row): array => ['name' => (string) $row['name'], 'total' => (int) $row['total']],
            $builder->get()->getResultArray(),
        );
    }

    /**
     * @return list<array{name: string, total: int}>
     */
    public function countsByAgency(int $limit = 10): array
    {
        /** @var BaseConnection $db */
        $db = $this->db;

        $rows = $db->table($this->table)
            ->select('agencies.name AS name, COUNT(complaints.id) AS total')
            ->join('agencies', 'agencies.id = complaints.agency_id', 'left')
            ->where('complaints.deleted_at', null)
            ->groupBy('agencies.id, agencies.name')
            ->orderBy('total', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        return array_map(
            static fn (array $row): array => ['name' => (string) $row['name'], 'total' => (int) $row['total']],
            $rows,
        );
    }

    /**
     * @return list<array{label: string, total: int}>
     */
    public function countsByMonth(int $months = 12, ?int $agencyId = null): array
    {
        /** @var BaseConnection $db */
        $db = $this->db;

        $start = date('Y-m-01', strtotime('-' . ($months - 1) . ' months'));

        $builder = $db->table($this->table)
            ->select("DATE_FORMAT(created_at, '%Y-%m') AS periode, COUNT(*) AS total")
            ->where('deleted_at', null)
            ->where('created_at >=', $start . ' 00:00:00')
            ->groupBy('periode');

        if ($agencyId !== null) {
            $builder->where('agency_id', $agencyId);
        }

        $raw = [];

        foreach ($builder->get()->getResultArray() as $row) {
            $raw[(string) $row['periode']] = (int) $row['total'];
        }

        $labels = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $series = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $key      = date('Y-m', strtotime('-' . $i . ' months'));
            $series[] = [
                'label' => $labels[(int) date('n', strtotime($key . '-01'))] . ' ' . date('y', strtotime($key . '-01')),
                'total' => $raw[$key] ?? 0,
            ];
        }

        return $series;
    }

    public function countByUserSince(int $userId, string $since): int
    {
        /** @var BaseConnection $db */
        $db = $this->db;

        return $db->table($this->table)
            ->where('deleted_at', null)
            ->where('user_id', $userId)
            ->where('created_at >=', $since)
            ->countAllResults();
    }

    /**
     * Aduan "menunggu" milik instansi tertentu (untuk badge dashboard).
     */
    public function countPendingByAgency(int $agencyId): int
    {
        /** @var BaseConnection $db */
        $db = $this->db;

        return $db->table($this->table)
            ->where('deleted_at', null)
            ->where('agency_id', $agencyId)
            ->where('status', ComplaintStatus::Menunggu->value)
            ->countAllResults();
    }
}
