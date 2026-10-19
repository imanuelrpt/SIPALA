<?php

declare(strict_types=1);

namespace App\Controllers\Masyarakat;

use App\Controllers\BaseController;
use App\Controllers\Concerns\ManagesOwnAccount;

/**
 * Akun masyarakat.
 */
final class AccountController extends BaseController
{
    use ManagesOwnAccount;

    protected function accountRouteName(): string
    {
        return 'masyarakat.account';
    }
}
