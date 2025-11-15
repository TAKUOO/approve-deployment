# ApproveDeploy

Web製作者向けのSaaS「承認 → 自動アップ」サービス

テスト環境でクライアントが内容を確認し、「承認」ボタンを押すだけでGitHub Actionsを通じて本番環境へ自動的にアップされる仕組みを提供します。

## 📋 目次

- [概要](#概要)
- [技術スタック](#技術スタック)
- [システムアーキテクチャ](#システムアーキテクチャ)
- [データベーススキーマ](#データベーススキーマ)
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
| google_id | string | Google OAuth ID（nullable） |
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

### cache テーブル

Laravel標準のキャッシュ管理用テーブル

## 認証フロー

### Google OAuth認証

1. ユーザーが「Googleでログイン」ボタンをクリック
2. `/auth/google`にリダイレクト（`GoogleController@redirect`）
3. Google OAuth認証画面にリダイレクト
4. ユーザーがGoogleアカウントで認証
5. `/auth/google/callback`にコールバック（`GoogleController@callback`）
6. Googleからユーザー情報を取得
7. 既存ユーザーの場合: `google_id`と`avatar`を更新
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
10. GitHub Actions API に workflow_dispatch リクエスト
    ↓
11. GitHub Actions がデプロイを実行
    ↓
12. GitHub Webhook でデプロイ完了を検知
    ↓
13. DeployLog を更新（status: success/failed）
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
| GET | `/auth/google` | GoogleController@redirect | Google認証開始 | 不要 |
| GET | `/auth/google/callback` | GoogleController@callback | Google認証コールバック | 不要 |

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
- GitHub Personal Access Token（`workflow_dispatch`権限が必要）

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
APP_NAME=ApproveDeploy
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=approve_deployment
DB_USERNAME=root
DB_PASSWORD=

# GitHub設定
GITHUB_TOKEN=your_github_personal_access_token

# Google OAuth設定
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
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
| `APP_NAME` | アプリケーション名 | ApproveDeploy |
| `APP_URL` | アプリケーションURL | http://localhost:8000 |
| `DB_*` | データベース接続情報 | - |
| `GITHUB_TOKEN` | GitHub Personal Access Token | ghp_xxxxx |
| `GOOGLE_CLIENT_ID` | Google OAuth Client ID | xxxxx.apps.googleusercontent.com |
| `GOOGLE_CLIENT_SECRET` | Google OAuth Client Secret | GOCSPX-xxxxx |
| `GOOGLE_REDIRECT_URI` | Google OAuth リダイレクトURI | http://localhost:8000/auth/google/callback |

### GitHub Personal Access Token の取得方法

1. GitHubにログイン
2. Settings → Developer settings → Personal access tokens → Tokens (classic)
3. "Generate new token"をクリック
4. 以下のスコープを選択:
   - `repo` (Full control of private repositories)
   - `workflow` (Update GitHub Action workflows)
5. トークンを生成してコピー

### Google OAuth設定

1. [Google Cloud Console](https://console.cloud.google.com/)にアクセス
2. プロジェクトを作成
3. APIとサービス → 認証情報
4. OAuth 2.0 クライアントIDを作成
5. 承認済みのJavaScript生成元: `http://localhost:8000`
6. 承認済みのリダイレクトURI: `http://localhost:8000/auth/google/callback`

## ディレクトリ構造

```
approve-deployment/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── GoogleController.php      # Google OAuth認証
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
