<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int         $id
 * @property int|null    $user_id
 * @property string      $action
 * @property string|null $entity
 * @property int|null    $entity_id
 * @property string|null $description
 * @property string|null $ip_address
 * @property string|null $user_agent
 */
class ActivityLog extends Entity
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id'        => 'integer',
        'user_id'   => '?integer',
        'entity_id' => '?integer',
    ];

    /**
     * @var list<string>
     */
    protected $dates = ['created_at'];
}
