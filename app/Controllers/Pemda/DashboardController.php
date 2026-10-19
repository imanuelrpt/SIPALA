<?php

declare(strict_types=1);

namespace App\Controllers\Pemda;

use App\Controllers\BaseController;
use App\Entities\User;
use App\Services\ReportService;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Dasbor instansi: ringkasan aduan yang menjadi tanggung jawabnya.
 */
final class DashboardController extends BaseController
{
    public function index(): string
    {
        $user = $this->requireAgencyUser();

        /** @var ReportService $reports */
        $reports = service('reportService');

        $overview = $reports->overview((int) $user->agencyId(), 12, 8);

        return view('pemda/dasbor', $this->withLayout([
            'title'    => lang('App.dashboard'),
            'overview' => $overview,
            'agency'   => $this->agencyName($user),
        ]));
    }

    private function requireAgencyUser(): User
    {
        $user = $this->currentUser;

        if ($user === null || ! $user->hasAgency()) {
            throw new PageNotFoundException('Akun instansi tidak terhubung ke instansi mana pun.');
        }

        return $user;
    }

    private function agencyName(User $user): string
    {
        /** @var array<int, string> $agencies */
        $agencies = service('agencyService')->options(false);

        return $agencies[$user->agencyId()] ?? '-';
    }
}
