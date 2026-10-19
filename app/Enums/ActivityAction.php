<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Aksi yang dicatat pada tabel activity_logs.
 */
enum ActivityAction: string
{
    case LoginSucceeded      = 'login_sukses';
    case LoginFailed         = 'login_gagal';
    case Logout              = 'logout';
    case Registered          = 'register';
    case PasswordChanged     = 'ubah_password';
    case PasswordResetAsked  = 'minta_reset_password';
    case PasswordReset       = 'reset_password';
    case AccountDeactivated  = 'akun_dinonaktifkan';
    case AccountActivated    = 'akun_diaktifkan';

    case ComplaintCreated    = 'aduan_dibuat';
    case ComplaintUpdated    = 'aduan_diubah';
    case ComplaintCancelled  = 'aduan_dibatalkan';
    case ComplaintStatusChanged = 'status_aduan_diubah';
    case ComplaintDispatched = 'aduan_didisposisi';
    case ComplaintResponded  = 'tanggapan_ditambahkan';
    case ComplaintRated      = 'aduan_dinilai';
    case AttachmentUploaded  = 'lampiran_diunggah';
    case AttachmentDownloaded = 'lampiran_diunduh';

    case UserCreated         = 'pengguna_dibuat';
    case UserUpdated         = 'pengguna_diubah';
    case UserPasswordReset   = 'password_direset_admin';

    case AgencyCreated       = 'instansi_dibuat';
    case AgencyUpdated       = 'instansi_diubah';
    case CategoryCreated     = 'kategori_dibuat';
    case CategoryUpdated     = 'kategori_diubah';

    case ReportExported      = 'laporan_diekspor';

    public function label(): string
    {
        return match ($this) {
            self::LoginSucceeded        => 'Login berhasil',
            self::LoginFailed           => 'Login gagal',
            self::Logout                => 'Logout',
            self::Registered            => 'Registrasi akun',
            self::PasswordChanged       => 'Ubah kata sandi',
            self::PasswordResetAsked    => 'Permintaan reset kata sandi',
            self::PasswordReset         => 'Reset kata sandi',
            self::AccountDeactivated    => 'Nonaktifkan akun',
            self::AccountActivated      => 'Aktifkan akun',
            self::ComplaintCreated      => 'Aduan dibuat',
            self::ComplaintUpdated      => 'Aduan diubah',
            self::ComplaintCancelled    => 'Aduan dibatalkan',
            self::ComplaintStatusChanged => 'Status aduan diubah',
            self::ComplaintDispatched   => 'Aduan didisposisi',
            self::ComplaintResponded    => 'Tanggapan ditambahkan',
            self::ComplaintRated        => 'Aduan dinilai',
            self::AttachmentUploaded    => 'Lampiran diunggah',
            self::AttachmentDownloaded  => 'Lampiran diunduh',
            self::UserCreated           => 'Pengguna dibuat',
            self::UserUpdated           => 'Pengguna diubah',
            self::UserPasswordReset     => 'Reset kata sandi oleh admin',
            self::AgencyCreated         => 'Instansi dibuat',
            self::AgencyUpdated         => 'Instansi diubah',
            self::CategoryCreated       => 'Kategori dibuat',
            self::CategoryUpdated       => 'Kategori diubah',
            self::ReportExported        => 'Laporan diekspor',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
