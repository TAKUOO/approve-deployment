<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Deploy API
Route::post('/projects/{project}/deploy', [\App\Http\Controllers\DeployController::class, 'trigger'])->name('deploy.trigger');

// GitHub Webhook
Route::post('/github/webhook', [\App\Http\Controllers\GitHubWebhookController::class, 'handle'])->name('github.webhook');

