<?php

use App\Models\GithubInstallation;
use App\Models\User;
use Illuminate\Support\Facades\Http;

test('it returns the issue preview when found', function () {
    GithubInstallation::factory()->create(['account_login' => 'octocat']);

    Http::fake([
        'api.github.com/app/installations/*/access_tokens' => Http::response(['token' => 'ghs_test_token'], 201),
        'api.github.com/repos/octocat/hermes/issues/7' => Http::response([
            'title' => 'Bug found',
            'state' => 'open',
            'html_url' => 'https://github.com/octocat/hermes/issues/7',
        ]),
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('testes.issue-lookup', ['repo_name' => 'hermes', 'issue_number' => 7]))
        ->assertOk()
        ->assertJson(['title' => 'Bug found', 'state' => 'open']);
});

test('it returns 404 when the issue does not exist', function () {
    GithubInstallation::factory()->create(['account_login' => 'octocat']);

    Http::fake([
        'api.github.com/app/installations/*/access_tokens' => Http::response(['token' => 'ghs_test_token'], 201),
        'api.github.com/repos/octocat/hermes/issues/*' => Http::response(['message' => 'Not Found'], 404),
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('testes.issue-lookup', ['repo_name' => 'hermes', 'issue_number' => 999]))
        ->assertNotFound();
});
