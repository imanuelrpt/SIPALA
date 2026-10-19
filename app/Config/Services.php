<?php

declare(strict_types=1);

namespace Config;

use App\Models\ActivityLogModel;
use App\Models\AgencyModel;
use App\Models\CategoryModel;
use App\Models\ComplaintAttachmentModel;
use App\Models\ComplaintModel;
use App\Models\ComplaintResponseModel;
use App\Models\ComplaintStatusHistoryModel;
use App\Models\LoginAttemptModel;
use App\Models\PasswordResetModel;
use App\Models\UserModel;
use App\Services\ActivityLogService;
use App\Services\AgencyService;
use App\Services\AuthService;
use App\Services\CategoryService;
use App\Services\ComplaintService;
use App\Services\ImageUploadService;
use App\Services\LoginThrottleService;
use App\Services\PasswordResetService;
use App\Services\ReportService;
use App\Services\UserService;
use CodeIgniter\Config\BaseService;

/**
 * Service container aplikasi.
 *
 * Semua dependensi disuntikkan dari sini agar controller tetap tipis dan
 * service mudah diganti pada pengujian.
 */
class Services extends BaseService
{
    public static function activityLogger(bool $getShared = true): ActivityLogService
    {
        if ($getShared) {
            return static::getSharedInstance('activityLogger');
        }

        return new ActivityLogService(new ActivityLogModel());
    }

    public static function loginThrottle(bool $getShared = true): LoginThrottleService
    {
        if ($getShared) {
            return static::getSharedInstance('loginThrottle');
        }

        return new LoginThrottleService(new LoginAttemptModel(), config(Sipala::class));
    }

    public static function authService(bool $getShared = true): AuthService
    {
        if ($getShared) {
            return static::getSharedInstance('authService');
        }

        return new AuthService(
            new UserModel(),
            static::loginThrottle(),
            static::activityLogger(),
            config(Sipala::class),
        );
    }

    public static function imageUploads(bool $getShared = true): ImageUploadService
    {
        if ($getShared) {
            return static::getSharedInstance('imageUploads');
        }

        return new ImageUploadService(config(Sipala::class));
    }

    public static function complaintService(bool $getShared = true): ComplaintService
    {
        if ($getShared) {
            return static::getSharedInstance('complaintService');
        }

        return new ComplaintService(
            new ComplaintModel(),
            new ComplaintAttachmentModel(),
            new ComplaintResponseModel(),
            new ComplaintStatusHistoryModel(),
            new CategoryModel(),
            new AgencyModel(),
            static::imageUploads(),
            static::activityLogger(),
            config(Sipala::class),
        );
    }

    public static function userService(bool $getShared = true): UserService
    {
        if ($getShared) {
            return static::getSharedInstance('userService');
        }

        return new UserService(
            new UserModel(),
            new AgencyModel(),
            static::authService(),
            static::activityLogger(),
        );
    }

    public static function agencyService(bool $getShared = true): AgencyService
    {
        if ($getShared) {
            return static::getSharedInstance('agencyService');
        }

        return new AgencyService(
            new AgencyModel(),
            new CategoryModel(),
            static::activityLogger(),
        );
    }

    public static function categoryService(bool $getShared = true): CategoryService
    {
        if ($getShared) {
            return static::getSharedInstance('categoryService');
        }

        return new CategoryService(
            new CategoryModel(),
            new AgencyModel(),
            new ComplaintModel(),
            static::activityLogger(),
        );
    }

    public static function passwordResetService(bool $getShared = true): PasswordResetService
    {
        if ($getShared) {
            return static::getSharedInstance('passwordResetService');
        }

        return new PasswordResetService(
            new UserModel(),
            new PasswordResetModel(),
            static::authService(),
            static::activityLogger(),
            config(Sipala::class),
        );
    }

    public static function reportService(bool $getShared = true): ReportService
    {
        if ($getShared) {
            return static::getSharedInstance('reportService');
        }

        return new ReportService(new ComplaintModel());
    }
}
