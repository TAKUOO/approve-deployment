<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    // ログイン済みの場合はプロジェクト一覧にリダイレクト
    // セッションクッキーの存在を先にチェックしてから認証チェック（パフォーマンス最適化）
    $sessionName = config('session.cookie');
    if ($sessionName && request()->cookie($sessionName)) {
        // セッションが存在する可能性がある場合のみ詳細チェック
        if (auth()->check()) {
            return redirect()->route('projects.index');
        }
    }
    
    return Inertia::render('Welcome', [
        'canLogin' => true, // GitHubログインは常に利用可能
        'canRegister' => false, // GitHubログインのみのため false
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/docs', function () {
    return Inertia::render('Documentation');
})->name('docs');

Route::get('/terms', function () {
    return Inertia::render('Terms');
})->name('terms');

Route::get('/privacy', function () {
    return Inertia::render('PrivacyPolicy');
})->name('privacy');

Route::get('/sitemap.xml', function () {
    $appUrl = config('app.url', 'https://autorelease.matsui-dev.net');
    $sitemap = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>' . $appUrl . '</loc>
        <lastmod>' . date('Y-m-d') . '</lastmod>
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>' . $appUrl . '/docs</loc>
        <lastmod>' . date('Y-m-d') . '</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>' . $appUrl . '/terms</loc>
        <lastmod>' . date('Y-m-d') . '</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    <url>
        <loc>' . $appUrl . '/privacy</loc>
        <lastmod>' . date('Y-m-d') . '</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
</urlset>';
    
    return response($sitemap, 200)
        ->header('Content-Type', 'text/xml');
});

Route::get('/dashboard', function () {
    return redirect()->route('projects.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Projects
    Route::get('/projects', [\App\Http\Controllers\ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [\App\Http\Controllers\ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [\App\Http\Controllers\ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}/edit', [\App\Http\Controllers\ProjectController::class, 'edit'])->name('projects.edit');
    Route::patch('/projects/{project}', [\App\Http\Controllers\ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [\App\Http\Controllers\ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::post('/projects/{project}/generate-approval-url', [\App\Http\Controllers\ProjectController::class, 'generateApprovalUrl'])->name('projects.generate-approval-url');
    
    // GitHub API Proxy
    Route::get('/api/github/organizations', [\App\Http\Controllers\ProjectController::class, 'getOrganizations'])->name('api.github.organizations');
    Route::get('/api/github/repositories', [\App\Http\Controllers\ProjectController::class, 'getRepositories'])->name('api.github.repositories');
    Route::get('/api/github/workflows', [\App\Http\Controllers\ProjectController::class, 'getWorkflows'])->name('api.github.workflows');
    Route::get('/api/github/branches', [\App\Http\Controllers\ProjectController::class, 'getBranches'])->name('api.github.branches');
    
    // Deploy Log Sync
    Route::post('/api/deploy-logs/sync', [\App\Http\Controllers\ProjectController::class, 'syncDeployLogs'])->name('api.deploy-logs.sync');
});

// 承認ページ（ログイン不要、レート制限付き）
Route::get('/approve/{token}', [\App\Http\Controllers\ApproveController::class, 'show'])
    ->middleware('throttle:60,1') // 1分間に60回まで
    ->name('approve.show');
Route::post('/approve/{token}', [\App\Http\Controllers\ApproveController::class, 'approve'])
    ->middleware('throttle:30,10') // 10分間に30回まで
    ->name('approve.approve');
Route::get('/approve/{token}/status/{deployLog}', [\App\Http\Controllers\ApproveController::class, 'status'])
    ->middleware('throttle:60,1') // 1分間に60回まで
    ->name('approve.status');

require __DIR__.'/auth.php';
