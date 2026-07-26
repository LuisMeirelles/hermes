<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyGithubWebhookSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $signature = (string) $request->header('X-Hub-Signature-256');
        $secret = (string) config('services.github.webhook_secret');

        $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), $secret);

        if ($signature === '' || $secret === '' || ! hash_equals($expected, $signature)) {
            abort(403, 'Invalid webhook signature.');
        }

        return $next($request);
    }
}
