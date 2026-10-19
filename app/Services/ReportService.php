<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\Complaint;
use App\Enums\ActivityAction;
use App\Models\ComplaintModel;

final class ReportService
{
    public function __construct(private readonly ComplaintModel $complaints)
    {
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return list<Complaint>
     */
    public function rows(array $filters, int $limit = 5000): array
    {
        return $this->complaints->findWithRelations($filters, $limit);
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return array{total: int, per_status: array<string, int>, per_agency: list<array{name: string, total: int}>, per_category: list<array{name: string, total: int}>, average_days: float}
     */
    public function summary(array $filters = []): array
    {
        $rows = $this->rows($filters);

        $perStatus = [];

        foreach ($rows as $row) {
            $key             = $row->status()->value;
            $perStatus[$key] = ($perStatus[$key] ?? 0) + 1;
        }

        return [
            'total'       => count($rows),
            'per_status'  => $perStatus,
            'per_agency'  => $this->complaints->countsByAgency(50),
            'per_category' => $this->complaints->countsByCategory(null, 50),
            'average_days' => $this->complaints->averageResolutionDays(),
        ];
    }

    /**
     * Membentuk CSV dari daftar aduan.
     *
     * @param list<Complaint> $rows
     */
    public function toCsv(array $rows): string
    {
        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            return '';
        }

        // BOM agar Excel membaca UTF-8 dengan benar.
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'Kode Tiket',
            'Tanggal Masuk',
            'Judul',
            'Kategori',
            'Instansi',
            'Status',
            'Kabupaten/Kota',
            'Kecamatan',
            'Kelurahan',
            'Latitude',
            'Longitude',
            'Penilaian',
            'Tanggal Selesai',
        ], ';');

        foreach ($rows as $row) {
            fputcsv($handle, [
                (string) $row->ticket_code,
                format_tanggal_waktu($row->created_at),
                $this->sanitize((string) $row->title),
                $this->sanitize((string) ($row->category_name ?? '')),
                $this->sanitize((string) ($row->agency_name ?? '')),
                $row->status()->label(),
                $this->sanitize((string) ($row->city ?? '')),
                $this->sanitize((string) ($row->district ?? '')),
                $this->sanitize((string) ($row->village ?? '')),
                (string) $row->latitude,
                (string) $row->longitude,
                $row->hasRating() ? (string) $row->rating : '',
                $row->status()->isFinal() ? format_tanggal_waktu($row->updated_at) : '',
            ], ';');
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv === false ? '' : $csv;
    }

    /**
     * Mencegah CSV formula injection (=, +, -, @).
     */
    private function sanitize(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        return in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'" . $value : $value;
    }

    public function logExport(int $actorId, int $rowCount, string $period): void
    {
        service('activityLogger')->log(
            ActivityAction::ReportExported,
            'report',
            null,
            $rowCount . ' baris diekspor (' . $period . ')',
            $actorId,
        );
    }

    /**
     * Data ringkas untuk dasbor (publik, instansi, atau admin).
     *
     * @return array{
     *     total: int,
     *     status_counts: array<string, int>,
     *     resolved: int,
     *     awaiting: int,
     *     average_days: float,
     *     by_category: list<array{name: string, total: int}>,
     *     by_agency: list<array{name: string, total: int}>,
     *     by_month: list<array{label: string, total: int}>,
     *     latest: list<Complaint>
     * }
     */
    public function overview(?int $agencyId = null, int $months = 12, int $latest = 5): array
    {
        $statusCounts = $this->complaints->statusCounts($agencyId);

        return [
            'total'         => $this->complaints->countAllComplaints($agencyId),
            'status_counts' => $statusCounts,
            'resolved'      => $statusCounts['selesai'] ?? 0,
            'awaiting'      => $statusCounts['menunggu'] ?? 0,
            'average_days'  => $this->complaints->averageResolutionDays($agencyId),
            'by_category'   => $this->complaints->countsByCategory($agencyId, 8),
            'by_agency'     => $this->complaints->countsByAgency(8),
            'by_month'      => $this->complaints->countsByMonth($months, $agencyId),
            'latest'        => $this->complaints->findWithRelations(
                $agencyId === null ? [] : ['agency_id' => $agencyId],
                $latest,
            ),
        ];
    }
}
