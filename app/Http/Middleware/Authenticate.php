<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo(Request $request): ?string
    {
        // 承認画面のパスを除外（デフォルトの動作を維持）
        if (str_starts_with($request->path(), 'approve/')) {
            return null;
        }
        
        // Welcomeページにリダイレクト
        return '/';
    }
}
