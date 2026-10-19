<?php

declare(strict_types=1);

namespace App\Entities;

use App\Enums\ComplaintStatus;
use CodeIgniter\Entity\Entity;

/**
 * @property int         $id
 * @property int         $complaint_id
 * @property int|null    $changed_by
 * @property string|null $from_status
 * @property string      $to_status
 * @property string|null $note
 */
class ComplaintStatusHistory extends Entity
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id'           => 'integer',
        'complaint_id' => 'integer',
        'changed_by'   => '?integer',
    ];

    /**
     * @var list<string>
     */
    protected $dates = ['created_at'];

    public function toStatus(): ComplaintStatus
    {
        return ComplaintStatus::tryFrom((string) $this->attributes['to_status']) ?? ComplaintStatus::Menunggu;
    }

    public function fromStatus(): ?ComplaintStatus
    {
        $value = $this->attributes['from_status'] ?? null;

        return $value === null ? null : ComplaintStatus::tryFrom((string) $value);
    }
}
