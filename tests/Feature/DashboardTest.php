<?php

use App\Models\TbUser;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = TbUser::where('username', 'sch001_kasir')->firstOrFail();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('kasir can not visit admin pages', function () {
    $user = TbUser::where('username', 'sch001_kasir')->firstOrFail();
    $this->actingAs($user);

    $this->get(route('produk'))->assertRedirect(route('dashboard'));
});

test('admin can not visit kasir pages', function () {
    $user = TbUser::where('username', 'sch001_admin')->firstOrFail();
    $this->actingAs($user);

    $this->get(route('transaksi'))->assertRedirect(route('dashboard'));
});
