<?php

use App\Models\GithubInstallation;
use App\Services\GithubApp;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('it signs a jwt with the configured app id as issuer and a 10 minute window', function () {
    $jwt = (new GithubApp)->issueJwt();

    $publicKey = file_get_contents(base_path('tests/Fixtures/github-app-test-public-key.pem'));
    $decoded = JWT::decode($jwt, new Key($publicKey, 'RS256'));

    expect($decoded->iss)->toBe(config('services.github.app_id'));
    expect($decoded->exp - $decoded->iat)->toBe(600);
});

test('it fetches and caches an installation access token', function () {
    Http::fake([
        'api.github.com/app/installations/*/access_tokens' => Http::response(['token' => 'ghs_test_token'], 201),
    ]);

    $githubApp = new GithubApp;

    $first = $githubApp->installationToken(123);
    $second = $githubApp->installationToken(123);

    expect($first)->toBe('ghs_test_token');
    expect($second)->toBe('ghs_test_token');
    Http::assertSentCount(1);
});

test('it fetches an issue using the active installation token', function () {
    GithubInstallation::factory()->create(['account_login' => 'octocat']);

    Http::fake([
        'api.github.com/app/installations/*/access_tokens' => Http::response(['token' => 'ghs_test_token'], 201),
        'api.github.com/repos/octocat/hermes/issues/7' => Http::response([
            'title' => 'Bug found',
            'state' => 'open',
            'html_url' => 'https://github.com/octocat/hermes/issues/7',
        ]),
    ]);

    $issue = (new GithubApp)->getIssue('hermes', 7);

    expect($issue['title'])->toBe('Bug found');
    Http::assertSent(fn ($request): bool => $request->url() === 'https://api.github.com/repos/octocat/hermes/issues/7'
        && $request->hasHeader('Authorization', 'Bearer ghs_test_token'));
});

test('it lists repositories for the active installation', function () {
    GithubInstallation::factory()->create(['account_login' => 'octocat']);

    Http::fake([
        'api.github.com/app/installations/*/access_tokens' => Http::response(['token' => 'ghs_test_token'], 201),
        'api.github.com/installation/repositories*' => Http::response([
            'repositories' => [['name' => 'hermes', 'full_name' => 'octocat/hermes']],
        ]),
    ]);

    $repositories = (new GithubApp)->listRepositories();

    expect($repositories)->toBe([['name' => 'hermes', 'full_name' => 'octocat/hermes']]);
});
