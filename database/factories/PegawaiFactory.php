<?php

namespace Database\Factories;

use App\Models\Pegawai;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pegawai>
 */
class PegawaiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nomor_pegawai' => fake()->unique()->numerify('##################'),
            'jenis_nomor' => 'nip',
            'nama' => fake()->name(),
            'jenis_pegawai' => fake()->randomElement(['dosen', 'tendik']),
            'jenis_kelamin' => fake()->randomElement(['L', 'P']),
            'tanggal_lahir' => fake()->dateTimeBetween('-60 years', '-25 years')->format('Y-m-d'),
            'jabatan' => fake()->jobTitle(),
            'status_aktif' => true,
        ];
    }

    /**
     * Indicate that the employee is inactive.
     */
    public function nonaktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_aktif' => false,
            'tanggal_nonaktif' => fake()->dateTimeBetween('-1 year')->format('Y-m-d'),
        ]);
    }
}
