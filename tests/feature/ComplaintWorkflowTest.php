<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\TestCase\FeatureTestCase;

/**
 * Alur pembuatan dan perubahan status aduan.
 *
 * Skenario wajib:
 *  - validasi koordinat lokasi;
 *  - transisi status tidak sah ditolak.
 *
 * @internal
 */
final class ComplaintWorkflowTest extends FeatureTestCase
{
    private int $agencyId;

    private int $categoryId;

    private int $reporter;

    private int $pemda;

    private int $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agencyId   = $this->makeAgency();
        $this->categoryId = $this->makeCategory($this->agencyId);
        $this->reporter   = $this->makeUser('masyarakat');
        $this->pemda      = $this->makeUser('pemda', ['agency_id' => $this->agencyId]);
        $this->admin      = $this->makeUser('admin');
    }

    public function testMasyarakatCanCreateComplaint(): void
    {
        $result = $this->withSession($this->sessionFor($this->reporter, 'masyarakat'))
            ->post('masyarakat/aduan', array_merge($this->csrf(), $this->complaintPayload($this->categoryId)));

        $result->assertRedirect();

        $row = $this->db->table('complaints')->where('user_id', $this->reporter)->get()->getRowArray();

        $this->assertIsArray($row);
        $this->assertSame('menunggu', $row['status']);
        $this->assertSame($this->agencyId, (int) $row['agency_id'], 'Instansi diambil dari kategori, bukan dari input.');
        $this->assertSame($this->categoryId, (int) $row['category_id']);
        $this->assertMatchesRegularExpression('/^SIP-\d{8}-[A-Z0-9]{6}$/', (string) $row['ticket_code']);

        $this->seeInDatabase('complaint_status_histories', [
            'complaint_id' => (int) $row['id'],
            'from_status'  => null,
            'to_status'    => 'menunggu',
        ]);
        $this->seeInDatabase('activity_logs', ['action' => 'aduan_dibuat']);
    }

    public function testComplaintCoordinatesMustBeInsideIndonesia(): void
    {
        $result = $this->withSession($this->sessionFor($this->reporter, 'masyarakat'))
            ->post('masyarakat/aduan', array_merge($this->csrf(), $this->complaintPayload($this->categoryId, [
                'latitude'  => '40.712776',
                'longitude' => '74.005974',
            ])));

        $result->assertRedirect();
        $this->assertSame(0, $this->db->table('complaints')->countAllResults());
    }

    public function testComplaintRejectsMissingCoordinates(): void
    {
        $payload = $this->complaintPayload($this->categoryId);
        unset($payload['latitude'], $payload['longitude']);

        $result = $this->withSession($this->sessionFor($this->reporter, 'masyarakat'))
            ->post('masyarakat/aduan', array_merge($this->csrf(), $payload));

        $result->assertRedirect();
        $this->assertSame(0, $this->db->table('complaints')->countAllResults());
    }

    public function testComplaintRejectsFutureIncidentDate(): void
    {
        $result = $this->withSession($this->sessionFor($this->reporter, 'masyarakat'))
            ->post('masyarakat/aduan', array_merge($this->csrf(), $this->complaintPayload($this->categoryId, [
                'incident_date' => date('Y-m-d', strtotime('+3 day')),
            ])));

        $result->assertRedirect();
        $this->assertSame(0, $this->db->table('complaints')->countAllResults());
    }

    public function testComplaintHourlyLimitIsEnforced(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->makeComplaint($this->reporter, $this->categoryId, $this->agencyId);
        }

        $result = $this->withSession($this->sessionFor($this->reporter, 'masyarakat'))
            ->post('masyarakat/aduan', array_merge($this->csrf(), $this->complaintPayload($this->categoryId)));

        $result->assertRedirect();
        $this->assertSame(5, $this->db->table('complaints')->countAllResults());
    }

    public function testPemdaCanAdvanceComplaintThroughWorkflow(): void
    {
        $complaintId = $this->makeComplaint($this->reporter, $this->categoryId, $this->agencyId);

        $result = $this->withSession($this->sessionFor($this->pemda, 'pemda'))
            ->post('pemda/aduan/' . $complaintId . '/status', array_merge($this->csrf(), [
                'status' => 'diverifikasi',
                'note'   => 'Lokasi sudah dicek petugas.',
            ]));

        $result->assertRedirect();
        $this->assertSame('diverifikasi', $this->grabFromDatabase('complaints', 'status', ['id' => $complaintId]));
        $this->seeInDatabase('complaint_status_histories', [
            'complaint_id' => $complaintId,
            'from_status'  => 'menunggu',
            'to_status'    => 'diverifikasi',
        ]);
        $this->seeInDatabase('activity_logs', ['action' => 'status_aduan_diubah']);
    }

    public function testPemdaCannotSkipWorkflowSteps(): void
    {
        $complaintId = $this->makeComplaint($this->reporter, $this->categoryId, $this->agencyId);

        $result = $this->withSession($this->sessionFor($this->pemda, 'pemda'))
            ->post('pemda/aduan/' . $complaintId . '/status', array_merge($this->csrf(), [
                'status' => 'selesai',
            ]));

        $result->assertRedirect();
        $this->assertSame('menunggu', $this->grabFromDatabase('complaints', 'status', ['id' => $complaintId]));
        $this->assertSame(0, $this->db->table('complaint_status_histories')->countAllResults());
    }

    public function testRejectionRequiresReason(): void
    {
        $complaintId = $this->makeComplaint($this->reporter, $this->categoryId, $this->agencyId);

        $this->withSession($this->sessionFor($this->pemda, 'pemda'))
            ->post('pemda/aduan/' . $complaintId . '/status', array_merge($this->csrf(), ['status' => 'ditolak']))
            ->assertRedirect();

        $this->assertSame('menunggu', $this->grabFromDatabase('complaints', 'status', ['id' => $complaintId]));

        $this->withSession($this->sessionFor($this->pemda, 'pemda'))
            ->post('pemda/aduan/' . $complaintId . '/status', array_merge($this->csrf(), [
                'status' => 'ditolak',
                'note'   => 'Bukan kewenangan instansi kami.',
            ]))
            ->assertRedirect();

        $this->assertSame('ditolak', $this->grabFromDatabase('complaints', 'status', ['id' => $complaintId]));
        $this->assertSame(
            'Bukan kewenangan instansi kami.',
            $this->grabFromDatabase('complaints', 'rejected_reason', ['id' => $complaintId]),
        );
    }

    public function testAdminMayOverrideStatusFlow(): void
    {
        $complaintId = $this->makeComplaint($this->reporter, $this->categoryId, $this->agencyId);

        $this->withSession($this->sessionFor($this->admin, 'admin'))
            ->post('admin/aduan/' . $complaintId . '/status', array_merge($this->csrf(), ['status' => 'selesai']))
            ->assertRedirect();

        $this->assertSame('selesai', $this->grabFromDatabase('complaints', 'status', ['id' => $complaintId]));
    }

    public function testMasyarakatCanCancelOwnPendingComplaint(): void
    {
        $complaintId = $this->makeComplaint($this->reporter, $this->categoryId, $this->agencyId);

        $this->withSession($this->sessionFor($this->reporter, 'masyarakat'))
            ->post('masyarakat/aduan/' . $complaintId . '/batal', $this->csrf())
            ->assertRedirect();

        $this->assertSame('ditolak', $this->grabFromDatabase('complaints', 'status', ['id' => $complaintId]));
    }

    public function testMasyarakatCanRateCompletedComplaint(): void
    {
        $complaintId = $this->makeComplaint($this->reporter, $this->categoryId, $this->agencyId, [
            'status' => 'selesai',
        ]);

        $this->withSession($this->sessionFor($this->reporter, 'masyarakat'))
            ->post('masyarakat/aduan/' . $complaintId . '/nilai', array_merge($this->csrf(), [
                'rating'         => '5',
                'rating_comment' => 'Terima kasih, jalan sudah diperbaiki.',
            ]))
            ->assertRedirect();

        $this->assertSame(5, (int) $this->grabFromDatabase('complaints', 'rating', ['id' => $complaintId]));
    }

    public function testComplaintCannotBeRatedTwice(): void
    {
        $complaintId = $this->makeComplaint($this->reporter, $this->categoryId, $this->agencyId, [
            'status' => 'selesai',
            'rating' => 4,
        ]);

        $this->withSession($this->sessionFor($this->reporter, 'masyarakat'))
            ->post('masyarakat/aduan/' . $complaintId . '/nilai', array_merge($this->csrf(), ['rating' => '2']))
            ->assertRedirect();

        $this->assertSame(4, (int) $this->grabFromDatabase('complaints', 'rating', ['id' => $complaintId]));
    }
}
