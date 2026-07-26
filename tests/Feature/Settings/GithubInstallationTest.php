<?php

use App\Models\GithubInstallation;
use App\Models\User;
use Illuminate\Support\Facades\Http;

test('guests cannot access the github settings page', function () {
    $this->get(route('settings.github.edit'))->assertRedirect(route('login'));
});

test('it shows the settings page as not connected when no installation exists', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('settings.github.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('installation', null));
});

test('it links a new installation via the setup callback', function () {
    Http::fake([
        'api.github.com/app/installations/*' => Http::response([
            'account' => ['login' => 'octocat', 'type' => 'Organization'],
        ]),
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('settings.github.callback', ['installation_id' => 555, 'setup_action' => 'install']))
        ->assertRedirect(route('settings.github.edit'));

    $this->assertDatabaseHas('github_installations', [
        'installation_id' => 555,
        'account_login' => 'octocat',
        'account_type' => 'Organization',
    ]);
});

test('it shows the settings page as connected once an installation exists', function () {
    GithubInstallation::query()->create([
        'installation_id' => 999,
        'account_login' => 'octocat',
        'account_type' => 'User',
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('settings.github.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('installation.account_login', 'octocat'));
});
