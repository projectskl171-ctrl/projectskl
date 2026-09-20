<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TbBarang;
use App\Models\TbPembelian;
use App\Models\TbPenjualan;
use App\Models\TbSekolah;
use App\Models\TbUser;
use App\Support\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Notifikasi READ-ONLY, dihitung dari kondisi database nyata.
 * Tidak ada tabel notifikasi di schema, jadi tidak ada CRUD.
 */
class NotifikasiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $role = $user->roleName();
        $items = [];

        $schoolFilter = fn ($q) => Tenant::isSuperAdmin($user) ? $q : $q->where('id_sekolah', $user->id_sekolah);

        // Stok menipis & habis (admin + kasir + super admin).
        $menipis = $schoolFilter(TbBarang::aktif()->where('stok', '<=', 10)->where('stok', '>', 0))->count();
        if ($menipis > 0) {
            $items[] = ['tipe' => 'stok_menipis', 'judul' => 'Stok menipis', 'pesan' => "{$menipis} produk memiliki stok 10 atau kurang.", 'jumlah' => $menipis];
        }
        $habis = $schoolFilter(TbBarang::aktif()->where('stok', '<=', 0))->count();
        if ($habis > 0) {
            $items[] = ['tipe' => 'stok_habis', 'judul' => 'Stok habis', 'pesan' => "{$habis} produk kehabisan stok.", 'jumlah' => $habis];
        }

        // Pembelian draft (admin).
        if (in_array($role, ['super admin', 'admin'], true)) {
            $draft = $schoolFilter(TbPembelian::aktif()->where('status_pembelian', 'draft'))->count();
            if ($draft > 0) {
                $items[] = ['tipe' => 'pembelian_draft', 'judul' => 'Draft pembelian', 'pesan' => "{$draft} pembelian masih berstatus draft.", 'jumlah' => $draft];
            }
        }

        // Transaksi kredit belum lunas (semua role).
        $piutang = $schoolFilter(TbPenjualan::aktif()->where('status_pembayaran', 'belum bayar'))->count();
        if ($piutang > 0) {
            $items[] = ['tipe' => 'piutang', 'judul' => 'Kredit belum lunas', 'pesan' => "{$piutang} transaksi kredit belum dibayar.", 'jumlah' => $piutang];
        }

        // Monitoring lintas sekolah (super admin).
        if (Tenant::isSuperAdmin($user)) {
            $sekolahOff = TbSekolah::where('is_active', 0)->count();
            if ($sekolahOff > 0) {
                $items[] = ['tipe' => 'sekolah_nonaktif', 'judul' => 'Sekolah nonaktif', 'pesan' => "{$sekolahOff} sekolah berstatus nonaktif.", 'jumlah' => $sekolahOff];
            }
            $userOff = TbUser::where('is_active', 0)->count();
            if ($userOff > 0) {
                $items[] = ['tipe' => 'user_nonaktif', 'judul' => 'User nonaktif', 'pesan' => "{$userOff} akun user berstatus nonaktif.", 'jumlah' => $userOff];
            }
        }

        return response()->json(['data' => $items, 'total' => count($items)]);
    }
}
