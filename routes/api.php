<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\NotifikasiController;
use App\Http\Controllers\Api\PelangganController;
use App\Http\Controllers\Api\PembelianController;
use App\Http\Controllers\Api\PenjualanController;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\SekolahController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
 * API POS KasirKu — session-based (same-origin dengan Vue/Inertia).
 * Otorisasi: `auth` + `role:...`. Isolasi sekolah ditegakkan di controller
 * via App\Support\Tenant (selalu berdasarkan user login, bukan input frontend).
 */

/*
 * Seluruh API memakai middleware `web` agar session/cookies/CSRF aktif
 * (login berbasis session, same-origin dengan Vue/Inertia).
 */
Route::middleware('web')->group(function () {

    Route::post('/auth/login', [AuthController::class, 'login'])->name('api.auth.login');

    Route::middleware('auth')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::get('/auth/me', [AuthController::class, 'me'])->name('api.auth.me');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('api.dashboard');

        // Sekolah: baca semua role (terbatas miliknya), tulis super admin.
        Route::get('/sekolah', [SekolahController::class, 'index'])->name('api.sekolah.index');
        Route::get('/sekolah/{id}', [SekolahController::class, 'show'])->name('api.sekolah.show');
        Route::middleware('role:super admin')->group(function () {
            Route::post('/sekolah', [SekolahController::class, 'store'])->name('api.sekolah.store');
            Route::put('/sekolah/{id}', [SekolahController::class, 'update'])->name('api.sekolah.update');
            Route::patch('/sekolah/{id}/toggle', [SekolahController::class, 'toggle'])->name('api.sekolah.toggle');
        });

        // Users: super admin + admin (admin dibatasi kasir di controller).
        Route::middleware('role:super admin,admin')->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('api.users.index');
            Route::post('/users', [UserController::class, 'store'])->name('api.users.store');
            Route::put('/users/{id}', [UserController::class, 'update'])->name('api.users.update');
            Route::patch('/users/{id}/toggle', [UserController::class, 'toggle'])->name('api.users.toggle');
            Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('api.users.destroy');
        });
        Route::get('/roles', [UserController::class, 'roles'])->name('api.roles');

        // Produk: baca semua role, tulis admin.
        Route::get('/produk', [ProdukController::class, 'index'])->name('api.produk.index');
        Route::get('/produk/lookup', [ProdukController::class, 'lookup'])->name('api.produk.lookup');
        Route::get('/produk/{id}', [ProdukController::class, 'show'])->name('api.produk.show');
        Route::middleware('role:admin')->group(function () {
            Route::post('/produk', [ProdukController::class, 'store'])->name('api.produk.store');
            Route::put('/produk/{id}', [ProdukController::class, 'update'])->name('api.produk.update');
            Route::delete('/produk/{id}', [ProdukController::class, 'destroy'])->name('api.produk.destroy');
        });

        // Kategori: baca semua role, tulis admin.
        Route::get('/kategori', [KategoriController::class, 'index'])->name('api.kategori.index');
        Route::middleware('role:admin')->group(function () {
            Route::post('/kategori', [KategoriController::class, 'store'])->name('api.kategori.store');
            Route::post('/kategori/kelompok', [KategoriController::class, 'storeKelompok'])->name('api.kategori.storeKelompok');
            Route::put('/kategori/{id}', [KategoriController::class, 'update'])->name('api.kategori.update');
            Route::delete('/kategori/{id}', [KategoriController::class, 'destroy'])->name('api.kategori.destroy');
        });

        // Supplier: baca semua role, tulis admin.
        Route::get('/supplier', [SupplierController::class, 'index'])->name('api.supplier.index');
        Route::get('/supplier/{id}', [SupplierController::class, 'show'])->name('api.supplier.show');
        Route::middleware('role:admin')->group(function () {
            Route::post('/supplier', [SupplierController::class, 'store'])->name('api.supplier.store');
            Route::put('/supplier/{id}', [SupplierController::class, 'update'])->name('api.supplier.update');
            Route::delete('/supplier/{id}', [SupplierController::class, 'destroy'])->name('api.supplier.destroy');
        });

        // Pelanggan: baca semua role, tulis kasir + admin.
        Route::get('/pelanggan/kelompok', [PelangganController::class, 'kelompok'])->name('api.pelanggan.kelompok');
        Route::get('/pelanggan', [PelangganController::class, 'index'])->name('api.pelanggan.index');
        Route::get('/pelanggan/{id}', [PelangganController::class, 'show'])->name('api.pelanggan.show');
        Route::middleware('role:kasir,admin')->group(function () {
            Route::post('/pelanggan', [PelangganController::class, 'store'])->name('api.pelanggan.store');
            Route::put('/pelanggan/{id}', [PelangganController::class, 'update'])->name('api.pelanggan.update');
            Route::delete('/pelanggan/{id}', [PelangganController::class, 'destroy'])->name('api.pelanggan.destroy');
        });

        // Pembelian: baca admin + super admin, tulis admin.
        Route::middleware('role:admin,super admin')->group(function () {
            Route::get('/pembelian', [PembelianController::class, 'index'])->name('api.pembelian.index');
            Route::get('/pembelian/{id}', [PembelianController::class, 'show'])->name('api.pembelian.show');
        });
        Route::middleware('role:admin')->group(function () {
            Route::post('/pembelian', [PembelianController::class, 'store'])->name('api.pembelian.store');
            Route::post('/pembelian/{id}/selesai', [PembelianController::class, 'selesaikan'])->name('api.pembelian.selesaikan');
            Route::delete('/pembelian/{id}', [PembelianController::class, 'destroy'])->name('api.pembelian.destroy');
        });

        // Penjualan: baca semua, tulis kasir + admin, void dengan batas kasir di controller.
        Route::get('/penjualan', [PenjualanController::class, 'index'])->name('api.penjualan.index');
        Route::get('/penjualan/{id}', [PenjualanController::class, 'show'])->name('api.penjualan.show');
        Route::middleware('role:kasir,admin')->group(function () {
            Route::post('/penjualan', [PenjualanController::class, 'store'])->name('api.penjualan.store');
        });
        Route::middleware('role:kasir,admin,super admin')->group(function () {
            Route::delete('/penjualan/{id}', [PenjualanController::class, 'destroy'])->name('api.penjualan.destroy');
        });

        // Laporan & notifikasi: semua role (scope otomatis per sekolah).
        Route::get('/laporan/penjualan', [LaporanController::class, 'penjualan'])->name('api.laporan.penjualan');
        Route::get('/laporan/pembelian', [LaporanController::class, 'pembelian'])->name('api.laporan.pembelian');
        Route::get('/laporan/stok', [LaporanController::class, 'stok'])->name('api.laporan.stok');
        Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('api.notifikasi');
    }); // end auth group

}); // end web group
