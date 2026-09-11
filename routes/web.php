<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => redirect('/dashboard'))->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Dashboard', ['title' => 'Dashboard']))->name('dashboard');
    Route::get('/transaksi', fn () => Inertia::render('Transaksi', ['title' => 'Transaksi']))->name('transaksi');
    Route::get('/pembelian', fn () => Inertia::render('Pembelian', ['title' => 'Pembelian']))->name('pembelian');
    Route::get('/produk', fn () => Inertia::render('Produk', ['title' => 'Produk']))->name('produk');
    Route::get('/pelanggan', fn () => Inertia::render('Pelanggan', ['title' => 'Pelanggan']))->name('pelanggan');
    Route::get('/supplier', fn () => Inertia::render('Supplier', ['title' => 'Supplier']))->name('supplier');
    Route::get('/user', fn () => Inertia::render('User', ['title' => 'User']))->name('user');
    Route::get('/laporan', fn () => Inertia::render('Laporan', ['title' => 'Laporan']))->name('laporan');
});

require __DIR__.'/settings.php';
