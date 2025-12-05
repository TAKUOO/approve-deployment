# デプロイ前準備チェックリスト

デプロイ完了を待つ間に、先に準備できることを確認します。

## ✅ すぐにできる準備

### 1. GitHub OAuth App（本番環境用）の作成

**実行手順:**
1. GitHubにログイン
2. Settings → Developer settings → OAuth Apps
3. 「**New OAuth App**」をクリック
4. 以下の情報を入力：
   - **Application name**: `AutoRelease (Production)`
   - **Homepage URL**: `https://domains.squarespace.com/ja`
   - **Authorization callback URL**: `https://domains.squarespace.com/ja/auth/github/callback`
5. 「**Register application**」をクリック
6. **Client ID**と**Client Secret**をコピーして安全な場所に保存

**確認事項:**
- [ ] GitHub OAuth Appが作成されている
- [ ] Client IDとClient Secretをメモしている
- [ ] コールバックURLが正しい（`/ja/auth/github/callback`）

**必要なスコープ:**
- `repo`: リポジトリへのフルアクセス（workflow_dispatchに必要）
- `read:user`: ユーザー情報の読み取り
- `read:org`: 組織情報の読み取り（組織のリポジトリ選択に必要）

---

### 2. Railway設定の確認

**確認事項:**
- [ ] Railwayプロジェクトが作成されている
- [ ] MySQLデータベースが追加されている
- [ ] データベースサービスのステータスが「Active」である

**Railway Dashboardでの確認:**
1. プロジェクトを開く
2. 「**Settings**」タブを開く
3. 「**Build Command**」が以下になっているか確認：
   ```bash
   composer install --no-dev --optimize-autoloader && npm ci && npm run build
   ```
4. 「**Start Command**」が以下になっているか確認：
   ```bash
   ./start.sh
   ```

**注意:** `railway.json`が存在する場合、これらの設定は自動的に適用されます。

---

### 3. 環境変数の準備

**準備するもの:**
- `APP_KEY`: 既に生成済み（`base64:2ErZuoQ0PD3CpKXAy5s7i1uv0Pw89Jt4NbTkiEfkx1Q=`）
- `GITHUB_CLIENT_ID`: ステップ1で取得
- `GITHUB_CLIENT_SECRET`: ステップ1で取得

**環境変数テンプレート:**
`ENV_VARIABLES_TEMPLATE.md`を参照してください。

**設定タイミング:**
- デプロイが成功したら、Railway Dashboardで環境変数を設定
- 環境変数を設定後、サービスを再起動

---

### 4. ドメイン設定の確認

**確認事項:**
- [ ] Railwayの生成ドメインが利用可能か確認
- [ ] カスタムドメイン（`domains.squarespace.com`）の設定が必要か確認
- [ ] `/ja`パスの設定が必要か確認

**現在の設定:**
- `APP_URL`: `https://domains.squarespace.com/ja`
- `GITHUB_REDIRECT_URI`: `https://domains.squarespace.com/ja/auth/github/callback`

**注意:** カスタムドメインを設定する場合は、DNS設定が必要です。

---

## 📋 デプロイ完了後の作業

### 1. 環境変数の設定
- Railway Dashboardで環境変数を設定
- `ENV_VARIABLES_TEMPLATE.md`を参照

### 2. 動作確認
- アプリケーションにアクセス
- GitHub OAuth認証のテスト
- データベース接続の確認

### 3. エラーログの確認
- Railway DashboardでDeploy Logsを確認
- HTTP Logsでエラーがないか確認

---

## 🎯 優先順位

1. **最優先:** GitHub OAuth Appの作成（デプロイ完了を待たずに実行可能）
2. **次:** Railway設定の確認（プロジェクトとデータベースが作成済みか確認）
3. **最後:** 環境変数の設定（デプロイ成功後に実行）

---

## 📝 メモ

- GitHub OAuth AppのClient IDとClient Secretは安全に保管してください
- 環境変数はデプロイ成功後に設定します
- デプロイが失敗した場合は、エラーログを確認して修正します

