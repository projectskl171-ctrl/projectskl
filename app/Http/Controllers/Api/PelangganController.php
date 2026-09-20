<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PelangganRequest;
use App\Models\TbKelompokPelanggan;
use App\Models\TbPelanggan;
use App\Support\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    /** Tenant pelanggan dicek via relasi kelompok -> sekolah. */
    protected function scopeTenant($query, $user): void
    {
        if (Tenant::isSuperAdmin($user)) {
            return;
        }
        $query->whereHas('kelompok', fn ($q) => $q->where('id_sekolah', $user->id_sekolah));
    }

    public function kelompok(Request $request): JsonResponse
    {
        $q = TbKelompokPelanggan::query()->withCount(['pelanggan' => fn ($qq) => $qq->where('is_delete', 0)]);
        Tenant::scopeSchool($q, $request->user());

        return response()->json(['data' => $q->orderBy('id_kelompok_pelanggan')->get()]);
    }

    public function index(Request $request): JsonResponse
    {
        $q = TbPelanggan::query()->aktif()->with('kelompok:id_kelompok_pelanggan,nama_kelompok,id_sekolah');
        $this->scopeTenant($q, $request->user());

        if ($s = $request->query('search')) {
            $q->where(function ($w) use ($s) {
                $w->where('nama_pelanggan', 'like', "%{$s}%")->orWhere('telepon', 'like', "%{$s}%");
            });
        }
        if ($request->filled('id_kelompok_pelanggan')) {
            $q->where('id_kelompok_pelanggan', $request->query('id_kelompok_pelanggan'));
        }
        $q->orderByDesc('id_pelanggan');

        return response()->json($q->paginate((int) $request->query('per_page', 15)));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $pelanggan = TbPelanggan::aktif()->with('kelompok')->findOrFail($id);
        $this->assertTenant($pelanggan, $request->user());

        return response()->json(['data' => $pelanggan]);
    }

    public function store(PelangganRequest $request): JsonResponse
    {
        $user = $request->user();
        $pelanggan = TbPelanggan::create($request->validated() + [
            'created_by' => $user->id_user, 'is_delete' => 0,
        ]);

        return response()->json(['message' => 'Pelanggan berhasil ditambahkan.', 'data' => $pelanggan], 201);
    }

    public function update(PelangganRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        $pelanggan = TbPelanggan::aktif()->findOrFail($id);
        $this->assertTenant($pelanggan, $user);
        $pelanggan->update($request->validated() + ['updated_by' => $user->id_user]);

        return response()->json(['message' => 'Pelanggan berhasil diperbarui.', 'data' => $pelanggan->fresh()]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $pelanggan = TbPelanggan::aktif()->findOrFail($id);
        $this->assertTenant($pelanggan, $user);
        $pelanggan->update(['is_delete' => 1, 'deleted_at' => now(), 'deleted_by' => $user->id_user]);

        return response()->json(['message' => 'Pelanggan berhasil dihapus.']);
    }

    protected function assertTenant(TbPelanggan $pelanggan, $user): void
    {
        if (Tenant::isSuperAdmin($user)) {
            return;
        }
        $pelanggan->loadMissing('kelompok');
        if ((int) ($pelanggan->kelompok?->id_sekolah ?? 0) !== (int) $user->id_sekolah) {
            abort(404, 'Data tidak ditemukan.');
        }
    }
}
