<?php

namespace Database\Factories;

use App\Enums\CenarioStatus;
use App\Enums\PalavraChaveGherkin;
use App\Enums\Severidade;
use App\Models\CasoDeTeste;
use App\Models\Cenario;
use App\Models\Teste;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cenario>
 */
class CenarioFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teste_id' => Teste::factory(),
            'caso_de_teste_id' => CasoDeTeste::factory(),
            'titulo' => fake()->sentence(),
            'passos_snapshot' => [
                ['ordem' => 0, 'palavra_chave' => PalavraChaveGherkin::Dado->value, 'texto' => fake()->sentence()],
                ['ordem' => 1, 'palavra_chave' => PalavraChaveGherkin::Entao->value, 'texto' => fake()->sentence()],
            ],
            'status' => CenarioStatus::AFazer,
            'severidade' => fake()->randomElement(Severidade::cases()),
        ];
    }
}
