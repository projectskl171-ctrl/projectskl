<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\TbBarang;
use App\Models\TbDetailPembelian;
use App\Models\TbDetailPenjualan;
use App\Models\TbKategori;
use App\Models\TbKelompokKategori;
use App\Models\TbKelompokPelanggan;
use App\Models\TbPelanggan;
use App\Models\TbPembelian;
use App\Models\TbPenjualan;
use App\Models\TbSekolah;
use App\Models\TbSupplier;
use App\Models\TbUser;
use App\Support\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Halaman Inertia KasirKu. Setiap halaman mengirim data awal dari database
 * agar `hydrate(props)` di frontend langsung memakai data nyata
 * (tanpa mengubah komponen Vue).
 */
class PageController extends Controller
{
    /** Scope sekolah untuk user non-super-admin (null = semua). */
    protected function schoolId(Request $request): ?int
    {
        return Tenant::isSuperAdmin($request->user()) ? null : $request->user()->id_sekolah;
    }

    protected function barang(Request $request, int $limit = 500)
    {
        // Sengaja TANPA scope aktif: baris terhapus (is_delete=1) tetap dikirim
        // agar lookup nama di riwayat transaksi tidak hilang ("#id").
        // Katalog kasir & admin memakai barangAktif (filter is_delete) sehingga
        // produk terhapus otomatis hilang realtime dari kasir.
        $q = TbBarang::query()
            ->with(['kategori:id_kategori,nama,id_kelompok', 'supplier:id_supplier,nama'])
            ->orderByDesc('id_barang')->limit($limit);
        if ($sid = $this->schoolId($request)) {
            $q->where('id_sekolah', $sid);
        }

        return $q->get();
    }

    public function dashboard(Request $request): Response
    {
        $sid = $this->schoolId($request);

        $penjualanQ = TbPenjualan::aktif()->orderByDesc('id_penjualan')->limit(100);
        $pembelianQ = TbPembelian::aktif()->orderByDesc('id_pembelian')->limit(100);
        $detailJualIds = (clone $penjualanQ)->pluck('id_penjualan');
        if ($sid) {
            $penjualanQ->where('id_sekolah', $sid);
            $pembelianQ->where('id_sekolah', $sid);
            $detailJualIds = (clone $penjualanQ)->pluck('id_penjualan');
        }
        $detailBeliIds = (clone $pembelianQ)->pluck('id_pembelian');

        return Inertia::render('Dashboard', [
            'title' => 'Dashboard',
            'subtitle' => 'Ringkasan aktivitas toko hari ini',
            'barang' => $this->barang($request),
            'penjualan' => $penjualanQ->get(),
            'pembelian' => $pembelianQ->get(),
            'detailPenjualan' => TbDetailPenjualan::whereIn('id_penjualan', $detailJualIds)->get(),
            'detailPembelian' => TbDetailPembelian::whereIn('id_pembelian', $detailBeliIds)->get(),
            'supplier' => $this->suppliers($request),
            'pelanggan' => $this->pelangganList($request),
            'kelompokPelanggan' => $this->kelompokPelanggan($request),
            'sekolah' => $this->sekolahList($request),
            'users' => $this->userList($request),
        ]);
    }

    public function transaksi(Request $request): Response
    {
        return Inertia::render('Transaksi', [
            'title' => 'Transaksi',
            'subtitle' => 'Kasir penjualan',
            'barang' => $this->barang($request),
            'pelanggan' => $this->pelangganList($request),
            'kategori' => $this->kategori($request),
            'kelompokPelanggan' => $this->kelompokPelanggan($request),
            'sekolah' => $this->sekolahList($request),
        ]);
    }

    public function riwayat(Request $request): Response
    {
        $sid = $this->schoolId($request);
        $q = TbPenjualan::aktif()->with(['pelanggan:id_pelanggan,nama_pelanggan', 'user:id_user,nama_lengkap'])
            ->orderByDesc('id_penjualan')->limit(200);
        if ($sid) {
            $q->where('id_sekolah', $sid);
        }
        $penjualan = $q->get();
        $ids = $penjualan->pluck('id_penjualan');

        return Inertia::render('RiwayatTransaksi', [
            'title' => 'Riwayat Transaksi',
            'subtitle' => 'Semua penjualan: search, filter & cetak ulang',
            'penjualan' => $penjualan,
            'detailPenjualan' => TbDetailPenjualan::whereIn('id_penjualan', $ids)->get(),
            'barang' => $this->barang($request),
            'sekolah' => $this->sekolahList($request),
        ]);
    }

    public function pembelian(Request $request): Response
    {
        $sid = $this->schoolId($request);
        $q = TbPembelian::aktif()->with('supplier:id_supplier,nama')->orderByDesc('id_pembelian')->limit(200);
        if ($sid) {
            $q->where('id_sekolah', $sid);
        }
        $pembelian = $q->get();
        $ids = $pembelian->pluck('id_pembelian');

        return Inertia::render('Pembelian', [
            'title' => 'Pembelian',
            'subtitle' => 'Stok masuk dari supplier',
            'pembelian' => $pembelian,
            'detailPembelian' => TbDetailPembelian::whereIn('id_pembelian', $ids)->get(),
            'supplier' => $this->suppliers($request),
            'barang' => $this->barang($request),
        ]);
    }

    public function produk(Request $request): Response
    {
        return Inertia::render('Produk', [
            'title' => 'Produk',
            'subtitle' => 'Master barang, kategori & stok',
            'barang' => $this->barang($request),
            'kategori' => $this->kategori($request),
            'kelompok' => $this->kelompokKategori($request),
            'supplier' => $this->suppliers($request),
            'sekolah' => $this->sekolahList($request),
        ]);
    }

    public function pelanggan(Request $request): Response
    {
        return Inertia::render('Pelanggan', [
            'title' => 'Pelanggan',
            'subtitle' => 'Master pelanggan',
            'pelanggan' => $this->pelangganList($request),
            'kelompokPelanggan' => $this->kelompokPelanggan($request),
        ]);
    }

    public function supplier(Request $request): Response
    {
        return Inertia::render('Supplier', [
            'title' => 'Supplier',
            'subtitle' => 'Rekanan pemasok barang',
            'supplier' => $this->suppliers($request),
            'barang' => $this->barang($request),
            'pembelian' => $this->recentPembelian($request),
        ]);
    }

    public function user(Request $request): Response
    {
        $actor = $request->user();
        $q = TbUser::with(['role:id_role,nama_role', 'sekolah:id_sekolah,nama_sekolah,kode_sekolah'])
            ->whereNull('deleted_at')
            ->orderByDesc('id_user');
        if (! Tenant::isSuperAdmin($actor)) {
            $q->where('id_sekolah', $actor->id_sekolah);
        }
        if ($actor->isAdmin()) {
            $q->whereHas('role', fn ($qq) => $qq->where('nama_role', Role::KASIR));
        }
        if (Tenant::isSuperAdmin($actor)) {
            // Super admin hanya mengelola admin (kasir urusan admin
            // sekolah masing-masing).
            $q->whereHas('role', fn ($qq) => $qq->whereIn('nama_role', [Role::SUPER_ADMIN, Role::ADMIN]));
        }

        return Inertia::render('User', [
            'title' => 'User',
            'subtitle' => 'Kelola akun & hak akses',
            'users' => $q->get()->makeHidden(['password']),
            'roles' => Role::orderBy('id_role')->get(),
            'sekolah' => $this->sekolahList($request),
        ]);
    }

    public function sekolah(Request $request): Response
    {
        return Inertia::render('Sekolah', [
            'title' => 'Sekolah',
            'subtitle' => 'Profil & langganan sekolah',
            'sekolah' => $this->sekolahList($request),
        ]);
    }

    public function laporan(Request $request): Response
    {
        $sid = $this->schoolId($request);
        $jualQ = TbPenjualan::aktif()->orderByDesc('id_penjualan')->limit(300);
        $beliQ = TbPembelian::aktif()->orderByDesc('id_pembelian')->limit(300);
        if ($sid) {
            $jualQ->where('id_sekolah', $sid);
            $beliQ->where('id_sekolah', $sid);
        }
        $penjualan = $jualQ->get();
        $pembelian = $beliQ->get();

        return Inertia::render('Laporan', [
            'title' => 'Laporan',
            'subtitle' => 'Omzet, laba & piutang penjualan',
            'penjualan' => $penjualan,
            'pembelian' => $pembelian,
            'detailPenjualan' => TbDetailPenjualan::whereIn('id_penjualan', $penjualan->pluck('id_penjualan'))->get(),
            'detailPembelian' => TbDetailPembelian::whereIn('id_pembelian', $pembelian->pluck('id_pembelian'))->get(),
            'barang' => $this->barang($request),
            'pelanggan' => $this->pelangganList($request),
        ]);
    }

    public function notifikasi(Request $request): Response
    {
        return Inertia::render('Notifikasi', [
            'title' => 'Notifikasi',
            'subtitle' => 'Stok, piutang & draft yang butuh perhatian',
            'barang' => $this->barang($request),
            'penjualan' => $this->recentPenjualan($request),
            'pembelian' => $this->recentPembelian($request),
        ]);
    }

    public function settings(): Response
    {
        return Inertia::render('Settings', [
            'title' => 'Settings',
            'subtitle' => 'Profil, sekolah & tampilan',
        ]);
    }

    /** Semua user dalam scope aktor (tanpa batas role) untuk dashboard. */
    protected function userList(Request $request)
    {
        $actor = $request->user();
        $q = TbUser::with(['role:id_role,nama_role', 'sekolah:id_sekolah,nama_sekolah,kode_sekolah'])
            ->whereNull('deleted_at')
            ->orderByDesc('id_user');
        if (! Tenant::isSuperAdmin($actor)) {
            $q->where('id_sekolah', $actor->id_sekolah);
        }

        return $q->get()->makeHidden(['password']);
    }

    // ---------- helpers ----------

    protected function suppliers(Request $request)
    {
        $q = TbSupplier::aktif()->orderBy('nama');
        if ($sid = $this->schoolId($request)) {
            $q->where('id_sekolah', $sid);
        }

        return $q->get();
    }

    protected function kategori(Request $request)
    {
        $q = TbKategori::aktif()->orderBy('nama');
        if ($sid = $this->schoolId($request)) {
            $q->whereHas('kelompok', fn ($qq) => $qq->where('id_sekolah', $sid));
        }

        return $q->get();
    }

    protected function kelompokKategori(Request $request)
    {
        $q = TbKelompokKategori::with(['kategori' => fn ($qq) => $qq->aktif()])->orderBy('nama_kelompok');
        if ($sid = $this->schoolId($request)) {
            $q->where('id_sekolah', $sid);
        }

        return $q->get();
    }

    protected function pelangganList(Request $request)
    {
        $q = TbPelanggan::aktif()->with('kelompok:id_kelompok_pelanggan,nama_kelompok,id_sekolah')->orderByDesc('id_pelanggan');
        if ($sid = $this->schoolId($request)) {
            $q->whereHas('kelompok', fn ($qq) => $qq->where('id_sekolah', $sid));
        }

        return $q->get();
    }

    protected function kelompokPelanggan(Request $request)
    {
        $q = TbKelompokPelanggan::orderBy('id_kelompok_pelanggan');
        if ($sid = $this->schoolId($request)) {
            $q->where('id_sekolah', $sid);
        }

        return $q->get();
    }

    protected function sekolahList(Request $request)
    {
        $q = TbSekolah::orderBy('id_sekolah');
        if ($sid = $this->schoolId($request)) {
            $q->where('id_sekolah', $sid);
        }

        return $q->get();
    }

    protected function recentPenjualan(Request $request)
    {
        $q = TbPenjualan::aktif()->orderByDesc('id_penjualan')->limit(100);
        if ($sid = $this->schoolId($request)) {
            $q->where('id_sekolah', $sid);
        }

        return $q->get();
    }

    protected function recentPembelian(Request $request)
    {
        $q = TbPembelian::aktif()->orderByDesc('id_pembelian')->limit(100);
        if ($sid = $this->schoolId($request)) {
            $q->where('id_sekolah', $sid);
        }

        return $q->get();
    }
}
