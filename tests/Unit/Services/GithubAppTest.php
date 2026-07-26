<?php

use App\Services\GithubApp;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Http;

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
