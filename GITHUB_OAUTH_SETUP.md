# GitHub OAuth設定手順

GitHubログイン機能を使用するには、GitHub側でOAuth Appを登録する必要があります。

## 1. GitHubでOAuth Appを作成

### ステップ1: GitHub Developer Settingsにアクセス

1. GitHubにログイン
2. 右上のプロフィールアイコンをクリック
3. **Settings** を選択
4. 左サイドバーの一番下にある **Developer settings** をクリック
5. **OAuth Apps** を選択
6. **New OAuth App** ボタンをクリック

### ステップ2: OAuth App情報を入力

以下の情報を入力します：

#### Application name（アプリケーション名）
```
AutoRelease
```
または任意の名前

#### Homepage URL（ホームページURL）
```
http://localhost:8000
```
開発環境の場合は `http://localhost:8000`、本番環境の場合は実際のドメインを入力

#### Authorization callback URL（認証コールバックURL）
```
http://localhost:8000/auth/github/callback
```
**重要**: このURLは正確に入力してください。開発環境と本番環境で異なる場合は、それぞれ登録するか、本番環境のURLを入力してください。

### ステップ3: OAuth Appを登録

**Register application** ボタンをクリックして登録します。

### ステップ4: Client IDとClient Secretを取得

登録後、以下の情報が表示されます：

- **Client ID**: この値をコピーします（例: `Iv1.xxxxxxxxxxxxxxxx`）
- **Client secrets**: **Generate a new client secret** ボタンをクリックしてシークレットを生成し、コピーします

**注意**: Client Secretは一度しか表示されません。必ずコピーして保存してください。

## 2. 環境変数の設定

取得したClient IDとClient Secretを `.env` ファイルに設定します：

```env
# GitHub OAuth設定
GITHUB_CLIENT_ID=your_github_client_id
GITHUB_CLIENT_SECRET=your_github_client_secret
GITHUB_REDIRECT_URI=http://localhost:8000/auth/github/callback
```

### 本番環境の場合

本番環境では、GitHub OAuth Appの設定も本番URLに更新してください：

1. GitHubのOAuth App設定画面に戻る
2. **Authorization callback URL** を本番環境のURLに変更
   ```
   https://yourdomain.com/auth/github/callback
   ```
3. `.env` ファイルも本番環境のURLに更新
   ```env
   GITHUB_REDIRECT_URI=https://yourdomain.com/auth/github/callback
   ```

## 3. 必要なスコープ（権限）

GitHubControllerでは以下のスコープを要求しています：

- `repo`: リポジトリへのフルアクセス（workflow_dispatchに必要）
- `read:user`: ユーザー情報の読み取り

これらのスコープは、ユーザーが初回ログイン時にGitHub側で承認を求められます。

## 4. 動作確認

設定が完了したら、以下を確認してください：

1. `.env` ファイルに正しく設定されているか
2. アプリケーションを再起動（設定変更を反映）
3. ブラウザで `http://localhost:8000` にアクセス
4. 「GitHubでログイン」ボタンをクリック
5. GitHubの認証画面が表示され、承認できることを確認

## トラブルシューティング

### エラー: "redirect_uri_mismatch"

- GitHub OAuth Appの **Authorization callback URL** と `.env` の `GITHUB_REDIRECT_URI` が完全に一致しているか確認
- 末尾のスラッシュ（`/`）の有無も確認

### エラー: "invalid_client"

- `GITHUB_CLIENT_ID` と `GITHUB_CLIENT_SECRET` が正しく設定されているか確認
- Client Secretが再生成されていないか確認（再生成した場合は新しい値を設定）

### エラー: "access_denied"

- ユーザーがGitHub側でアプリの承認を拒否した場合に発生
- 再度ログインを試みて、承認を許可してください

## 参考リンク

- [GitHub OAuth Apps Documentation](https://docs.github.com/en/apps/oauth-apps/building-oauth-apps/creating-an-oauth-app)
- [Laravel Socialite GitHub Documentation](https://laravel.com/docs/socialite#github)

