<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\GitHubController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // GitHubログインのみ
    Route::get('auth/github', [GitHubController::class, 'redirect'])->name('github.login');
    Route::get('auth/github/callback', [GitHubController::class, 'callback'])->name('github.callback');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
