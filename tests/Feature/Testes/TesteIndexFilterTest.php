<?php

use App\Enums\TesteStatus;
use App\Models\Teste;
use App\Models\User;

test('it returns all testes when no status filter is given', function () {
    Teste::factory()->count(3)->create();

    $this->actingAs(User::factory()->create())
        ->get(route('testes.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('testes', 3)
            ->where('statusFilter', null));
});

test('it filters testes by an exact status', function () {
    Teste::factory()->create(['status' => TesteStatus::Falhou]);
    Teste::factory()->count(2)->create(['status' => TesteStatus::Passou]);

    $this->actingAs(User::factory()->create())
        ->get(route('testes.index', ['status' => 'falhou']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('testes', 1)
            ->where('statusFilter', 'falhou'));
});

test('it filters testes by the pendente virtual group', function () {
    Teste::factory()->create(['status' => TesteStatus::NaoIniciado]);
    Teste::factory()->create(['status' => TesteStatus::EmAndamento]);
    Teste::factory()->create(['status' => TesteStatus::Passou]);
    Teste::factory()->create(['status' => TesteStatus::Falhou]);
    Teste::factory()->create(['status' => TesteStatus::Parcial]);

    $this->actingAs(User::factory()->create())
        ->get(route('testes.index', ['status' => 'pendente']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('testes', 2)
            ->where('statusFilter', 'pendente'));
});

test('it rejects an invalid status filter value', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('testes.index', ['status' => 'bogus']))
        ->assertInvalid(['status']);
});
