<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AuthController;
use App\Models\TbUser;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $payload = null;

        if ($user instanceof TbUser) {
            $user->loadMissing(['role', 'sekolah']);
            // Payload aman: tanpa password/hash. Dipakai sidebar + guard role di Vue.
            $payload = AuthController::userPayload($user);
        } elseif ($user) {
            // Model auth lain (mis. akun starter-kit pada test): bagikan
            // representasi aman minimal tanpa asumsi relasi tb_user.
            $payload = collect($user->makeVisible([])->toArray())
                ->except(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])
                ->all();
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => ['user' => $payload],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
