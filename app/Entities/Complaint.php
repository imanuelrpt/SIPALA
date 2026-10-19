<?php

declare(strict_types=1);

namespace App\Entities;

use App\Enums\ComplaintStatus;
use CodeIgniter\Entity\Entity;

/**
 * @property int         $id
 * @property string      $ticket_code
 * @property int         $user_id
 * @property int         $category_id
 * @property int         $agency_id
 * @property string      $title
 * @property string      $description
 * @property string      $incident_date
 * @property float       $latitude
 * @property float       $longitude
 * @property string|null $address_detail
 * @property string|null $village
 * @property string|null $district
 * @property string|null $city
 * @property bool        $hide_identity
 * @property string      $status
 * @property string|null $rejected_reason
 * @property int|null    $rating
 * @property string|null $rating_comment
 */
class Complaint extends Entity
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id'            => 'integer',
        'user_id'       => 'integer',
        'category_id'   => 'integer',
        'agency_id'     => 'integer',
        'latitude'      => 'float',
        'longitude'     => 'float',
        'hide_identity' => 'boolean',
        'rating'        => '?integer',
    ];

    /**
     * @var list<string>
     */
    protected $dates = ['incident_date', 'created_at', 'updated_at', 'deleted_at'];

    public function status(): ComplaintStatus
    {
        return ComplaintStatus::tryFrom((string) $this->attributes['status']) ?? ComplaintStatus::Menunggu;
    }

    public function isOwnedBy(?int $userId): bool
    {
        return $userId !== null && (int) $this->attributes['user_id'] === $userId;
    }

    public function belongsToAgency(?int $agencyId): bool
    {
        return $agencyId !== null && (int) $this->attributes['agency_id'] === $agencyId;
    }

    public function hideIdentity(): bool
    {
        return (bool) ($this->attributes['hide_identity'] ?? false);
    }

    /**
     * Pelapor hanya boleh mengubah/membatalkan aduan saat masih berstatus menunggu.
     */
    public function isEditableByReporter(): bool
    {
        return $this->status() === ComplaintStatus::Menunggu;
    }

    public function isFinal(): bool
    {
        return $this->status()->isFinal();
    }

    public function hasRating(): bool
    {
        return isset($this->attributes['rating']) && $this->attributes['rating'] !== null;
    }

    /**
     * Koordinat dalam format siap pakai untuk atribut data.
     */
    public function coordinatePair(): string
    {
        return sprintf('%F,%F', (float) $this->attributes['latitude'], (float) $this->attributes['longitude']);
    }

    /**
     * Alamat lengkap gabungan untuk ditampilkan.
     */
    public function fullAddress(): string
    {
        $parts = array_filter([
            trim((string) ($this->attributes['address_detail'] ?? '')),
            trim((string) ($this->attributes['village'] ?? '')),
            trim((string) ($this->attributes['district'] ?? '')),
            trim((string) ($this->attributes['city'] ?? '')),
        ], static fn (string $part): bool => $part !== '');

        return $parts === [] ? 'Lokasi tidak disebutkan' : implode(', ', $parts);
    }

    /**
     * Rentang waktu penyelesaian (hari) bila aduan sudah selesai.
     */
    public function resolutionDays(): ?int
    {
        if ($this->status() !== ComplaintStatus::Selesai) {
            return null;
        }

        $created = $this->attributes['created_at'] ?? null;
        $updated = $this->attributes['updated_at'] ?? null;

        if ($created === null || $updated === null) {
            return null;
        }

        $start = strtotime((string) $created);
        $end   = strtotime((string) $updated);

        if ($start === false || $end === false) {
            return null;
        }

        return max(0, (int) round(($end - $start) / 86400));
    }
}
