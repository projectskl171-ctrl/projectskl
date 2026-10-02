<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TbNotifikasi;
use App\Support\NotifikasiService;
use App\Support\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Notifikasi persisten (tb_notifikasi).
 * - GET /api/notifikasi: generate terbaru + list sesuai role/sekolah.
 * - POST /api/notifikasi/{id}/read: tandai 1 dibaca.
 * - POST /api/notifikasi/read-all: tandai semua dibaca (scope user).
 */
class NotifikasiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        NotifikasiService::ensureForUser($user);

        $items = NotifikasiService::listForUser($user, 100);
        $unread = NotifikasiService::unreadCount($user);

        return response()->json([
            'data' => $items,
            'total' => $items->count(),
            'unread' => $unread,
        ]);
    }

    public function read(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $notif = TbNotifikasi::findOrFail($id);
        $this->assertVisible($notif, $user);

        $notif->update(['is_read' => 1, 'read_at' => now()]);

        return response()->json([
            'message' => 'Notifikasi ditandai dibaca.',
            'unread' => NotifikasiService::unreadCount($user),
        ]);
    }

    public function readAll(Request $request): JsonResponse
    {
        $user = $request->user();

        NotifikasiService::ensureForUser($user);
        $items = NotifikasiService::listForUser($user, 500);
        $ids = $items->where('is_read', 0)->pluck('id_notifikasi');

        if ($ids->isNotEmpty()) {
            TbNotifikasi::whereIn('id_notifikasi', $ids)->update(['is_read' => 1, 'read_at' => now()]);
        }

        return response()->json([
            'message' => 'Semua notifikasi ditandai dibaca.',
            'unread' => 0,
        ]);
    }

    protected function assertVisible(TbNotifikasi $notif, $user): void
    {
        if (Tenant::isSuperAdmin($user)) {
            if ($notif->role_target !== 'super admin') {
                abort(404, 'Data tidak ditemukan.');
            }

            return;
        }

        if ((int) ($notif->id_sekolah ?? 0) !== (int) $user->id_sekolah) {
            abort(404, 'Data tidak ditemukan.');
        }

        $role = $user->roleName();
        if ($role === 'kasir') {
            $ok = $notif->role_target === 'kasir' || (int) ($notif->id_user ?? 0) === (int) $user->id_user;
            if (! $ok) {
                abort(404, 'Data tidak ditemukan.');
            }
        } elseif ($role === 'admin') {
            // Admin HANYA stok + congrats sekolah.
            if ($notif->role_target !== 'admin') {
                abort(404, 'Data tidak ditemukan.');
            }
        }
    }
}
