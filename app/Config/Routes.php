<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ---------------------------------------------------------------------
// Publik
// ---------------------------------------------------------------------
$routes->get('/', 'Publik\HomeController::index', ['as' => 'home']);
$routes->get('panduan', 'Publik\HomeController::guide', ['as' => 'guide']);
$routes->get('kebijakan-privasi', 'Publik\HomeController::privacy', ['as' => 'privacy']);
$routes->get('statistik', 'Publik\HomeController::statistics', ['as' => 'statistics']);

$routes->get('cek-aduan', 'Publik\TrackingController::form', ['as' => 'tracking.form']);
$routes->post('cek-aduan', 'Publik\TrackingController::lookup', ['as' => 'tracking.lookup', 'filter' => 'honeypot']);

// ---------------------------------------------------------------------
// Autentikasi (hanya tamu)
// ---------------------------------------------------------------------
$routes->group('', ['filter' => 'guest'], static function (RouteCollection $routes): void {
    $routes->get('masuk', 'Auth\LoginController::form', ['as' => 'auth.login']);
    $routes->post('masuk', 'Auth\LoginController::attempt', ['as' => 'auth.login.attempt', 'filter' => 'honeypot']);

    $routes->get('daftar', 'Auth\RegisterController::form', ['as' => 'auth.register']);
    $routes->post('daftar', 'Auth\RegisterController::store', ['as' => 'auth.register.store', 'filter' => 'honeypot']);

    $routes->get('lupa-password', 'Auth\PasswordResetController::form', ['as' => 'auth.forgot']);
    $routes->post('lupa-password', 'Auth\PasswordResetController::send', ['as' => 'auth.forgot.send', 'filter' => 'honeypot']);

    $routes->get('lupa-password/reset/(:segment)', 'Auth\PasswordResetController::resetForm/$1', ['as' => 'auth.reset']);
    $routes->post('lupa-password/reset', 'Auth\PasswordResetController::reset', ['as' => 'auth.reset.save']);
});

// Logout wajib POST + CSRF (tidak boleh via tautan GET).
$routes->post('keluar', 'Auth\LogoutController::logout', ['as' => 'auth.logout', 'filter' => 'auth']);

// ---------------------------------------------------------------------
// Berkas unggahan (selalu lewat pemeriksaan hak akses)
// ---------------------------------------------------------------------
$routes->get('berkas/(:num)', 'Files\FileController::show/$1', ['as' => 'files.show', 'filter' => 'auth']);

// ---------------------------------------------------------------------
// Masyarakat
// ---------------------------------------------------------------------
$routes->group('masyarakat', [
    'namespace' => 'App\Controllers\Masyarakat',
    'filter'    => ['auth', 'role:masyarakat'],
], static function (RouteCollection $routes): void {
    $routes->get('/', 'DashboardController::index', ['as' => 'masyarakat.dashboard']);

    $routes->get('aduan', 'ComplaintController::index', ['as' => 'masyarakat.complaints.index']);
    $routes->get('aduan/baru', 'ComplaintController::create', ['as' => 'masyarakat.complaints.create']);
    $routes->post('aduan', 'ComplaintController::store', ['as' => 'masyarakat.complaints.store']);
    $routes->get('aduan/(:num)', 'ComplaintController::show/$1', ['as' => 'masyarakat.complaints.show']);
    $routes->post('aduan/(:num)/tanggapan', 'ComplaintController::respond/$1', ['as' => 'masyarakat.complaints.respond']);
    $routes->post('aduan/(:num)/batal', 'ComplaintController::cancel/$1', ['as' => 'masyarakat.complaints.cancel']);
    $routes->post('aduan/(:num)/nilai', 'ComplaintController::rate/$1', ['as' => 'masyarakat.complaints.rate']);

    $routes->get('akun', 'AccountController::index', ['as' => 'masyarakat.account']);
    $routes->post('akun/password', 'AccountController::updatePassword', ['as' => 'masyarakat.password.update']);
});

// ---------------------------------------------------------------------
// Pemda
// ---------------------------------------------------------------------
$routes->group('pemda', [
    'namespace' => 'App\Controllers\Pemda',
    'filter'    => ['auth', 'role:pemda'],
], static function (RouteCollection $routes): void {
    $routes->get('/', 'DashboardController::index', ['as' => 'pemda.dashboard']);

    $routes->get('aduan', 'ComplaintController::index', ['as' => 'pemda.complaints.index']);
    $routes->get('aduan/(:num)', 'ComplaintController::show/$1', ['as' => 'pemda.complaints.show']);
    $routes->post('aduan/(:num)/status', 'ComplaintController::updateStatus/$1', ['as' => 'pemda.complaints.status']);
    $routes->post('aduan/(:num)/tanggapan', 'ComplaintController::respond/$1', ['as' => 'pemda.complaints.respond']);
    $routes->post('aduan/(:num)/bukti', 'ComplaintController::uploadEvidence/$1', ['as' => 'pemda.complaints.evidence']);

    $routes->get('peta', 'MapController::index', ['as' => 'pemda.map']);

    $routes->get('akun', 'AccountController::index', ['as' => 'pemda.account']);
    $routes->post('akun/password', 'AccountController::updatePassword', ['as' => 'pemda.password.update']);
});

// ---------------------------------------------------------------------
// Admin
// ---------------------------------------------------------------------
$routes->group('admin', [
    'namespace' => 'App\Controllers\Admin',
    'filter'    => ['auth', 'role:admin'],
], static function (RouteCollection $routes): void {
    $routes->get('/', 'DashboardController::index', ['as' => 'admin.dashboard']);

    $routes->get('pengguna', 'UserController::index', ['as' => 'admin.users.index']);
    $routes->get('pengguna/baru', 'UserController::create', ['as' => 'admin.users.create']);
    $routes->post('pengguna', 'UserController::store', ['as' => 'admin.users.store']);
    $routes->get('pengguna/(:num)/ubah', 'UserController::edit/$1', ['as' => 'admin.users.edit']);
    $routes->post('pengguna/(:num)', 'UserController::update/$1', ['as' => 'admin.users.update']);
    $routes->post('pengguna/(:num)/aktif', 'UserController::activate/$1', ['as' => 'admin.users.activate']);
    $routes->post('pengguna/(:num)/nonaktif', 'UserController::deactivate/$1', ['as' => 'admin.users.deactivate']);
    $routes->post('pengguna/(:num)/reset', 'UserController::resetPassword/$1', ['as' => 'admin.users.reset']);

    $routes->get('instansi', 'AgencyController::index', ['as' => 'admin.agencies.index']);
    $routes->post('instansi', 'AgencyController::store', ['as' => 'admin.agencies.store']);
    $routes->post('instansi/(:num)', 'AgencyController::update/$1', ['as' => 'admin.agencies.update']);
    $routes->post('instansi/(:num)/hapus', 'AgencyController::delete/$1', ['as' => 'admin.agencies.delete']);

    $routes->get('kategori', 'CategoryController::index', ['as' => 'admin.categories.index']);
    $routes->post('kategori', 'CategoryController::store', ['as' => 'admin.categories.store']);
    $routes->post('kategori/(:num)', 'CategoryController::update/$1', ['as' => 'admin.categories.update']);
    $routes->post('kategori/(:num)/hapus', 'CategoryController::delete/$1', ['as' => 'admin.categories.delete']);

    $routes->get('aduan', 'ComplaintController::index', ['as' => 'admin.complaints.index']);
    $routes->get('aduan/(:num)', 'ComplaintController::show/$1', ['as' => 'admin.complaints.show']);
    $routes->post('aduan/(:num)/status', 'ComplaintController::updateStatus/$1', ['as' => 'admin.complaints.status']);
    $routes->post('aduan/(:num)/disposisi', 'ComplaintController::dispatch/$1', ['as' => 'admin.complaints.dispatch']);
    $routes->post('aduan/(:num)/tanggapan', 'ComplaintController::respond/$1', ['as' => 'admin.complaints.respond']);

    $routes->get('peta', 'MapController::index', ['as' => 'admin.map']);

    $routes->get('laporan', 'ReportController::index', ['as' => 'admin.reports.index']);
    $routes->get('laporan/ekspor', 'ReportController::export', ['as' => 'admin.reports.export']);

    $routes->get('log', 'ActivityLogController::index', ['as' => 'admin.logs.index']);

    $routes->get('akun', 'AccountController::index', ['as' => 'admin.account']);
    $routes->post('akun/password', 'AccountController::updatePassword', ['as' => 'admin.password.update']);
});
