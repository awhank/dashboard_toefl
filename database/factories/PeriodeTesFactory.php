<?php

namespace Database\Factories;

use App\Models\JenisTes;
use App\Models\PeriodeTes;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PeriodeTes>
 */
class PeriodeTesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tahun = fake()->numberBetween(2023, 2026);
        $gelombang = fake()->numberBetween(1, 2);

        return [
            'jenis_tes_id' => JenisTes::factory(),
            'tahun' => $tahun,
            'gelombang' => $gelombang,
            'nama' => "TOEFL ITP {$tahun} Gelombang {$gelombang}",
            'tanggal_pelaksanaan' => fake()->dateTimeBetween("{$tahun}-01-01", "{$tahun}-12-31")->format('Y-m-d'),
            'penyelenggara' => 'Pusat Bahasa Poltekkes Banjarmasin',
            'keterangan' => null,
            'created_by' => User::factory()->admin(),
        ];
    }
}
