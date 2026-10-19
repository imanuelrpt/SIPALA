<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\TestCase\FeatureTestCase;

/**
 * Otorisasi kepemilikan aduan (IDOR).
 *
 * Skenario wajib:
 *  - masyarakat tidak dapat membuka aduan milik orang lain;
 *  - Pemda tidak dapat membuka aduan instansi lain.
 *
 * @internal
 */
final class ComplaintAccessTest extends FeatureTestCase
{
    private int $agencyA;

    private int $agencyB;

    private int $categoryA;

    private int $reporter;

    private int $otherReporter;

    private int $pemdaA;

    private int $pemdaB;

    private int $admin;

    private int $complaint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agencyA       = $this->makeAgency();
        $this->agencyB       = $this->makeAgency();
        $this->categoryA     = $this->makeCategory($this->agencyA);
        $this->reporter      = $this->makeUser('masyarakat');
        $this->otherReporter = $this->makeUser('masyarakat');
        $this->pemdaA        = $this->makeUser('pemda', ['agency_id' => $this->agencyA]);
        $this->pemdaB        = $this->makeUser('pemda', ['agency_id' => $this->agencyB]);
        $this->admin         = $this->makeUser('admin');

        $this->complaint = $this->makeComplaint($this->reporter, $this->categoryA, $this->agencyA);
    }

    public function testReporterCanOpenOwnComplaint(): void
    {
        $this->asUser($this->reporter, 'masyarakat')
            ->get('masyarakat/aduan/' . $this->complaint)
            ->assertStatus(200);
    }

    public function testMasyarakatCannotOpenAnotherUsersComplaint(): void
    {
        $this->assertForbidden(
            'masyarakat/aduan/' . $this->complaint,
            $this->otherReporter,
            'masyarakat',
        );
    }

    public function testMasyarakatCannotRespondToAnotherUsersComplaint(): void
    {
        $this->assertForbiddenPost(
            'masyarakat/aduan/' . $this->complaint . '/tanggapan',
            $this->otherReporter,
            'masyarakat',
            ['message' => 'Saya bukan pemilik aduan ini.'],
        );

        $this->assertSame(0, $this->db->table('complaint_responses')->countAllResults());
    }

    public function testPemdaCannotOpenOtherAgencysComplaint(): void
    {
        $this->assertForbidden(
            'pemda/aduan/' . $this->complaint,
            $this->pemdaB,
            'pemda',
        );
    }

    public function testPemdaCannotChangeOtherAgencysComplaintStatus(): void
    {
        $this->assertForbiddenPost(
            'pemda/aduan/' . $this->complaint . '/status',
            $this->pemdaB,
            'pemda',
            ['status' => 'diverifikasi'],
        );

        $this->assertSame('menunggu', $this->grabFromDatabase('complaints', 'status', ['id' => $this->complaint]));
    }

    public function testPemdaCanOpenOwnAgencysComplaint(): void
    {
        $this->asUser($this->pemdaA, 'pemda')
            ->get('pemda/aduan/' . $this->complaint)
            ->assertStatus(200);
    }

    public function testAdminCanOpenAnyComplaint(): void
    {
        $this->asUser($this->admin, 'admin')
            ->get('admin/aduan/' . $this->complaint)
            ->assertStatus(200);
    }

    public function testUnknownComplaintReturns404(): void
    {
        $this->assertNotFound('masyarakat/aduan/999999', $this->reporter, 'masyarakat');
    }
}
