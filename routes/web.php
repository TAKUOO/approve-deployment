<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Projects
    Route::get('/projects', [\App\Http\Controllers\ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [\App\Http\Controllers\ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [\App\Http\Controllers\ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [\App\Http\Controllers\ProjectController::class, 'show'])->name('projects.show');
});

// 承認ページ（ログイン不要）
Route::get('/approve/{token}', [\App\Http\Controllers\ApproveController::class, 'show'])->name('approve.show');
Route::post('/approve/{token}', [\App\Http\Controllers\ApproveController::class, 'approve'])->name('approve.approve');

require __DIR__.'/auth.php';
