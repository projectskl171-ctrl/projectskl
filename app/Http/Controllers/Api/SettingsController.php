<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TbUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Pengaturan profil milik sendiri (admin & super admin).
 *
 * - Kasir TIDAK boleh mengubah profilnya (diatur admin sekolahnya).
 * - nama_lengkap: jeda 7 hari setelah ganti.
 * - username: jeda 30 hari setelah ganti + harus unik.
 * - password: wajib verifikasi password lama dulu, dilarang null/kosong.
 */
class SettingsController extends Controller
{
    public const FULLNAME_COOLDOWN_DAYS = 7;

    public const USERNAME_COOLDOWN_DAYS = 30;

    /** Profil sendiri + sisa cooldown (hari). */
    public function me(Request $request): JsonResponse
    {
        /** @var TbUser $user */
        $user = $request->user()->load(['role', 'sekolah']);

        return response()->json(['data' => [
            'id_user' => $user->id_user,
            'username' => $user->username,
            'nama_lengkap' => $user->nama_lengkap,
            'role' => $user->role?->nama_role,
            'sekolah' => $user->sekolah ? [
                'id_sekolah' => $user->sekolah->id_sekolah,
                'nama_sekolah' => $user->sekolah->nama_sekolah,
            ] : null,
            'nama_lengkap_cooldown_sisa_hari' => $this->sisaCooldown($user->nama_lengkap_changed_at, self::FULLNAME_COOLDOWN_DAYS),
            'username_cooldown_sisa_hari' => $this->sisaCooldown($user->username_changed_at, self::USERNAME_COOLDOWN_DAYS),
        ]]);
    }

    /** Ubah nama_lengkap / username sendiri (satu per satu, hormati cooldown). */
    public function updateProfile(Request $request): JsonResponse
    {
        /** @var TbUser $user */
        $user = $request->user();

        $data = $request->validate([
            'nama_lengkap' => ['sometimes', 'required', 'string', 'max:100'],
            'username' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('tb_user', 'username')->ignore($user->id_user, 'id_user')],
        ]);

        if (array_key_exists('nama_lengkap', $data) && $data['nama_lengkap'] !== $user->nama_lengkap) {
            if ($sisa = $this->sisaCooldown($user->nama_lengkap_changed_at, self::FULLNAME_COOLDOWN_DAYS)) {
                return response()->json([
                    'message' => "Nama lengkap baru bisa diganti lagi dalam {$sisa} hari.",
                    'errors' => ['nama_lengkap' => ["Tunggu {$sisa} hari lagi untuk ganti nama."]],
                ], 422);
            }
            $user->nama_lengkap = $data['nama_lengkap'];
            $user->nama_lengkap_changed_at = now();
        }

        if (array_key_exists('username', $data) && $data['username'] !== $user->username) {
            if ($sisa = $this->sisaCooldown($user->username_changed_at, self::USERNAME_COOLDOWN_DAYS)) {
                return response()->json([
                    'message' => "Username baru bisa diganti lagi dalam {$sisa} hari.",
                    'errors' => ['username' => ["Tunggu {$sisa} hari lagi untuk ganti username."]],
                ], 422);
            }
            $user->username = $data['username'];
            $user->username_changed_at = now();
        }

        $user->updated_by = $user->id_user;
        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'data' => $user->fresh()->makeHidden(['password']),
        ]);
    }

    /** Verifikasi password lama (langkah 1 popup ganti password). */
    public function checkPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'password_lama' => ['required', 'string'],
        ]);

        /** @var TbUser $user */
        $user = $request->user();

        if (! Hash::check($data['password_lama'], $user->password)) {
            return response()->json(['message' => 'Password lama salah.'], 422);
        }

        return response()->json(['message' => 'Password lama benar. Silakan isi password baru.']);
    }

    /** Simpan password baru (langkah 2, dilarang null/kosong). */
    public function updatePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'password_baru' => ['required', 'string', 'min:3', 'max:100', 'confirmed'],
        ]);

        /** @var TbUser $user */
        $user = $request->user();
        $user->password = $data['password_baru']; // cast `hashed`
        $user->updated_by = $user->id_user;
        $user->save();

        return response()->json(['message' => 'Password berhasil diganti.']);
    }

    /** Sisa hari cooldown, 0 = boleh ganti. */
    protected function sisaCooldown($changedAt, int $days): int
    {
        if (! $changedAt) {
            return 0;
        }
        $next = $changedAt->copy()->addDays($days)->startOfDay();
        $sisa = (int) now()->startOfDay()->diffInDays($next, false);

        return max(0, $sisa);
    }
}
