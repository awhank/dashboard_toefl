<?php

namespace Database\Factories;

use App\Models\ImportBatch;
use App\Models\PeriodeTes;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImportBatch>
 */
class ImportBatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $baris = fake()->numberBetween(10, 60);
        $gagal = fake()->numberBetween(0, 3);

        return [
            'periode_tes_id' => PeriodeTes::factory(),
            'user_id' => User::factory()->admin(),
            'nama_file' => 'hasil_toefl_'.fake()->unique()->numerify('####').'.xlsx',
            'jumlah_baris' => $baris,
            'jumlah_sukses' => $baris - $gagal,
            'jumlah_gagal' => $gagal,
            'status' => 'selesai',
        ];
    }
}
