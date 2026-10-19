<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ReportService;

/**
 * Dasbor admin: gambaran menyeluruh sistem.
 */
final class DashboardController extends BaseController
{
    public function index(): string
    {
        /** @var ReportService $reports */
        $reports = service('reportService');

        $overview = $reports->overview(null, 12, 8);

        return view('admin/dasbor', $this->withLayout([
            'title'       => lang('App.dashboard'),
            'overview'    => $overview,
            'userStats'   => service('userService')->stats(),
            'agencies'    => service('agencyService')->all(),
            'categoryCnt' => count(service('categoryService')->withAgency(false)),
        ]));
    }
}
