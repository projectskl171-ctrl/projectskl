<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SekolahRequest;
use App\Models\TbSekolah;
use App\Support\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SekolahController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $q = TbSekolah::query()->withCount([
            'users', 'barang as barang_count' => fn ($qq) => $qq->where('is_delete', 0),
        ]);

        if (! Tenant::isSuperAdmin($user)) {
            $q->where('id_sekolah', $user->id_sekolah);
        }
        if ($s = $request->query('search')) {
            $q->where(function ($w) use ($s) {
                $w->where('nama_sekolah', 'like', "%{$s}%")->orWhere('kode_sekolah', 'like', "%{$s}%");
            });
        }

        return response()->json(['data' => $q->orderBy('id_sekolah')->get()]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $sekolah = TbSekolah::findOrFail($id);
        Tenant::ensureOwnSchool($sekolah, $request->user());

        return response()->json(['data' => $sekolah]);
    }

    public function store(SekolahRequest $request): JsonResponse
    {
        $sekolah = TbSekolah::create($request->validated() + ['created_at' => now()]);

        return response()->json(['message' => 'Sekolah berhasil ditambahkan.', 'data' => $sekolah], 201);
    }

    public function update(SekolahRequest $request, int $id): JsonResponse
    {
        $sekolah = TbSekolah::findOrFail($id);
        $sekolah->update($request->validated());

        return response()->json(['message' => 'Sekolah berhasil diperbarui.', 'data' => $sekolah->fresh()]);
    }

    /** Aktif/nonaktif sekolah. Minimal 1 sekolah tetap aktif.
     *  Menonaktifkan sekolah ikut menonaktifkan SEMUA akun kasir & admin
     *  di sekolah itu (tidak bisa login). Mengaktifkan kembali
     *  mengembalikan akun-akun yang belum dihapus. */
    public function toggle(int $id): JsonResponse
    {
        $sekolah = TbSekolah::findOrFail($id);

        if ($sekolah->is_active && TbSekolah::where('is_active', 1)->count() <= 1) {
            return response()->json(['message' => 'Minimal 1 sekolah harus tetap aktif.'], 422);
        }

        $baru = $sekolah->is_active ? 0 : 1;
        $sekolah->update(['is_active' => $baru]);

        // Cascade ke akun: nonaktif => semua user ikut mati;
        // aktif => semua user yang belum dihapus ikut hidup lagi.
        $sekolah->users()->whereNull('deleted_at')->update(['is_active' => $baru]);

        return response()->json([
            'message' => $baru
                ? 'Sekolah diaktifkan. Akun kasir & admin di dalamnya ikut aktif.'
                : 'Sekolah dinonaktifkan. Akun kasir & admin di dalamnya ikut nonaktif.',
            'data' => $sekolah->fresh(),
        ]);
    }

    /** Hapus sekolah beserta akun user di dalamnya.
     *  Ditolak bila sekolah masih punya data operasional
     *  (barang / penjualan / pembelian). */
    public function destroy(int $id): JsonResponse
    {
        $sekolah = TbSekolah::findOrFail($id);

        if (TbSekolah::where('is_active', 1)->count() <= 1 && $sekolah->is_active) {
            return response()->json(['message' => 'Tidak dapat menghapus satu-satunya sekolah aktif.'], 422);
        }

        $punyaBarang = $sekolah->barang()->where('is_delete', 0)->count();
        $punyaJual = $sekolah->penjualan()->where('is_delete', 0)->count();
        $punyaBeli = $sekolah->pembelian()->where('is_delete', 0)->count();
        if ($punyaBarang + $punyaJual + $punyaBeli > 0) {
            return response()->json([
                'message' => "Sekolah masih punya data ({$punyaBarang} barang, {$punyaJual} penjualan, {$punyaBeli} pembelian). Kosongkan dulu sebelum menghapus.",
            ], 422);
        }

        $sekolah->users()->delete();
        $sekolah->delete();

        return response()->json(['message' => 'Sekolah beserta akun di dalamnya berhasil dihapus.']);
    }
}
