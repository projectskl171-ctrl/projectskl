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

    /** Aktif/nonaktif sekolah. Minimal 1 sekolah tetap aktif. */
    public function toggle(int $id): JsonResponse
    {
        $sekolah = TbSekolah::findOrFail($id);

        if ($sekolah->is_active && TbSekolah::where('is_active', 1)->count() <= 1) {
            return response()->json(['message' => 'Minimal 1 sekolah harus tetap aktif.'], 422);
        }

        $sekolah->update(['is_active' => $sekolah->is_active ? 0 : 1]);

        return response()->json(['message' => 'Status sekolah diperbarui.', 'data' => $sekolah->fresh()]);
    }
}
