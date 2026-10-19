<?php

declare(strict_types=1);

namespace Config;

use App\Filters\AuthFilter;
use App\Filters\GuestFilter;
use App\Filters\RoleFilter;
use App\Filters\SecurityHeadersFilter;
use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;

class Filters extends BaseFilters
{
    /**
     * Alias filter aplikasi.
     *
     * @var array<string, class-string|list<class-string>>
     */
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecurityHeadersFilter::class,
        'cors'          => Cors::class,
        'forcehttps'    => ForceHTTPS::class,
        'pagecache'     => PageCache::class,
        'performance'   => PerformanceMetrics::class,
        'auth'          => AuthFilter::class,
        'guest'         => GuestFilter::class,
        'role'          => RoleFilter::class,
    ];

    /**
     * Filter wajib dari framework.
     *
     * @var array{before: list<string>, after: list<string>}
     */
    public array $required = [
        'before' => [
            'forcehttps', // Force Global Secure Requests
            'pagecache',  // Web Page Caching
        ],
        'after' => [
            'pagecache',   // Web Page Caching
            'performance', // Performance Metrics
            'toolbar',     // Debug Toolbar (hanya aktif di development)
        ],
    ];

    /**
     * Filter global: CSRF untuk semua request pengubah data,
     * header keamanan untuk semua response.
     *
     * @var array{
     *     before: array<string, array{except: list<string>|string}>|list<string>,
     *     after: array<string, array{except: list<string>|string}>|list<string>
     * }
     */
    public array $globals = [
        'before' => [
            'csrf',
            // 'honeypot' diaktifkan per-rute pada form publik (lihat Routes.php)
        ],
        'after' => [
            'secureheaders',
        ],
    ];

    /** @var array<string, list<string>> */
    public array $methods = [];

    /**
     * Filter berbasis pola URI.
     *
     * @var array<string, array<string, list<string>>>
     */
    public array $filters = [];
}
