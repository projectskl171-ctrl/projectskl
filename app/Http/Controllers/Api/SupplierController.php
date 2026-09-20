<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
use App\Models\TbSupplier;
use App\Support\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = TbSupplier::query()->aktif();
        Tenant::scopeSchool($q, $request->user());

        if ($s = $request->query('search')) {
            $q->where(function ($w) use ($s) {
                $w->where('nama', 'like', "%{$s}%")->orWhere('no_telepon', 'like', "%{$s}%");
            });
        }
        $q->orderByDesc('id_supplier');

        return response()->json($q->paginate((int) $request->query('per_page', 15)));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $supplier = TbSupplier::aktif()->findOrFail($id);
        Tenant::ensureOwnSchool($supplier, $request->user());

        return response()->json(['data' => $supplier]);
    }

    public function store(SupplierRequest $request): JsonResponse
    {
        $user = $request->user();
        $supplier = TbSupplier::create($request->validated() + [
            'id_sekolah' => Tenant::resolveSchoolId($request->input('id_sekolah'), $user),
            'created_by' => $user->id_user,
            'is_delete' => 0,
        ]);

        return response()->json(['message' => 'Supplier berhasil ditambahkan.', 'data' => $supplier], 201);
    }

    public function update(SupplierRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        $supplier = TbSupplier::aktif()->findOrFail($id);
        Tenant::ensureOwnSchool($supplier, $user);
        $supplier->update($request->validated());

        return response()->json(['message' => 'Supplier berhasil diperbarui.', 'data' => $supplier->fresh()]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $supplier = TbSupplier::aktif()->findOrFail($id);
        Tenant::ensureOwnSchool($supplier, $user);
        $supplier->update(['is_delete' => 1, 'deleted_at' => now(), 'deleted_by' => $user->id_user]);

        return response()->json(['message' => 'Supplier berhasil dihapus.']);
    }
}
