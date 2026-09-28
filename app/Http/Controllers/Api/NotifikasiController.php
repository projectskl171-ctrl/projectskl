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

        // Super admin tidak butuh stok/piutang/draft: cukup info
        // sekolah & admin yang bertambah hari ini.
        if (Tenant::isSuperAdmin($user)) {
            return response()->json([
                'data' => $this->superAdminHariIni(),
                'total' => count($this->superAdminHariIni()),
            ]);
        }

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

        return response()->json(['data' => $items, 'total' => count($items)]);
    }

    /** Super admin: sekolah & admin yang bertambah hari ini saja. */
    protected function superAdminHariIni(): array
    {
        $items = [];
        $today = today()->toDateString();

        $sekolahBaru = TbSekolah::whereDate('created_at', $today)->orderBy('id_sekolah')->get();
        foreach ($sekolahBaru as $s) {
            $items[] = [
                'tipe' => 'sekolah_baru', 'judul' => "Sekolah baru: {$s->nama_sekolah}",
                'pesan' => "{$s->kode_sekolah} • terdaftar hari ini.", 'jumlah' => 1,
            ];
        }

        $adminBaru = TbUser::whereDate('created_at', $today)
            ->whereHas('role', fn ($q) => $q->where('nama_role', 'admin'))
            ->with('sekolah:id_sekolah,nama_sekolah')
            ->orderBy('id_user')->get();
        foreach ($adminBaru as $u) {
            $items[] = [
                'tipe' => 'admin_baru', 'judul' => "Admin baru: {$u->nama_lengkap}",
                'pesan' => "@{$u->username} • ".($u->sekolah?->nama_sekolah ?? 'tanpa sekolah').' • hari ini.', 'jumlah' => 1,
            ];
        }

        return $items;
    }
}
