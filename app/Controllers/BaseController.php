<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entities\User;
use App\Services\AuthService;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Sipala;
use Psr\Log\LoggerInterface;

/**
 * Base controller aplikasi.
 *
 * Menyediakan akses ke pengguna aktif, konfigurasi aplikasi, dan pembantu
 * flash message. Logika bisnis tetap berada di App\Services.
 */
abstract class BaseController extends Controller
{
    /** @var list<string> */
    protected $helpers = ['url', 'form', 'sipala'];

    protected AuthService $auth;

    protected Sipala $sipalaConfig;

    protected ?User $currentUser = null;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);

        $this->auth         = service('authService');
        $this->sipalaConfig = config(Sipala::class);
        $this->currentUser  = $this->auth->user();
    }

    /**
     * Data umum yang selalu tersedia untuk layout.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function withLayout(array $data = []): array
    {
        return array_merge([
            'currentUser' => $this->currentUser,
            'appName'     => $this->sipalaConfig->appName,
            'appTagline'  => $this->sipalaConfig->appTagline,
        ], $data);
    }

    protected function successRedirect(string $route, string $message): RedirectResponse
    {
        return redirect()->to($route === '' ? current_url() : $route)->with('success', $message);
    }

    /**
     * Menyiapkan data paginasi memakai templat Bootstrap SIPALA.
     *
     * Wajib dipanggil sebelum merender view yang memuat `$pager->links('default', 'bootstrap')`.
     */
    protected function initPager(int $page, int $perPage, int $total): void
    {
        service('pager')->makeLinks($page, $perPage, $total, 'bootstrap');
    }

    protected function errorRedirect(string $route, string $message): RedirectResponse
    {
        return redirect()->to($route === '' ? current_url() : $route)
            ->with('error', $message)
            ->withInput();
    }

    /**
     * Menyalin error validasi ke flash data agar dapat ditampilkan kembali.
     */
    protected function backWithErrors(string $fallbackRoute = ''): RedirectResponse
    {
        $errors = $this->validator === null ? [] : $this->validator->getErrors();

        $response = redirect()->to($fallbackRoute === '' ? current_url() : $fallbackRoute)
            ->withInput()
            ->with('error', 'Periksa kembali data yang Anda isi.')
            ->with('errors', $errors);

        return $response;
    }
}
