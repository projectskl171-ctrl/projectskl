<?php

namespace App\Http\Controllers;

use App\Models\TbUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Login JSON (dipakai fetch/Axios dari Vue) berbasis tb_user + session.
 * Login form Inertia (POST /login) ditangani Fortify + callback authenticateUsing.
 */
class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        /** @var TbUser|null $user */
        $user = TbUser::with(['role', 'sekolah'])
            ->where('username', $data['username'])
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Username atau password salah.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'username' => ['Akun nonaktif. Hubungi admin.'],
            ]);
        }

        Auth::login($user, (bool) ($data['remember'] ?? false));
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => self::userPayload($user->fresh(['role', 'sekolah'])),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var TbUser $user */
        $user = $request->user()->load(['role', 'sekolah']);

        return response()->json(['data' => self::userPayload($user)]);
    }

    public static function userPayload(TbUser $user): array
    {
        return [
            'id_user' => $user->id_user,
            'username' => $user->username,
            'nama_lengkap' => $user->nama_lengkap,
            'id_sekolah' => $user->id_sekolah,
            'id_role' => $user->id_role,
            'role' => $user->role?->nama_role,
            'is_active' => $user->is_active,
            'sekolah' => $user->sekolah ? [
                'id_sekolah' => $user->sekolah->id_sekolah,
                'kode_sekolah' => $user->sekolah->kode_sekolah,
                'nama_sekolah' => $user->sekolah->nama_sekolah,
            ] : null,
        ];
    }
}
