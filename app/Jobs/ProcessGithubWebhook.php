<?php

namespace App\Jobs;

use App\Models\GithubInstallation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessGithubWebhook implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public readonly string $event,
        public readonly array $payload,
    ) {}

    public function handle(): void
    {
        match ($this->event) {
            'installation' => $this->handleInstallation(),
            'installation_repositories' => $this->handleInstallationRepositories(),
            default => null,
        };
    }

    private function handleInstallation(): void
    {
        $installationId = (int) data_get($this->payload, 'installation.id');
        $action = data_get($this->payload, 'action');

        match ($action) {
            'created', 'new_permissions_accepted', 'unsuspend' => GithubInstallation::query()->updateOrCreate(
                ['installation_id' => $installationId],
                [
                    'account_login' => data_get($this->payload, 'installation.account.login'),
                    'account_type' => data_get($this->payload, 'installation.account.type'),
                    'uninstalled_at' => null,
                    'suspended_at' => null,
                ]
            ),
            'suspend' => GithubInstallation::query()
                ->where('installation_id', $installationId)
                ->update(['suspended_at' => now()]),
            'deleted' => GithubInstallation::query()
                ->where('installation_id', $installationId)
                ->update(['uninstalled_at' => now()]),
            default => null,
        };
    }

    private function handleInstallationRepositories(): void
    {
        GithubInstallation::query()
            ->where('installation_id', (int) data_get($this->payload, 'installation.id'))
            ->update(['updated_at' => now()]);
    }
}
