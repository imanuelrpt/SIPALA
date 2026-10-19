<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\ComplaintAttachment;
use CodeIgniter\Model;

class ComplaintAttachmentModel extends Model
{
    protected $table            = 'complaint_attachments';
    protected $primaryKey       = 'id';
    protected $returnType       = ComplaintAttachment::class;
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = '';

    /** @var list<string> */
    protected $allowedFields = [
        'complaint_id',
        'uploaded_by',
        'type',
        'file_path',
        'original_name',
        'mime_type',
        'size',
    ];

    /**
     * @return list<ComplaintAttachment>
     */
    public function forComplaint(int $complaintId): array
    {
        /** @var list<ComplaintAttachment> $attachments */
        $attachments = $this->where('complaint_id', $complaintId)->orderBy('id', 'ASC')->findAll();

        return $attachments;
    }

    /**
     * @return list<ComplaintAttachment>
     */
    public function forComplaintByType(int $complaintId, \App\Enums\AttachmentType $type): array
    {
        /** @var list<ComplaintAttachment> $attachments */
        $attachments = $this->where('complaint_id', $complaintId)
            ->where('type', $type->value)
            ->orderBy('id', 'ASC')
            ->findAll();

        return $attachments;
    }

    public function countForComplaint(int $complaintId, ?\App\Enums\AttachmentType $type = null): int
    {
        $builder = $this->where('complaint_id', $complaintId);

        if ($type !== null) {
            $builder->where('type', $type->value);
        }

        return $builder->countAllResults();
    }
}
