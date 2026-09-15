<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// 1. Root / langsung nampilin AuthPage (Simulasi Auth)
Route::get('/', fn () => Inertia::render('AuthPage'))->name('home');

// 1b. Auth bawaan Fortify (/login & /register) dinonaktifkan: KasirKu hanya
//     pakai 1 pintu masuk di `/` (pilih peran kasir/admin/super admin).
//     Route ini didefinisikan di sini agar menimpa route GET Fortify.
//     Nama route dipertahankan (`login`/`register`) supaya helper wayfinder
//     di resources/js/routes tetap valid — yang berubah hanya aksinya (redirect).
Route::get('/login', fn () => redirect('/'))->name('login');
Route::get('/register', fn () => redirect('/'))->name('register');

// 2. Semua route dikeluarin dari middleware auth/verified
//    Props dummy dikosongkan — frontend pakai mock lokal (lihat HANDOVER_BACKEND.md).
//    Saat backend jadi, kirim data asli via Inertia::render('X', [...]) tanpa ubah Vue.
Route::get('/dashboard', fn () => Inertia::render('Dashboard', ['title' => 'Dashboard', 'subtitle' => 'Ringkasan aktivitas toko hari ini']))->name('dashboard');
Route::get('/transaksi', fn () => Inertia::render('Transaksi', ['title' => 'Transaksi', 'subtitle' => 'Kasir penjualan (tb_penjualan + detail)']))->name('transaksi');
Route::get('/pembelian', fn () => Inertia::render('Pembelian', ['title' => 'Pembelian', 'subtitle' => 'Stok masuk dari supplier (tb_pembelian + detail)']))->name('pembelian');
Route::get('/produk', fn () => Inertia::render('Produk', ['title' => 'Produk', 'subtitle' => 'Master barang, kategori & stok (tb_barang)']))->name('produk');
Route::get('/pelanggan', fn () => Inertia::render('Pelanggan', ['title' => 'Pelanggan', 'subtitle' => 'Master pelanggan koperasi (tb_pelanggan)']))->name('pelanggan');
Route::get('/supplier', fn () => Inertia::render('Supplier', ['title' => 'Supplier', 'subtitle' => 'Rekanan pemasok barang (tb_supplier)']))->name('supplier');
Route::get('/user', fn () => Inertia::render('User', ['title' => 'User', 'subtitle' => 'Kelola akun & role (tb_user + roles)']))->name('user');
Route::get('/laporan', fn () => Inertia::render('Laporan', ['title' => 'Laporan', 'subtitle' => 'Omzet, laba & piutang penjualan']))->name('laporan');
Route::get('/notifikasi', fn () => Inertia::render('Notifikasi', ['title' => 'Notifikasi', 'subtitle' => 'Stok, piutang & draft yang butuh perhatian']))->name('notifikasi');
Route::get('/settings', fn () => Inertia::render('Settings', ['title' => 'Settings', 'subtitle' => 'Profil, sekolah & tampilan']))->name('settings');

require __DIR__.'/settings.php';
