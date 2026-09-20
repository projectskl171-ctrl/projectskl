<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BarangRequest;
use App\Models\TbBarang;
use App\Support\Tenant;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = TbBarang::query()->aktif()
            ->with(['kategori:id_kategori,nama,id_kelompok', 'kelompokKategori:id_kelompok,nama_kelompok', 'supplier:id_supplier,nama']);
        Tenant::scopeSchool($q, $request->user());

        if ($s = $request->query('search')) {
            $q->where(function ($w) use ($s) {
                $w->where('nama', 'like', "%{$s}%")->orWhere('barcode', 'like', "%{$s}%");
            });
        }
        if ($request->filled('id_kategori')) {
            $q->where('id_kategori', $request->query('id_kategori'));
        }
        if ($request->filled('id_kelompok_kategori')) {
            $q->where('id_kelompok_kategori', $request->query('id_kelompok_kategori'));
        }
        if ($request->filled('id_supplier')) {
            $q->where('id_supplier', $request->query('id_supplier'));
        }
        if ($request->filled('stok_menipis')) {
            $q->where('stok', '<=', 10);
        }

        $q->orderByDesc('id_barang');

        return response()->json($q->paginate((int) $request->query('per_page', 15)));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $barang = TbBarang::aktif()
            ->with(['kategori', 'kelompokKategori', 'supplier'])
            ->findOrFail($id);
        Tenant::ensureOwnSchool($barang, $request->user());

        return response()->json(['data' => $barang]);
    }

    public function store(BarangRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $data['id_sekolah'] = Tenant::resolveSchoolId($request->input('id_sekolah'), $user);
        $data['created_by'] = $user->id_user;
        $data['is_delete'] = 0;

        try {
            $barang = TbBarang::create($data);
        } catch (QueryException $e) {
            // Pelanggaran UNIQUE (barcode, id_sekolah) — mis. barcode bekas data terhapus.
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json(['message' => 'Barcode sudah dipakai di sekolah ini.', 'errors' => ['barcode' => ['Barcode sudah dipakai di sekolah ini.']]], 422);
            }
            throw $e;
        }

        return response()->json(['message' => 'Produk berhasil ditambahkan.', 'data' => $barang], 201);
    }

    public function update(BarangRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        $barang = TbBarang::aktif()->findOrFail($id);
        Tenant::ensureOwnSchool($barang, $user);

        $data = $request->validated();
        unset($data['id_sekolah']); // sekolah tidak boleh dipindah via update
        $data['updated_by'] = $user->id_user;

        try {
            $barang->update($data);
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json(['message' => 'Barcode sudah dipakai di sekolah ini.', 'errors' => ['barcode' => ['Barcode sudah dipakai di sekolah ini.']]], 422);
            }
            throw $e;
        }

        return response()->json(['message' => 'Produk berhasil diperbarui.', 'data' => $barang->fresh()]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $barang = TbBarang::aktif()->findOrFail($id);
        Tenant::ensureOwnSchool($barang, $user);

        $barang->update([
            'is_delete' => 1, 'deleted_at' => now(),
            'deleted_by' => $user->id_user, 'is_active' => 0,
        ]);

        return response()->json(['message' => 'Produk berhasil dihapus.']);
    }

    /** Lookup barcode untuk kasir (scan): hanya sekolah sendiri. */
    public function lookup(Request $request): JsonResponse
    {
        $request->validate(['barcode' => ['required', 'string']]);
        $q = TbBarang::query()->aktif()->where('barcode', $request->input('barcode'));
        Tenant::scopeSchool($q, $request->user());
        $barang = $q->first();

        if (! $barang) {
            return response()->json(['message' => 'Produk tidak ditemukan.'], 404);
        }

        return response()->json(['data' => $barang]);
    }
}
