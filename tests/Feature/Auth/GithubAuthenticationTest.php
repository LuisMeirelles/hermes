<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

function mockGithubSocialiteUser(string $githubId, string $login = 'octocat', string $name = 'The Octocat', string $email = 'octocat@github.com'): void
{
    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn($githubId);
    $socialiteUser->shouldReceive('getNickname')->andReturn($login);
    $socialiteUser->shouldReceive('getName')->andReturn($name);
    $socialiteUser->shouldReceive('getEmail')->andReturn($email);
    $socialiteUser->shouldReceive('getAvatar')->andReturn("https://avatars.githubusercontent.com/u/{$githubId}");

    Socialite::shouldReceive('driver->scopes->redirect')->andReturn(redirect('https://github.com/login/oauth/authorize'));
    Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);
}

test('redirecting to github starts the oauth flow', function () {
    $this->get(route('github.redirect'))->assertRedirect();
});

test('a new user can log in via github for the first time', function () {
    mockGithubSocialiteUser(githubId: '12345');

    $this->get(route('github.callback'))
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'github_id' => '12345',
        'github_username' => 'octocat',
        'email' => 'octocat@github.com',
    ]);
    $this->assertDatabaseCount('users', 1);
});

test('a returning user is found by github_id and logged in without duplicating the row', function () {
    $user = User::factory()->create(['github_id' => '12345']);

    mockGithubSocialiteUser(githubId: '12345');

    $this->get(route('github.callback'))
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseCount('users', 1);
});

test('guests are redirected to login when visiting the dashboard', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('an authenticated user can log out', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('home'));

    $this->assertGuest();
});
