<?php

declare(strict_types=1);

namespace App\Controllers\Concerns;

use App\Validation\ComplaintRules;

/**
 * Pembacaan filter daftar aduan dari query string.
 *
 * Nilai hanya diteruskan ke model bila lolos aturan ComplaintRules::filters,
 * sehingga parameter yang tidak sah otomatis diabaikan (bukan diteruskan
 * ke kueri).
 */
trait ReadsComplaintFilters
{
    /**
     * @return array<string, mixed>
     */
    protected function complaintListFilters(?int $forceUserId = null): array
    {
        $rules = new ComplaintRules();

        $raw = [
            'status'      => $this->request->getGet('status'),
            'category_id' => $this->request->getGet('category_id'),
            'agency_id'   => $this->request->getGet('agency_id'),
            'date_from'   => $this->request->getGet('date_from'),
            'date_to'     => $this->request->getGet('date_to'),
            'search'      => $this->request->getGet('search'),
        ];

        $data = [];

        foreach ($raw as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $data[$field] = is_string($value) ? trim($value) : $value;
        }

        if ($data !== []) {
            $validation = service('validation');
            $validation->reset();
            $validation->setRules($rules->filters, $rules->messages);

            if (! $validation->run($data)) {
                foreach (array_keys($validation->getErrors()) as $field) {
                    unset($data[$field]);
                }
            }
        }

        if ($forceUserId !== null) {
            $data['user_id'] = $forceUserId;
        }

        return $data;
    }

    /**
     * Nomor halaman dari query string (aman dari nilai negatif/non-angka).
     */
    protected function currentPage(): int
    {
        $page = $this->request->getGet('page');

        return is_numeric($page) ? max(1, (int) $page) : 1;
    }
}
