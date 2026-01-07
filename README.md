# AutoRelease

Web製作者向けのSaaS「承認 → 自動アップ」サービス

テスト環境でクライアントが内容を確認し、「承認」ボタンを押すだけでGitHub Actionsを通じて本番環境へ自動的にアップされる仕組みを提供します。

## 📋 目次

- [概要](#概要)
- [主な機能](#主な機能)
- [技術スタック](#技術スタック)
- [システムアーキテクチャ](#システムアーキテクチャ)
- [データベーススキーマ](#データベーススキーマ)
- [認証フロー](#認証フロー)
- [デプロイフロー](#デプロイフロー)
- [セットアップ](#セットアップ)
- [環境変数](#環境変数)
- [ディレクトリ構造](#ディレクトリ構造)
- [開発ガイドライン](#開発ガイドライン)

## 概要

### 解決する課題

- クライアントからの承認待ちで本番環境へのアップが遅れる
- メールやチャットでの承認プロセスが煩雑
- 手動でのアップ作業でミスが発生する可能性
- 承認から本番環境へのアップまでの時間がかかる
- クライアントにログインを求める必要がある

### 提供する価値

- **承認URLを送るだけで即座に承認可能**: トークンベースの承認URLで、クライアントはログイン不要で承認できます
- **シンプルな承認フロー**: テスト環境へのアクセスと承認ボタンだけのシンプルなビュー
- **自動アップでミスゼロ、時間短縮**: GitHub Actionsと連携して自動的に本番環境へデプロイ
- **承認から本番環境へのアップまで数秒で完了**: workflow_dispatchで即座にデプロイを開始
- **承認URLの有効期限管理**: セキュリティを考慮した有効期限付きトークン
- **マークダウン対応の改善内容入力**: 承認時に改善内容を分かりやすく記載可能

## 主な機能

### プロジェクト管理
- プロジェクトの作成、一覧表示、編集、削除
- GitHub組織・リポジトリ・ワークフロー・ブランチの選択
- ステージング環境URLと本番環境URLの設定

### 承認URL生成
- トークンベースの承認URLを自動生成
- 有効期限付きトークン（デフォルト7日間）
- マークダウンエディタで改善内容を入力
- 承認URLの再生成に対応

### 承認ページ
- ログイン不要でアクセス可能
- テスト環境へのリンクと改善内容の表示
- ワンクリックで承認・デプロイ実行
- 承認ステータスのリアルタイム表示

### GitHub Actions連携
- `workflow_dispatch`イベントで本番環境へのアップをトリガー
- プロジェクト所有者のGitHubトークンを使用（暗号化保存）
- GitHub Webhookでデプロイ完了を自動検知
- デプロイステータスの自動更新

### デプロイ履歴管理
- デプロイ履歴とステータスを記録・表示
- 承認メッセージとデプロイログの保存
- 所要時間の自動計算
- リアルタイムでのステータス更新

### セキュリティ機能
- レート制限（承認操作: 1時間に5回、ページアクセス: 1分間に30回）
- トークンベース認証
- XSS対策（DOMPurifyによるサニタイズ）
- 承認URLの有効期限管理
- GitHubトークンの暗号化保存

## 技術スタック

### バックエンド
- **PHP**: ^8.2
- **Laravel**: ^11.0
- **Laravel Breeze**: ^2.3（認証基盤）
- **Laravel Socialite**: ^5.23（GitHub OAuth認証）
- **Laravel Sanctum**: ^4.0（API認証）
- **MySQL/SQLite**: データベース

### フロントエンド
- **Vue.js**: ^3.4.0
- **Inertia.js**: ^2.0（SPA体験を提供）
- **Tailwind CSS**: ^3.2.1（スタイリング）
- **Ziggy**: ^2.0（LaravelルートをJavaScriptで利用）
- **md-editor-v3**: ^6.2.0（マークダウンエディタ）
- **marked**: ^17.0.1（マークダウンパーサー）
- **DOMPurify**: ^3.3.0（XSS対策）

### ビルドツール
- **Vite**: ^5.0.0（アセットバンドラー）
- **PostCSS**: ^8.4.31
- **Autoprefixer**: ^10.4.12

### 外部サービス連携
- **GitHub Actions API**: `workflow_dispatch`イベントでデプロイをトリガー
- **GitHub OAuth 2.0**: ユーザー認証
- **GitHub Webhook**: デプロイ完了の検知

## システムアーキテクチャ

```
┌─────────────┐
│   Client    │
│  (Browser)  │
└──────┬──────┘
       │
       │ HTTP/HTTPS
       │
┌──────▼─────────────────────────────────────┐
│         Laravel Application                 │
│  ┌──────────────────────────────────────┐  │
│  │  Inertia.js (SPA-like Experience)    │  │
│  └──────────────────────────────────────┘  │
│  ┌──────────────────────────────────────┐  │
│  │  Controllers                          │  │
│  │  - ProjectController                  │  │
│  │  - ApproveController                 │  │
│  │  - DeployController                  │  │
│  │  - GitHubWebhookController           │  │
│  │  - GitHubController (OAuth)          │  │
│  └──────────────────────────────────────┘  │
│  ┌──────────────────────────────────────┐  │
│  │  Models                               │  │
│  │  - Project                            │  │
│  │  - DeployLog                          │  │
│  │  - Approval                           │  │
│  │  - ApprovalMessage                    │  │
│  │  - User                               │  │
│  └──────────────────────────────────────┘  │
└──────┬─────────────────────────────────────┘
       │
       ├─────────────────┬──────────────────┐
       │                 │                  │
┌──────▼──────┐  ┌──────▼──────┐  ┌──────▼──────┐
│   MySQL/    │  │   GitHub    │  │   GitHub    │
│   SQLite    │  │   Actions   │  │    OAuth    │
│  Database   │  │     API     │  │             │
└─────────────┘  └─────────────┘  └─────────────┘
```

## データベーススキーマ

### users テーブル

| カラム名 | 型 | 説明 |
|---------|-----|------|
| id | bigint | 主キー |
| name | string | ユーザー名 |
| email | string | メールアドレス（ユニーク） |
| github_id | string | GitHub ID（nullable） |
| github_username | string | GitHubユーザー名（nullable） |
| github_token | text | GitHubアクセストークン（encrypted, nullable） |
| avatar | string | アバター画像URL（nullable） |
| email_verified_at | timestamp | メール確認日時（nullable） |
| password | string | パスワード（ハッシュ化） |
| remember_token | string | リメンバートークン |
| created_at | timestamp | 作成日時 |
| updated_at | timestamp | 更新日時 |

### projects テーブル

| カラム名 | 型 | 説明 |
|---------|-----|------|
| id | bigint | 主キー |
| user_id | bigint | ユーザーID（外部キー） |
| name | string | プロジェクト名 |
| staging_url | string | ステージング環境URL |
| production_url | string | 本番環境URL |
| approve_token | string | 承認用トークン（ユニーク） |
| approve_token_expires_at | timestamp | トークン有効期限（nullable） |
| github_owner | string | GitHubオーナー名 |
| github_repo | string | GitHubリポジトリ名 |
| github_workflow_id | string | GitHub ActionsワークフローID |
| github_branch | string | デプロイ対象ブランチ（デフォルト: main） |
| server_dir | string | サーバーディレクトリ（nullable） |
| created_at | timestamp | 作成日時 |
| updated_at | timestamp | 更新日時 |

### deploy_logs テーブル

| カラム名 | 型 | 説明 |
|---------|-----|------|
| id | bigint | 主キー |
| project_id | bigint | プロジェクトID（外部キー） |
| status | string | デプロイステータス（pending/running/success/failed） |
| github_run_id | string | GitHub Actions実行ID（nullable） |
| started_at | timestamp | 開始日時（nullable） |
| finished_at | timestamp | 終了日時（nullable） |
| raw_log | longtext | ログ内容（nullable） |
| created_at | timestamp | 作成日時 |
| updated_at | timestamp | 更新日時 |

### approvals テーブル

| カラム名 | 型 | 説明 |
|---------|-----|------|
| id | bigint | 主キー |
| project_id | bigint | プロジェクトID（外部キー） |
| ip_address | string | 承認時のIPアドレス |
| user_agent | string | 承認時のユーザーエージェント |
| approved_at | timestamp | 承認日時 |
| created_at | timestamp | 作成日時 |
| updated_at | timestamp | 更新日時 |

### approval_messages テーブル

| カラム名 | 型 | 説明 |
|---------|-----|------|
| id | bigint | 主キー |
| project_id | bigint | プロジェクトID（外部キー） |
| message | text | 承認メッセージ（マークダウン） |
| created_at | timestamp | 作成日時 |
| updated_at | timestamp | 更新日時 |

### sessions テーブル

Laravel標準のセッション管理用テーブル

| カラム名 | 型 | 説明 |
|---------|-----|------|
| id | string | セッションID（主キー） |
| user_id | bigint | ユーザーID（nullable、外部キー） |
| ip_address | string | IPアドレス（nullable） |
| user_agent | text | ユーザーエージェント（nullable） |
| payload | longtext | セッションデータ |
| last_activity | integer | 最終アクセス時刻 |

### cache テーブル

Laravel標準のキャッシュ管理用テーブル

| カラム名 | 型 | 説明 |
|---------|-----|------|
| key | string | キャッシュキー（主キー） |
| value | mediumtext | キャッシュ値 |
| expiration | integer | 有効期限（Unixタイムスタンプ） |

## 認証フロー

### GitHub OAuth認証

1. ユーザーが「GitHubでログイン」ボタンをクリック
2. `/auth/github`にリダイレクト（`GitHubController@redirect`）
3. GitHub OAuth認証画面にリダイレクト（スコープ: `repo`, `read:user`, `read:org`）
4. ユーザーがGitHubアカウントで認証
5. `/auth/github/callback`にコールバック（`GitHubController@callback`）
6. GitHubからユーザー情報とアクセストークンを取得
7. 既存ユーザーの場合: `github_id`, `github_username`, `github_token`, `avatar`を更新
8. 新規ユーザーの場合: ユーザーを作成（`email_verified_at`を自動設定）
9. GitHubトークンを暗号化して保存
10. セッションにログイン情報を保存
11. プロジェクト一覧ページにリダイレクト

### 承認ページ（認証不要）

承認ページ（`/approve/{token}`）は認証不要で、トークンベースでアクセス可能です。

- レート制限: ページアクセスは1分間に30回まで
- 承認操作は1時間に5回まで
- トークンの有効期限をチェック

## デプロイフロー

```
1. プロジェクト作成
   ↓
2. 改善内容をマークダウンで入力
   ↓
3. 承認URL生成（approve_token + 有効期限設定）
   ↓
4. クライアントに承認URLを共有
   ↓
5. クライアントが承認URLにアクセス
   ↓
6. クライアントがテスト環境を確認
   ↓
7. 「承認」ボタンをクリック
   ↓
8. ApproveController@approve が呼ばれる
   ↓
9. Approval レコードを作成（IP、User-Agentを記録）
   ↓
10. ApprovalMessage レコードを作成（改善内容を保存）
    ↓
11. DeployLog レコードを作成（status: pending）
    ↓
12. DeployController@trigger が呼ばれる
    ↓
13. プロジェクト所有者のGitHubトークンを復号
    ↓
14. GitHub Actions API に workflow_dispatch リクエスト
    ↓
15. GitHub Actions がデプロイを実行
    ↓
16. GitHub Webhook でデプロイ完了を検知
    ↓
17. DeployLog を更新（status: success/failed, finished_at を設定）
```

## セットアップ

### 前提条件

- PHP ^8.2
- Composer
- Node.js (v18以上推奨)
- MySQL 5.7以上 または SQLite
- GitHub OAuth App

### 1. リポジトリのクローン

```bash
git clone <repository-url>
cd approve-deployment
```

### 2. 依存関係のインストール

```bash
# PHP依存関係
composer install

# JavaScript依存関係
npm install
```

### 3. 環境変数の設定

```bash
cp .env.example .env
php artisan key:generate
```

`.env`ファイルを編集して以下を設定：

```env
APP_NAME=AutoRelease
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=approve_deployment
DB_USERNAME=root
DB_PASSWORD=

# GitHub OAuth設定
GITHUB_CLIENT_ID=your_github_client_id
GITHUB_CLIENT_SECRET=your_github_client_secret
GITHUB_REDIRECT_URI=http://localhost:8000/auth/github/callback
```

### 4. データベースのセットアップ

```bash
php artisan migrate
```

### 5. アセットのビルド

```bash
# 本番用ビルド
npm run build

# 開発モード（ホットリロード）
npm run dev
```

### 6. サーバー起動

```bash
php artisan serve
```

アプリケーションは `http://localhost:8000` でアクセス可能です。

## 環境変数

### 必須環境変数

| 変数名 | 説明 | 例 |
|--------|------|-----|
| `APP_NAME` | アプリケーション名 | AutoRelease |
| `APP_URL` | アプリケーションURL | http://localhost:8000 |
| `DB_*` | データベース接続情報 | - |
| `GITHUB_CLIENT_ID` | GitHub OAuth Client ID | Iv1.xxxxx |
| `GITHUB_CLIENT_SECRET` | GitHub OAuth Client Secret | xxxxx |
| `GITHUB_REDIRECT_URI` | GitHub OAuth リダイレクトURI | http://localhost:8000/auth/github/callback |

### GitHub OAuth App の作成方法

詳細は [GITHUB_OAUTH_SETUP.md](./GITHUB_OAUTH_SETUP.md) を参照してください。

1. GitHubにログイン
2. Settings → Developer settings → OAuth Apps
3. "New OAuth App"をクリック
4. 以下を入力:
   - Application name: AutoRelease (任意)
   - Homepage URL: http://localhost:8000
   - Authorization callback URL: http://localhost:8000/auth/github/callback
5. Client IDとClient Secretを取得

**必要なスコープ**:
- `repo`: リポジトリへのフルアクセス（workflow_dispatchに必要）
- `read:user`: ユーザー情報の読み取り
- `read:org`: 組織情報の読み取り（組織のリポジトリ選択に必要）

## ディレクトリ構造

```
approve-deployment/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── GitHubController.php      # GitHub OAuth認証
│   │   │   │   └── AuthenticatedSessionController.php
│   │   │   ├── ApproveController.php        # 承認ページ
│   │   │   ├── DeployController.php         # デプロイトリガー
│   │   │   ├── DeployLogController.php      # デプロイログ表示
│   │   │   ├── GitHubWebhookController.php  # GitHub Webhook
│   │   │   ├── ProjectController.php         # プロジェクト管理
│   │   │   └── ProfileController.php
│   │   └── Middleware/
│   │       └── HandleInertiaRequests.php
│   ├── Models/
│   │   ├── Project.php                       # プロジェクトモデル
│   │   ├── DeployLog.php                     # デプロイログモデル
│   │   ├── Approval.php                      # 承認モデル
│   │   ├── ApprovalMessage.php               # 承認メッセージモデル
│   │   └── User.php                          # ユーザーモデル
│   └── Providers/
├── config/
│   └── services.php                          # 外部サービス設定
├── database/
│   └── migrations/                          # データベースマイグレーション
├── public/
│   └── build/                                # ビルド済みアセット
│   └── images/                               # 画像ファイル（ロゴなど）
├── resources/
│   ├── js/
│   │   ├── Components/                       # Vueコンポーネント
│   │   │   ├── AppFooter.vue                # フッターコンポーネント
│   │   │   └── ApplicationLogo.vue           # ロゴコンポーネント
│   │   ├── Layouts/                          # レイアウトコンポーネント
│   │   ├── Pages/                            # ページコンポーネント
│   │   │   ├── Dashboard/                    # ダッシュボード関連
│   │   │   ├── Profile/                       # プロフィール関連
│   │   │   ├── Auth/                          # 認証関連
│   │   │   ├── Approve.vue                   # 承認ページ
│   │   │   ├── ApproveStatus.vue             # 承認ステータスページ
│   │   │   ├── Welcome.vue                   # トップページ
│   │   │   ├── Documentation.vue             # ドキュメントページ
│   │   │   ├── Terms.vue                     # 利用規約ページ
│   │   │   └── PrivacyPolicy.vue             # プライバシーポリシーページ
│   │   └── app.js                            # エントリーポイント
│   └── views/
│       └── app.blade.php                     # Inertia.jsルート
├── routes/
│   ├── web.php                               # Webルート
│   ├── api.php                               # APIルート
│   └── auth.php                              # 認証ルート
├── .env.example
├── composer.json
├── package.json
├── README.md
└── GITHUB_OAUTH_SETUP.md                     # GitHub OAuth設定手順
```

## 開発ガイドライン

### コーディング規約

- PHP: [Laravel Coding Standards](https://laravel.com/docs/11.x/contributions#coding-style)
- JavaScript/Vue: [Vue.js Style Guide](https://vuejs.org/style-guide/)

### コードフォーマット

```bash
# PHP
./vendor/bin/pint

# JavaScript/Vue
npm run format  # (設定されている場合)
```

### テスト

```bash
php artisan test
```

### デバッグ

```bash
# Laravel Tinker
php artisan tinker

# ログ確認
tail -f storage/logs/laravel.log
```

### よくある問題と解決方法

#### 1. GitHub Actionsがトリガーされない

- プロジェクト所有者の`github_token`が正しく保存されているか確認
- GitHub Personal Access Tokenに`workflow`スコープがあるか確認
- ワークフローIDが正しいか確認
- GitHub Actionsのワークフローで`workflow_dispatch`が有効になっているか確認

#### 2. GitHub OAuth認証が失敗する

- `GITHUB_CLIENT_ID`と`GITHUB_CLIENT_SECRET`が正しいか確認
- GitHub OAuth AppでリダイレクトURIが正しく設定されているか確認
- `APP_URL`と`GITHUB_REDIRECT_URI`が一致しているか確認
- 詳細は [GITHUB_OAUTH_SETUP.md](./GITHUB_OAUTH_SETUP.md) を参照

#### 3. アセットが読み込まれない

```bash
# キャッシュクリア
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# アセット再ビルド
npm run build
```

#### 4. 承認URLが無効になる

- トークンの有効期限（`approve_token_expires_at`）を確認
- デフォルトで7日間の有効期限が設定されています
- 有効期限切れの場合は、プロジェクト詳細ページから承認URLを再生成してください

## GitHub Actionsワークフロー設定例

プロジェクトのリポジトリに`.github/workflows/deploy.yml`を配置：

```yaml
name: Deploy to Production

on:
  workflow_dispatch:
    inputs:
      project_id:
        description: 'Project ID'
        required: true
      deploy_log_id:
        description: 'Deploy Log ID'
        required: false
      server_dir:
        description: 'Server directory path'
        required: false
        default: '/public_html/'

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout repository
        uses: actions/checkout@v4
        with:
          ref: ${{ github.event.inputs.ref || github.ref }}

      - name: Setup SSH
        uses: webfactory/ssh-agent@v0.9.0
        with:
          ssh-private-key: ${{ secrets.SSH_PRIVATE_KEY }}

      - name: Add known hosts
        run: |
          mkdir -p ~/.ssh
          ssh-keyscan -p ${{ secrets.SSH_PORT || 22 }} \
            ${{ secrets.SSH_HOST }} >> ~/.ssh/known_hosts

      - name: Deploy via rsync
        run: |
          rsync -avz --delete \
            --exclude='.git' \
            --exclude='.gitignore' \
            --exclude='node_modules' \
            --exclude='.env' \
            --exclude='.env.*' \
            ./ \
            -e "ssh -p ${{ secrets.SSH_PORT || 22 }}" \
            ${{ secrets.SSH_USER }}@${{ secrets.SSH_HOST }}:${{ inputs.server_dir || '/public_html/' }}
```

### GitHub Secretsの設定

GitHubリポジトリの Settings → Secrets and variables → Actions で以下を設定：

- `SSH_HOST`: SSHサーバーのアドレス（例: example.com）
- `SSH_USER`: SSHユーザー名（例: root）
- `SSH_PORT`: SSHポート番号（オプション、デフォルト: 22）
- `SSH_PRIVATE_KEY`: SSH秘密鍵（-----BEGIN RSA PRIVATE KEY----- から始まる内容全体）

### rsyncの利点

- **高速**: 変更されたファイルのみを転送するため、FTPと比べてデプロイ時間を大幅に短縮
- **効率的**: 差分転送により、大量のファイルがあるプロジェクトでも数分で完了
- **信頼性**: 転送の再開が可能で、ネットワークエラーにも強い

## GitHub Webhook設定

GitHub Webhookを設定することで、デプロイ完了を自動的に検知できます。

1. GitHubリポジトリの Settings → Webhooks → Add webhook
2. Payload URL: `https://yourdomain.com/api/github/webhook`
3. Content type: `application/json`
4. Events: `Workflow run` を選択
5. Active にチェックを入れて保存

## セキュリティ機能

### レート制限

- 承認ページアクセス: 1分間に30回まで
- 承認操作: 1時間に5回まで
- デプロイログAPI: 1分間に60回まで

### トークン管理

- 承認トークンは64文字のランダム文字列
- 有効期限付き（デフォルト7日間）
- トークンはユニーク制約あり

### XSS対策

- 承認メッセージの表示にはDOMPurifyによるサニタイズ処理を実装
- マークダウンからHTMLへの変換時にサニタイズ

### データ保護

- GitHubトークンは暗号化して保存（Laravel Crypt）
- 承認ページでは必要最小限の情報のみ表示
- IPアドレスとUser-Agentを記録して監査対応

## ライセンス

MIT

## コントリビューション

プルリクエストを歓迎します。大きな変更の場合は、まずIssueを開いて変更内容を議論してください。

## サポート

問題が発生した場合は、Issueを作成してください。

## 運営元

本サービスは [RUBY DESIGN](https://rubydesign.jp/) が運営しています。
