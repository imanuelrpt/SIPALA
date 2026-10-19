<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\Complaint;
use App\Entities\ComplaintAttachment;
use App\Entities\User;
use App\Enums\ActivityAction;
use App\Enums\AttachmentType;
use App\Enums\ComplaintStatus;
use App\Exceptions\AccessDeniedException;
use App\Exceptions\BusinessRuleException;
use App\Models\AgencyModel;
use App\Models\CategoryModel;
use App\Models\ComplaintAttachmentModel;
use App\Models\ComplaintModel;
use App\Models\ComplaintResponseModel;
use App\Models\ComplaintStatusHistoryModel;
use Config\Sipala;

/**
 * Seluruh aturan bisnis pengaduan.
 *
 * Controller hanya memanggil service ini; pemeriksaan hak akses dilakukan
 * di sini (bukan sekadar menyembunyikan menu di UI).
 */
final class ComplaintService
{
    private const TICKET_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function __construct(
        private readonly ComplaintModel $complaints,
        private readonly ComplaintAttachmentModel $attachments,
        private readonly ComplaintResponseModel $responses,
        private readonly ComplaintStatusHistoryModel $histories,
        private readonly CategoryModel $categories,
        private readonly AgencyModel $agencies,
        private readonly ImageUploadService $uploads,
        private readonly ActivityLogService $activity,
        private readonly Sipala $config,
    ) {
    }

    // -----------------------------------------------------------------
    // Pembuatan aduan
    // -----------------------------------------------------------------

    /**
     * @param array<string, mixed>       $data  Data form yang sudah divalidasi
     * @param list<\CodeIgniter\HTTP\Files\UploadedFile> $files
     */
    public function create(User $user, array $data, array $files = []): Complaint
    {
        $category = $this->categories->find((int) $data['category_id']);

        if ($category === null || ! $category->isActive()) {
            throw new BusinessRuleException('Kategori yang dipilih tidak tersedia.');
        }

        $this->assertWithinHourlyLimit((int) $user->id);

        if (count($files) > $this->config->uploadMaxFiles) {
            throw new BusinessRuleException('Maksimal ' . $this->config->uploadMaxFiles . ' foto per aduan.');
        }

        $payload = [
            'category_id'   => (int) $category->id,
            'title'         => trim((string) $data['title']),
            'description'   => trim((string) $data['description']),
            'incident_date' => (string) $data['incident_date'],
            'latitude'      => (float) $data['latitude'],
            'longitude'     => (float) $data['longitude'],
            'address_detail' => $this->nullable($data['address_detail'] ?? null),
            'village'       => $this->nullable($data['village'] ?? null),
            'district'      => $this->nullable($data['district'] ?? null),
            'city'          => $this->nullable($data['city'] ?? null),
            'hide_identity' => ((string) ($data['hide_identity'] ?? '0')) === '1' ? 1 : 0,
        ];

        $db = db_connect();
        $db->transBegin();

        $storedFiles = [];

        try {
            $complaintId = (int) $this->complaints->insertPrivileged([
                ...$payload,
                'ticket_code' => $this->generateTicketCode(),
                'user_id'     => (int) $user->id,
                'agency_id'   => (int) $category->agency_id,
                'status'      => ComplaintStatus::Menunggu->value,
            ]);

            if ($complaintId <= 0) {
                throw new BusinessRuleException('Gagal menyimpan aduan. Silakan coba lagi.');
            }

            if ($files !== []) {
                $storedFiles = $this->uploads->storeMany($files, $complaintId, (int) $user->id, AttachmentType::BuktiAduan);

                foreach ($storedFiles as $file) {
                    $this->attachments->insert([
                        'complaint_id'  => $complaintId,
                        'uploaded_by'   => (int) $user->id,
                        'type'          => AttachmentType::BuktiAduan->value,
                        'file_path'     => $file['file_path'],
                        'original_name' => $file['original_name'],
                        'mime_type'     => $file['mime_type'],
                        'size'          => $file['size'],
                    ]);
                }
            }

            $this->histories->insert([
                'complaint_id' => $complaintId,
                'changed_by'   => (int) $user->id,
                'from_status'  => null,
                'to_status'    => ComplaintStatus::Menunggu->value,
                'note'         => 'Aduan dibuat oleh pelapor.',
            ]);

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            foreach ($storedFiles as $file) {
                $this->uploads->delete($file['file_path']);
            }

            throw $e;
        }

        /** @var Complaint $complaint */
        $complaint = $this->complaints->find($complaintId);

        $this->activity->log(
            ActivityAction::ComplaintCreated,
            'complaint',
            $complaintId,
            'Aduan ' . $complaint->ticket_code . ' dibuat',
            (int) $user->id,
        );

        return $complaint;
    }

    public function generateTicketCode(): string
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $code = sprintf('SIP-%s-%s', date('Ymd'), $this->randomToken());

            if ($this->complaints->findByTicket($code) === null) {
                return $code;
            }
        }

        throw new BusinessRuleException('Gagal membuat kode tiket unik, silakan coba lagi.');
    }

    // -----------------------------------------------------------------
    // Akses & otorisasi
    // -----------------------------------------------------------------

    /**
     * Memastikan pengguna berhak melihat aduan (mencegah IDOR).
     */
    public function assertCanView(User $user, Complaint $complaint): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($user->isPemda()) {
            if ($complaint->belongsToAgency($user->agencyId())) {
                return;
            }

            throw new AccessDeniedException('Aduan ini bukan tanggung jawab instansi Anda.');
        }

        if ($complaint->isOwnedBy((int) $user->id)) {
            return;
        }

        throw new AccessDeniedException('Anda hanya dapat melihat aduan yang Anda kirim.');
    }

    /**
     * Memastikan pengguna berhak mengubah status/menanggapi aduan.
     */
    public function assertCanManage(User $user, Complaint $complaint): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($user->isPemda() && $complaint->belongsToAgency($user->agencyId())) {
            return;
        }

        throw new AccessDeniedException('Anda tidak berwenang menindaklanjuti aduan ini.');
    }

    public function findOrFail(int $id): Complaint
    {
        $complaint = $this->complaints->findReport($id);

        if ($complaint === null) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Aduan tidak ditemukan.');
        }

        return $complaint;
    }

    /**
     * Mencari aduan berdasarkan kode tiket (pelacakan publik).
     */
    public function findByTicket(string $ticketCode): ?Complaint
    {
        $ticketCode = strtoupper(trim($ticketCode));

        return $ticketCode === '' ? null : $this->complaints->findByTicket($ticketCode);
    }

    /**
     * Mengambil lampiran setelah memastikan pemohon berhak melihat aduannya.
     *
     * @throws AccessDeniedException
     */
    public function attachmentForViewing(User $user, int $attachmentId): ComplaintAttachment
    {
        $attachment = $this->attachments->find($attachmentId);

        if ($attachment === null) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Lampiran tidak ditemukan.');
        }

        $complaint = $this->complaints->findReport((int) $attachment->complaint_id);

        if ($complaint === null) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Aduan lampiran tidak ditemukan.');
        }

        $this->assertCanView($user, $complaint);

        return $attachment;
    }

    // -----------------------------------------------------------------
    // Tindak lanjut
    // -----------------------------------------------------------------

    public function changeStatus(
        User $actor,
        Complaint $complaint,
        ComplaintStatus $target,
        ?string $note = null,
    ): Complaint {
        $this->assertCanManage($actor, $complaint);

        $current = $complaint->status();

        if ($current === $target) {
            throw new BusinessRuleException('Status aduan sudah "' . $target->label() . '".');
        }

        // Admin boleh mengoreksi status; Pemda harus mengikuti alur.
        if (! $actor->isAdmin() && ! $current->canTransitionTo($target)) {
            throw new BusinessRuleException(sprintf(
                'Status tidak dapat diubah dari "%s" ke "%s".',
                $current->label(),
                $target->label(),
            ));
        }

        $note = $note === null ? null : trim($note);

        if ($target->requiresNote() && ($note === null || $note === '')) {
            throw new BusinessRuleException('Alasan penolakan wajib diisi.');
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $this->complaints->updatePrivileged((int) $complaint->id, [
                'status'          => $target->value,
                'rejected_reason' => $target === ComplaintStatus::Ditolak ? $note : null,
            ]);

            $this->histories->insert([
                'complaint_id' => (int) $complaint->id,
                'changed_by'   => (int) $actor->id,
                'from_status'  => $current->value,
                'to_status'    => $target->value,
                'note'         => $note,
            ]);

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }

        $this->activity->log(
            ActivityAction::ComplaintStatusChanged,
            'complaint',
            (int) $complaint->id,
            sprintf(
                '%s -> %s pada %s',
                $current->label(),
                $target->label(),
                (string) $complaint->ticket_code,
            ),
            (int) $actor->id,
        );

        /** @var Complaint $fresh */
        $fresh = $this->complaints->find((int) $complaint->id);

        return $fresh;
    }

    /**
     * Disposisi aduan ke instansi lain (khusus Admin).
     */
    public function dispatch(User $admin, Complaint $complaint, int $agencyId, ?string $note = null): Complaint
    {
        if (! $admin->isAdmin()) {
            throw new AccessDeniedException('Hanya admin yang dapat mendisposisikan aduan.');
        }

        $agency = $this->agencies->find($agencyId);

        if ($agency === null || ! $agency->isActive()) {
            throw new BusinessRuleException('Instansi tujuan tidak tersedia.');
        }

        if ($complaint->belongsToAgency($agencyId)) {
            throw new BusinessRuleException('Aduan sudah ditangani instansi tersebut.');
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $this->complaints->updatePrivileged((int) $complaint->id, ['agency_id' => $agencyId]);

            $this->histories->insert([
                'complaint_id' => (int) $complaint->id,
                'changed_by'   => (int) $admin->id,
                'from_status'  => $complaint->status()->value,
                'to_status'    => $complaint->status()->value,
                'note'         => 'Disposisi ke ' . $agency->name . ($note === null || $note === '' ? '' : ' — ' . trim($note)),
            ]);

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }

        $this->activity->log(
            ActivityAction::ComplaintDispatched,
            'complaint',
            (int) $complaint->id,
            'Disposisi ' . (string) $complaint->ticket_code . ' ke ' . (string) $agency->name,
            (int) $admin->id,
        );

        /** @var Complaint $fresh */
        $fresh = $this->complaints->find((int) $complaint->id);

        return $fresh;
    }

    public function addResponse(User $author, Complaint $complaint, string $message, AttachmentType $type = AttachmentType::BuktiAduan): void
    {
        $this->assertCanView($author, $complaint);

        if ($author->isMasyarakat() && ! $complaint->isOwnedBy((int) $author->id)) {
            throw new AccessDeniedException('Anda hanya dapat menanggapi aduan milik Anda.');
        }

        $this->responses->insert([
            'complaint_id' => (int) $complaint->id,
            'user_id'      => (int) $author->id,
            'message'      => trim($message),
        ]);

        $this->activity->log(
            ActivityAction::ComplaintResponded,
            'complaint',
            (int) $complaint->id,
            'Tanggapan pada ' . (string) $complaint->ticket_code,
            (int) $author->id,
        );
    }

    /**
     * Unggah foto bukti penyelesaian oleh Pemda/Admin.
     *
     * @param list<\CodeIgniter\HTTP\Files\UploadedFile> $files
     */
    public function attachResolutionEvidence(User $actor, Complaint $complaint, array $files): int
    {
        $this->assertCanManage($actor, $complaint);

        if ($files === []) {
            return 0;
        }

        $existing = $this->attachments->countForComplaint((int) $complaint->id, AttachmentType::BuktiPenyelesaian);
        $stored   = $this->uploads->storeMany($files, (int) $complaint->id, (int) $actor->id, AttachmentType::BuktiPenyelesaian, $existing);

        foreach ($stored as $file) {
            $this->attachments->insert([
                'complaint_id'  => (int) $complaint->id,
                'uploaded_by'   => (int) $actor->id,
                'type'          => AttachmentType::BuktiPenyelesaian->value,
                'file_path'     => $file['file_path'],
                'original_name' => $file['original_name'],
                'mime_type'     => $file['mime_type'],
                'size'          => $file['size'],
            ]);
        }

        $this->activity->log(
            ActivityAction::AttachmentUploaded,
            'complaint',
            (int) $complaint->id,
            count($stored) . ' bukti penyelesaian pada ' . (string) $complaint->ticket_code,
            (int) $actor->id,
        );

        return count($stored);
    }

    public function cancel(User $user, Complaint $complaint): void
    {
        if (! $complaint->isOwnedBy((int) $user->id)) {
            throw new AccessDeniedException('Anda hanya dapat membatalkan aduan milik Anda.');
        }

        if (! $complaint->isEditableByReporter()) {
            throw new BusinessRuleException('Aduan yang sudah diproses tidak dapat dibatalkan.');
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $this->histories->insert([
                'complaint_id' => (int) $complaint->id,
                'changed_by'   => (int) $user->id,
                'from_status'  => $complaint->status()->value,
                'to_status'    => ComplaintStatus::Ditolak->value,
                'note'         => 'Dibatalkan oleh pelapor.',
            ]);

            $this->complaints->updatePrivileged((int) $complaint->id, [
                'status'          => ComplaintStatus::Ditolak->value,
                'rejected_reason' => 'Dibatalkan oleh pelapor.',
            ]);

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }

        $this->activity->log(
            ActivityAction::ComplaintCancelled,
            'complaint',
            (int) $complaint->id,
            'Pelapor membatalkan ' . (string) $complaint->ticket_code,
            (int) $user->id,
        );
    }

    public function rate(User $user, Complaint $complaint, int $rating, ?string $comment): void
    {
        if (! $complaint->isOwnedBy((int) $user->id)) {
            throw new AccessDeniedException('Anda hanya dapat menilai aduan milik Anda.');
        }

        if ($complaint->status() !== ComplaintStatus::Selesai) {
            throw new BusinessRuleException('Penilaian hanya dapat diberikan untuk aduan yang sudah selesai.');
        }

        if ($complaint->hasRating()) {
            throw new BusinessRuleException('Anda sudah menilai aduan ini.');
        }

        $this->complaints->updatePrivileged((int) $complaint->id, [
            'rating'         => $rating,
            'rating_comment' => $comment === null || trim($comment) === '' ? null : trim($comment),
        ]);

        $this->activity->log(
            ActivityAction::ComplaintRated,
            'complaint',
            (int) $complaint->id,
            'Penilaian ' . $rating . '/5 untuk ' . (string) $complaint->ticket_code,
            (int) $user->id,
        );
    }

    // -----------------------------------------------------------------
    // Query baca
    // -----------------------------------------------------------------

    /**
     * @param array<string, mixed> $filters
     *
     * @return array{items: list<Complaint>, total: int}
     */
    public function paginate(array $filters, int $perPage, int $page): array
    {
        return [
            'items' => $this->complaints->paginateReport($filters, $perPage, $page),
            'total' => $this->complaints->countReport($filters),
        ];
    }

    /**
     * @return list<\App\Entities\ComplaintAttachment>
     */
    public function attachments(int $complaintId, ?AttachmentType $type = null): array
    {
        return $type === null
            ? $this->attachments->forComplaint($complaintId)
            : $this->attachments->forComplaintByType($complaintId, $type);
    }

    /**
     * Gabungan riwayat status dan tanggapan, diurutkan kronologis.
     *
     * @return list<array<string, mixed>>
     */
    public function thread(int $complaintId): array
    {
        $items = [];

        foreach ($this->histories->forComplaintWithActor($complaintId) as $row) {
            $items[] = [
                'kind'       => 'status',
                'at'         => (string) $row['created_at'],
                'actor'      => (string) ($row['actor_name'] ?? 'Sistem'),
                'actor_role' => (string) ($row['actor_role'] ?? ''),
                'from'       => $row['from_status'] === null ? null : ComplaintStatus::tryFrom((string) $row['from_status']),
                'to'         => ComplaintStatus::tryFrom((string) $row['to_status']),
                'note'       => $row['note'],
            ];
        }

        foreach ($this->responses->forComplaintWithActor($complaintId) as $row) {
            $items[] = [
                'kind'       => 'response',
                'at'         => (string) $row['created_at'],
                'actor'      => (string) ($row['actor_name'] ?? 'Pengguna'),
                'actor_role' => (string) ($row['actor_role'] ?? ''),
                'message'    => (string) $row['message'],
            ];
        }

        usort($items, static fn (array $a, array $b): int => strcmp($a['at'], $b['at']));

        return $items;
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return list<array{id: int, ticket_code: string, title: string, status: string, status_label: string, latitude: float, longitude: float, category_name: string, agency_name: string, created_at: string, url: string}>
     */
    public function mapPoints(array $filters = [], string $urlPrefix = 'aduan/'): array
    {
        $points = [];

        foreach ($this->complaints->findWithRelations($filters) as $complaint) {
            $points[] = [
                'id'            => (int) $complaint->id,
                'ticket_code'   => (string) $complaint->ticket_code,
                'title'         => (string) $complaint->title,
                'status'        => $complaint->status()->value,
                'status_label'  => $complaint->status()->label(),
                'agency_name'   => (string) ($complaint->agency_name ?? '-'),
                'category_name' => (string) ($complaint->category_name ?? '-'),
                'latitude'      => (float) $complaint->latitude,
                'longitude'     => (float) $complaint->longitude,
                'url'           => site_url($urlPrefix . $complaint->id),
            ];
        }

        return $points;
    }

    /**
     * Deret jumlah aduan per bulan untuk grafik.
     *
     * @return list<array{label: string, total: int}>
     */
    public function monthlySeries(int $months = 12, ?int $agencyId = null): array
    {
        return $this->complaints->countsByMonth($months, $agencyId);
    }

    private function assertWithinHourlyLimit(int $userId): void
    {
        $limit = $this->config->complaintHourlyLimit;

        if ($limit <= 0) {
            return;
        }

        $since = date('Y-m-d H:i:s', time() - 3600);

        if ($this->complaints->countByUserSince($userId, $since) >= $limit) {
            throw new BusinessRuleException(
                'Anda telah mengirim ' . $limit . ' aduan dalam satu jam terakhir. Silakan coba lagi nanti.',
            );
        }
    }

    private function randomToken(): string
    {
        $alphabet = self::TICKET_ALPHABET;
        $max      = strlen($alphabet) - 1;
        $token    = '';

        for ($i = 0; $i < 6; $i++) {
            $token .= $alphabet[random_int(0, $max)];
        }

        return $token;
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
