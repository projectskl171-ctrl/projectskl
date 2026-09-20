<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\Role;
use App\Models\TbUser;
use App\Support\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function roles(): JsonResponse
    {
        return response()->json(['data' => Role::orderBy('id_role')->get()]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $q = TbUser::query()->with(['role:id_role,nama_role', 'sekolah:id_sekolah,nama_sekolah,kode_sekolah']);
        Tenant::scopeSchool($q, $user);

        // Admin sekolah hanya melihat kasir (sesuai batas UI).
        if ($user->isAdmin()) {
            $q->whereHas('role', fn ($qq) => $qq->where('nama_role', Role::KASIR));
        }

        if ($s = $request->query('search')) {
            $q->where(function ($w) use ($s) {
                $w->where('username', 'like', "%{$s}%")->orWhere('nama_lengkap', 'like', "%{$s}%");
            });
        }
        $q->orderByDesc('id_user');

        $paginator = $q->paginate((int) $request->query('per_page', 15));
        // $hidden password otomatis disembunyikan model; pastikan juga di array.
        $paginator->getCollection()->makeHidden(['password']);

        return response()->json($paginator);
    }

    public function store(UserRequest $request): JsonResponse
    {
        $actor = $request->user();
        $data = $request->validated();
        // Jangan pernah terima id_sekolah dari frontend untuk admin; paksa miliknya.
        $data['id_sekolah'] = Tenant::resolveSchoolId($request->input('id_sekolah'), $actor);
        $data['created_by'] = $actor->id_user;
        $data['is_active'] = $data['is_active'] ?? 1;

        $created = TbUser::create($data);

        return response()->json([
            'message' => 'User berhasil ditambahkan.',
            'data' => $created->fresh(['role', 'sekolah'])->makeHidden(['password']),
        ], 201);
    }

    public function update(UserRequest $request, int $id): JsonResponse
    {
        $actor = $request->user();
        $target = TbUser::findOrFail($id);
        Tenant::ensureOwnSchool($target, $actor);
        $this->assertManageable($actor, $target);

        $data = $request->validated();
        unset($data['id_sekolah']); // sekolah user tidak boleh dipindah via update
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $data['updated_by'] = $actor->id_user;
        $target->update($data);

        return response()->json([
            'message' => 'User berhasil diperbarui.',
            'data' => $target->fresh(['role', 'sekolah'])->makeHidden(['password']),
        ]);
    }

    /** Aktif/nonaktif akun. */
    public function toggle(Request $request, int $id): JsonResponse
    {
        $actor = $request->user();
        $target = TbUser::findOrFail($id);
        Tenant::ensureOwnSchool($target, $actor);
        $this->assertManageable($actor, $target);

        if ((int) $target->id_user === (int) $actor->id_user) {
            return response()->json(['message' => 'Tidak dapat menonaktifkan akun sendiri.'], 422);
        }

        $target->update([
            'is_active' => $target->is_active ? 0 : 1,
            'updated_by' => $actor->id_user,
        ]);

        return response()->json(['message' => 'Status user diperbarui.', 'data' => $target->fresh()->makeHidden(['password'])]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $actor = $request->user();
        $target = TbUser::findOrFail($id);
        Tenant::ensureOwnSchool($target, $actor);
        $this->assertManageable($actor, $target);

        if ((int) $target->id_user === (int) $actor->id_user) {
            return response()->json(['message' => 'Tidak dapat menghapus akun sendiri.'], 422);
        }

        // tb_user tidak punya is_delete: penonaktifan + cap hapus.
        $target->update([
            'is_active' => 0, 'deleted_at' => now(), 'deleted_by' => $actor->id_user,
        ]);

        return response()->json(['message' => 'User berhasil dinonaktifkan.']);
    }

    /** Admin hanya boleh mengelola kasir; cegah privilege escalation. */
    protected function assertManageable(TbUser $actor, TbUser $target): void
    {
        $actor->loadMissing('role');
        $target->loadMissing('role');
        if ($actor->role?->nama_role === Role::ADMIN && $target->role?->nama_role !== Role::KASIR) {
            abort(403, 'Admin hanya boleh mengelola akun kasir.');
        }
    }
}
