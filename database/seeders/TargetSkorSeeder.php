<?php

namespace Database\Seeders;

use App\Models\TargetSkor;
use App\Models\User;
use Illuminate\Database\Seeder;

class TargetSkorSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->where('username', 'admin')->firstOrFail();

        foreach ([2023, 2024, 2025, 2026] as $tahun) {
            TargetSkor::query()->updateOrCreate(
                ['tahun' => $tahun],
                [
                    'skor_minimal' => 500,
                    'keterangan' => 'Target skor TOEFL ITP untuk syarat studi lanjut & sertifikasi dosen.',
                    'created_by' => $admin->id,
                ],
            );
        }
    }
}
