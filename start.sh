#!/bin/bash

# 作業ディレクトリを確認
echo "=== Debug Information ==="
echo "Current directory: $(pwd)"
echo "Listing /app directory:"
ls -la /app/ 2>&1 | head -20 || echo "Failed to list /app"
echo ""
echo "Checking bootstrap directory:"
ls -la /app/bootstrap/ 2>&1 || echo "bootstrap directory not found"
echo ""
echo "Checking bootstrap/app.php:"
ls -la /app/bootstrap/app.php 2>&1 || echo "bootstrap/app.php not found"
echo ""
echo "APP_KEY is set: $([ -n "$APP_KEY" ] && echo "yes (length: ${#APP_KEY})" || echo "no")"
echo "========================"
echo ""

# 作業ディレクトリを/appに変更
cd /app || {
    echo "ERROR: Failed to change to /app directory"
    exit 1
}

# パッケージディスカバリーを実行（composer install後の処理）
php artisan package:discover --ansi || echo "Warning: package:discover failed"

# 環境変数が設定されているか確認
if [ -z "$APP_KEY" ]; then
    echo "Warning: APP_KEY is not set. Skipping cache commands."
    php artisan migrate --force || true
else
    # マイグレーション実行
    php artisan migrate --force || true
    
    # キャッシュをクリア
    php artisan config:clear || echo "Warning: config:clear failed"
    php artisan route:clear || echo "Warning: route:clear failed"
    php artisan view:clear || echo "Warning: view:clear failed"
    
    # config/session.phpの存在確認
    if [ ! -f "config/session.php" ]; then
        echo "ERROR: config/session.php not found!"
        php artisan config:publish session || echo "Warning: config:publish session failed"
    fi
    
    # キャッシュコマンド実行（エラーが発生しても続行）
    # 本番環境ではキャッシュコマンドをスキップして、設定ファイルを直接読み込む
    # セッションエラーが発生しているため、キャッシュコマンドはスキップ
    # php artisan config:cache || echo "Warning: config:cache failed"
    # php artisan route:cache || echo "Warning: route:cache failed"
    # php artisan view:cache || echo "Warning: view:cache failed"
fi

# サーバー起動
exec php -S 0.0.0.0:$PORT -t public

