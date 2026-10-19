<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use DateTimeImmutable;

class CategorySeeder extends Seeder
{
    /**
     * Kategori dipetakan ke instansi penanggung jawab default.
     *
     * @var list<array{name: string, agency: string}>
     */
    private const CATEGORIES = [
        ['name' => 'Jalan & Jembatan Rusak', 'agency' => 'Dinas Pekerjaan Umum dan Penataan Ruang'],
        ['name' => 'Drainase & Banjir', 'agency' => 'Dinas Pekerjaan Umum dan Penataan Ruang'],
        ['name' => 'Penerangan Jalan Umum', 'agency' => 'Dinas Pekerjaan Umum dan Penataan Ruang'],
        ['name' => 'Transportasi Umum', 'agency' => 'Dinas Perhubungan'],
        ['name' => 'Rambu & Marka Jalan', 'agency' => 'Dinas Perhubungan'],
        ['name' => 'Sampah & Kebersihan', 'agency' => 'Dinas Lingkungan Hidup'],
        ['name' => 'Taman & Ruang Terbuka Hijau', 'agency' => 'Dinas Lingkungan Hidup'],
        ['name' => 'Pelayanan Administrasi Kependudukan', 'agency' => 'Dinas Kependudukan dan Pencatatan Sipil'],
        ['name' => 'Dokumen Kependudukan', 'agency' => 'Dinas Kependudukan dan Pencatatan Sipil'],
        ['name' => 'Fasilitas Kesehatan', 'agency' => 'Dinas Kesehatan'],
    ];

    public function run(): void
    {
        $agencyIds = [];

        foreach ($this->db->table('agencies')->get()->getResultArray() as $row) {
            $agencyIds[(string) $row['name']] = (int) $row['id'];
        }

        $now  = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $rows = [];

        foreach (self::CATEGORIES as $category) {
            $rows[] = [
                'name'       => $category['name'],
                'agency_id'  => $agencyIds[$category['agency']],
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->db->table('categories')->insertBatch($rows);
    }
}
