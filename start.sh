#!/bin/bash

# パッケージディスカバリーを実行（composer install後の処理）
php artisan package:discover --ansi || echo "Warning: package:discover failed"

# 環境変数が設定されているか確認
if [ -z "$APP_KEY" ]; then
    echo "Warning: APP_KEY is not set. Skipping cache commands."
    php artisan migrate --force || true
else
    # マイグレーション実行
    php artisan migrate --force || true
    
    # キャッシュコマンド実行（エラーが発生しても続行）
    php artisan config:cache || echo "Warning: config:cache failed"
    php artisan route:cache || echo "Warning: route:cache failed"
    php artisan view:cache || echo "Warning: view:cache failed"
fi

# サーバー起動
exec php -S 0.0.0.0:$PORT -t public

