<?php

namespace Database\Factories;

use App\Models\CasoDeTeste;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CasoDeTeste>
 */
class CasoDeTesteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulo' => fake()->sentence(),
            'descricao' => fake()->optional()->paragraph(),
        ];
    }
}
