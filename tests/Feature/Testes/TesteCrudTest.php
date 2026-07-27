<?php

use App\Models\Cenario;
use App\Models\GithubInstallation;
use App\Models\Teste;
use App\Models\User;
use Illuminate\Support\Facades\Http;

function fakeGithubInstallation(): GithubInstallation
{
    return GithubInstallation::factory()->create(['account_login' => 'octocat']);
}

test('guests cannot access testes', function () {
    $this->get(route('testes.index'))->assertRedirect(route('login'));
});

test('create loads repositories from the active installation', function () {
    fakeGithubInstallation();

    Http::fake([
        'api.github.com/app/installations/*/access_tokens' => Http::response(['token' => 'ghs_test_token'], 201),
        'api.github.com/installation/repositories*' => Http::response([
            'repositories' => [['name' => 'hermes', 'full_name' => 'octocat/hermes']],
        ]),
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('testes.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('repositorios.0.full_name', 'octocat/hermes'));
});

test('it creates a teste when the linked issue exists', function () {
    fakeGithubInstallation();

    Http::fake([
        'api.github.com/app/installations/*/access_tokens' => Http::response(['token' => 'ghs_test_token'], 201),
        'api.github.com/repos/octocat/hermes/issues/7' => Http::response([
            'title' => 'Bug found',
            'state' => 'open',
            'html_url' => 'https://github.com/octocat/hermes/issues/7',
        ]),
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('testes.store'), [
            'repo_name' => 'hermes',
            'issue_number' => 7,
            'titulo' => 'Bug found',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('testes', ['repo_name' => 'hermes', 'issue_number' => 7]);
});

test('it rejects a teste when the linked issue does not exist', function () {
    fakeGithubInstallation();

    Http::fake([
        'api.github.com/app/installations/*/access_tokens' => Http::response(['token' => 'ghs_test_token'], 201),
        'api.github.com/repos/octocat/hermes/issues/*' => Http::response(['message' => 'Not Found'], 404),
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('testes.store'), [
            'repo_name' => 'hermes',
            'issue_number' => 999,
        ])
        ->assertInvalid(['issue_number']);

    expect(Teste::query()->count())->toBe(0);
});

test('show fetches the linked issue live', function () {
    fakeGithubInstallation();
    $teste = Teste::factory()->create(['repo_name' => 'hermes', 'issue_number' => 7]);

    Http::fake([
        'api.github.com/app/installations/*/access_tokens' => Http::response(['token' => 'ghs_test_token'], 201),
        'api.github.com/repos/octocat/hermes/issues/7' => Http::response([
            'title' => 'Bug found',
            'state' => 'open',
            'html_url' => 'https://github.com/octocat/hermes/issues/7',
        ]),
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('testes.show', $teste))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('issue.title', 'Bug found'));
});

test('deleting a teste cascades its cenarios', function () {
    fakeGithubInstallation();
    $teste = Teste::factory()->create();
    $cenario = Cenario::factory()->for($teste)->create();

    $this->actingAs(User::factory()->create())
        ->delete(route('testes.destroy', $teste))
        ->assertRedirect(route('testes.index'));

    expect(Teste::query()->find($teste->id))->toBeNull();
    expect(Cenario::query()->find($cenario->id))->toBeNull();
});
