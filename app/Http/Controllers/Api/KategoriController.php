<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TbKategori;
use App\Models\TbKelompokKategori;
use App\Support\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    /** Daftar kelompok + kategori milik sekolah (dipakai halaman Produk). */
    public function index(Request $request): JsonResponse
    {
        $q = TbKelompokKategori::query()->with(['kategori' => fn ($qq) => $qq->aktif()->orderBy('nama')]);
        Tenant::scopeSchool($q, $request->user());

        return response()->json(['data' => $q->orderBy('nama_kelompok')->get()]);
    }

    public function storeKelompok(Request $request): JsonResponse
    {
        $data = $request->validate(['nama_kelompok' => ['required', 'string', 'max:100']]);
        $user = $request->user();
        $kelompok = TbKelompokKategori::create($data + [
            'id_sekolah' => Tenant::resolveSchoolId($request->input('id_sekolah'), $user),
            'created_at' => now(), 'created_by' => $user->id_user,
        ]);

        return response()->json(['message' => 'Kelompok kategori berhasil ditambahkan.', 'data' => $kelompok], 201);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_kelompok' => ['required', 'integer', 'exists:tb_kelompok_kategori,id_kelompok'],
            'nama' => ['required', 'string', 'max:100'],
        ]);
        $user = $request->user();

        $kelompok = TbKelompokKategori::findOrFail($data['id_kelompok']);
        Tenant::ensureOwnSchool($kelompok, $user);

        $kategori = TbKategori::create($data + [
            'created_at' => now(), 'created_by' => $user->id_user, 'is_delete' => 0,
        ]);

        return response()->json(['message' => 'Kategori berhasil ditambahkan.', 'data' => $kategori], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['nama' => ['required', 'string', 'max:100']]);
        $user = $request->user();
        $kategori = TbKategori::aktif()->with('kelompok')->findOrFail($id);
        Tenant::ensureOwnSchool($kategori->kelompok, $user);
        $kategori->update($data + ['updated_by' => $user->id_user]);

        return response()->json(['message' => 'Kategori berhasil diperbarui.', 'data' => $kategori->fresh()]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $kategori = TbKategori::aktif()->with('kelompok')->findOrFail($id);
        Tenant::ensureOwnSchool($kategori->kelompok, $user);
        $kategori->update(['is_delete' => 1, 'deleted_at' => now(), 'deleted_by' => $user->id_user]);

        return response()->json(['message' => 'Kategori berhasil dihapus.']);
    }
}
