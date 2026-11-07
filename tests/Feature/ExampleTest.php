<?php

use App\Models\User;

test('unauthenticated users are redirected to login', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('login'));
});

test('authenticated admin or pimpinan users are redirected to dashboard', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->get(route('home'));

    $response->assertRedirect('/dashboard');
});

test('authenticated pegawai users are redirected to profil', function () {
    $pegawaiUser = User::factory()->create([
        'role' => 'pegawai',
        'is_active' => true,
    ]);

    $response = $this->actingAs($pegawaiUser)->get(route('home'));

    $response->assertRedirect('/profil');
});
