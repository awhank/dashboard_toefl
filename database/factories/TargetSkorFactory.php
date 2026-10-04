<?php

namespace Database\Factories;

use App\Models\TargetSkor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TargetSkor>
 */
class TargetSkorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tahun' => fake()->unique()->numberBetween(2023, 2030),
            'skor_minimal' => 500,
            'keterangan' => null,
            'created_by' => User::factory()->admin(),
        ];
    }
}
