<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TbBarang;
use App\Models\TbPembelian;
use App\Models\TbPenjualan;
use App\Models\TbSekolah;
use App\Models\TbSupplier;
use App\Models\TbUser;
use App\Support\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (Tenant::isSuperAdmin($user)) {
            return response()->json(['data' => $this->superAdmin()]);
        }

        return response()->json(['data' => $this->sekolah($user->id_sekolah)]);
    }

    protected function sekolah(int $schoolId): array
    {
        $today = today()->toDateString();

        $produk = TbBarang::aktif()->where('id_sekolah', $schoolId);
        $jualHariIni = TbPenjualan::aktif()->where('id_sekolah', $schoolId)->whereDate('tanggal_penjualan', $today);

        $omzet7 = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = today()->subDays($i);
            $omzet7[] = [
                'label' => $d->locale('id')->dayName,
                'key' => $d->toDateString(),
                'total' => (float) TbPenjualan::aktif()->where('id_sekolah', $schoolId)
                    ->whereDate('tanggal_penjualan', $d->toDateString())->sum('total_faktur'),
            ];
        }

        return [
            'total_produk' => (clone $produk)->count(),
            'stok_menipis' => (clone $produk)->where('stok', '<=', 10)->count(),
            'stok_habis' => (clone $produk)->where('stok', '<=', 0)->count(),
            'total_penjualan' => (float) TbPenjualan::aktif()->where('id_sekolah', $schoolId)->sum('total_faktur'),
            'total_pembelian' => (float) TbPembelian::aktif()->where('id_sekolah', $schoolId)->sum('total_bayar'),
            'transaksi_hari_ini' => (clone $jualHariIni)->count(),
            'omzet_hari_ini' => (float) (clone $jualHariIni)->sum('total_faktur'),
            'pembelian_draft' => TbPembelian::aktif()->where('id_sekolah', $schoolId)->where('status_pembelian', 'draft')->count(),
            'piutang' => TbPenjualan::aktif()->where('id_sekolah', $schoolId)->where('status_pembayaran', 'belum bayar')->count(),
            'total_piutang' => (float) TbPenjualan::aktif()->where('id_sekolah', $schoolId)
                ->where('status_pembayaran', 'belum bayar')->sum('total_faktur'),
            'omzet_7_hari' => $omzet7,
            'transaksi_terbaru' => TbPenjualan::aktif()->where('id_sekolah', $schoolId)
                ->with(['pelanggan:id_pelanggan,nama_pelanggan', 'user:id_user,nama_lengkap'])
                ->orderByDesc('id_penjualan')->limit(8)->get(),
            'stok_menipis_list' => TbBarang::aktif()->where('id_sekolah', $schoolId)
                ->where('stok', '<=', 10)->orderBy('stok')->limit(8)
                ->get(['id_barang', 'nama', 'barcode', 'stok', 'satuan']),
        ];
    }

    protected function superAdmin(): array
    {
        $perSekolah = TbSekolah::query()
            ->withCount([
                'users',
                'barang as produk_count' => fn ($q) => $q->where('is_delete', 0),
                'penjualan as transaksi_count' => fn ($q) => $q->where('is_delete', 0),
            ])
            ->withSum(['penjualan as omzet' => fn ($q) => $q->aktif()], 'total_faktur')
            ->withSum(['pembelian as belanja' => fn ($q) => $q->aktif()], 'total_bayar')
            ->orderBy('id_sekolah')->get();

        return [
            'jumlah_sekolah' => TbSekolah::count(),
            'sekolah_aktif' => TbSekolah::where('is_active', 1)->count(),
            'sekolah_nonaktif' => TbSekolah::where('is_active', 0)->count(),
            'jumlah_user' => TbUser::count(),
            'user_nonaktif' => TbUser::where('is_active', 0)->count(),
            'total_produk' => TbBarang::aktif()->count(),
            'total_penjualan' => (float) TbPenjualan::aktif()->sum('total_faktur'),
            'total_pembelian' => (float) TbPembelian::aktif()->sum('total_bayar'),
            'transaksi_hari_ini' => TbPenjualan::aktif()->whereDate('tanggal_penjualan', today()->toDateString())->count(),
            'total_supplier' => TbSupplier::aktif()->count(),
            'per_sekolah' => $perSekolah,
            'transaksi_terbaru' => TbPenjualan::aktif()
                ->with(['sekolah:id_sekolah,nama_sekolah', 'user:id_user,nama_lengkap'])
                ->orderByDesc('id_penjualan')->limit(8)->get(),
        ];
    }
}
