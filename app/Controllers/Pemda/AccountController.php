<?php

declare(strict_types=1);

namespace App\Controllers\Pemda;

use App\Controllers\BaseController;
use App\Controllers\Concerns\ManagesOwnAccount;

/**
 * Akun petugas instansi.
 */
final class AccountController extends BaseController
{
    use ManagesOwnAccount;

    protected function accountRouteName(): string
    {
        return 'pemda.account';
    }
}
