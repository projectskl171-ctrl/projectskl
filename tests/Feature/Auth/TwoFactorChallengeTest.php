<?php

use Database\Seeders\DatabaseSeeder;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());
    $this->seed(DatabaseSeeder::class);
});

test('two factor challenge redirects to login when not authenticated', function () {
    $response = $this->get(route('two-factor.login'));

    $response->assertRedirect(route('login'));
});

test('tb_user accounts have no two factor challenge step', function () {
    // tb_user (schema guru) tidak memiliki kolom 2FA; login username
    // langsung terautentikasi tanpa challenge.
    $this->post(route('login.store'), [
        'username' => 'sch001_kasir',
        'password' => '123',
    ]);

    $this->assertAuthenticated();
    // Tidak ada challenge: user terautentikasi penuh langsung diarahkan
    // ke dashboard bila membuka halaman challenge.
    $this->get(route('two-factor.login'))->assertRedirect(route('dashboard'));
});
