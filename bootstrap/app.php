<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Inertia\Inertia;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // 認証ミドルウェアをカスタムのものに置き換え
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 未認証ユーザーが認証が必要なページにアクセスした場合、Welcomeページにリダイレクト
        // ただし、承認画面（/approve/*）には影響しない
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
            // 承認画面のパスを除外
            if (str_starts_with($request->path(), 'approve/')) {
                return null; // デフォルトの動作を維持
            }
            
            // Welcomeページにリダイレクト
            return redirect('/');
        });

        // 404エラー（ページが見つからない）
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'ページが見つかりません'], 404);
            }
            
            return Inertia::render('Error', [
                'status' => 404,
                'message' => 'お探しのページは存在しないか、移動または削除された可能性があります。',
            ])->toResponse($request)->setStatusCode(404);
        });

        // 405エラー（メソッドが許可されていない）
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'メソッドが許可されていません'], 405);
            }
            
            $allowedMethods = $e->getHeaders()['Allow'] ?? '';
            return Inertia::render('Error', [
                'status' => 405,
                'message' => $allowedMethods ? "このページは以下のメソッドのみをサポートしています: {$allowedMethods}" : 'このページは、リクエストされたHTTPメソッドをサポートしていません。',
            ])->toResponse($request)->setStatusCode(405);
        });

        // 403エラー（アクセス拒否）
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'アクセスが拒否されました'], 403);
            }
            
            return Inertia::render('Error', [
                'status' => 403,
                'message' => $e->getMessage() ?: 'このページにアクセスする権限がありません。',
            ])->toResponse($request)->setStatusCode(403);
        });

        // 500エラー（サーバーエラー）
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            // 既に処理されたエラーはスキップ
            if ($e instanceof \Illuminate\Auth\AuthenticationException ||
                $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException ||
                $e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException ||
                $e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'サーバーでエラーが発生しました'], 500);
            }

            // 本番環境では詳細なエラー情報を表示しない
            $message = app()->environment('production') 
                ? 'サーバーでエラーが発生しました。しばらくしてから再度お試しください。'
                : $e->getMessage();

            // セッションエラーの場合は、Inertiaを使わずに直接HTMLを返す
            if ($e instanceof \Illuminate\Contracts\Container\BindingResolutionException && 
                str_contains($e->getMessage(), 'session')) {
                return response()->view('errors::500', [
                    'message' => $message,
                ], 500);
            }

            try {
                return Inertia::render('Error', [
                    'status' => 500,
                    'message' => $message,
                ])->toResponse($request)->setStatusCode(500);
            } catch (\Throwable $renderError) {
                // Inertiaのレンダリングに失敗した場合は、シンプルなHTMLレスポンスを返す
                return response('<h1>サーバーエラー</h1><p>' . htmlspecialchars($message) . '</p>', 500);
            }
        });
    })->create();


