<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\TestCase\FeatureTestCase;

/**
 * Pendaftaran mandiri masyarakat.
 *
 * Skenario wajib: "register publik hanya boleh menghasilkan akun masyarakat".
 *
 * @internal
 */
final class RegistrationTest extends FeatureTestCase
{
    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge($this->csrf(), [
            'name'             => 'Warga Baru',
            'email'            => 'warga.baru@sipala.test',
            'phone'            => '081234567890',
            'password'         => 'Rahasia12345',
            'password_confirm' => 'Rahasia12345',
            'privacy_accepted' => '1',
        ], $overrides);
    }

    public function testRegisterCreatesMasyarakatAccountOnly(): void
    {
        // Upaya eskalasi hak akses: nilai role/agency_id/is_active dari input
        // harus diabaikan sepenuhnya.
        $result = $this->post('daftar', $this->payload([
            'role'      => 'admin',
            'agency_id' => '1',
            'is_active' => '0',
        ]));

        $result->assertRedirectTo('masyarakat');

        $row = $this->db->table('users')->where('email', 'warga.baru@sipala.test')->get()->getRowArray();

        $this->assertIsArray($row, 'Akun masyarakat baru harus tersimpan.');
        $this->assertSame('masyarakat', $row['role']);
        $this->assertNull($row['agency_id']);
        $this->assertSame(1, (int) $row['is_active']);
        $this->assertStringStartsWith('$2y$', (string) $row['password_hash']);

        $this->seeInDatabase('activity_logs', [
            'action' => 'register',
        ]);
    }

    public function testRegisterRejectsDuplicateEmail(): void
    {
        $this->makeUser('masyarakat', ['email' => 'sudah.ada@sipala.test']);

        $result = $this->post('daftar', $this->payload(['email' => 'sudah.ada@sipala.test']));

        $result->assertRedirect();
        $this->assertSame(1, $this->db->table('users')->where('email', 'sudah.ada@sipala.test')->countAllResults());
    }

    public function testRegisterRequiresPrivacyConsent(): void
    {
        $payload = $this->payload();
        unset($payload['privacy_accepted']);

        $result = $this->post('daftar', $payload);

        $result->assertRedirect();
        $this->dontSeeInDatabase('users', ['email' => 'warga.baru@sipala.test']);
    }

    public function testRegisterRejectsPasswordWithoutNumber(): void
    {
        $result = $this->post('daftar', $this->payload([
            'password'         => 'TanpaAngkaSama',
            'password_confirm' => 'TanpaAngkaSama',
        ]));

        $result->assertRedirect();
        $this->dontSeeInDatabase('users', ['email' => 'warga.baru@sipala.test']);
    }

    public function testRegisterRejectsMismatchedPasswordConfirmation(): void
    {
        $result = $this->post('daftar', $this->payload(['password_confirm' => 'Berbeda12345']));

        $result->assertRedirect();
        $this->dontSeeInDatabase('users', ['email' => 'warga.baru@sipala.test']);
    }
}
