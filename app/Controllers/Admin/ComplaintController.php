<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Concerns\ReadsComplaintFilters;
use App\Entities\User;
use App\Enums\AttachmentType;
use App\Enums\ComplaintStatus;
use App\Exceptions\BusinessRuleException;
use App\Services\ComplaintService;
use App\Validation\ComplaintRules;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Pengelolaan seluruh aduan oleh Admin (koreksi status, disposisi, tanggapan).
 */
final class ComplaintController extends BaseController
{
    use ReadsComplaintFilters;

    private const PER_PAGE = 15;

    public function index(): string
    {
        $filters = $this->complaintListFilters();
        $page    = $this->currentPage();

        $result = service('complaintService')->paginate($filters, self::PER_PAGE, $page);

        $this->initPager($page, self::PER_PAGE, $result['total']);

        return view('admin/aduan/index', $this->withLayout([
            'title'      => lang('App.allComplaints'),
            'items'      => $result['items'],
            'total'      => $result['total'],
            'filters'    => $filters,
            'agencies'   => service('agencyService')->options(false),
            'categories' => service('categoryService')->options(false),
            'pager'      => service('pager'),
        ]));
    }

    public function show(int $id): string
    {
        /** @var ComplaintService $complaints */
        $complaints = service('complaintService');

        $complaint = $complaints->findOrFail($id);

        return view('admin/aduan/detail', $this->withLayout([
            'title'              => (string) $complaint->title,
            'complaint'          => $complaint,
            'thread'             => $complaints->thread($id),
            'attachments'        => $complaints->attachments($id, AttachmentType::BuktiAduan),
            'resolutionEvidence' => $complaints->attachments($id, AttachmentType::BuktiPenyelesaian),
            'statusOptions'      => ComplaintStatus::options(),
            'agencies'           => service('agencyService')->options(true),
            'errors'             => session()->getFlashdata('errors') ?? [],
        ]));
    }

    public function updateStatus(int $id): RedirectResponse
    {
        $rules  = new ComplaintRules();
        $target = route_to('admin.complaints.show', $id);

        if (! $this->validate($rules->statusUpdate, $rules->messages)) {
            return $this->backWithErrors($target);
        }

        $status = ComplaintStatus::tryFrom((string) $this->request->getPost('status'));

        if ($status === null) {
            return $this->errorRedirect($target, 'Status yang dipilih tidak dikenal.');
        }

        $note = trim((string) ($this->request->getPost('note') ?? ''));

        if ($status->requiresNote() && $note === '') {
            $note = trim((string) ($this->request->getPost('rejected_reason') ?? ''));
        }

        /** @var ComplaintService $complaints */
        $complaints = service('complaintService');

        try {
            $complaints->changeStatus($this->actor(), $complaints->findOrFail($id), $status, $note);
        } catch (BusinessRuleException $e) {
            return $this->errorRedirect($target, $e->getMessage());
        }

        return $this->successRedirect($target, 'Status aduan diperbarui menjadi "' . $status->label() . '".');
    }

    public function dispatch(int $id): RedirectResponse
    {
        $rules  = new ComplaintRules();
        $target = route_to('admin.complaints.show', $id);

        if (! $this->validate($rules->disposition, $rules->messages)) {
            return $this->backWithErrors($target);
        }

        /** @var ComplaintService $complaints */
        $complaints = service('complaintService');

        try {
            $complaints->dispatch(
                $this->actor(),
                $complaints->findOrFail($id),
                (int) $this->request->getPost('agency_id'),
                (string) $this->request->getPost('note'),
            );
        } catch (BusinessRuleException $e) {
            return $this->errorRedirect($target, $e->getMessage());
        }

        return $this->successRedirect($target, 'Aduan berhasil didisposisikan ke instansi tujuan.');
    }

    public function respond(int $id): RedirectResponse
    {
        $rules  = new ComplaintRules();
        $target = route_to('admin.complaints.show', $id);

        if (! $this->validate($rules->response, $rules->messages)) {
            return $this->backWithErrors($target);
        }

        /** @var ComplaintService $complaints */
        $complaints = service('complaintService');

        try {
            $complaints->addResponse($this->actor(), $complaints->findOrFail($id), (string) $this->request->getPost('message'));
        } catch (BusinessRuleException $e) {
            return $this->errorRedirect($target, $e->getMessage());
        }

        return $this->successRedirect($target, 'Tanggapan berhasil dikirim.');
    }

    private function actor(): User
    {
        $actor = $this->currentUser;

        if ($actor === null) {
            throw new PageNotFoundException('Pengguna tidak ditemukan.');
        }

        return $actor;
    }
}
