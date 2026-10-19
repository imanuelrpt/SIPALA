<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Concerns\ReadsComplaintFilters;
use App\Services\ReportService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Laporan & rekap aduan, termasuk ekspor CSV.
 */
final class ReportController extends BaseController
{
    use ReadsComplaintFilters;

    private const EXPORT_LIMIT = 10000;

    public function index(): string
    {
        $filters = $this->complaintListFilters();

        /** @var ReportService $reports */
        $reports = service('reportService');

        return view('admin/laporan/index', $this->withLayout([
            'title'      => lang('App.reports'),
            'filters'    => $filters,
            'summary'    => $reports->summary($filters),
            'monthly'    => service('complaintService')->monthlySeries(12),
            'agencies'   => service('agencyService')->options(false),
            'categories' => service('categoryService')->options(false),
        ]));
    }

    public function export(): ResponseInterface
    {
        $filters = $this->complaintListFilters();

        /** @var ReportService $reports */
        $reports = service('reportService');

        $rows = $reports->rows($filters, self::EXPORT_LIMIT);
        $csv  = $reports->toCsv($rows);

        $actor = $this->currentUser;

        if ($actor !== null) {
            $reports->logExport((int) $actor->id, count($rows), $this->periodLabel($filters));
        }

        $filename = 'laporan-aduan-' . date('Ymd-His') . '.csv';

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'no-store')
            ->setBody($csv);
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function periodLabel(array $filters): string
    {
        $from = (string) ($filters['date_from'] ?? '');
        $to   = (string) ($filters['date_to'] ?? '');

        if ($from === '' && $to === '') {
            return 'semua periode';
        }

        return ($from === '' ? 'awal' : $from) . ' s/d ' . ($to === '' ? 'kini' : $to);
    }
}
