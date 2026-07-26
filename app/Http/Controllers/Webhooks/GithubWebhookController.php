<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessGithubWebhook;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GithubWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $event = (string) $request->header('X-GitHub-Event');

        if (in_array($event, ['installation', 'installation_repositories'], true)) {
            ProcessGithubWebhook::dispatch($event, $request->json()->all());
        }

        return response()->noContent();
    }
}
