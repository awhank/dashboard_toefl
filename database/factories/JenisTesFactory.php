<?php

namespace Database\Factories;

use App\Models\JenisTes;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JenisTes>
 */
class JenisTesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode' => fake()->unique()->lexify('???'),
            'nama' => fake()->words(3, true),
            'is_resmi' => true,
            'skor_bagian_min' => 31,
            'skor_bagian_max' => 68,
            'skor_total_min' => 310,
            'skor_total_max' => 677,
            'masa_berlaku_bulan' => 24,
        ];
    }

    /**
     * TOEFL ITP: resmi, berlaku 2 tahun.
     */
    public function itp(): static
    {
        return $this->state(fn (array $attributes) => [
            'kode' => 'ITP',
            'nama' => 'TOEFL ITP',
            'is_resmi' => true,
            'masa_berlaku_bulan' => 24,
        ]);
    }

    /**
     * TOEFL Prediction/EPT: informasi saja, tanpa masa berlaku.
     */
    public function prediction(): static
    {
        return $this->state(fn (array $attributes) => [
            'kode' => 'PRED',
            'nama' => 'TOEFL Prediction',
            'is_resmi' => false,
            'masa_berlaku_bulan' => null,
        ]);
    }
}
