<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ComplaintService;

/**
 * Peta sebaran seluruh aduan.
 */
final class MapController extends BaseController
{
    public function index(): string
    {
        /** @var ComplaintService $complaints */
        $complaints = service('complaintService');

        return view('admin/peta', $this->withLayout([
            'title'       => lang('App.map'),
            'points'      => $complaints->mapPoints([], 'admin/aduan/'),
            'showFilters' => true,
        ]));
    }
}
