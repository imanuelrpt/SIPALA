<?php

declare(strict_types=1);

namespace App\Controllers\Masyarakat;

use App\Controllers\BaseController;
use App\Services\ReportService;

/**
 * Dasbor masyarakat: ringkasan aduan milik sendiri.
 */
final class DashboardController extends BaseController
{
    public function index(): string
    {
        $user = $this->currentUser;

        if ($user === null) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Pengguna tidak ditemukan.');
        }

        /** @var ReportService $reports */
        $reports = service('reportService');

        $summary = $reports->summary(['user_id' => (int) $user->id]);

        return view('masyarakat/dasbor', $this->withLayout([
            'title'   => lang('App.dashboard'),
            'summary' => $summary,
            'recent'  => service('complaintService')->paginate(['user_id' => (int) $user->id], 5, 1)['items'],
        ]));
    }
}
