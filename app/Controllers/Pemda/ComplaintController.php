<?php

declare(strict_types=1);

namespace App\Controllers\Pemda;

use App\Controllers\BaseController;
use App\Controllers\Concerns\ReadsComplaintFilters;
use App\Entities\User;
use App\Enums\AttachmentType;
use App\Enums\ComplaintStatus;
use App\Exceptions\BusinessRuleException;
use App\Services\ComplaintService;
use App\Validation\ComplaintRules;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Tindak lanjut aduan oleh instansi.
 *
 * Semua kueri dipaksa memakai `agency_id` milik pengguna sehingga akun Pemda
 * tidak dapat membuka aduan instansi lain (juga ditegakkan di service).
 */
final class ComplaintController extends BaseController
{
    use ReadsComplaintFilters;

    private const PER_PAGE = 10;

    public function index(): string
    {
        $user = $this->requireAgencyUser();

        $filters = $this->complaintListFilters();
        // Pemda hanya melihat aduan instansinya — agency_id tidak dapat diubah dari UI.
        $filters['agency_id'] = (int) $user->agencyId();

        $page   = $this->currentPage();
        $result = service('complaintService')->paginate($filters, self::PER_PAGE, $page);

        $this->initPager($page, self::PER_PAGE, $result['total']);

        return view('pemda/aduan/index', $this->withLayout([
            'title'      => lang('App.incomingComplaints'),
            'items'      => $result['items'],
            'total'      => $result['total'],
            'filters'    => $filters,
            'categories' => service('categoryService')->options(true),
            'pager'      => service('pager'),
        ]));
    }

    public function show(int $id): string
    {
        $user = $this->requireAgencyUser();

        /** @var ComplaintService $complaints */
        $complaints = service('complaintService');

        $complaint = $complaints->findOrFail($id);
        $complaints->assertCanView($user, $complaint);

        return view('pemda/aduan/detail', $this->withLayout([
            'title'              => (string) $complaint->title,
            'complaint'          => $complaint,
            'thread'             => $complaints->thread($id),
            'attachments'        => $complaints->attachments($id, AttachmentType::BuktiAduan),
            'resolutionEvidence' => $complaints->attachments($id, AttachmentType::BuktiPenyelesaian),
            'transitions'        => $complaint->status()->allowedTransitions(),
            'errors'             => session()->getFlashdata('errors') ?? [],
        ]));
    }

    public function updateStatus(int $id): RedirectResponse
    {
        $user   = $this->requireAgencyUser();
        $rules  = new ComplaintRules();
        $target = route_to('pemda.complaints.show', $id);

        if (! $this->validate($rules->statusUpdate, $rules->messages)) {
            return $this->backWithErrors($target);
        }

        $status = ComplaintStatus::tryFrom((string) $this->request->getPost('status'));

        if ($status === null) {
            return $this->errorRedirect($target, 'Status yang dipilih tidak dikenal.');
        }

        $note = (string) ($this->request->getPost('note') ?? '');

        if ($status->requiresNote() && trim($note) === '') {
            $note = (string) ($this->request->getPost('rejected_reason') ?? '');
        }

        /** @var ComplaintService $complaints */
        $complaints = service('complaintService');

        try {
            $complaints->changeStatus($user, $complaints->findOrFail($id), $status, $note);
        } catch (BusinessRuleException $e) {
            return $this->errorRedirect($target, $e->getMessage());
        }

        return $this->successRedirect($target, 'Status aduan diperbarui menjadi "' . $status->label() . '".');
    }

    public function respond(int $id): RedirectResponse
    {
        $user   = $this->requireAgencyUser();
        $rules  = new ComplaintRules();
        $target = route_to('pemda.complaints.show', $id);

        if (! $this->validate($rules->response, $rules->messages)) {
            return $this->backWithErrors($target);
        }

        /** @var ComplaintService $complaints */
        $complaints = service('complaintService');

        try {
            $complaints->addResponse($user, $complaints->findOrFail($id), (string) $this->request->getPost('message'));
        } catch (BusinessRuleException $e) {
            return $this->errorRedirect($target, $e->getMessage());
        }

        return $this->successRedirect($target, 'Tanggapan berhasil dikirim.');
    }

    public function uploadEvidence(int $id): RedirectResponse
    {
        $user   = $this->requireAgencyUser();
        $target = route_to('pemda.complaints.show', $id);

        /** @var ComplaintService $complaints */
        $complaints = service('complaintService');

        $files = array_values(array_filter(
            $this->request->getFileMultiple('evidence') ?? [],
            static fn ($file): bool => $file instanceof UploadedFile && $file->getError() !== UPLOAD_ERR_NO_FILE,
        ));

        if ($files === []) {
            return $this->errorRedirect($target, 'Pilih minimal satu foto bukti penyelesaian.');
        }

        try {
            $stored = $complaints->attachResolutionEvidence($user, $complaints->findOrFail($id), $files);
        } catch (BusinessRuleException $e) {
            return $this->errorRedirect($target, $e->getMessage());
        }

        return $this->successRedirect($target, $stored . ' foto bukti penyelesaian berhasil diunggah.');
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
