<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\GithubController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::inertia('login', 'auth/login')->name('login');

    Route::get('auth/github/redirect', [GithubController::class, 'redirect'])->name('github.redirect');
    Route::get('auth/github/callback', [GithubController::class, 'callback'])->name('github.callback');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
