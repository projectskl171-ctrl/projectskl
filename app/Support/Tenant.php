<?php

namespace App\Support;

use App\Models\Role;
use App\Models\TbUser;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Helper isolasi tenant (sekolah).
 *
 * - super admin: lintas sekolah (tanpa filter).
 * - admin/kasir: hanya id_sekolah miliknya.
 */
class Tenant
{
    public static function user(): ?TbUser
    {
        /** @var TbUser|null $user */
        $user = auth()->user();

        return $user;
    }

    public static function isSuperAdmin(?TbUser $user = null): bool
    {
        $user ??= self::user();
        if (! $user) {
            return false;
        }
        $user->loadMissing('role');

        return $user->role?->nama_role === Role::SUPER_ADMIN;
    }

    public static function schoolId(?TbUser $user = null): ?int
    {
        $user ??= self::user();

        return $user?->id_sekolah;
    }

    /**
     * Terapkan filter sekolah pada query, kecuali super admin.
     * Untuk tabel tanpa id_sekolah langsung (tb_pelanggan), gunakan $via.
     */
    public static function scopeSchool(Builder $query, ?TbUser $user = null, string $column = 'id_sekolah'): Builder
    {
        $user ??= self::user();
        if (! $user || self::isSuperAdmin($user)) {
            return $query;
        }

        return $query->where($query->getModel()->getTable().'.'.$column, $user->id_sekolah);
    }

    /**
     * Pastikan sebuah model milik sekolah user. Lempar 404 bila tidak cocok
     * (404 disengaja agar tidak membocorkan keberadaan data sekolah lain).
     */
    public static function ensureOwnSchool(object $model, ?TbUser $user = null, string $column = 'id_sekolah'): void
    {
        $user ??= self::user();
        if (! $user || self::isSuperAdmin($user)) {
            return;
        }

        if ((int) ($model->{$column} ?? null) !== (int) $user->id_sekolah) {
            throw new HttpException(404, 'Data tidak ditemukan.');
        }
    }

    /**
     * Resolve id_sekolah yang boleh dipakai untuk create:
     * non-super-admin selalu memakai sekolahnya sendiri (abaikan input frontend).
     */
    public static function resolveSchoolId(?int $requested, ?TbUser $user = null): ?int
    {
        $user ??= self::user();
        if (! $user) {
            return $requested;
        }
        if (self::isSuperAdmin($user)) {
            return $requested ?? $user->id_sekolah;
        }

        return $user->id_sekolah;
    }
}
