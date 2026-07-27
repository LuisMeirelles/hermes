<?php

namespace Database\Factories;

use App\Enums\PalavraChaveGherkin;
use App\Models\CasoDeTeste;
use App\Models\CasoDeTestePasso;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CasoDeTestePasso>
 */
class CasoDeTestePassoFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'caso_de_teste_id' => CasoDeTeste::factory(),
            'ordem' => fake()->unique()->numberBetween(0, 999),
            'palavra_chave' => fake()->randomElement(PalavraChaveGherkin::cases()),
            'texto' => fake()->sentence(),
        ];
    }
}
