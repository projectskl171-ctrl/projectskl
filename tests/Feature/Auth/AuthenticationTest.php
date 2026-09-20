<?php

use App\Models\TbUser;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('login screen redirects to the single AuthPage entry', function () {
    $response = $this->get(route('login'));

    $response->assertRedirect('/');
});

test('users can authenticate with username and password', function () {
    $response = $this->post(route('login.store'), [
        'username' => 'sch001_kasir',
        'password' => '123',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can authenticate via the JSON API', function () {
    $response = $this->postJson('/api/auth/login', [
        'username' => 'sch001_admin',
        'password' => '123',
    ]);

    $response->assertOk()->assertJsonPath('data.username', 'sch001_admin');
    $response->assertJsonPath('data.role', 'admin');
    $response->assertJsonMissingPath('data.password');
    $this->assertAuthenticated();
});

test('users can not authenticate with invalid password', function () {
    $response = $this->postJson('/api/auth/login', [
        'username' => 'sch001_kasir',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422);
    $this->assertGuest();
});

test('inactive users can not authenticate', function () {
    $user = TbUser::where('username', 'sch001_kasir')->firstOrFail();
    $user->update(['is_active' => 0]);

    $response = $this->postJson('/api/auth/login', [
        'username' => 'sch001_kasir',
        'password' => '123',
    ]);

    $response->assertStatus(422);
    $this->assertGuest();
});

test('users can logout via the JSON API', function () {
    $user = TbUser::where('username', 'sch001_kasir')->firstOrFail();

    $response = $this->actingAs($user)->postJson('/api/auth/logout');

    $response->assertOk();
    $this->assertGuest();
});

test('users are rate limited', function () {
    RateLimiter::increment(md5('login'.implode('|', ['sch001_kasir', '127.0.0.1'])), amount: 5);

    $response = $this->post(route('login.store'), [
        'username' => 'sch001_kasir',
        'password' => 'wrong-password',
    ]);

    $response->assertTooManyRequests();
});
