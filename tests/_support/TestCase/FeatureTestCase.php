<?php

declare(strict_types=1);

namespace Tests\Support\TestCase;

use App\Enums\ComplaintStatus;
use App\Exceptions\AccessDeniedException;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Security;
use Config\Services;

/**
 * Basis pengujian fitur SIPALA.
 *
 * Menyediakan:
 *  - skema database yang selalu bersih untuk tiap tes (`$refresh`);
 *  - pembuat data uji minimal (instansi, kategori, pengguna, aduan);
 *  - token CSRF yang valid;
 *  - pembantu pemeriksaan akses 403.
 */
abstract class FeatureTestCase extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    /**
     * Kata sandi standar akun uji.
     */
    public const PASSWORD = 'Sipala#2026';

    /**
     * Skema dibuat ulang setiap tes agar hasil tes tidak saling memengaruhi.
     */
    protected $refresh = true;

    /**
     * Jalankan migrasi aplikasi saja (bukan migrasi bawaan framework).
     *
     * @var string
     */
    protected $namespace = 'App';

    /**
     * Service container bersifat statis antar tes. Tanpa reset, AuthService
     * akan menyimpan pengguna dari tes sebelumnya (cache `cachedUser`).
     *
     * @var list<string>
     */
    protected $setUpMethods = [
        'resetServices',
        'mockCache',
        'mockEmail',
        'mockSession',
    ];

    // -----------------------------------------------------------------
    // CSRF
    // -----------------------------------------------------------------

    /**
     * Token CSRF yang sah untuk permintaan berikutnya.
     *
     * @return array<string, string>
     */
    protected function csrf(): array
    {
        return [config(Security::class)->tokenName => (string) service('security')->getHash()];
    }

    // -----------------------------------------------------------------
    // Data uji
    // -----------------------------------------------------------------

    /**
     * @param array<string, mixed> $overrides
     */
    protected function makeAgency(array $overrides = []): int
    {
        $this->db->table('agencies')->insert(array_merge([
            'name'       => 'Dinas ' . bin2hex(random_bytes(5)),
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], $overrides));

        return (int) $this->db->insertID();
    }

    /**
     * @param array<string, mixed> $overrides
     */
    protected function makeCategory(int $agencyId, array $overrides = []): int
    {
        $this->db->table('categories')->insert(array_merge([
            'name'       => 'Kategori ' . bin2hex(random_bytes(5)),
            'agency_id'  => $agencyId,
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], $overrides));

        return (int) $this->db->insertID();
    }

    /**
     * @param array<string, mixed> $overrides
     */
    protected function makeUser(string $role, array $overrides = []): int
    {
        $this->db->table('users')->insert(array_merge([
            'name'          => 'Pengguna Uji',
            'email'         => bin2hex(random_bytes(6)) . '@sipala.test',
            'phone'         => '081234567890',
            'password_hash' => password_hash(self::PASSWORD, PASSWORD_DEFAULT),
            'role'          => $role,
            'agency_id'     => null,
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ], $overrides));

        return (int) $this->db->insertID();
    }

    /**
     * @param array<string, mixed> $overrides
     */
    protected function makeComplaint(int $userId, int $categoryId, int $agencyId, array $overrides = []): int
    {
        $this->db->table('complaints')->insert(array_merge([
            'ticket_code'   => $this->ticketCode(),
            'user_id'       => $userId,
            'category_id'   => $categoryId,
            'agency_id'     => $agencyId,
            'title'         => 'Jalan berlubang membahayakan pengendara',
            'description'   => 'Terdapat lubang besar di tengah jalan dan sudah memakan korban.',
            'incident_date' => date('Y-m-d', strtotime('-1 day')),
            'latitude'      => -6.9175,
            'longitude'     => 107.6191,
            'status'        => ComplaintStatus::Menunggu->value,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ], $overrides));

        return (int) $this->db->insertID();
    }

    protected function ticketCode(): string
    {
        return 'SIP-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }

    /**
     * Payload form pengaduan yang valid.
     *
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    protected function complaintPayload(int $categoryId, array $overrides = []): array
    {
        return array_merge([
            'title'          => 'Lampu jalan mati sepanjang gang',
            'category_id'    => $categoryId,
            'description'    => 'Lampu penerangan jalan mati sejak dua minggu lalu sehingga gang sangat gelap.',
            'incident_date'  => date('Y-m-d', strtotime('-2 day')),
            'latitude'       => '-6.9175',
            'longitude'      => '107.6191',
            'address_detail' => 'Jl. Merdeka No. 10',
            'village'        => 'Citarum',
            'district'       => 'Bandung Wetan',
            'city'           => 'Kota Bandung',
            'hide_identity'  => '0',
        ], $overrides);
    }

    /**
     * Data sesi pengguna yang sudah masuk.
     *
     * @return array<string, mixed>
     */
    protected function sessionFor(int $userId, string $role): array
    {
        return [
            'auth_user_id'   => $userId,
            'auth_last_seen' => time(),
            'auth_role'      => $role,
        ];
    }

    // -----------------------------------------------------------------
    // Pembantu akses
    // -----------------------------------------------------------------

    /**
     * Membaca satu nilai kolom mentah (tanpa konversi), termasuk NULL.
     *
     * `grabFromDatabase()` mengembalikan `false` untuk kolom NULL sehingga
     * tidak dapat dipakai membedakan NULL dari nilai lain. Pembantu ini
     * mengembalikan nilai asli (atau null bila baris tidak ada).
     *
     * @param array<string, mixed> $where
     */
    protected function dbValue(string $table, string $column, array $where): mixed
    {
        $row = $this->db->table($table)
            ->select($column)
            ->where($where)
            ->get()
            ->getRowArray();

        return $row === null ? null : $row[$column];
    }

    /**
     * Menyiapkan klien uji sebagai pengguna tertentu.
     *
     * Service container bersifat statis; AuthService menyimpan pengguna yang
     * sudah diambil (cache `cachedUser`). Agar beberapa permintaan sebagai
     * pengguna berbeda dalam satu tes tetap benar, instansi AuthService baru
     * disuntikkan sebelum setiap permintaan.
     */
    protected function asUser(int $userId, string $role): static
    {
        Services::injectMock('authService', single_service('authService'));

        return $this->withSession($this->sessionFor($userId, $role));
    }

    /**
     * Memastikan rute mengembalikan status HTTP tertentu.
     *
     * Exception AccessDeniedException/PageNotFoundException memang dilempar
     * oleh filter peran maupun pengontrol dan diubah menjadi halaman 403/404
     * oleh ExceptionHandler pada permintaan HTTP sungguhan; pada pengujian
     * keduanya diperiksa.
     *
     * @param array<string, mixed> $params
     */
    private function assertRequestStatus(
        string $method,
        string $uri,
        int $userId,
        string $role,
        array $params,
        int $expected,
    ): void {
        try {
            $client = $this->asUser($userId, $role);

            $result = $method === 'post'
                ? $client->post($uri, array_merge($this->csrf(), $params))
                : $client->get($uri);
        } catch (AccessDeniedException|PageNotFoundException $e) {
            $this->assertSame(
                $expected,
                $e->getCode(),
                $e::class . ' harus berstatus ' . $expected . '.',
            );

            return;
        }

        $result->assertStatus($expected);
    }

    /**
     * Memastikan rute mengembalikan 403.
     */
    protected function assertForbidden(string $uri, int $userId, string $role): void
    {
        $this->assertRequestStatus('get', $uri, $userId, $role, [], 403);
    }

    /**
     * Memastikan permintaan POST ditolak dengan 403.
     *
     * @param array<string, mixed> $params
     */
    protected function assertForbiddenPost(string $uri, int $userId, string $role, array $params = []): void
    {
        $this->assertRequestStatus('post', $uri, $userId, $role, $params, 403);
    }

    /**
     * Memastikan rute mengembalikan 404.
     */
    protected function assertNotFound(string $uri, int $userId, string $role): void
    {
        $this->assertRequestStatus('get', $uri, $userId, $role, [], 404);
    }
}
