<?php

declare(strict_types=1);

namespace App\Controllers\Masyarakat;

use App\Controllers\BaseController;
use App\Controllers\Concerns\ReadsComplaintFilters;
use App\Entities\User;
use App\Enums\AttachmentType;
use App\Exceptions\BusinessRuleException;
use App\Services\ComplaintService;
use App\Validation\ComplaintRules;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Pengaduan milik masyarakat: daftar, buat, detail, tanggapi, batalkan, nilai.
 */
final class ComplaintController extends BaseController
{
    use ReadsComplaintFilters;

    private const PER_PAGE = 10;

    public function index(): string
    {
        $user = $this->requireUser();

        $filters = $this->complaintListFilters((int) $user->id);
        $page    = $this->currentPage();

        $result = service('complaintService')->paginate($filters, self::PER_PAGE, $page);

        $this->initPager($page, self::PER_PAGE, $result['total']);

        return view('masyarakat/aduan/index', $this->withLayout([
            'title'   => lang('App.myComplaints'),
            'items'   => $result['items'],
            'total'   => $result['total'],
            'filters' => $filters,
            'pager'   => service('pager'),
        ]));
    }

    public function create(): string
    {
        return view('masyarakat/aduan/form', $this->withLayout([
            'title'      => 'Buat Aduan Baru',
            'categories' => service('categoryService')->withAgency(true),
            'errors'     => session()->getFlashdata('errors') ?? [],
        ]));
    }

    public function store(): RedirectResponse
    {
        $user = $this->requireUser();

        $rules = new ComplaintRules();

        if (! $this->validate($rules->store, $rules->messages)) {
            return $this->backWithErrors(route_to('masyarakat.complaints.create'));
        }

        /** @var ComplaintService $complaints */
        $complaints = service('complaintService');

        try {
            $complaint = $complaints->create(
                $user,
                [
                    'title'          => (string) $this->request->getPost('title'),
                    'category_id'    => (int) $this->request->getPost('category_id'),
                    'description'    => (string) $this->request->getPost('description'),
                    'incident_date'  => (string) $this->request->getPost('incident_date'),
                    'latitude'       => (string) $this->request->getPost('latitude'),
                    'longitude'      => (string) $this->request->getPost('longitude'),
                    'address_detail' => (string) $this->request->getPost('address_detail'),
                    'village'        => (string) $this->request->getPost('village'),
                    'district'       => (string) $this->request->getPost('district'),
                    'city'           => (string) $this->request->getPost('city'),
                    'hide_identity'  => (string) $this->request->getPost('hide_identity'),
                ],
                $this->collectUploadedFiles('photos'),
            );
        } catch (BusinessRuleException $e) {
            return $this->errorRedirect(route_to('masyarakat.complaints.create'), $e->getMessage());
        }

        return redirect()->to(route_to('masyarakat.complaints.show', (int) $complaint->id))
            ->with('success', 'Aduan berhasil dikirim. Kode tiket Anda: ' . (string) $complaint->ticket_code);
    }

    public function show(int $id): string
    {
        $user = $this->requireUser();

        /** @var ComplaintService $complaints */
        $complaints = service('complaintService');

        $complaint = $complaints->findOrFail($id);
        $complaints->assertCanView($user, $complaint);

        return view('masyarakat/aduan/detail', $this->withLayout([
            'title'               => (string) $complaint->title,
            'complaint'           => $complaint,
            'thread'              => $complaints->thread($id),
            'attachments'         => $complaints->attachments($id),
            'resolutionEvidence'  => $complaints->attachments($id, AttachmentType::BuktiPenyelesaian),
            'canCancel'           => $complaint->isEditableByReporter(),
            'canRate'             => $complaint->status()->value === 'selesai' && ! $complaint->hasRating(),
            'errors'              => session()->getFlashdata('errors') ?? [],
        ]));
    }

    public function respond(int $id): RedirectResponse
    {
        $user   = $this->requireUser();
        $rules  = new ComplaintRules();
        $target = route_to('masyarakat.complaints.show', $id);

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

        return $this->successRedirect($target, 'Tanggapan Anda berhasil dikirim.');
    }

    public function cancel(int $id): RedirectResponse
    {
        $user = $this->requireUser();

        /** @var ComplaintService $complaints */
        $complaints = service('complaintService');

        $target = route_to('masyarakat.complaints.show', $id);

        try {
            $complaints->cancel($user, $complaints->findOrFail($id));
        } catch (BusinessRuleException $e) {
            return $this->errorRedirect($target, $e->getMessage());
        }

        return $this->successRedirect($target, 'Aduan telah dibatalkan.');
    }

    public function rate(int $id): RedirectResponse
    {
        $user   = $this->requireUser();
        $rules  = new ComplaintRules();
        $target = route_to('masyarakat.complaints.show', $id);

        if (! $this->validate($rules->rating, $rules->messages)) {
            return $this->backWithErrors($target);
        }

        /** @var ComplaintService $complaints */
        $complaints = service('complaintService');

        try {
            $complaints->rate(
                $user,
                $complaints->findOrFail($id),
                (int) $this->request->getPost('rating'),
                (string) $this->request->getPost('rating_comment'),
            );
        } catch (BusinessRuleException $e) {
            return $this->errorRedirect($target, $e->getMessage());
        }

        return $this->successRedirect($target, 'Terima kasih atas penilaian Anda.');
    }

    private function requireUser(): User
    {
        $user = $this->currentUser;

        if ($user === null) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Pengguna tidak ditemukan.');
        }

        return $user;
    }

    /**
     * Menyaring berkas unggahan yang benar-benar dikirim pengguna.
     *
     * @return list<UploadedFile>
     */
    private function collectUploadedFiles(string $field): array
    {
        $files = $this->request->getFileMultiple($field);

        if (! is_array($files)) {
            return [];
        }

        return array_values(array_filter(
            $files,
            static fn ($file): bool => $file instanceof UploadedFile && $file->getError() !== UPLOAD_ERR_NO_FILE,
        ));
    }
}
