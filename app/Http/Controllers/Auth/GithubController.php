<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GithubProvider;

class GithubController extends Controller
{
    public function redirect(): RedirectResponse
    {
        /** @var GithubProvider $driver */
        $driver = Socialite::driver('github');

        return $driver->scopes(['user:email'])->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $githubUser = Socialite::driver('github')->user();

        $user = User::firstOrCreate(
            ['github_id' => (string) $githubUser->getId()],
            [
                'name' => $githubUser->getName() ?: $githubUser->getNickname(),
                'email' => $githubUser->getEmail(),
            ]
        );

        $user->forceFill([
            'github_username' => $githubUser->getNickname(),
            'avatar' => $githubUser->getAvatar(),
        ])->save();

        Auth::login($user, remember: true);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
