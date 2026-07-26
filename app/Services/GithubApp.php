<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GithubApp
{
    public function issueJwt(): string
    {
        $now = now();

        return JWT::encode([
            'iat' => $now->subSeconds(60)->timestamp,
            'exp' => $now->addMinutes(9)->timestamp,
            'iss' => config('services.github.app_id'),
        ], $this->privateKey(), 'RS256');
    }

    public function client(): PendingRequest
    {
        return Http::withToken($this->issueJwt())
            ->withHeader('Accept', 'application/vnd.github+json')
            ->withHeader('X-GitHub-Api-Version', '2022-11-28')
            ->baseUrl('https://api.github.com');
    }

    public function installationToken(int $installationId): string
    {
        return Cache::remember(
            "github.installation_token.{$installationId}",
            now()->addMinutes(50),
            fn (): string => $this->client()
                ->post("/app/installations/{$installationId}/access_tokens")
                ->throw()
                ->json('token'),
        );
    }

    private function privateKey(): string
    {
        $path = config('services.github.private_key_path');

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('GitHub App private key path is not configured.');
        }

        if (! str_starts_with($path, '/')) {
            $path = base_path($path);
        }

        if (! is_file($path)) {
            throw new RuntimeException("GitHub App private key not found at [{$path}].");
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException("Unable to read GitHub App private key at [{$path}].");
        }

        return $contents;
    }
}
