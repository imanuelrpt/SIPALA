<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\ComplaintResponse;
use CodeIgniter\Model;

class ComplaintResponseModel extends Model
{
    protected $table            = 'complaint_responses';
    protected $primaryKey       = 'id';
    protected $returnType       = ComplaintResponse::class;
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = '';

    /** @var list<string> */
    protected $allowedFields = ['complaint_id', 'user_id', 'message'];

    /** @var array<string, string> */
    protected $validationRules = [
        'message' => 'required|min_length[3]|max_length[2000]',
    ];

    /** @var array<string, array<string, string>> */
    protected $validationMessages = [
        'message' => [
            'required'   => 'Tanggapan wajib diisi.',
            'min_length' => 'Tanggapan minimal 3 karakter.',
            'max_length' => 'Tanggapan maksimal 2000 karakter.',
        ],
    ];

    /**
     * @return list<ComplaintResponse>
     */
    public function forComplaint(int $complaintId): array
    {
        /** @var list<ComplaintResponse> $responses */
        $responses = $this->where('complaint_id', $complaintId)->orderBy('created_at', 'ASC')->findAll();

        return $responses;
    }

    /**
     * Tanggapan beserta nama dan peran penulis (read-model untuk timeline).
     *
     * @return list<array<string, mixed>>
     */
    public function forComplaintWithActor(int $complaintId): array
    {
        return $this->db->table($this->table)
            ->select('complaint_responses.*, users.name AS actor_name, users.role AS actor_role')
            ->join('users', 'users.id = complaint_responses.user_id', 'left')
            ->where('complaint_responses.complaint_id', $complaintId)
            ->orderBy('complaint_responses.created_at', 'ASC')
            ->orderBy('complaint_responses.id', 'ASC')
            ->get()
            ->getResultArray();
    }
}
