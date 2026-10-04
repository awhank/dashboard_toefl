<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Akun administratif (bukan pegawai yang dipantau TOEFL-nya). Password default sama dengan
     * username, sesuai kebijakan login — wajib diganti oleh pemilik akun setelah login pertama.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['username' => 'admin'],
            [
                'pegawai_id' => null,
                'name' => 'Administrator',
                'password' => Hash::make('admin'),
                'role' => 'admin',
                'is_active' => true,
            ],
        );

        User::query()->updateOrCreate(
            ['username' => 'pimpinan'],
            [
                'pegawai_id' => null,
                'name' => 'Pimpinan',
                'password' => Hash::make('pimpinan'),
                'role' => 'pimpinan',
                'is_active' => true,
            ],
        );
    }
}
