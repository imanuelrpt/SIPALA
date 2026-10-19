<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\User;
use App\Enums\ActivityAction;
use App\Exceptions\BusinessRuleException;
use App\Models\PasswordResetModel;
use App\Models\UserModel;
use Config\Sipala;

/**
 * Lupa password dengan token sekali pakai.
 *
 * Token mentah hanya dikirim ke pengguna; basis data menyimpan hash-nya.
 */
final class PasswordResetService
{
    public function __construct(
        private readonly UserModel $users,
        private readonly PasswordResetModel $resets,
        private readonly AuthService $auth,
        private readonly ActivityLogService $activity,
        private readonly Sipala $config,
    ) {
    }

    /**
     * Tidak membocorkan apakah email terdaftar atau tidak.
     */
    public function request(string $email): void
    {
        $email = strtolower(trim($email));
        $user  = $this->users->findActiveByEmail($email);

        if ($user === null) {
            $this->activity->log(ActivityAction::PasswordResetAsked, 'user', null, 'Permintaan reset untuk email tidak terdaftar');

            return;
        }

        $this->resets->invalidateForEmail($email);

        $token = bin2hex(random_bytes(32));

        $this->resets->insert([
            'email'      => $email,
            'token_hash' => $this->hashToken($token),
            'expires_at' => date('Y-m-d H:i:s', time() + ($this->config->passwordResetExpiryMinutes * 60)),
            'used_at'    => null,
        ]);

        $this->deliverLink($user, $token);

        $this->activity->log(ActivityAction::PasswordResetAsked, 'user', (int) $user->id, 'Permintaan reset kata sandi', (int) $user->id);
    }

    public function reset(string $token, string $newPassword): void
    {
        $record = $this->resets->findValidByHash($this->hashToken($token));

        if ($record === null) {
            throw new BusinessRuleException('Tautan reset tidak valid atau sudah kedaluwarsa.');
        }

        $user = $this->users->findActiveByEmail((string) $record['email']);

        if ($user === null) {
            throw new BusinessRuleException('Akun tidak ditemukan atau sudah nonaktif.');
        }

        $this->users->update((int) $user->id, ['password_hash' => $this->auth->hashPassword($newPassword)]);

        $this->resets->update((int) $record['id'], ['used_at' => date('Y-m-d H:i:s')]);
        $this->resets->invalidateForEmail((string) $record['email']);

        $this->activity->log(ActivityAction::PasswordReset, 'user', (int) $user->id, 'Kata sandi direset melalui token', (int) $user->id);
    }

    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * Mengirim tautan reset. Bila SMTP belum dikonfigurasi (umum di lokal),
     * tautan dicatat ke log aplikasi — hanya di luar environment production.
     */
    private function deliverLink(User $user, string $token): void
    {
        $link = site_url('lupa-password/reset/' . $token);

        try {
            $email = \Config\Services::email();
            $email->setTo((string) $user->email)
                ->setSubject('Reset kata sandi SIPALA')
                ->setMessage(view('emails/reset_password', ['user' => $user, 'link' => $link]));

            if ($email->send()) {
                return;
            }
        } catch (\Throwable $e) {
            // Diabaikan: dilanjutkan ke pencatatan log di bawah.
        }

        if (ENVIRONMENT !== 'production') {
            log_message('info', '[SIPALA] Tautan reset kata sandi: ' . $link);
        }
    }
}
