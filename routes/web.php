<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
 * Route halaman Inertia KasirKu.
 * - `/` = pintu masuk AuthPage (tanpa mengubah desain). Sudah login -> dashboard.
 * - Login form (POST /login) ditangani Fortify dengan callback authenticateUsing
 *   (tb_user + username). Lihat App\Providers\FortifyServiceProvider.
 * - Data awal tiap halaman dikirim dari database agar hydrate(props) frontend
 *   memakai data nyata. Lihat App\Http\Controllers\PageController.
 */

// 1. Root: AuthPage untuk tamu, dashboard bila sudah login.
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('AuthPage');
})->name('home');

// 1b. Auth bawaan Fortify (/login & /register) dinonaktifkan dari sisi view:
// KasirKu hanya pakai 1 pintu masuk di `/`. Nama route dipertahankan agar
// helper wayfinder di resources/js/routes tetap valid.
Route::get('/login', fn () => redirect('/'))->name('login');
Route::get('/register', fn () => redirect('/'))->name('register');

// 2. Halaman aplikasi: wajib login + peran sesuai matriks sidebar.
// Web role yang gagal -> redirect dashboard (lihat RoleMiddleware);
// penegakan ketat (403) diterapkan pada API.
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [PageController::class, 'dashboard'])
        ->middleware('role:kasir,admin,super admin')->name('dashboard');

    Route::get('/transaksi', [PageController::class, 'transaksi'])
        ->middleware('role:kasir')->name('transaksi');
    Route::get('/riwayat-transaksi', [PageController::class, 'riwayat'])
        ->middleware('role:kasir')->name('riwayat-transaksi');
    Route::get('/pelanggan', [PageController::class, 'pelanggan'])
        ->middleware('role:kasir')->name('pelanggan');

    Route::get('/pembelian', [PageController::class, 'pembelian'])
        ->middleware('role:admin')->name('pembelian');
    Route::get('/produk', [PageController::class, 'produk'])
        ->middleware('role:admin')->name('produk');
    Route::get('/supplier', [PageController::class, 'supplier'])
        ->middleware('role:admin')->name('supplier');

    Route::get('/user', [PageController::class, 'user'])
        ->middleware('role:admin,super admin')->name('user');
    Route::get('/sekolah', [PageController::class, 'sekolah'])
        ->middleware('role:super admin')->name('sekolah');
    Route::get('/laporan', [PageController::class, 'laporan'])
        ->middleware('role:admin,super admin')->name('laporan');

    Route::get('/notifikasi', [PageController::class, 'notifikasi'])
        ->middleware('role:kasir,admin,super admin')->name('notifikasi');
    Route::get('/settings', [PageController::class, 'settings'])
        ->middleware('role:kasir,admin,super admin')->name('settings');
});

require __DIR__.'/settings.php';
