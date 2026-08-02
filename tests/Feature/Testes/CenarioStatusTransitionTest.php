<?php

use App\Enums\CenarioStatus;
use App\Enums\Severidade;
use App\Enums\TesteStatus;
use App\Models\Cenario;
use App\Models\GithubInstallation;
use App\Models\Teste;
use App\Models\User;

test('a valid status transition persists and recalculates the teste aggregate', function () {
    GithubInstallation::factory()->create();
    $teste = Teste::factory()->create();
    $cenario = Cenario::factory()->for($teste)->create([
        'status' => CenarioStatus::AFazer,
        'severidade' => Severidade::Critica,
    ]);

    $this->actingAs(User::factory()->create())
        ->patch(route('testes.cenarios.update', [$teste, $cenario]), ['status' => CenarioStatus::EmAndamento->value])
        ->assertRedirect(route('testes.show', $teste));

    expect($cenario->fresh()->status)->toBe(CenarioStatus::EmAndamento);
    expect($teste->fresh()->status)->toBe(TesteStatus::EmAndamento);
});

test('an invalid status transition is rejected', function () {
    GithubInstallation::factory()->create();
    $teste = Teste::factory()->create();
    $cenario = Cenario::factory()->for($teste)->create(['status' => CenarioStatus::AFazer]);

    $this->actingAs(User::factory()->create())
        ->patch(route('testes.cenarios.update', [$teste, $cenario]), ['status' => CenarioStatus::Passou->value])
        ->assertInvalid(['status']);

    expect($cenario->fresh()->status)->toBe(CenarioStatus::AFazer);
});

test('reopening a terminal cenario back to em_andamento recalculates the teste aggregate', function () {
    GithubInstallation::factory()->create();
    $teste = Teste::factory()->create();
    $cenario = Cenario::factory()->for($teste)->create([
        'status' => CenarioStatus::Falhou,
        'severidade' => Severidade::Menor,
    ]);

    expect($teste->fresh()->status)->toBe(TesteStatus::Falhou);

    $this->actingAs(User::factory()->create())
        ->patch(route('testes.cenarios.update', [$teste, $cenario]), ['status' => CenarioStatus::EmAndamento->value])
        ->assertRedirect(route('testes.show', $teste));

    expect($teste->fresh()->status)->toBe(TesteStatus::EmAndamento);
});
