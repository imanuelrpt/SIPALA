<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use DateTimeImmutable;

/**
 * Akun demo. Kata sandi semua akun: lihat README (hanya untuk lingkungan lokal).
 */
class UserSeeder extends Seeder
{
    public const DEMO_PASSWORD = 'Sipala#2026';

    /**
     * @var list<array{name: string, email: string, phone: string}>
     */
    private const ADMIN = [
        ['name' => 'Administrator SIPALA', 'email' => 'admin@sipala.test', 'phone' => '081100000001'],
    ];

    /**
     * Dua akun Pemda untuk setiap instansi.
     *
     * @var array<string, list<array{name: string, email: string, phone: string}>>
     */
    private const PEMDA = [
        'Dinas Pekerjaan Umum dan Penataan Ruang' => [
            ['name' => 'Rahmat Hidayat', 'email' => 'pupr1@sipala.test', 'phone' => '081200000101'],
            ['name' => 'Siti Nurhaliza', 'email' => 'pupr2@sipala.test', 'phone' => '081200000102'],
        ],
        'Dinas Perhubungan' => [
            ['name' => 'Bagus Prasetyo', 'email' => 'dishub1@sipala.test', 'phone' => '081200000201'],
            ['name' => 'Ratna Kusuma', 'email' => 'dishub2@sipala.test', 'phone' => '081200000202'],
        ],
        'Dinas Lingkungan Hidup' => [
            ['name' => 'Dedi Kurniawan', 'email' => 'dlh1@sipala.test', 'phone' => '081200000301'],
            ['name' => 'Maya Anggraini', 'email' => 'dlh2@sipala.test', 'phone' => '081200000302'],
        ],
        'Dinas Kependudukan dan Pencatatan Sipil' => [
            ['name' => 'Yusuf Maulana', 'email' => 'dukcapil1@sipala.test', 'phone' => '081200000401'],
            ['name' => 'Lestari Wulandari', 'email' => 'dukcapil2@sipala.test', 'phone' => '081200000402'],
        ],
        'Dinas Kesehatan' => [
            ['name' => 'dr. Andini Puspita', 'email' => 'dinkes1@sipala.test', 'phone' => '081200000501'],
            ['name' => 'Hendra Gunawan', 'email' => 'dinkes2@sipala.test', 'phone' => '081200000502'],
        ],
    ];

    /**
     * @var list<array{name: string, email: string, phone: string}>
     */
    private const MASYARAKAT = [
        ['name' => 'Budi Santoso', 'email' => 'warga1@sipala.test', 'phone' => '081300000001'],
        ['name' => 'Ani Maryani', 'email' => 'warga2@sipala.test', 'phone' => '081300000002'],
        ['name' => 'Cahyo Nugroho', 'email' => 'warga3@sipala.test', 'phone' => '081300000003'],
        ['name' => 'Dewi Anggraeni', 'email' => 'warga4@sipala.test', 'phone' => '081300000004'],
        ['name' => 'Eko Prasetyo', 'email' => 'warga5@sipala.test', 'phone' => '081300000005'],
        ['name' => 'Fitri Handayani', 'email' => 'warga6@sipala.test', 'phone' => '081300000006'],
        ['name' => 'Gilang Ramadhan', 'email' => 'warga7@sipala.test', 'phone' => '081300000007'],
        ['name' => 'Hesti Purwanti', 'email' => 'warga8@sipala.test', 'phone' => '081300000008'],
    ];

    public function run(): void
    {
        $agencyIds = [];

        foreach ($this->db->table('agencies')->get()->getResultArray() as $row) {
            $agencyIds[(string) $row['name']] = (int) $row['id'];
        }

        $now  = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $hash = password_hash(self::DEMO_PASSWORD, PASSWORD_DEFAULT);
        $rows = [];

        foreach (self::ADMIN as $admin) {
            $rows[] = [
                'name'          => $admin['name'],
                'email'         => $admin['email'],
                'phone'         => $admin['phone'],
                'password_hash' => $hash,
                'role'          => 'admin',
                'agency_id'     => null,
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        }

        foreach (self::PEMDA as $agencyName => $accounts) {
            foreach ($accounts as $account) {
                $rows[] = [
                    'name'          => $account['name'],
                    'email'         => $account['email'],
                    'phone'         => $account['phone'],
                    'password_hash' => $hash,
                    'role'          => 'pemda',
                    'agency_id'     => $agencyIds[$agencyName],
                    'is_active'     => 1,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }
        }

        foreach (self::MASYARAKAT as $warga) {
            $rows[] = [
                'name'          => $warga['name'],
                'email'         => $warga['email'],
                'phone'         => $warga['phone'],
                'password_hash' => $hash,
                'role'          => 'masyarakat',
                'agency_id'     => null,
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        }

        $this->db->table('users')->insertBatch($rows);
    }
}
