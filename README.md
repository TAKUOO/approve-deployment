# Quicknee

Web製作者向けのSaaS「承認 → 自動アップ」サービス

テスト環境でクライアントが内容を確認し、「承認」ボタンを押すだけでGitHub Actionsを通じて本番環境へ自動的にアップされる仕組みを提供します。

## 📋 目次

- [概要](#概要)
- [技術スタック](#技術スタック)
- [システムアーキテクチャ](#システムアーキテクチャ)
- [データベーススキーマ](#データベーススキーマ)
- [各テーブルの必要性](#各テーブルの必要性)
- [認証フロー](#認証フロー)
- [デプロイフロー](#デプロイフロー)
- [API仕様](#api仕様)
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

- 承認URLを送るだけで即座に承認可能
- シンプルな承認フローでスムーズに進行
- 自動アップでミスゼロ、時間短縮
- 承認から本番環境へのアップまで数秒で完了
- クライアントはログイン不要で簡単

### 主な機能

- **プロジェクト管理**: プロジェクトの作成、一覧表示、詳細表示
- **承認URL生成**: トークンベースの承認URLを自動生成
- **GitHub Actions連携**: `workflow_dispatch`イベントで本番環境へのアップをトリガー
- **アップ履歴管理**: 本番環境へのアップ履歴とステータスを記録・表示
- **GitHub Webhook連携**: アップ作業完了を検知してログを更新

## 技術スタック

### バックエンド

- **PHP**: ^8.2
- **Laravel**: ^11.0
- **Laravel Breeze**: ^2.3（認証基盤）
- **Laravel Socialite**: ^5.23（Google OAuth認証）
- **Laravel Sanctum**: ^4.0（API認証）
- **MySQL**: データベース

### フロントエンド

- **Vue.js**: ^3.4.0
- **Inertia.js**: ^2.0（SPA体験を提供）
- **Tailwind CSS**: ^3.2.1（スタイリング）
- **Ziggy**: ^2.0（LaravelルートをJavaScriptで利用）

### ビルドツール

- **Vite**: ^5.0.0（アセットバンドラー）
- **PostCSS**: ^8.4.31
- **Autoprefixer**: ^10.4.12

### 外部サービス連携

- **GitHub Actions API**: `workflow_dispatch`イベントでデプロイをトリガー
- **Google OAuth 2.0**: ユーザー認証

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
│  │  - GoogleController                  │  │
│  └──────────────────────────────────────┘  │
│  ┌──────────────────────────────────────┐  │
│  │  Models                               │  │
│  │  - Project                            │  │
│  │  - DeployLog                          │  │
│  │  - User                               │  │
│  └──────────────────────────────────────┘  │
└──────┬─────────────────────────────────────┘
       │
       ├─────────────────┬──────────────────┐
       │                 │                  │
┌──────▼──────┐  ┌──────▼──────┐  ┌──────▼──────┐
│   MySQL     │  │   GitHub    │  │   Google    │
│  Database   │  │   Actions   │  │    OAuth    │
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
| github_owner | string | GitHubオーナー名 |
| github_repo | string | GitHubリポジトリ名 |
| github_workflow_id | string | GitHub ActionsワークフローID |
| github_branch | string | デプロイ対象ブランチ（デフォルト: main） |
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

## 各テーブルの必要性

### 1. **users テーブル** - 必須

**役割**: ユーザー情報の管理

**必要な理由**:
- GitHub OAuthでログインしたユーザー情報を保存
- プロジェクトの所有権を管理（どのユーザーがどのプロジェクトを作成したか）
- 認証状態の管理（ログイン/ログアウト）
- `github_id`で既存ユーザーを識別し、新規作成/更新を判断
- `github_token`を保存し、GitHub APIへのアクセス（リポジトリ一覧取得、デプロイ実行）に使用

**使用箇所**:
- GitHubログイン時のユーザー作成/更新
- プロジェクト作成時に`user_id`を保存
- プロジェクト一覧で自分のプロジェクトのみ表示
- デプロイ実行時にトークンを復号して使用

---

### 2. **projects テーブル** - 必須

**役割**: プロジェクト情報の管理

**必要な理由**:
- 各プロジェクトの設定を保存（ステージングURL、本番URL、GitHub情報）
- `approve_token`で承認ページへのアクセスを制御（ログイン不要で承認可能）
- ユーザーとプロジェクトの紐付け（`user_id`）
- GitHub Actions連携に必要な情報を保持

**使用箇所**:
- プロジェクト作成・一覧・詳細表示
- 承認ページでトークンからプロジェクトを特定
- デプロイ時にGitHub情報を取得

---

### 3. **deploy_logs テーブル** - 必須

**役割**: デプロイ履歴の記録

**必要な理由**:
- 各デプロイの実行履歴を保存
- デプロイステータス（pending/running/success/failed）を追跡
- GitHub Actionsの実行ID（`github_run_id`）を保存して連携
- デプロイ開始・終了時刻を記録
- エラー時のログ（`raw_log`）を保存してトラブルシューティング

**使用箇所**:
- プロジェクト詳細ページでデプロイ履歴を表示
- GitHub Webhookでデプロイ完了を検知してステータス更新
- デプロイの成功/失敗を確認

---

### 4. **sessions テーブル** - 必須

**役割**: セッション管理

**必要な理由**:
- Laravelのセッション管理に必要
- ログイン状態を維持（ページ遷移後もログイン状態を保持）
- セキュリティ（CSRFトークン、セッションIDの管理）
- 複数デバイスでのログイン状態を管理

**使用箇所**:
- ユーザーがログインしている間、セッション情報を保存
- ログアウト時にセッションを削除

---

### 5. **cache テーブル** - 推奨（削除可能）

**役割**: データベースキャッシュ

**必要な理由**:
- Laravelの`Cache::store('database')`を使用する場合に必要
- パフォーマンス向上（頻繁にアクセスするデータをキャッシュ）
- 設定のキャッシュ（`config:cache`）

**削除可能な場合**:
- ファイルキャッシュ（`CACHE_DRIVER=file`）を使用している場合
- RedisやMemcachedを使用している場合

**現在の使用状況**: Laravelの標準機能で使用される可能性があるため、残しておくことを推奨

---

## テーブル間の関係

```
users (1) ──< (多) projects (1) ──< (多) deploy_logs
  │
  └── sessions (1対多: 1ユーザーが複数セッションを持つ可能性)
```

- **users → projects**: 1ユーザーが複数のプロジェクトを作成可能
- **projects → deploy_logs**: 1プロジェクトが複数のデプロイ履歴を持つ
- **users → sessions**: 1ユーザーが複数のセッション（複数デバイス）を持つ可能性

## テーブル必要性まとめ

| テーブル | 必要性 | 理由 |
|---------|--------|------|
| **users** | 必須 | ユーザー認証とプロジェクト所有権の管理 |
| **projects** | 必須 | プロジェクト設定と承認トークンの管理 |
| **deploy_logs** | 必須 | デプロイ履歴とステータスの追跡 |
| **sessions** | 必須 | ログイン状態の維持 |
| **cache** | 推奨 | パフォーマンス向上（削除可能） |

## 認証フロー

### GitHub OAuth認証

1. ユーザーが「GitHubでログイン」ボタンをクリック
2. `/auth/github`にリダイレクト（`GitHubController@redirect`）
3. GitHub OAuth認証画面にリダイレクト
4. ユーザーがGitHubアカウントで認証
5. `/auth/github/callback`にコールバック（`GitHubController@callback`）
6. GitHubからユーザー情報とアクセストークンを取得
7. 既存ユーザーの場合: `github_id`, `github_username`, `github_token`, `avatar`を更新
8. 新規ユーザーの場合: ユーザーを作成（`email_verified_at`を自動設定）
9. セッションにログイン情報を保存
10. ダッシュボードにリダイレクト

### 承認ページ（認証不要）

承認ページ（`/approve/{token}`）は認証不要で、トークンベースでアクセス可能です。

## デプロイフロー

```
1. プロジェクト作成
   ↓
2. 承認URL生成（approve_token）
   ↓
3. クライアントに承認URLを共有
   ↓
4. クライアントが承認URLにアクセス
   ↓
5. クライアントがテスト環境を確認
   ↓
6. 「承認」ボタンをクリック
   ↓
7. ApproveController@approve が呼ばれる
   ↓
8. DeployController@trigger が呼ばれる
   ↓
9. DeployLog レコードを作成（status: pending）
   ↓
10. プロジェクト所有者のGitHubトークンを復号
    ↓
11. GitHub Actions API に workflow_dispatch リクエスト
    ↓
12. GitHub Actions がデプロイを実行
    ↓
13. GitHub Webhook でデプロイ完了を検知
    ↓
14. DeployLog を更新（status: success/failed）
```

## API仕様

### Web Routes

| Method | Path | Controller | 説明 | 認証 |
|--------|------|------------|------|------|
| GET | `/` | - | トップページ | 不要 |
| GET | `/dashboard` | - | ダッシュボード | 必要 |
| GET | `/projects` | ProjectController@index | プロジェクト一覧 | 必要 |
| GET | `/projects/create` | ProjectController@create | プロジェクト作成フォーム | 必要 |
| POST | `/projects` | ProjectController@store | プロジェクト作成 | 必要 |
| GET | `/projects/{project}` | ProjectController@show | プロジェクト詳細 | 必要 |
| GET | `/approve/{token}` | ApproveController@show | 承認ページ | 不要 |
| POST | `/approve/{token}` | ApproveController@approve | 承認実行 | 不要 |
| GET | `/auth/github` | GitHubController@redirect | GitHub認証開始 | 不要 |
| GET | `/auth/github/callback` | GitHubController@callback | GitHub認証コールバック | 不要 |
| GET | `/api/github/repositories` | ProjectController@getRepositories | リポジトリ一覧取得 | 必要 |
| GET | `/api/github/workflows` | ProjectController@getWorkflows | ワークフロー一覧取得 | 必要 |
| GET | `/api/github/branches` | ProjectController@getBranches | ブランチ一覧取得 | 必要 |

### API Routes

| Method | Path | Controller | 説明 | 認証 |
|--------|------|------------|------|------|
| POST | `/api/projects/{project}/deploy` | DeployController@trigger | デプロイトリガー | 内部API |
| POST | `/api/github/webhook` | GitHubWebhookController@handle | GitHub Webhook受信 | Webhook署名検証 |

### GitHub Actions API

**エンドポイント**: `POST https://api.github.com/repos/{owner}/{repo}/actions/workflows/{workflow_id}/dispatches`

**リクエストボディ**:
```json
{
  "ref": "main",
  "inputs": {
    "project_id": 1,
    "deploy_log_id": 1
  }
}
```

## セットアップ

### 前提条件

- PHP ^8.2
- Composer
- Node.js (v18以上推奨)
- MySQL 5.7以上
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
APP_NAME=Quicknee
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
| `APP_NAME` | アプリケーション名 | Quicknee |
| `APP_URL` | アプリケーションURL | http://localhost:8000 |
| `DB_*` | データベース接続情報 | - |
| `GITHUB_CLIENT_ID` | GitHub OAuth Client ID | Iv1.xxxxx |
| `GITHUB_CLIENT_SECRET` | GitHub OAuth Client Secret | xxxxx |
| `GITHUB_REDIRECT_URI` | GitHub OAuth リダイレクトURI | http://localhost:8000/auth/github/callback |

### GitHub OAuth App の作成方法

1. GitHubにログイン
2. Settings → Developer settings → OAuth Apps
3. "New OAuth App"をクリック
4. 以下を入力:
   - Application name: Quicknee (任意)
   - Homepage URL: http://localhost:8000
   - Authorization callback URL: http://localhost:8000/auth/github/callback
5. Client IDとClient Secretを取得

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
│   │   │   ├── GitHubWebhookController.php  # GitHub Webhook
│   │   │   ├── ProjectController.php         # プロジェクト管理
│   │   │   └── ProfileController.php
│   │   └── Middleware/
│   │       └── HandleInertiaRequests.php
│   ├── Models/
│   │   ├── Project.php                       # プロジェクトモデル
│   │   ├── DeployLog.php                     # デプロイログモデル
│   │   └── User.php                          # ユーザーモデル
│   └── Providers/
├── config/
│   └── services.php                          # 外部サービス設定
├── database/
│   └── migrations/                           # データベースマイグレーション
├── public/
│   └── build/                                # ビルド済みアセット
├── resources/
│   ├── js/
│   │   ├── Components/                      # Vueコンポーネント
│   │   ├── Layouts/                          # レイアウトコンポーネント
│   │   ├── Pages/                            # ページコンポーネント
│   │   │   ├── Dashboard/                    # ダッシュボード関連
│   │   │   ├── Profile/                       # プロフィール関連
│   │   │   ├── Auth/                          # 認証関連
│   │   │   ├── Approve.vue                   # 承認ページ
│   │   │   └── Welcome.vue                   # トップページ
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
└── README.md
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

- `GITHUB_TOKEN`が正しく設定されているか確認
- GitHub Personal Access Tokenに`workflow`スコープがあるか確認
- ワークフローIDが正しいか確認

#### 2. Google OAuth認証が失敗する

- `GOOGLE_CLIENT_ID`と`GOOGLE_CLIENT_SECRET`が正しいか確認
- Google Cloud ConsoleでリダイレクトURIが正しく設定されているか確認
- `APP_URL`と`GOOGLE_REDIRECT_URI`が一致しているか確認

#### 3. アセットが読み込まれない

```bash
# キャッシュクリア
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# アセット再ビルド
npm run build
```

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
        required: true

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Deploy to production
        run: |
          echo "Deploying project ${{ github.event.inputs.project_id }}"
          # ここにデプロイコマンドを記述
          # 例: SSH接続してデプロイスクリプトを実行
```

## ライセンス

MIT

## コントリビューション

プルリクエストを歓迎します。大きな変更の場合は、まずIssueを開いて変更内容を議論してください。

## サポート

問題が発生した場合は、Issueを作成してください。
