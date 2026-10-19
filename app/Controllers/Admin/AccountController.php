<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Concerns\ManagesOwnAccount;

/**
 * Akun admin.
 */
final class AccountController extends BaseController
{
    use ManagesOwnAccount;

    protected function accountRouteName(): string
    {
        return 'admin.account';
    }
}
