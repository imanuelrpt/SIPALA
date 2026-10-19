<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int    $id
 * @property int    $complaint_id
 * @property int    $user_id
 * @property string $message
 */
class ComplaintResponse extends Entity
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id'           => 'integer',
        'complaint_id' => 'integer',
        'user_id'      => 'integer',
    ];

    /**
     * @var list<string>
     */
    protected $dates = ['created_at'];
}
