<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        // キャッシュを使用してパフォーマンスを改善（リクエストごとにmd5_file()を実行しない）
        static $cachedVersion = null;
        static $cachedMtime = null;
        
        $manifestPath = public_path('build/manifest.json');
        
        if (file_exists($manifestPath)) {
            // ファイルの更新時刻をチェック（デプロイ時にキャッシュを無効化）
            $currentMtime = filemtime($manifestPath);
            
            // キャッシュが無効または存在しない場合のみ再計算
            if ($cachedVersion === null || $cachedMtime !== $currentMtime) {
                $cachedVersion = md5_file($manifestPath);
                $cachedMtime = $currentMtime;
            }
            
            return $cachedVersion;
        }
        
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Welcomeページでは認証チェックをスキップしてパフォーマンスを向上
        // セッションクッキーが存在する場合のみ認証チェックを実行
        $user = null;
        if ($request->routeIs('welcome') || $request->is('/')) {
            // Welcomeページでは認証情報を遅延読み込み（必要時のみ）
            $sessionName = config('session.cookie');
            if ($sessionName && $request->cookie($sessionName)) {
                $user = $request->user();
            }
        } else {
            // その他のページでは通常通り認証情報を取得
            $user = $request->user();
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
        ];
    }
}
