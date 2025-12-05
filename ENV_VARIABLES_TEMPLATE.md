# Railway環境変数設定テンプレート

このファイルは、Railwayの環境変数を設定する際のコピー&ペースト用テンプレートです。

## 必須環境変数（Railway Dashboard → Variablesタブで設定）

### アプリケーション設定

```
APP_NAME=AutoRelease
APP_ENV=production
APP_KEY=base64:2ErZuoQ0PD3CpKXAy5s7i1uv0Pw89Jt4NbTkiEfkx1Q=
APP_DEBUG=false
APP_URL=https://domains.squarespace.com/ja
```

### データベース設定（MySQLサービスの環境変数参照）

**重要:** MySQLサービス名が`MySQL`の場合の例です。実際のサービス名に合わせて変更してください。

```
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQL_HOST}}
DB_PORT=${{MySQL.MYSQL_PORT}}
DB_DATABASE=${{MySQL.MYSQL_DATABASE}}
DB_USERNAME=${{MySQL.MYSQL_USER}}
DB_PASSWORD=${{MySQL.MYSQL_PASSWORD}}
```

### GitHub OAuth設定（本番環境用）

**重要:** ステップ3で取得した値に置き換えてください。

```
GITHUB_CLIENT_ID=your_production_github_client_id
GITHUB_CLIENT_SECRET=your_production_github_client_secret
GITHUB_REDIRECT_URI=https://domains.squarespace.com/ja/auth/github/callback
```

### セッション設定

```
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

### キャッシュ設定

```
CACHE_DRIVER=database
QUEUE_CONNECTION=database
```

## 設定手順

1. Railway Dashboardでプロジェクトを開く
2. 「**Variables**」タブをクリック
3. 「**+ New Variable**」をクリック
4. 上記の変数を1つずつ追加（または一括インポート）
5. `GITHUB_CLIENT_ID`と`GITHUB_CLIENT_SECRET`は、GitHub OAuth App作成後に取得した値に置き換える

## 確認事項

- [ ] すべての環境変数が設定されている
- [ ] `APP_KEY`が正しく設定されている
- [ ] `GITHUB_CLIENT_ID`と`GITHUB_CLIENT_SECRET`が設定されている
- [ ] `DB_*`変数が`${{MySQL.*}}`形式で参照されている
- [ ] `APP_URL`と`GITHUB_REDIRECT_URI`が一致している

