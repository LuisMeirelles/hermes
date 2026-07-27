<?php

use App\Enums\CenarioStatus;
use App\Enums\Severidade;
use App\Enums\TesteStatus;
use App\Models\Cenario;
use App\Models\GithubInstallation;
use App\Models\Teste;
use App\Models\User;

test('removing a cenario recalculates the teste aggregate', function () {
    GithubInstallation::factory()->create();
    $teste = Teste::factory()->create();
    $falho = Cenario::factory()->for($teste)->create([
        'status' => CenarioStatus::Falhou,
        'severidade' => Severidade::Critica,
    ]);
    Cenario::factory()->for($teste)->create(['status' => CenarioStatus::Passou]);

    expect($teste->fresh()->status)->toBe(TesteStatus::Falhou);

    $this->actingAs(User::factory()->create())
        ->delete(route('testes.cenarios.destroy', [$teste, $falho]))
        ->assertRedirect(route('testes.show', $teste));

    expect(Cenario::query()->find($falho->id))->toBeNull();
    expect($teste->fresh()->status)->toBe(TesteStatus::Passou);
});
