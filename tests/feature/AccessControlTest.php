<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\TestCase\FeatureTestCase;

/**
 * Kontrol akses lintas peran (harus 403).
 *
 * Skenario wajib: "akses lintas peran ditolak (403)".
 *
 * @internal
 */
final class AccessControlTest extends FeatureTestCase
{
    public function testMasyarakatCannotAccessAdminOrPemdaAreas(): void
    {
        $userId = $this->makeUser('masyarakat');

        $this->assertForbidden('admin', $userId, 'masyarakat');
        $this->assertForbidden('admin/pengguna', $userId, 'masyarakat');
        $this->assertForbidden('admin/laporan/ekspor', $userId, 'masyarakat');
        $this->assertForbidden('pemda', $userId, 'masyarakat');
        $this->assertForbidden('pemda/aduan', $userId, 'masyarakat');
    }

    public function testPemdaCannotAccessAdminOrMasyarakatAreas(): void
    {
        $agencyId = $this->makeAgency();
        $userId   = $this->makeUser('pemda', ['agency_id' => $agencyId]);

        $this->assertForbidden('admin', $userId, 'pemda');
        $this->assertForbidden('admin/pengguna', $userId, 'pemda');
        $this->assertForbidden('masyarakat', $userId, 'pemda');
        $this->assertForbidden('masyarakat/aduan', $userId, 'pemda');
    }

    public function testAdminCannotAccessMasyarakatOrPemdaAreas(): void
    {
        $userId = $this->makeUser('admin');

        $this->assertForbidden('masyarakat', $userId, 'admin');
        $this->assertForbidden('pemda', $userId, 'admin');
    }

    public function testEachRoleCanAccessItsOwnDashboard(): void
    {
        $agencyId   = $this->makeAgency();
        $masyarakat = $this->makeUser('masyarakat');
        $pemda      = $this->makeUser('pemda', ['agency_id' => $agencyId]);
        $admin      = $this->makeUser('admin');

        $this->asUser($masyarakat, 'masyarakat')->get('masyarakat')->assertStatus(200);
        $this->asUser($pemda, 'pemda')->get('pemda')->assertStatus(200);
        $this->asUser($admin, 'admin')->get('admin')->assertStatus(200);
    }

    public function testDeactivatedUserLosesAccess(): void
    {
        $userId = $this->makeUser('masyarakat');

        // Akun dinonaktifkan setelah sesi terbentuk.
        $this->db->table('users')->where('id', $userId)->update(['is_active' => 0]);

        $this->asUser($userId, 'masyarakat')
            ->get('masyarakat')
            ->assertRedirectTo('masuk');
    }
}
