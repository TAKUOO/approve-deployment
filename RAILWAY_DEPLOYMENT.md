# Railway デプロイメント手順

このドキュメントでは、`domains.squarespace.com/ja` をドメインとして使用し、Railwayでサーバーとデータベースを含めたアプリケーションをリリースする手順を説明します。

## 前提条件

- Railwayアカウント（[railway.app](https://railway.app)）
- GitHubアカウント
- GitHub OAuth Appの設定（本番環境用）

## 1. Railwayプロジェクトの作成

### 1.1 Railwayにログイン

1. [Railway](https://railway.app)にアクセス
2. GitHubアカウントでログイン

### 1.2 新しいプロジェクトを作成

1. Dashboardで「**New Project**」をクリック
2. 「**Deploy from GitHub repo**」を選択
3. このリポジトリ（`approve-deployment`）を選択
4. プロジェクト名を設定（例: `approve-deployment`）

## 2. データベースのセットアップ

### 2.1 MySQLデータベースを追加

1. RailwayプロジェクトのDashboardで「**+ New**」をクリック
2. 「**Database**」を選択
3. 「**Add MySQL**」を選択
4. データベースが作成されるまで待機（数分かかります）

### 2.2 データベース接続情報の確認

データベースが作成されると、以下の環境変数が自動的に設定されます：

- `MYSQL_HOST`
- `MYSQL_PORT`
- `MYSQL_DATABASE`
- `MYSQL_USER`
- `MYSQL_PASSWORD`
- `MYSQL_URL`（接続文字列）

これらの値は後で使用します。

## 3. 環境変数の設定

### 3.1 アプリケーションの環境変数を設定

プロジェクトのDashboardで「**Variables**」タブを開き、以下の環境変数を設定します：

#### 必須環境変数

```env
# アプリケーション設定
APP_NAME=AutoRelease
APP_ENV=production
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
APP_DEBUG=false
APP_URL=https://domains.squarespace.com/ja

# データベース設定（MySQLデータベースの環境変数が自動設定されます）
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQL_HOST}}
DB_PORT=${{MySQL.MYSQL_PORT}}
DB_DATABASE=${{MySQL.MYSQL_DATABASE}}
DB_USERNAME=${{MySQL.MYSQL_USER}}
DB_PASSWORD=${{MySQL.MYSQL_PASSWORD}}

# GitHub OAuth設定（本番環境用）
GITHUB_CLIENT_ID=your_production_github_client_id
GITHUB_CLIENT_SECRET=your_production_github_client_secret
GITHUB_REDIRECT_URI=https://domains.squarespace.com/ja/auth/github/callback

# セッション設定
SESSION_DRIVER=database
SESSION_LIFETIME=120

# キャッシュ設定
CACHE_DRIVER=database
QUEUE_CONNECTION=database
```

### 3.2 APP_KEYの生成

`APP_KEY`は以下のコマンドで生成できます：

```bash
# ローカル環境で実行
php artisan key:generate --show
```

または、RailwayのDeploy Logで以下のコマンドを実行：

```bash
php artisan key:generate
```

生成されたキーを`APP_KEY`環境変数に設定してください。

### 3.3 GitHub OAuth Appの設定（本番環境用）

1. GitHubにログイン
2. Settings → Developer settings → OAuth Apps
3. 「**New OAuth App**」をクリック
4. 以下の情報を入力：
   - **Application name**: AutoRelease (Production)
   - **Homepage URL**: `https://domains.squarespace.com/ja`
   - **Authorization callback URL**: `https://domains.squarespace.com/ja/auth/github/callback`
5. 「**Register application**」をクリック
6. **Client ID**と**Client Secret**をコピー
7. Railwayの環境変数に設定

## 4. ビルドとデプロイ設定

### 4.1 Railwayの設定ファイル（オプション）

プロジェクトルートに`railway.json`を作成（オプション）：

```json
{
  "$schema": "https://railway.app/railway.schema.json",
  "build": {
    "builder": "NIXPACKS",
    "buildCommand": "composer install --no-dev --optimize-autoloader && npm ci && npm run build"
  },
  "deploy": {
    "startCommand": "php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache && php -S 0.0.0.0:$PORT -t public",
    "restartPolicyType": "ON_FAILURE",
    "restartPolicyMaxRetries": 10
  }
}
```

### 4.2 サービス設定

RailwayのDashboardで：

1. サービスを選択
2. 「**Settings**」タブを開く
3. 「**Build Command**」を設定：
   ```bash
   composer install --no-dev --optimize-autoloader && npm ci && npm run build
   ```
4. 「**Start Command**」を設定：
   ```bash
   php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache && php -S 0.0.0.0:$PORT -t public
   ```

## 5. ドメインの設定

### 5.1 カスタムドメインの設定

1. RailwayプロジェクトのDashboardで「**Settings**」タブを開く
2. 「**Networking**」セクションで「**Generate Domain**」をクリック（一時的なRailwayドメインを生成）
3. 「**Custom Domain**」セクションで「**Add Custom Domain**」をクリック
4. `domains.squarespace.com`を入力
5. Railwayが提供するDNS設定を確認

### 5.2 DNS設定

SquarespaceのDNS設定で以下を設定：

- **Type**: CNAME
- **Name**: `domains`（またはサブドメイン部分）
- **Value**: Railwayが提供するCNAMEレコード

または、Aレコードを使用する場合：

- **Type**: A
- **Name**: `domains`
- **Value**: Railwayが提供するIPアドレス

### 5.3 パスの設定

`/ja`パスを使用する場合、以下のいずれかの方法を選択：

#### 方法1: Railwayのリバースプロキシ設定（推奨）

Railwayの設定で、パスベースルーティングを設定します。

#### 方法2: Laravelのルーティング設定

`routes/web.php`で、すべてのルートに`/ja`プレフィックスを追加：

```php
Route::prefix('ja')->group(function () {
    // 既存のルート
});
```

ただし、この方法は既存のコードに影響を与えるため、慎重に検討してください。

#### 方法3: Nginxリバースプロキシ（推奨）

別途Nginxサーバーを用意し、`/ja`パスをRailwayアプリケーションにプロキシします。

## 6. 初回デプロイ

### 6.1 デプロイの実行

1. RailwayはGitHubリポジトリに変更があると自動的にデプロイを開始します
2. または、Dashboardで「**Deploy**」ボタンをクリックして手動デプロイ

### 6.2 デプロイログの確認

1. Dashboardで「**Deployments**」タブを開く
2. デプロイログを確認してエラーがないかチェック

### 6.3 データベースマイグレーションの実行

初回デプロイ時は、マイグレーションが自動的に実行されます（`--force`フラグ付き）。

手動で実行する場合：

```bash
php artisan migrate --force
```

## 7. 動作確認

### 7.1 アプリケーションへのアクセス

1. `https://domains.squarespace.com/ja`にアクセス
2. トップページが表示されることを確認

### 7.2 GitHub OAuth認証の確認

1. 「GitHubでログイン」ボタンをクリック
2. GitHub認証画面が表示されることを確認
3. 認証後、コールバックが正常に動作することを確認

### 7.3 データベース接続の確認

RailwayのDeploy Logで以下を実行：

```bash
php artisan tinker
```

Tinkerで以下を実行：

```php
DB::connection()->getPdo();
```

エラーがなければ、データベース接続は正常です。

## 8. 継続的なデプロイ

### 8.1 自動デプロイ

RailwayはGitHubリポジトリの`main`ブランチ（または設定したブランチ）にプッシュされると自動的にデプロイします。

### 8.2 手動デプロイ

1. Dashboardで「**Deploy**」ボタンをクリック
2. デプロイするブランチを選択
3. 「**Deploy**」をクリック

## 9. トラブルシューティング

### 9.1 デプロイが失敗する場合

1. 「**Deployments**」タブでログを確認
2. エラーメッセージを確認
3. よくある原因：
   - 環境変数が設定されていない
   - ビルドコマンドが失敗している
   - データベース接続エラー

### 9.2 データベース接続エラー

1. データベースサービスの環境変数を確認
2. `DB_*`環境変数が正しく設定されているか確認
3. データベースサービスが起動しているか確認

### 9.3 アセットが読み込まれない

1. `npm run build`が正常に実行されているか確認
2. `public/build`ディレクトリにファイルが生成されているか確認
3. `APP_URL`が正しく設定されているか確認

### 9.4 GitHub OAuth認証が失敗する

1. `GITHUB_CLIENT_ID`と`GITHUB_CLIENT_SECRET`が正しいか確認
2. GitHub OAuth AppのコールバックURLが`https://domains.squarespace.com/ja/auth/github/callback`になっているか確認
3. `APP_URL`と`GITHUB_REDIRECT_URI`が一致しているか確認

## 10. 本番環境の最適化

### 10.1 キャッシュの設定

デプロイ時に自動的に実行されますが、手動で実行する場合：

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 10.2 ログの確認

RailwayのDeploy Logで以下を実行：

```bash
tail -f storage/logs/laravel.log
```

### 10.3 パフォーマンスモニタリング

RailwayのDashboardで以下を確認：

- CPU使用率
- メモリ使用率
- ネットワークトラフィック
- エラーレート

## 11. セキュリティチェックリスト

- [ ] `APP_DEBUG=false`に設定されている
- [ ] `APP_KEY`が設定されている
- [ ] データベースパスワードが強力である
- [ ] GitHub OAuthのClient Secretが安全に管理されている
- [ ] HTTPSが有効になっている
- [ ] セッション設定が適切である

## 12. バックアップ

### 12.1 データベースのバックアップ

RailwayのMySQLデータベースは自動バックアップが有効になっていますが、手動でバックアップを取得する場合：

```bash
mysqldump -h $MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DATABASE > backup.sql
```

## まとめ

この手順に従うことで、`domains.squarespace.com/ja`をドメインとして使用し、Railwayでアプリケーションとデータベースをリリースできます。

問題が発生した場合は、RailwayのDeploy Logを確認し、エラーメッセージに基づいてトラブルシューティングを行ってください。


