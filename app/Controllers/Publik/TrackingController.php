<?php

declare(strict_types=1);

namespace App\Controllers\Publik;

use App\Controllers\BaseController;
use App\Entities\Complaint;
use App\Services\ComplaintService;
use App\Validation\TrackingRules;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Pelacakan aduan publik melalui kode tiket.
 *
 * Kode tiket bersifat rahasia (32 pangkat 6 kombinasi) sehingga berfungsi
 * sebagai token akses. Jumlah percobaan tetap dibatasi untuk mencegah
 * penebakan massal.
 */
final class TrackingController extends BaseController
{
    private const MAX_LOOKUPS     = 20;
    private const LOOKUP_WINDOW_S = 600;
    private const SESSION_KEY     = 'tracking_attempts';

    public function form(): string
    {
        $tracked = session()->getFlashdata('tracked_ticket');
        $result  = null;

        if (is_string($tracked) && $tracked !== '') {
            /** @var ComplaintService $complaints */
            $complaints = service('complaintService');
            $complaint  = $complaints->findByTicket($tracked);

            if ($complaint instanceof Complaint) {
                $result = [
                    'complaint' => $complaint,
                    'thread'    => $this->publicThread($complaints->thread((int) $complaint->id)),
                ];
            }
        }

        return view('publik/lacak', $this->withLayout([
            'title'  => lang('App.tracking'),
            'result' => $result,
            'errors' => session()->getFlashdata('errors') ?? [],
        ]));
    }

    public function lookup(): RedirectResponse
    {
        $rules = new TrackingRules();

        if (! $this->validate($rules->lookup, $rules->messages)) {
            return $this->backWithErrors(route_to('tracking.form'));
        }

        if ($this->rateLimited()) {
            return redirect()->to(route_to('tracking.form'))
                ->with('error', 'Terlalu banyak percobaan pencarian. Silakan coba lagi beberapa menit lagi.');
        }

        $code = strtoupper(trim((string) $this->request->getPost('ticket_code')));

        /** @var ComplaintService $complaints */
        $complaints = service('complaintService');

        if ($complaints->findByTicket($code) === null) {
            return redirect()->to(route_to('tracking.form'))
                ->with('error', 'Kode tiket tidak ditemukan. Periksa kembali kode pada bukti pengiriman Anda.');
        }

        return redirect()->to(route_to('tracking.form'))->with('tracked_ticket', $code);
    }

    /**
     * Linimasa versi publik: nama pelapor disamarkan dan catatan internal
     * instansi tidak ditampilkan.
     *
     * @param list<array<string, mixed>> $thread
     *
     * @return list<array<string, mixed>>
     */
    private function publicThread(array $thread): array
    {
        $items = [];

        foreach ($thread as $item) {
            $items[] = [
                'kind'       => $item['kind'],
                'at'         => $item['at'],
                'actor_role' => $item['actor_role'] ?? '',
                'from'       => $item['from'] ?? null,
                'to'         => $item['to'] ?? null,
                'message'    => $item['message'] ?? null,
            ];
        }

        return $items;
    }

    private function rateLimited(): bool
    {
        $session = session();
        $now     = time();
        $state   = $session->get(self::SESSION_KEY);

        if (
            ! is_array($state)
            || ! isset($state['start'], $state['count'])
            || ($now - (int) $state['start']) > self::LOOKUP_WINDOW_S
        ) {
            $session->set(self::SESSION_KEY, ['start' => $now, 'count' => 1]);

            return false;
        }

        $count = (int) $state['count'] + 1;
        $session->set(self::SESSION_KEY, ['start' => (int) $state['start'], 'count' => $count]);

        return $count > self::MAX_LOOKUPS;
    }
}
