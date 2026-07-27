<?php

namespace Database\Factories;

use App\Enums\TesteStatus;
use App\Models\Teste;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teste>
 */
class TesteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'repo_name' => fake()->slug(2),
            'issue_number' => fake()->unique()->numberBetween(1, 5000),
            'titulo' => fake()->sentence(),
            'status' => TesteStatus::NaoIniciado,
            'percent_complete' => 0,
        ];
    }
}
