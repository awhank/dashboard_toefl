<?php

namespace Database\Seeders;

use App\Models\JenisTes;
use Illuminate\Database\Seeder;

class JenisTesSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        JenisTes::query()->updateOrCreate(
            ['kode' => 'ITP'],
            [
                'nama' => 'TOEFL ITP',
                'is_resmi' => true,
                'skor_bagian_min' => 31,
                'skor_bagian_max' => 68,
                'skor_total_min' => 310,
                'skor_total_max' => 677,
                'masa_berlaku_bulan' => 24,
            ],
        );

        JenisTes::query()->updateOrCreate(
            ['kode' => 'PRED'],
            [
                'nama' => 'TOEFL Prediction',
                'is_resmi' => false,
                'skor_bagian_min' => 31,
                'skor_bagian_max' => 68,
                'skor_total_min' => 310,
                'skor_total_max' => 677,
                'masa_berlaku_bulan' => null,
            ],
        );
    }
}
