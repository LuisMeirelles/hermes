<?php

use App\Http\Controllers\Webhooks\GithubWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('webhooks/github', [GithubWebhookController::class, 'handle'])
    ->middleware('github.webhook.signature')
    ->name('webhooks.github');
