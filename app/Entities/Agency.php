<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int    $id
 * @property string $name
 * @property string|null $description
 * @property bool   $is_active
 */
class Agency extends Entity
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id'        => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * @var list<string>
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public function isActive(): bool
    {
        return (bool) ($this->attributes['is_active'] ?? false);
    }
}
