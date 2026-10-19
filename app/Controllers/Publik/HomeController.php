<?php

declare(strict_types=1);

namespace App\Controllers\Publik;

use App\Controllers\BaseController;
use App\Services\ReportService;

/**
 * Halaman publik: beranda, panduan, kebijakan privasi, dan statistik.
 */
final class HomeController extends BaseController
{
    public function index(): string
    {
        /** @var ReportService $reports */
        $reports = service('reportService');

        $overview = $reports->overview(null, 12, 5);

        return view('publik/beranda', $this->withLayout([
            'title'      => 'Beranda',
            'overview'   => $overview,
            'statusList' => $overview['status_counts'],
        ]));
    }

    public function guide(): string
    {
        return view('publik/panduan', $this->withLayout([
            'title' => lang('App.guide'),
        ]));
    }

    public function privacy(): string
    {
        return view('publik/privasi', $this->withLayout([
            'title' => lang('App.privacy'),
        ]));
    }

    public function statistics(): string
    {
        /** @var ReportService $reports */
        $reports = service('reportService');

        $overview = $reports->overview(null, 12, 0);

        return view('publik/statistik', $this->withLayout([
            'title'    => 'Statistik',
            'overview' => $overview,
        ]));
    }
}
