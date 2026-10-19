<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\Support\TestCase\FeatureTestCase;

/**
 * Login, logout, dan pembatasan percobaan login.
 *
 * @internal
 */
final class AuthenticationTest extends FeatureTestCase
{
    /**
     * @return array<string, string>
     */
    private function credentials(string $email, string $password): array
    {
        return array_merge($this->csrf(), [
            'email'    => $email,
            'password' => $password,
        ]);
    }

    public function testLoginSucceedsForValidCredentials(): void
    {
        $userId = $this->makeUser('masyarakat', ['email' => 'masuk@sipala.test']);

        $result = $this->post('masuk', $this->credentials('masuk@sipala.test', self::PASSWORD));

        $result->assertRedirectTo('masyarakat');

        $this->assertNotNull(
            $this->grabFromDatabase('users', 'last_login_at', ['id' => $userId]),
            'last_login_at harus diperbarui setelah login berhasil.',
        );
        $this->seeInDatabase('activity_logs', ['action' => 'login_sukses', 'user_id' => $userId]);
        $this->assertSame(0, $this->db->table('login_attempts')->countAllResults());
    }

    public function testLoginFailsWithWrongPassword(): void
    {
        $userId = $this->makeUser('masyarakat', ['email' => 'salah@sipala.test']);

        $result = $this->post('masuk', $this->credentials('salah@sipala.test', 'SalahSekali123'));

        $result->assertRedirectTo('masuk');
        $this->assertNull($this->dbValue('users', 'last_login_at', ['id' => $userId]));
        $this->assertSame(1, $this->db->table('login_attempts')->countAllResults());
    }

    public function testLoginIsThrottledAfterMaxAttempts(): void
    {
        $userId = $this->makeUser('masyarakat', ['email' => 'dibatasi@sipala.test']);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('masuk', $this->credentials('dibatasi@sipala.test', 'SalahSekali123'))->assertRedirect();
        }

        $this->assertSame(5, $this->db->table('login_attempts')->countAllResults());

        // Percobaan keenam, walaupun kata sandinya BENAR, harus tetap ditolak.
        $result = $this->post('masuk', $this->credentials('dibatasi@sipala.test', self::PASSWORD));

        $result->assertRedirectTo('masuk');
        $this->assertNull($this->dbValue('users', 'last_login_at', ['id' => $userId]));
    }

    public function testInactiveAccountCannotLogin(): void
    {
        $userId = $this->makeUser('masyarakat', [
            'email'     => 'nonaktif@sipala.test',
            'is_active' => 0,
        ]);

        $result = $this->post('masuk', $this->credentials('nonaktif@sipala.test', self::PASSWORD));

        $result->assertRedirectTo('masuk');
        $this->assertNull($this->dbValue('users', 'last_login_at', ['id' => $userId]));
    }

    public function testLogoutDestroysSessionAndIsAudited(): void
    {
        $userId = $this->makeUser('masyarakat', ['email' => 'keluar@sipala.test']);

        $result = $this->withSession($this->sessionFor($userId, 'masyarakat'))->post('keluar', $this->csrf());

        $result->assertRedirect();
        $this->seeInDatabase('activity_logs', ['action' => 'logout', 'user_id' => $userId]);
        $result->assertSessionMissing('auth_user_id');
    }

    public function testLogoutRequiresAuthentication(): void
    {
        $result = $this->post('keluar', $this->csrf());

        $result->assertRedirectTo('masuk');
    }

    public function testGuestIsRedirectedFromProtectedPages(): void
    {
        $this->get('masyarakat')->assertRedirectTo('masuk');
        $this->get('admin')->assertRedirectTo('masuk');
    }

    public function testAuthenticatedUserIsRedirectedAwayFromLoginForm(): void
    {
        $userId = $this->makeUser('admin');

        $this->withSession($this->sessionFor($userId, 'admin'))->get('masuk')->assertRedirect();
    }
}
