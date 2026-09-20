<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Otorisasi berbasis role tb_user -> roles.nama_role.
 * Pemakaian: ->middleware('role:super admin,admin')
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return $request->expectsJson() || $request->is('api/*')
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : redirect('/');
        }

        $role = null;
        if (method_exists($user, 'role')) {
            $user->loadMissing('role');
            $role = $user->role?->nama_role;
        }

        if (! in_array($role, $roles, true)) {
            // API: 403 JSON tegas. Web/Inertia: redirect agar UI tidak rusak
            // (sidebar + RoleDenied tetap menjadi lapisan UX).
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Forbidden. Peran anda tidak diizinkan.'], 403);
            }

            return redirect('/dashboard')->with('error', 'Halaman tidak tersedia untuk peran anda.');
        }

        return $next($request);
    }
}
