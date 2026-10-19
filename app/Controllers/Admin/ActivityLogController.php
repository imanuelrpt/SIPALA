<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ActivityLogService;

/**
 * Log aktivitas (audit trail) untuk Admin.
 */
final class ActivityLogController extends BaseController
{
    private const PER_PAGE = 25;

    public function index(): string
    {
        /** @var ActivityLogService $logger */
        $logger = service('activityLogger');

        $action  = trim((string) $this->request->getGet('action'));
        $search  = trim((string) $this->request->getGet('search'));
        $userId  = $this->request->getGet('user_id');
        $page    = max(1, (int) ($this->request->getGet('page') ?? 1));

        $filters = [
            'action'  => $action !== '' ? $action : null,
            'search'  => $search !== '' ? mb_substr($search, 0, 100) : null,
            'user_id' => is_numeric($userId) ? (int) $userId : null,
        ];

        $result = $logger->paginate($filters, self::PER_PAGE, $page);

        $this->initPager($page, self::PER_PAGE, $result['total']);

        return view('admin/log/index', $this->withLayout([
            'title'   => lang('App.activityLog'),
            'items'   => $result['items'],
            'total'   => $result['total'],
            'filters' => $filters,
            'actions' => $logger->actions(),
            'pager'   => service('pager'),
        ]));
    }
}
