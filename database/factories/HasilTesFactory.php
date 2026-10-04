<?php

namespace Database\Factories;

use App\Models\HasilTes;
use App\Models\Pegawai;
use App\Models\PeriodeTes;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<HasilTes>
 */
class HasilTesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $listening = fake()->numberBetween(31, 68);
        $structure = fake()->numberBetween(31, 68);
        $reading = fake()->numberBetween(31, 68);
        $tanggalTes = Carbon::instance(fake()->dateTimeBetween('-2 years', 'now'));

        return [
            'periode_tes_id' => PeriodeTes::factory(),
            'pegawai_id' => Pegawai::factory(),
            'skor_listening' => $listening,
            'skor_structure' => $structure,
            'skor_reading' => $reading,
            'skor_total' => (int) round(($listening + $structure + $reading) * 10 / 3),
            'tanggal_tes' => $tanggalTes->format('Y-m-d'),
            'berlaku_sampai' => $tanggalTes->copy()->addMonths(24)->format('Y-m-d'),
            'no_sertifikat' => fake()->unique()->numerify('SERT-####-########'),
            'sumber_input' => 'impor',
            'import_batch_id' => null,
            'created_by' => User::factory()->admin(),
            'updated_by' => null,
        ];
    }

    /**
     * Diinput lewat form manual, bukan impor Excel.
     */
    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'sumber_input' => 'manual',
            'import_batch_id' => null,
        ]);
    }
}
