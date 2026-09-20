<?php

use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen redirects to the single AuthPage entry', function () {
    $response = $this->get(route('register'));

    $response->assertRedirect('/');
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});
