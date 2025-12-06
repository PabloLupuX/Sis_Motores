<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
use Illuminate\Support\Facades\Auth;

test('registration screen can be rendered', function () {
    Auth::logout(); // <--- cerrar sesión
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'username' => 'testuser', // <--- Agregar
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});