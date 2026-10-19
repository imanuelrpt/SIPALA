<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\ComplaintStatusHistory;
use CodeIgniter\Model;

class ComplaintStatusHistoryModel extends Model
{
    protected $table            = 'complaint_status_histories';
    protected $primaryKey       = 'id';
    protected $returnType       = ComplaintStatusHistory::class;
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = '';

    /** @var list<string> */
    protected $allowedFields = [
        'complaint_id',
        'changed_by',
        'from_status',
        'to_status',
        'note',
    ];

    /**
     * @return list<ComplaintStatusHistory>
     */
    public function forComplaint(int $complaintId): array
    {
        /** @var list<ComplaintStatusHistory> $histories */
        $histories = $this->where('complaint_id', $complaintId)
            ->orderBy('created_at', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        return $histories;
    }

    /**
     * Riwayat status beserta nama dan peran pengubah (read-model untuk timeline).
     *
     * @return list<array<string, mixed>>
     */
    public function forComplaintWithActor(int $complaintId): array
    {
        return $this->db->table($this->table)
            ->select('complaint_status_histories.*, users.name AS actor_name, users.role AS actor_role')
            ->join('users', 'users.id = complaint_status_histories.changed_by', 'left')
            ->where('complaint_status_histories.complaint_id', $complaintId)
            ->orderBy('complaint_status_histories.created_at', 'ASC')
            ->orderBy('complaint_status_histories.id', 'ASC')
            ->get()
            ->getResultArray();
    }
}
