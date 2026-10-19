<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use DateTimeImmutable;

class AgencySeeder extends Seeder
{
    /**
     * @var list<array{name: string, description: string}>
     */
    private const AGENCIES = [
        [
            'name'        => 'Dinas Pekerjaan Umum dan Penataan Ruang',
            'description' => 'Menangani jalan, jembatan, drainase, dan penerangan jalan umum.',
        ],
        [
            'name'        => 'Dinas Perhubungan',
            'description' => 'Mengelola transportasi umum, rambu lalu lintas, dan marka jalan.',
        ],
        [
            'name'        => 'Dinas Lingkungan Hidup',
            'description' => 'Bertanggung jawab atas persampahan, kebersihan, dan ruang terbuka hijau.',
        ],
        [
            'name'        => 'Dinas Kependudukan dan Pencatatan Sipil',
            'description' => 'Melayani administrasi kependudukan dan pencatatan sipil.',
        ],
        [
            'name'        => 'Dinas Kesehatan',
            'description' => 'Membina fasilitas kesehatan, sanitasi, dan air bersih.',
        ],
    ];

    public function run(): void
    {
        $now  = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $rows = [];

        foreach (self::AGENCIES as $agency) {
            $rows[] = [
                'name'        => $agency['name'],
                'description' => $agency['description'],
                'is_active'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        $this->db->table('agencies')->insertBatch($rows);
    }
}
