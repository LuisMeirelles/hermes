<?php

use App\Jobs\ProcessGithubWebhook;
use App\Models\GithubInstallation;
use Illuminate\Support\Facades\Bus;

function signedGithubWebhookHeaders(array $payload, string $event, string $secret = 'test-webhook-secret'): array
{
    $body = json_encode($payload);

    return [
        'X-GitHub-Event' => $event,
        'X-Hub-Signature-256' => 'sha256='.hash_hmac('sha256', $body, $secret),
    ];
}

test('it rejects a webhook request with a missing signature', function () {
    $payload = ['action' => 'created', 'installation' => ['id' => 1]];

    $this->postJson(route('webhooks.github'), $payload, ['X-GitHub-Event' => 'installation'])
        ->assertForbidden();
});

test('it rejects a webhook request with an invalid signature', function () {
    $payload = ['action' => 'created', 'installation' => ['id' => 1]];
    $headers = signedGithubWebhookHeaders($payload, 'installation', secret: 'wrong-secret');

    $this->postJson(route('webhooks.github'), $payload, $headers)->assertForbidden();
});

test('it accepts a validly signed installation.created event and creates a github_installation row', function () {
    $payload = [
        'action' => 'created',
        'installation' => ['id' => 12345678, 'account' => ['login' => 'octocat', 'type' => 'User']],
    ];

    $this->postJson(route('webhooks.github'), $payload, signedGithubWebhookHeaders($payload, 'installation'))
        ->assertNoContent();

    $this->assertDatabaseHas('github_installations', [
        'installation_id' => 12345678,
        'account_login' => 'octocat',
        'account_type' => 'User',
        'uninstalled_at' => null,
    ]);
});

test('it marks an installation as uninstalled on installation.deleted', function () {
    GithubInstallation::query()->create([
        'installation_id' => 555,
        'account_login' => 'octocat',
        'account_type' => 'User',
    ]);

    $payload = ['action' => 'deleted', 'installation' => ['id' => 555]];

    $this->postJson(route('webhooks.github'), $payload, signedGithubWebhookHeaders($payload, 'installation'))
        ->assertNoContent();

    $installation = GithubInstallation::query()->where('installation_id', 555)->first();
    expect($installation->uninstalled_at)->not->toBeNull();
});

test('it marks an installation as suspended and then unsuspended', function () {
    GithubInstallation::query()->create([
        'installation_id' => 777,
        'account_login' => 'octocat',
        'account_type' => 'User',
    ]);

    $suspend = ['action' => 'suspend', 'installation' => ['id' => 777]];
    $this->postJson(route('webhooks.github'), $suspend, signedGithubWebhookHeaders($suspend, 'installation'))
        ->assertNoContent();

    expect(GithubInstallation::query()->where('installation_id', 777)->first()->suspended_at)->not->toBeNull();

    $unsuspend = [
        'action' => 'unsuspend',
        'installation' => ['id' => 777, 'account' => ['login' => 'octocat', 'type' => 'User']],
    ];
    $this->postJson(route('webhooks.github'), $unsuspend, signedGithubWebhookHeaders($unsuspend, 'installation'))
        ->assertNoContent();

    expect(GithubInstallation::query()->where('installation_id', 777)->first()->suspended_at)->toBeNull();
});

test('it acknowledges unsupported events without dispatching a job', function () {
    Bus::fake();

    $payload = ['zen' => 'Responsive is better than fast.'];

    $this->postJson(route('webhooks.github'), $payload, signedGithubWebhookHeaders($payload, 'ping'))
        ->assertNoContent();

    Bus::assertNotDispatched(ProcessGithubWebhook::class);
});
