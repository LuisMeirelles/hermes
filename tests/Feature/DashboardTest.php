<?php

use App\Enums\CenarioStatus;
use App\Enums\Severidade;
use App\Enums\TesteStatus;
use App\Models\CasoDeTeste;
use App\Models\Cenario;
use App\Models\Teste;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

test('it reports aggregate teste status counts', function () {
    Teste::factory()->count(2)->create(['status' => TesteStatus::Passou]);
    Teste::factory()->create(['status' => TesteStatus::Falhou]);
    Teste::factory()->create(['status' => TesteStatus::Parcial]);
    Teste::factory()->create(['status' => TesteStatus::NaoIniciado]);
    Teste::factory()->create(['status' => TesteStatus::EmAndamento]);

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('stats.total', 6)
            ->where('stats.sucesso', 2)
            ->where('stats.falha', 1)
            ->where('stats.parcial', 1)
            ->where('stats.pendente', 2));
});

test('it reports zero stats when there are no testes', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('stats.total', 0)
            ->where('stats.sucesso', 0)
            ->where('stats.falha', 0)
            ->where('stats.parcial', 0)
            ->where('stats.pendente', 0));
});

test('it lists open cenarios with bloqueante or critica severidade', function () {
    $incluidos = [
        Cenario::factory()->create(['status' => CenarioStatus::AFazer, 'severidade' => Severidade::Critica]),
        Cenario::factory()->create(['status' => CenarioStatus::EmAndamento, 'severidade' => Severidade::Bloqueante]),
        Cenario::factory()->create(['status' => CenarioStatus::Falhou, 'severidade' => Severidade::Critica]),
        Cenario::factory()->create(['status' => CenarioStatus::Bloqueado, 'severidade' => Severidade::Bloqueante]),
    ];

    Cenario::factory()->create(['status' => CenarioStatus::Passou, 'severidade' => Severidade::Bloqueante]);
    Cenario::factory()->create(['status' => CenarioStatus::AFazer, 'severidade' => Severidade::Menor]);
    Cenario::factory()->create(['status' => CenarioStatus::Bloqueado, 'severidade' => Severidade::Maior]);

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where(
                'cenariosBloqueantes',
                fn ($cenarios) => collect($cenarios)->pluck('id')->sort()->values()->all()
                    === collect($incluidos)->pluck('id')->sort()->values()->all(),
            ));
});

test('it lists the 5 most recently updated testes, most recent first', function () {
    $testes = Teste::factory()->count(6)->create();
    $maisRecente = $testes->first();
    $maisRecente->touch();

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('testesRecentes', 5)
            ->where('testesRecentes.0.id', $maisRecente->id));
});

test('it reports caso de teste library totals including unused count', function () {
    $usado = CasoDeTeste::factory()->create();
    CasoDeTeste::factory()->create();
    CasoDeTeste::factory()->create();

    Cenario::factory()->for($usado, 'casoDeTeste')->create();

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('casosDeTeste.total', 3)
            ->where('casosDeTeste.naoUtilizados', 2));
});
