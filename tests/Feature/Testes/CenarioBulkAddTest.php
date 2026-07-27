<?php

use App\Enums\CenarioStatus;
use App\Enums\Severidade;
use App\Enums\TesteStatus;
use App\Models\CasoDeTeste;
use App\Models\CasoDeTestePasso;
use App\Models\GithubInstallation;
use App\Models\Teste;
use App\Models\User;

test('it snapshots the caso de teste title and passos even after the library case is edited later', function () {
    GithubInstallation::factory()->create();
    $teste = Teste::factory()->create();
    $casoDeTeste = CasoDeTeste::factory()->create(['titulo' => 'Login com sucesso']);
    CasoDeTestePasso::factory()->for($casoDeTeste, 'casoDeTeste')->create([
        'ordem' => 0,
        'palavra_chave' => 'dado',
        'texto' => 'passo original',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('testes.cenarios.store', $teste), [
            'casos' => [
                ['caso_de_teste_id' => $casoDeTeste->id, 'severidade' => Severidade::Maior->value],
            ],
        ])
        ->assertRedirect(route('testes.show', $teste));

    $cenario = $teste->cenarios()->sole();

    expect($cenario->titulo)->toBe('Login com sucesso');
    expect($cenario->passos_snapshot[0]['texto'])->toBe('passo original');
    expect($cenario->status)->toBe(CenarioStatus::AFazer);

    $casoDeTeste->update(['titulo' => 'Título editado depois']);
    $cenario->refresh();

    expect($cenario->titulo)->toBe('Login com sucesso');
});

test('bulk adding cenarios recalculates the teste aggregate to nao_iniciado', function () {
    GithubInstallation::factory()->create();
    $teste = Teste::factory()->create();
    $casoDeTeste = CasoDeTeste::factory()->has(CasoDeTestePasso::factory(), 'passos')->create();

    $this->actingAs(User::factory()->create())
        ->post(route('testes.cenarios.store', $teste), [
            'casos' => [
                ['caso_de_teste_id' => $casoDeTeste->id, 'severidade' => Severidade::Menor->value],
            ],
        ]);

    $teste->refresh();

    expect($teste->status)->toBe(TesteStatus::NaoIniciado);
    expect((float) $teste->percent_complete)->toBe(0.0);
});
