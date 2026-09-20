<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TbBarang;
use App\Models\TbDetailPenjualan;
use App\Models\TbPembelian;
use App\Models\TbPenjualan;
use App\Support\Tenant;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    /** Resolve rentang tanggal dari preset UI. */
    protected function range(Request $request): array
    {
        $preset = $request->query('preset', 'semua');

        return match ($preset) {
            'hari_ini' => [today()->startOfDay(), today()->endOfDay()],
            '7_hari' => [today()->subDays(6)->startOfDay(), today()->endOfDay()],
            '30_hari' => [today()->subDays(29)->startOfDay(), today()->endOfDay()],
            default => [
                $request->query('dari') ? Carbon::parse($request->query('dari'))->startOfDay() : null,
                $request->query('sampai') ? Carbon::parse($request->query('sampai'))->endOfDay() : null,
            ],
        };
    }

    public function penjualan(Request $request): JsonResponse
    {
        $user = $request->user();
        [$dari, $sampai] = $this->range($request);

        $q = TbPenjualan::query()->aktif()->with(['pelanggan:id_pelanggan,nama_pelanggan', 'user:id_user,nama_lengkap']);
        if (! Tenant::isSuperAdmin($user)) {
            $q->where('id_sekolah', $user->id_sekolah);
        } elseif ($request->filled('id_sekolah')) {
            $q->where('id_sekolah', $request->query('id_sekolah'));
        }
        if ($dari) {
            $q->where('tanggal_penjualan', '>=', $dari);
        }
        if ($sampai) {
            $q->where('tanggal_penjualan', '<=', $sampai);
        }

        $rows = (clone $q)->orderByDesc('tanggal_penjualan')->paginate((int) $request->query('per_page', 20));

        // Agregat + HPP (snapshot harga_beli detail) + laba kotor.
        $ids = (clone $q)->pluck('id_penjualan');
        $hpp = (float) TbDetailPenjualan::whereIn('id_penjualan', $ids)->sum(DB::raw('harga_beli * jumlah_barang'));
        $omzet = (float) (clone $q)->sum('total_faktur');

        return response()->json([
            'data' => $rows->items(),
            'meta' => [
                'current_page' => $rows->currentPage(), 'per_page' => $rows->perPage(),
                'total' => $rows->total(), 'last_page' => $rows->lastPage(),
            ],
            'ringkasan' => [
                'omzet' => $omzet,
                'hpp' => $hpp,
                'laba_kotor' => $omzet - $hpp,
                'jumlah_transaksi' => (clone $q)->count(),
                'sudah_bayar' => (float) (clone $q)->where('status_pembayaran', 'sudah bayar')->sum('total_faktur'),
                'belum_bayar' => (float) (clone $q)->where('status_pembayaran', 'belum bayar')->sum('total_faktur'),
            ],
        ]);
    }

    public function pembelian(Request $request): JsonResponse
    {
        $user = $request->user();
        [$dari, $sampai] = $this->range($request);

        $q = TbPembelian::query()->aktif()->with(['supplier:id_supplier,nama', 'user:id_user,nama_lengkap']);
        if (! Tenant::isSuperAdmin($user)) {
            $q->where('id_sekolah', $user->id_sekolah);
        } elseif ($request->filled('id_sekolah')) {
            $q->where('id_sekolah', $request->query('id_sekolah'));
        }
        if ($dari) {
            $q->where('tanggal_faktur', '>=', $dari);
        }
        if ($sampai) {
            $q->where('tanggal_faktur', '<=', $sampai);
        }

        $rows = (clone $q)->orderByDesc('tanggal_faktur')->paginate((int) $request->query('per_page', 20));

        return response()->json([
            'data' => $rows->items(),
            'meta' => [
                'current_page' => $rows->currentPage(), 'per_page' => $rows->perPage(),
                'total' => $rows->total(), 'last_page' => $rows->lastPage(),
            ],
            'ringkasan' => [
                'total_belanja' => (float) (clone $q)->sum('total_bayar'),
                'jumlah_transaksi' => (clone $q)->count(),
                'selesai' => (clone $q)->where('status_pembelian', 'selesai')->count(),
                'draft' => (clone $q)->where('status_pembelian', 'draft')->count(),
            ],
        ]);
    }

    public function stok(Request $request): JsonResponse
    {
        $user = $request->user();
        $q = TbBarang::query()->aktif()
            ->with(['kategori:id_kategori,nama', 'supplier:id_supplier,nama']);
        if (! Tenant::isSuperAdmin($user)) {
            $q->where('id_sekolah', $user->id_sekolah);
        } elseif ($request->filled('id_sekolah')) {
            $q->where('id_sekolah', $request->query('id_sekolah'));
        }
        if ($s = $request->query('search')) {
            $q->where(function ($w) use ($s) {
                $w->where('nama', 'like', "%{$s}%")->orWhere('barcode', 'like', "%{$s}%");
            });
        }

        $rows = (clone $q)->orderBy('stok')->paginate((int) $request->query('per_page', 20));
        $nilai = (float) (clone $q)->sum(DB::raw('harga_beli * stok'));

        return response()->json([
            'data' => $rows->items(),
            'meta' => [
                'current_page' => $rows->currentPage(), 'per_page' => $rows->perPage(),
                'total' => $rows->total(), 'last_page' => $rows->lastPage(),
            ],
            'ringkasan' => [
                'nilai_persediaan' => $nilai,
                'stok_menipis' => (clone $q)->where('stok', '<=', 10)->count(),
                'stok_habis' => (clone $q)->where('stok', '<=', 0)->count(),
            ],
        ]);
    }
}
