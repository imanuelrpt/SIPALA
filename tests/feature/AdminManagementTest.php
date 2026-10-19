<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\UserModel;
use Tests\Support\TestCase\FeatureTestCase;

/**
 * Manajemen pengguna, instansi, kategori, dan disposisi oleh Admin.
 *
 * @internal
 */
final class AdminManagementTest extends FeatureTestCase
{
    private int $adminId;

    /** @var array<string, mixed> */
    private array $adminSession = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminId      = $this->makeUser('admin');
        $this->adminSession = $this->sessionFor($this->adminId, 'admin');
    }

    public function testAdminCreatesPemdaUserBoundToAgency(): void
    {
        $agencyId = $this->makeAgency();

        $result = $this->withSession($this->adminSession)->post('admin/pengguna', array_merge($this->csrf(), [
            'name'      => 'Petugas Instansi',
            'email'     => 'petugas@sipala.test',
            'phone'     => '081200000001',
            'role'      => 'pemda',
            'agency_id' => (string) $agencyId,
            'is_active' => '1',
            'password'  => 'Rahasia12345',
        ]));

        $result->assertRedirect();

        $row = $this->db->table('users')->where('email', 'petugas@sipala.test')->get()->getRowArray();

        $this->assertIsArray($row);
        $this->assertSame('pemda', $row['role']);
        $this->assertSame($agencyId, (int) $row['agency_id']);
        $this->assertSame(1, (int) $row['is_active']);
        $this->assertTrue(password_verify('Rahasia12345', (string) $row['password_hash']));
        $this->seeInDatabase('activity_logs', ['action' => 'pengguna_dibuat']);
    }

    public function testPemdaUserRequiresAgency(): void
    {
        $result = $this->withSession($this->adminSession)->post('admin/pengguna', array_merge($this->csrf(), [
            'name'      => 'Petugas Tanpa Instansi',
            'email'     => 'tanpa.instansi@sipala.test',
            'role'      => 'pemda',
            'is_active' => '1',
            'password'  => 'Rahasia12345',
        ]));

        $result->assertRedirect();
        $this->dontSeeInDatabase('users', ['email' => 'tanpa.instansi@sipala.test']);
    }

    public function testPrivilegedColumnsAreNotMassAssignable(): void
    {
        /** @var UserModel $users */
        $users = model(UserModel::class);

        $id = $users->insert([
            'name'          => 'Percobaan Eskalasi',
            'email'         => 'eskalasi@sipala.test',
            'password_hash' => 'bukan-hash-nyata',
            'role'          => 'admin',
            'agency_id'     => 1,
            'is_active'     => 0,
        ]);

        $this->assertIsNumeric($id);
        $this->assertSame('masyarakat', $this->grabFromDatabase('users', 'role', ['id' => $id]));
        $this->assertNull($this->dbValue('users', 'agency_id', ['id' => $id]));
        $this->assertSame(1, (int) $this->grabFromDatabase('users', 'is_active', ['id' => $id]));
    }

    public function testAdminCannotChangeOwnRole(): void
    {
        $this->withSession($this->adminSession)->post('admin/pengguna/' . $this->adminId, array_merge($this->csrf(), [
            'name'      => 'Admin Utama',
            'email'     => (string) $this->grabFromDatabase('users', 'email', ['id' => $this->adminId]),
            'role'      => 'masyarakat',
            'is_active' => '1',
        ]))->assertRedirect();

        $this->assertSame('admin', $this->grabFromDatabase('users', 'role', ['id' => $this->adminId]));
    }

    public function testAdminCannotDeactivateSelf(): void
    {
        $this->withSession($this->adminSession)
            ->post('admin/pengguna/' . $this->adminId . '/nonaktif', $this->csrf())
            ->assertRedirect();

        $this->assertSame(1, (int) $this->grabFromDatabase('users', 'is_active', ['id' => $this->adminId]));
    }

    public function testAdminCanDeactivateAndReactivateAnotherUser(): void
    {
        $userId = $this->makeUser('masyarakat');

        $this->withSession($this->adminSession)
            ->post('admin/pengguna/' . $userId . '/nonaktif', $this->csrf())
            ->assertRedirect();

        $this->assertSame(0, (int) $this->grabFromDatabase('users', 'is_active', ['id' => $userId]));

        $this->withSession($this->adminSession)
            ->post('admin/pengguna/' . $userId . '/aktif', $this->csrf())
            ->assertRedirect();

        $this->assertSame(1, (int) $this->grabFromDatabase('users', 'is_active', ['id' => $userId]));
    }

    public function testAdminCanResetUserPassword(): void
    {
        $userId = $this->makeUser('masyarakat');
        $before = (string) $this->grabFromDatabase('users', 'password_hash', ['id' => $userId]);

        $this->withSession($this->adminSession)
            ->post('admin/pengguna/' . $userId . '/reset', $this->csrf())
            ->assertRedirect();

        $after = (string) $this->grabFromDatabase('users', 'password_hash', ['id' => $userId]);

        $this->assertNotSame($before, $after);
        $this->seeInDatabase('activity_logs', ['action' => 'password_direset_admin', 'entity_id' => $userId]);
    }

    public function testAdminCreatesAndUpdatesAgency(): void
    {
        $name = 'Dinas Lingkungan Hidup ' . bin2hex(random_bytes(3));

        $this->withSession($this->adminSession)->post('admin/instansi', array_merge($this->csrf(), [
            'name'        => $name,
            'description' => 'Menangani kebersihan dan sampah.',
            'is_active'   => '1',
        ]))->assertRedirect();

        $this->seeInDatabase('agencies', ['name' => $name, 'is_active' => 1]);

        $agencyId = (int) $this->grabFromDatabase('agencies', 'id', ['name' => $name]);

        $this->withSession($this->adminSession)->post('admin/instansi/' . $agencyId, array_merge($this->csrf(), [
            'name'        => $name . ' (diperbarui)',
            'description' => 'Diperbarui.',
            'is_active'   => '0',
        ]))->assertRedirect();

        $this->assertSame(0, (int) $this->grabFromDatabase('agencies', 'is_active', ['id' => $agencyId]));
    }

    public function testAgencyWithCategoriesCannotBeDeleted(): void
    {
        $agencyId = $this->makeAgency();
        $this->makeCategory($agencyId);

        $this->withSession($this->adminSession)
            ->post('admin/instansi/' . $agencyId . '/hapus', $this->csrf())
            ->assertRedirect();

        $this->assertNull($this->dbValue('agencies', 'deleted_at', ['id' => $agencyId]));
    }

    public function testAdminCreatesCategoryAndCategoryUsedByComplaintCannotBeDeleted(): void
    {
        $agencyId = $this->makeAgency();
        $name     = 'Penerangan Jalan ' . bin2hex(random_bytes(3));

        $this->withSession($this->adminSession)->post('admin/kategori', array_merge($this->csrf(), [
            'name'      => $name,
            'agency_id' => (string) $agencyId,
            'is_active' => '1',
        ]))->assertRedirect();

        $this->seeInDatabase('categories', ['name' => $name, 'agency_id' => $agencyId]);

        $categoryId = (int) $this->grabFromDatabase('categories', 'id', ['name' => $name]);
        $reporter   = $this->makeUser('masyarakat');
        $this->makeComplaint($reporter, $categoryId, $agencyId);

        $this->withSession($this->adminSession)
            ->post('admin/kategori/' . $categoryId . '/hapus', $this->csrf())
            ->assertRedirect();

        $this->assertNull($this->dbValue('categories', 'deleted_at', ['id' => $categoryId]));
    }

    public function testAdminCanDispatchComplaintToAnotherAgency(): void
    {
        $agencyA = $this->makeAgency();
        $agencyB = $this->makeAgency();
        $categoryA = $this->makeCategory($agencyA);
        $reporter  = $this->makeUser('masyarakat');
        $complaint = $this->makeComplaint($reporter, $categoryA, $agencyA);

        $this->withSession($this->adminSession)->post('admin/aduan/' . $complaint . '/disposisi', array_merge($this->csrf(), [
            'agency_id' => (string) $agencyB,
            'note'      => 'Ditangani instansi lain.',
        ]))->assertRedirect();

        $this->assertSame($agencyB, (int) $this->grabFromDatabase('complaints', 'agency_id', ['id' => $complaint]));
        $this->seeInDatabase('activity_logs', ['action' => 'aduan_didisposisi']);
    }

    public function testReportExportReturnsCsv(): void
    {
        $agencyId  = $this->makeAgency();
        $category  = $this->makeCategory($agencyId);
        $reporter  = $this->makeUser('masyarakat');
        $this->makeComplaint($reporter, $category, $agencyId);

        $result = $this->withSession($this->adminSession)->get('admin/laporan/ekspor');

        $result->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $result->response()->getHeaderLine('Content-Type'));
        $this->assertStringContainsString('SIP-', $result->getBody() ?? '');
    }
}
