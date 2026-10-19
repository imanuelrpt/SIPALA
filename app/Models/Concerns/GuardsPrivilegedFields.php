<?php

declare(strict_types=1);

namespace App\Models\Concerns;

/**
 * Melindungi kolom sensitif (role, status, agency_id, is_active, rating, dst.)
 * dari mass assignment.
 *
 * Kolom tersebut sengaja TIDAK dimasukkan ke `$allowedFields`, sehingga
 * tidak akan pernah terisi dari data mentah request. Perubahan hanya dapat
 * dilakukan melalui metode khusus di bawah ini yang nilai-nilainya sudah
 * divalidasi di lapisan Service.
 */
trait GuardsPrivilegedFields
{
    /**
     * Menyimpan data termasuk kolom istimewa.
     *
     * @param array<string, mixed> $data
     */
    public function updatePrivileged(int|string $id, array $data): bool
    {
        return $this->withPrivilegedFields(fn (): bool => $this->update($id, $data));
    }

    /**
     * Menyisipkan data termasuk kolom istimewa.
     *
     * @param array<string, mixed> $data
     *
     * @return int|string
     */
    public function insertPrivileged(array $data, bool $returnId = true)
    {
        return $this->withPrivilegedFields(fn (): int|string => $this->insert($data, $returnId));
    }

    /**
     * Membuka `$allowedFields` hanya selama callback dijalankan.
     *
     * @template T
     *
     * @param callable(): T $callback
     *
     * @return T
     */
    private function withPrivilegedFields(callable $callback)
    {
        $original = $this->allowedFields;

        $this->allowedFields = array_values(array_unique(
            array_merge($original, $this->privilegedFields),
        ));

        try {
            return $callback();
        } finally {
            $this->allowedFields = $original;
        }
    }
}
