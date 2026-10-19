<?php

declare(strict_types=1);

namespace App\Entities;

use App\Enums\AttachmentType;
use CodeIgniter\Entity\Entity;

/**
 * @property int    $id
 * @property int    $complaint_id
 * @property int    $uploaded_by
 * @property string $type
 * @property string $file_path
 * @property string $original_name
 * @property string $mime_type
 * @property int    $size
 */
class ComplaintAttachment extends Entity
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id'           => 'integer',
        'complaint_id' => 'integer',
        'uploaded_by'  => 'integer',
        'size'         => 'integer',
    ];

    /**
     * @var list<string>
     */
    protected $dates = ['created_at'];

    public function type(): AttachmentType
    {
        return AttachmentType::tryFrom((string) $this->attributes['type']) ?? AttachmentType::BuktiAduan;
    }

    public function isComplaintEvidence(): bool
    {
        return $this->type() === AttachmentType::BuktiAduan;
    }

    public function isResolutionEvidence(): bool
    {
        return $this->type() === AttachmentType::BuktiPenyelesaian;
    }

    /**
     * URL pengambilan berkas. Selalu melalui controller yang memeriksa hak akses.
     */
    public function url(): string
    {
        return site_url('berkas/' . $this->attributes['id']);
    }
}
