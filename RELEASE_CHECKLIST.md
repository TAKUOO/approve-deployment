# リリース前チェックリスト

## ✅ 完了済み項目

- [x] `railway.json`設定ファイルの作成
- [x] `APP_KEY`の生成（生成されたキー: `base64:2ErZuoQ0PD3CpKXAy5s7i1uv0Pw89Jt4NbTkiEfkx1Q=`）

## 📋 リリース手順

### ステップ1: Railwayプロジェクトの作成

**確認事項:**
- [ ] Railwayアカウントにログイン済み
- [ ] GitHubリポジトリがRailwayに接続可能

**実行手順:**
1. [Railway](https://railway.app)にアクセス
2. 「**New Project**」をクリック
3. 「**Deploy from GitHub repo**」を選択
4. `approve-deployment`リポジトリを選択
5. プロジェクト名を設定（例: `approve-deployment`）

**次のステップに進む前に確認:**
- Railwayプロジェクトが作成されていること

---

### ステップ2: MySQLデータベースの追加

**実行手順:**
1. RailwayプロジェクトのDashboardで「**+ New**」をクリック
2. 「**Database**」を選択
3. 「**Add MySQL**」を選択
4. データベースが作成されるまで待機（数分かかります）

**確認事項:**
- [ ] データベースが作成されていること
- [ ] 以下の環境変数が自動設定されていること:
  - `MYSQL_HOST`
  - `MYSQL_PORT`
  - `MYSQL_DATABASE`
  - `MYSQL_USER`
  - `MYSQL_PASSWORD`
  - `MYSQL_URL`

**次のステップに進む前に確認:**
- データベースサービスのステータスが「Active」であること

---

### ステップ3: GitHub OAuth App（本番環境用）の作成

**実行手順:**
1. GitHubにログイン
2. Settings → Developer settings → OAuth Apps
3. 「**New OAuth App**」をクリック
4. 以下の情報を入力：
   - **Application name**: `AutoRelease (Production)`
   - **Homepage URL**: `https://domains.squarespace.com/ja`
   - **Authorization callback URL**: `https://domains.squarespace.com/ja/auth/github/callback`
5. 「**Register application**」をクリック
6. **Client ID**と**Client Secret**をコピー（後で使用します）

**確認事項:**
- [ ] GitHub OAuth Appが作成されていること
- [ ] Client IDとClient Secretをメモしていること

**次のステップに進む前に確認:**
- Client IDとClient Secretが準備できていること

---

### ステップ4: 環境変数の設定

**実行手順:**
1. RailwayプロジェクトのDashboardで「**Variables**」タブを開く
2. 以下の環境変数を設定：

```env
# アプリケーション設定
APP_NAME=AutoRelease
APP_ENV=production
APP_KEY=base64:2ErZuoQ0PD3CpKXAy5s7i1uv0Pw89Jt4NbTkiEfkx1Q=
APP_DEBUG=false
APP_URL=https://domains.squarespace.com/ja

# データベース設定（MySQLデータベースの環境変数参照）
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

**重要:** 
- `GITHUB_CLIENT_ID`と`GITHUB_CLIENT_SECRET`はステップ3で取得した値に置き換えてください
- `DB_*`変数は`${{MySQL.*}}`形式で参照してください（Railwayが自動的に解決します）

**確認事項:**
- [ ] すべての環境変数が設定されていること
- [ ] `GITHUB_CLIENT_ID`と`GITHUB_CLIENT_SECRET`が正しく設定されていること
- [ ] `APP_KEY`が設定されていること

**次のステップに進む前に確認:**
- すべての必須環境変数が設定されていること

---

### ステップ5: ビルドとデプロイ設定の確認

**確認事項:**
- [x] `railway.json`がプロジェクトルートに存在すること

**Railway Dashboardでの設定確認:**
1. サービスを選択
2. 「**Settings**」タブを開く
3. 「**Build Command**」が以下になっているか確認：
   ```bash
   composer install --no-dev --optimize-autoloader && npm ci && npm run build
   ```
4. 「**Start Command**」が以下になっているか確認：
   ```bash
   php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache && php -S 0.0.0.0:$PORT -t public
   ```

**確認事項:**
- [ ] ビルドコマンドが正しく設定されていること
- [ ] スタートコマンドが正しく設定されていること

**次のステップに進む前に確認:**
- ビルドとデプロイ設定が完了していること

---

### ステップ6: ドメインの設定（オプション）

**注意:** `/ja`パスを使用する場合、以下のいずれかの方法を選択する必要があります。

**方法1: Railwayのリバースプロキシ設定（推奨）**
- Railwayの設定でパスベースルーティングを設定

**方法2: Laravelのルーティング設定**
- `routes/web.php`で全てのルートに`/ja`プレフィックスを追加

**方法3: Nginxリバースプロキシ（推奨）**
- 別途Nginxサーバーを用意し、`/ja`パスをRailwayアプリケーションにプロキシ

**現在の状態:**
- ルーティングは`/ja`プレフィックスなしで設定されています
- ドメイン設定が必要な場合は、事前に確認してください

**確認事項:**
- [ ] ドメイン設定の方針が決定していること（必要に応じて）

---

### ステップ7: 初回デプロイの実行

**実行手順:**
1. Railway Dashboardで「**Deploy**」ボタンをクリック
2. または、GitHubリポジトリに変更をプッシュ（自動デプロイが開始されます）

**デプロイ中の確認:**
1. 「**Deployments**」タブでデプロイログを確認
2. エラーがないかチェック
3. ビルドが成功しているか確認
4. マイグレーションが実行されているか確認

**確認事項:**
- [ ] デプロイが成功していること
- [ ] ビルドログにエラーがないこと
- [ ] マイグレーションが正常に実行されていること

**次のステップに進む前に確認:**
- デプロイが完了し、サービスが起動していること

---

### ステップ8: 動作確認とテスト

**確認項目:**

1. **アプリケーションへのアクセス**
   - [ ] `https://domains.squarespace.com/ja`（またはRailwayの生成ドメイン）にアクセスできること
   - [ ] トップページが表示されること

2. **GitHub OAuth認証**
   - [ ] 「GitHubでログイン」ボタンが表示されること
   - [ ] GitHub認証画面が表示されること
   - [ ] 認証後、コールバックが正常に動作すること
   - [ ] ログイン後、プロジェクト一覧が表示されること

3. **データベース接続**
   - RailwayのDeploy Logで以下を実行：
     ```bash
     php artisan tinker
     ```
   - Tinkerで以下を実行：
     ```php
     DB::connection()->getPdo();
     ```
   - [ ] エラーがなく、データベース接続が正常であること

4. **基本機能の動作確認**
   - [ ] プロジェクトの作成ができること
   - [ ] プロジェクトの編集ができること
   - [ ] 承認URLの生成ができること
   - [ ] 承認ページが表示されること

**確認事項:**
- [ ] すべての基本機能が動作していること
- [ ] エラーログに重大なエラーがないこと

---

### ステップ9: セキュリティチェック

**確認項目:**
- [ ] `APP_DEBUG=false`に設定されていること
- [ ] `APP_KEY`が設定されていること
- [ ] データベースパスワードが強力であること
- [ ] GitHub OAuthのClient Secretが安全に管理されていること
- [ ] HTTPSが有効になっていること
- [ ] セッション設定が適切であること

---

## 🎉 リリース完了

すべてのステップが完了したら、リリースは成功です！

## 📝 トラブルシューティング

問題が発生した場合は、以下を確認してください：

1. **デプロイが失敗する場合**
   - Deploy Logでエラーメッセージを確認
   - 環境変数が正しく設定されているか確認
   - ビルドコマンドが正常に実行できるか確認

2. **データベース接続エラー**
   - データベースサービスの環境変数を確認
   - `DB_*`環境変数が正しく設定されているか確認
   - データベースサービスが起動しているか確認

3. **GitHub OAuth認証が失敗する**
   - `GITHUB_CLIENT_ID`と`GITHUB_CLIENT_SECRET`が正しいか確認
   - GitHub OAuth AppのコールバックURLが正しいか確認
   - `APP_URL`と`GITHUB_REDIRECT_URI`が一致しているか確認

4. **アセットが読み込まれない**
   - `npm run build`が正常に実行されているか確認
   - `public/build`ディレクトリにファイルが生成されているか確認
   - `APP_URL`が正しく設定されているか確認

