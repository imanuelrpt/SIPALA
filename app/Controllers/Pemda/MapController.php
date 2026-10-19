<?php

declare(strict_types=1);

namespace App\Controllers\Pemda;

use App\Controllers\BaseController;
use App\Entities\User;
use App\Services\ComplaintService;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Peta sebaran aduan instansi.
 */
final class MapController extends BaseController
{
    public function index(): string
    {
        $user = $this->requireAgencyUser();

        /** @var ComplaintService $complaints */
        $complaints = service('complaintService');

        $points = $complaints->mapPoints(['agency_id' => (int) $user->agencyId()], 'pemda/aduan/');

        return view('pemda/peta', $this->withLayout([
            'title'       => lang('App.map'),
            'points'      => $points,
            'showFilters' => true,
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
}
