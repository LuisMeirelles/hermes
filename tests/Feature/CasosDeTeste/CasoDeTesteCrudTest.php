<?php

use App\Models\CasoDeTeste;
use App\Models\CasoDeTestePasso;
use App\Models\User;

test('guests cannot access the caso de teste library', function () {
    $this->get(route('casos-de-teste.index'))->assertRedirect(route('login'));
});

test('it lists casos de teste with their passo count', function () {
    CasoDeTeste::factory()
        ->has(CasoDeTestePasso::factory()->count(2), 'passos')
        ->create(['titulo' => 'Login com sucesso']);

    $this->actingAs(User::factory()->create())
        ->get(route('casos-de-teste.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('casosDeTeste', 1)
            ->where('casosDeTeste.0.titulo', 'Login com sucesso')
            ->where('casosDeTeste.0.passos_count', 2));
});

test('it creates a caso de teste with ordered gherkin passos', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('casos-de-teste.store'), [
            'titulo' => 'Login com sucesso',
            'descricao' => null,
            'passos' => [
                ['palavra_chave' => 'dado', 'texto' => 'que o usuário está na tela de login'],
                ['palavra_chave' => 'quando', 'texto' => 'ele informa credenciais válidas'],
                ['palavra_chave' => 'entao', 'texto' => 'ele é redirecionado ao dashboard'],
            ],
        ])
        ->assertRedirect();

    $casoDeTeste = CasoDeTeste::query()->sole();

    expect($casoDeTeste->titulo)->toBe('Login com sucesso');
    expect($casoDeTeste->passos()->count())->toBe(3);
    expect($casoDeTeste->passos()->orderBy('ordem')->pluck('palavra_chave')->map->value->all())
        ->toBe(['dado', 'quando', 'entao']);
});

test('it rejects a caso de teste with zero passos', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('casos-de-teste.store'), [
            'titulo' => 'Sem passos',
            'passos' => [],
        ])
        ->assertInvalid(['passos']);

    expect(CasoDeTeste::query()->count())->toBe(0);
});

test('updating a caso de teste replaces all of its passos', function () {
    $casoDeTeste = CasoDeTeste::factory()
        ->has(CasoDeTestePasso::factory()->count(2), 'passos')
        ->create();

    $this->actingAs(User::factory()->create())
        ->patch(route('casos-de-teste.update', $casoDeTeste), [
            'titulo' => $casoDeTeste->titulo,
            'passos' => [
                ['palavra_chave' => 'dado', 'texto' => 'novo passo dado'],
                ['palavra_chave' => 'quando', 'texto' => 'novo passo quando'],
                ['palavra_chave' => 'entao', 'texto' => 'novo passo então'],
            ],
        ])
        ->assertRedirect(route('casos-de-teste.edit', $casoDeTeste));

    expect($casoDeTeste->passos()->count())->toBe(3);
    expect($casoDeTeste->passos()->orderBy('ordem')->pluck('texto')->all())
        ->toBe(['novo passo dado', 'novo passo quando', 'novo passo então']);
});

test('it deletes a caso de teste', function () {
    $casoDeTeste = CasoDeTeste::factory()->create();

    $this->actingAs(User::factory()->create())
        ->delete(route('casos-de-teste.destroy', $casoDeTeste))
        ->assertRedirect(route('casos-de-teste.index'));

    expect(CasoDeTeste::query()->find($casoDeTeste->id))->toBeNull();
});

test('it rejects passos out of the dado/quando/entao order', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('casos-de-teste.store'), [
            'titulo' => 'Fora de ordem',
            'passos' => [
                ['palavra_chave' => 'quando', 'texto' => 'ele faz algo'],
                ['palavra_chave' => 'dado', 'texto' => 'que o usuário está logado'],
            ],
        ])
        ->assertInvalid(['passos.1.palavra_chave']);

    expect(CasoDeTeste::query()->count())->toBe(0);
});

test('it rejects a passo starting with e/mas before any dado/quando/entao', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('casos-de-teste.store'), [
            'titulo' => 'Começa com E',
            'passos' => [
                ['palavra_chave' => 'e', 'texto' => 'algo mais acontece'],
            ],
        ])
        ->assertInvalid(['passos.0.palavra_chave']);

    expect(CasoDeTeste::query()->count())->toBe(0);
});

test('it returns json with the created caso de teste when the request wants json', function () {
    $response = $this->actingAs(User::factory()->create())
        ->postJson(route('casos-de-teste.store'), [
            'titulo' => 'Login com sucesso',
            'passos' => [
                ['palavra_chave' => 'dado', 'texto' => 'que o usuário está na tela de login'],
                ['palavra_chave' => 'quando', 'texto' => 'ele informa credenciais válidas'],
                ['palavra_chave' => 'entao', 'texto' => 'ele é redirecionado ao dashboard'],
            ],
        ]);

    $response->assertCreated()
        ->assertJsonPath('titulo', 'Login com sucesso')
        ->assertJsonCount(3, 'passos');
});

test('it rejects a caso de teste missing a whole fase', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('casos-de-teste.store'), [
            'titulo' => 'Sem a fase Quando',
            'passos' => [
                ['palavra_chave' => 'dado', 'texto' => 'que o usuário está logado'],
                ['palavra_chave' => 'entao', 'texto' => 'ele vê o painel'],
            ],
        ])
        ->assertInvalid(['passos']);

    expect(CasoDeTeste::query()->count())->toBe(0);
});
