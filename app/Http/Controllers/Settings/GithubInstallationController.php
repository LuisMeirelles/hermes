<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\GithubInstallation;
use App\Services\GithubApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GithubInstallationController extends Controller
{
    /**
     * Show the GitHub App connection settings page.
     */
    public function edit(): Response
    {
        return Inertia::render('settings/github', [
            'installation' => GithubInstallation::query()->active()->first(),
            'appSlug' => config('services.github.app_slug'),
        ]);
    }

    /**
     * Handle the GitHub App's Setup URL redirect after installation.
     */
    public function callback(Request $request, GithubApp $githubApp): RedirectResponse
    {
        $installationId = (int) $request->query('installation_id');

        $response = $githubApp->client()
            ->get("/app/installations/{$installationId}")
            ->throw();

        GithubInstallation::query()->updateOrCreate(
            ['installation_id' => $installationId],
            [
                'account_login' => $response->json('account.login'),
                'account_type' => $response->json('account.type'),
                'uninstalled_at' => null,
            ]
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('GitHub App connected.')]);

        return to_route('settings.github.edit');
    }
}
