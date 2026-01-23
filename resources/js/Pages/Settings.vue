<template>
    <Head title="設定マニュアル - AutoRelease" />
    
    <AuthenticatedLayout>
        <div class="py-12 min-h-screen bg-indigo-50">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-xl">
                    <div class="p-8 text-gray-900">
                        <!-- タイトル -->
                        <div class="mb-8">
                            <h1 class="text-2xl font-semibold text-gray-900">設定マニュアル</h1>
                            <p class="mt-2 text-gray-600">
                                プロジェクトのデプロイに必要な設定方法を確認できます。
                            </p>
                        </div>

                        <!-- タブナビゲーション -->
                        <div class="mb-8 border-b border-gray-200">
                            <nav class="flex -mb-px space-x-8">
                                <button
                                    v-for="tab in tabs"
                                    :key="tab.id"
                                    @click="activeTab = tab.id"
                                    :class="[
                                        'py-4 px-1 border-b-2 font-medium text-sm transition',
                                        activeTab === tab.id
                                            ? 'border-indigo-500 text-indigo-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    ]"
                                >
                                    {{ tab.name }}
                                </button>
                            </nav>
                        </div>

                        <!-- ワークフローの設定 -->
                        <div v-if="activeTab === 'workflow'" class="space-y-6">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900 mb-2">GitHub Actionsワークフローの設定</h2>
                                <p class="text-gray-600 mb-6">
                                    承認時に実行されるGitHub Actionsのワークフローを作成します。リポジトリ内の <code class="px-1.5 py-0.5 text-xs bg-gray-100 rounded">.github/workflows/</code> ディレクトリに配置されているワークフローが使用されます。
                                </p>
                            </div>

                            <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <h3 class="mb-3 font-semibold text-blue-900">セットアップ手順</h3>
                                <ol class="space-y-2 text-sm list-decimal list-inside text-blue-800">
                                    <li>GitHubリポジトリで、<code class="px-1.5 py-0.5 font-mono text-xs bg-blue-100 rounded">.github/workflows</code> ディレクトリを作成</li>
                                    <li>下記のテンプレートから適切なものを選択してコピー</li>
                                    <li><code class="px-1.5 py-0.5 font-mono text-xs bg-blue-100 rounded">deploy.yml</code> という名前でファイルを作成して貼り付け</li>
                                    <li>GitHubにコミット&プッシュ</li>
                                    <li>プロジェクト編集画面でワークフローを選択</li>
                                </ol>
                            </div>

                            <!-- テンプレート選択タブ -->
                            <div>
                                <div class="flex mb-3 space-x-2 border-b border-gray-200">
                                    <button
                                        v-for="template in workflowTemplates"
                                        :key="template.id"
                                        @click="selectedTemplate = template.id"
                                        :class="[
                                            'px-4 py-2 text-sm font-medium border-b-2 transition',
                                            selectedTemplate === template.id
                                                ? 'border-indigo-500 text-indigo-600'
                                                : 'border-transparent text-gray-400 hover:text-gray-600'
                                        ]"
                                    >
                                        {{ template.name }}
                                    </button>
                                </div>
                                
                                <!-- 選択されたテンプレートの表示 -->
                                <div class="relative p-4 bg-gray-900 rounded-lg">
                                    <button
                                        @click="copyTemplate"
                                        class="absolute top-2 right-2 px-3 py-1 text-xs font-medium text-white bg-indigo-600 rounded transition hover:bg-indigo-700"
                                    >
                                        {{ copied ? 'コピーしました！' : 'コピー' }}
                                    </button>
                                    <pre class="overflow-x-auto text-xs text-gray-100"><code>{{ getCurrentTemplate() }}</code></pre>
                                </div>
                                <p class="mt-2 text-xs text-gray-600">
                                    {{ getCurrentTemplateDescription() }}
                                </p>
                                
                                <!-- GitHub Secrets設定例 -->
                                <div class="p-4 mt-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <h5 class="mb-2 text-sm font-semibold text-gray-900">必要なGitHub Secrets設定</h5>
                                    <p class="mb-3 text-xs text-gray-600">
                                        GitHubリポジトリの <strong>Settings → Secrets and variables → Actions</strong> で以下を設定してください：
                                    </p>
                                    <div class="space-y-2 text-xs">
                                        <div v-for="secret in getCurrentTemplateSecrets()" :key="secret.name" class="flex items-start">
                                            <code class="px-2 py-1 font-mono bg-white border border-gray-300 rounded min-w-[140px]">{{ secret.name }}</code>
                                            <span class="ml-2 text-gray-700">{{ secret.description }}</span>
                                        </div>
                                    </div>
                                    <div class="p-3 mt-3 bg-blue-50 rounded border border-blue-200">
                                        <p class="mb-1 text-xs font-semibold text-blue-900">設定例：</p>
                                        <pre class="text-xs text-blue-800 whitespace-pre-wrap">{{ getCurrentTemplateSecretsExample() }}</pre>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                <p class="text-sm text-yellow-800">
                                    <strong>注意:</strong> Secretsは一度しか表示されません。コピーして安全に保管してください。また、Secret名は大文字・小文字を区別します。
                                </p>
                            </div>
                        </div>

                        <!-- SSHの設定 -->
                        <div v-if="activeTab === 'ssh'" class="space-y-6">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900 mb-2">SSH設定の詳細</h2>
                                <p class="text-gray-600 mb-6">
                                    デプロイのためにGitHub Secretsとサーバー側のSSH設定が必要です。
                                </p>
                            </div>

                            <div class="space-y-6 text-sm text-gray-700">
                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <h3 class="text-base font-semibold text-gray-900 mb-2">1. GitHub Secretsに登録</h3>
                                    <p class="mb-3">
                                        リポジトリの <strong>Settings → Secrets and variables → Actions</strong> に、以下を登録します。
                                    </p>
                                    <ul class="space-y-2">
                                        <li><code class="px-2 py-0.5 bg-white rounded border">SSH_HOST</code> SSHサーバーのアドレス</li>
                                        <li><code class="px-2 py-0.5 bg-white rounded border">SSH_USER</code> SSHユーザー名</li>
                                        <li><code class="px-2 py-0.5 bg-white rounded border">SSH_PORT</code> ポート番号（省略可、通常22）</li>
                                        <li><code class="px-2 py-0.5 bg-white rounded border">SSH_PRIVATE_KEY</code> 秘密鍵の内容</li>
                                        <li><code class="px-2 py-0.5 bg-white rounded border">SERVER_DIR</code> デプロイ先ディレクトリパス（省略可、デフォルト: /public_html/）</li>
                                    </ul>
                                </div>

                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <h3 class="text-base font-semibold text-gray-900 mb-2">2. 秘密鍵の確認</h3>
                                    <p class="mb-3">秘密鍵の内容を <code class="px-2 py-0.5 bg-white rounded border">SSH_PRIVATE_KEY</code> に設定します。</p>
                                    <pre class="p-3 rounded bg-gray-900 text-gray-100 text-xs"><code>cat ~/.ssh/id_rsa</code></pre>
                                    <p class="mt-2 text-xs text-gray-500">
                                        出力された <code class="px-1 py-0.5 bg-white rounded border">-----BEGIN RSA PRIVATE KEY-----</code> から最後までをコピーしてください。
                                    </p>
                                </div>

                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <h3 class="text-base font-semibold text-gray-900 mb-2">3. 公開鍵をサーバーに追加</h3>
                                    <p class="mb-3">公開鍵をサーバー側の <code class="px-2 py-0.5 bg-white rounded border">~/.ssh/authorized_keys</code> に追加します。</p>
                                    <pre class="p-3 rounded bg-gray-900 text-gray-100 text-xs"><code>cat ~/.ssh/id_rsa.pub</code></pre>
                                </div>

                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <h3 class="text-base font-semibold text-gray-900 mb-2">4. デプロイ先パスの設定</h3>
                                    <p class="mb-3">
                                        <code class="px-2 py-0.5 bg-white rounded border">SERVER_DIR</code> にデプロイ先ディレクトリパスを設定します。設定しない場合、デフォルトの <code class="px-2 py-0.5 bg-white rounded border">/public_html/</code> が使用されます。
                                    </p>
                                    <div class="p-3 mt-3 bg-blue-50 rounded border border-blue-200">
                                        <p class="mb-2 text-xs font-semibold text-blue-900">設定例：</p>
                                        <ul class="space-y-1 text-xs text-blue-800">
                                            <li>• <code class="px-1.5 py-0.5 bg-blue-100 rounded">/public_html/</code> - 一般的なレンタルサーバー</li>
                                            <li>• <code class="px-1.5 py-0.5 bg-blue-100 rounded">/home/example.com/public_html/</code> - カスタムドメイン</li>
                                            <li>• <code class="px-1.5 py-0.5 bg-blue-100 rounded">/var/www/html/</code> - VPS・専用サーバー</li>
                                            <li>• <code class="px-1.5 py-0.5 bg-blue-100 rounded">/home/example.com/public_html/wp-content/themes/mytheme/</code> - WordPressテーマ</li>
                                        </ul>
                                    </div>
                                    <p class="mt-3 text-xs text-gray-600">
                                        <strong>注意:</strong> パスの末尾にスラッシュ（/）を付けることを推奨します。
                                    </p>
                                </div>

                                <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200 text-yellow-900">
                                    <p class="text-sm">
                                        <strong>注意:</strong> Secrets名は大文字・小文字を区別します。SSH接続できない場合は、サーバーのアクセス権限とファイアウォール設定も確認してください。
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- GitHub Webhookの設定 -->
                        <div v-if="activeTab === 'webhook'" class="space-y-6">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900 mb-2">GitHub Webhookの設定</h2>
                                <p class="text-gray-600 mb-6">
                                    GitHub Webhookを設定することで、デプロイ完了を自動的に検知できます。設定しない場合、デプロイステータスの更新が遅れる可能性があります。
                                </p>
                            </div>

                            <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <h3 class="mb-3 font-semibold text-blue-900">設定手順</h3>
                                <ol class="ml-6 space-y-4 list-decimal text-blue-800">
                                    <li>
                                        <p class="text-sm">GitHubリポジトリの <strong>Settings</strong> → <strong>Webhooks</strong> → <strong>Add webhook</strong> を開く</p>
                                    </li>
                                    <li>
                                        <p class="text-sm mb-2">
                                            <strong>Payload URL</strong> に以下を入力：
                                        </p>
                                        <code class="block px-4 py-3 text-sm text-gray-700 bg-white rounded-lg border border-gray-300">{{ webhookUrl }}</code>
                                    </li>
                                    <li>
                                        <p class="text-sm"><strong>Content type</strong> を <code class="px-1.5 py-0.5 text-xs bg-blue-100 rounded">application/json</code> に設定</p>
                                    </li>
                                    <li>
                                        <p class="text-sm"><strong>Events</strong> で <strong>Workflow run</strong> を選択（または <strong>Let me select individual events</strong> を選択して <strong>Workflow run</strong> にチェック）</p>
                                    </li>
                                    <li>
                                        <p class="text-sm"><strong>Active</strong> にチェックを入れて保存</p>
                                    </li>
                                </ol>
                            </div>

                            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <h3 class="mb-2 text-base font-semibold text-gray-900">Webhookの動作</h3>
                                <p class="text-sm text-gray-700">
                                    Webhookが設定されると、GitHub Actionsのワークフロー実行が完了した際に、自動的にデプロイステータスが更新されます。これにより、承認画面でリアルタイムにデプロイの進行状況を確認できます。
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';

const activeTab = ref('workflow');
const selectedTemplate = ref('ftp');
const copied = ref(false);

const tabs = [
    { id: 'workflow', name: 'ワークフローの設定' },
    { id: 'ssh', name: 'SSHの設定' },
    { id: 'webhook', name: 'GitHub Webhookの設定' },
];

const webhookUrl = computed(() => {
    const appUrl = window.location.origin;
    return `${appUrl}/api/github/webhook`;
});

const workflowTemplates = [
    {
        id: 'ftp',
        name: 'rsyncデプロイ（推奨）',
        description: 'rsyncを使用して高速にファイルを同期します。変更されたファイルのみを転送するため、FTPよりも大幅に高速です。',
        secrets: [
            { name: 'SSH_HOST', description: 'SSHサーバーのアドレス（例: example.com または 123.45.67.89）' },
            { name: 'SSH_USER', description: 'SSHユーザー名（例: root または ubuntu）' },
            { name: 'SSH_PORT', description: 'SSHポート番号（オプション、デフォルト: 22）' },
            { name: 'SSH_PRIVATE_KEY', description: 'SSH秘密鍵（-----BEGIN RSA PRIVATE KEY----- から始まる内容全体）' },
            { name: 'SERVER_DIR', description: 'デプロイ先ディレクトリパス（オプション、デフォルト: /public_html/）' }
        ],
        secretsExample: `SSH_HOST: example.com
SSH_USER: root
SSH_PORT: 22
SSH_PRIVATE_KEY: |
  -----BEGIN RSA PRIVATE KEY-----
  MIIEpAIBAAKCAQEA...
  (秘密鍵の内容をそのまま貼り付け)
  -----END RSA PRIVATE KEY-----
SERVER_DIR: /public_html/

※ SSH秘密鍵は以下のコマンドで確認できます:
  cat ~/.ssh/id_rsa

※ 公開鍵（id_rsa.pub）をサーバー側の
  ~/.ssh/authorized_keys に追加してください

※ SSH_PORTとSERVER_DIRは省略可能です（デフォルト: SSH_PORT=22, SERVER_DIR=/public_html/）`,
        content: `name: Deploy to Production

on:
  workflow_dispatch:
    inputs:
      project_id:
        description: 'Project ID'
        required: true
      deploy_log_id:
        description: 'Deploy Log ID'
        required: false

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout repository
        uses: actions/checkout@v4
        with:
          ref: \${{ github.event.inputs.ref || github.ref }}

      - name: Setup SSH
        uses: webfactory/ssh-agent@v0.9.0
        with:
          ssh-private-key: \${{ secrets.SSH_PRIVATE_KEY }}

      - name: Add known hosts
        run: |
          mkdir -p ~/.ssh
          ssh-keyscan -p \${{ secrets.SSH_PORT || 22 }} \\
            \${{ secrets.SSH_HOST }} >> ~/.ssh/known_hosts

      - name: Deploy via rsync
        run: |
          rsync -avz --delete \\
            --exclude='.git' \\
            --exclude='.gitignore' \\
            --exclude='node_modules' \\
            --exclude='.env' \\
            --exclude='.env.*' \\
            ./ \\
            -e "ssh -p \${{ secrets.SSH_PORT || 22 }}" \\
            \${{ secrets.SSH_USER }}@\${{ secrets.SSH_HOST }}:\${{ secrets.SERVER_DIR || '/public_html/' }}
`
    },
    {
        id: 'ssh',
        name: 'rsyncデプロイ（カスタム）',
        description: 'rsyncを使用してファイルを同期します。カスタム除外設定や追加コマンドが必要な場合に使用します。',
        secrets: [
            { name: 'SSH_HOST', description: 'SSHサーバーのアドレス（例: example.com または 123.45.67.89）' },
            { name: 'SSH_USER', description: 'SSHユーザー名（例: root または ubuntu）' },
            { name: 'SSH_PORT', description: 'SSHポート番号（オプション、デフォルト: 22）' },
            { name: 'SSH_PRIVATE_KEY', description: 'SSH秘密鍵（-----BEGIN RSA PRIVATE KEY----- から始まる内容全体）' },
            { name: 'SERVER_DIR', description: 'デプロイ先ディレクトリパス（オプション、デフォルト: /public_html/）' }
        ],
        secretsExample: `SSH_HOST: example.com
SSH_USER: root
SSH_PORT: 22
SSH_PRIVATE_KEY: |
  -----BEGIN RSA PRIVATE KEY-----
  MIIEpAIBAAKCAQEA...
  (秘密鍵の内容をそのまま貼り付け)
  -----END RSA PRIVATE KEY-----
SERVER_DIR: /public_html/

※ SSH秘密鍵は以下のコマンドで確認できます:
  cat ~/.ssh/id_rsa

※ 公開鍵（id_rsa.pub）をサーバー側の
  ~/.ssh/authorized_keys に追加してください

※ SSH_PORTとSERVER_DIRは省略可能です（デフォルト: SSH_PORT=22, SERVER_DIR=/public_html/）`,
        content: `name: Deploy to Production

on:
  workflow_dispatch:
    inputs:
      project_id:
        description: 'Project ID'
        required: true
      deploy_log_id:
        description: 'Deploy Log ID'
        required: false

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout repository
        uses: actions/checkout@v4
        with:
          ref: \${{ github.event.inputs.ref || github.ref }}

      - name: Setup SSH
        uses: webfactory/ssh-agent@v0.9.0
        with:
          ssh-private-key: \${{ secrets.SSH_PRIVATE_KEY }}

      - name: Add known hosts
        run: |
          mkdir -p ~/.ssh
          ssh-keyscan -p \${{ secrets.SSH_PORT || 22 }} \\
            \${{ secrets.SSH_HOST }} >> ~/.ssh/known_hosts

      - name: Deploy via rsync
        run: |
          rsync -avz --delete \\
            --exclude='.git' \\
            --exclude='.gitignore' \\
            --exclude='node_modules' \\
            --exclude='.env' \\
            --exclude='.env.*' \\
            ./ \\
            -e "ssh -p \${{ secrets.SSH_PORT || 22 }}" \\
            \${{ secrets.SSH_USER }}@\${{ secrets.SSH_HOST }}:\${{ secrets.SERVER_DIR || '/public_html/' }}
`
    },
    {
        id: 'rsync',
        name: 'rsyncデプロイ（標準）',
        description: 'rsyncを使用して高速にファイルを同期します。変更されたファイルのみを転送するため、デプロイ時間を大幅に短縮できます。',
        secrets: [
            { name: 'SSH_HOST', description: 'SSHサーバーのアドレス（例: example.com または 123.45.67.89）' },
            { name: 'SSH_USER', description: 'SSHユーザー名（例: root または ubuntu）' },
            { name: 'SSH_PORT', description: 'SSHポート番号（オプション、デフォルト: 22）' },
            { name: 'SSH_PRIVATE_KEY', description: 'SSH秘密鍵（-----BEGIN RSA PRIVATE KEY----- から始まる内容全体）' },
            { name: 'SERVER_DIR', description: 'デプロイ先ディレクトリパス（オプション、デフォルト: /public_html/）' }
        ],
        secretsExample: `SSH_HOST: example.com
SSH_USER: root
SSH_PORT: 22
SSH_PRIVATE_KEY: |
  -----BEGIN RSA PRIVATE KEY-----
  MIIEpAIBAAKCAQEA...
  (秘密鍵の内容をそのまま貼り付け)
  -----END RSA PRIVATE KEY-----
SERVER_DIR: /public_html/

※ SSH秘密鍵は以下のコマンドで確認できます:
  cat ~/.ssh/id_rsa

※ 公開鍵（id_rsa.pub）をサーバー側の
  ~/.ssh/authorized_keys に追加してください

※ SSH_PORTとSERVER_DIRは省略可能です（デフォルト: SSH_PORT=22, SERVER_DIR=/public_html/）`,
        content: `name: Deploy to Production

on:
  workflow_dispatch:
    inputs:
      project_id:
        description: 'Project ID'
        required: true
      deploy_log_id:
        description: 'Deploy Log ID'
        required: false

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout repository
        uses: actions/checkout@v4
        with:
          ref: \${{ github.event.inputs.ref || github.ref }}

      - name: Setup SSH
        uses: webfactory/ssh-agent@v0.9.0
        with:
          ssh-private-key: \${{ secrets.SSH_PRIVATE_KEY }}

      - name: Add known hosts
        run: |
          mkdir -p ~/.ssh
          ssh-keyscan -p \${{ secrets.SSH_PORT || 22 }} \\
            \${{ secrets.SSH_HOST }} >> ~/.ssh/known_hosts

      - name: Deploy via rsync
        run: |
          rsync -avz --delete \\
            --exclude='.git' \\
            --exclude='.gitignore' \\
            --exclude='node_modules' \\
            --exclude='.env' \\
            --exclude='.env.*' \\
            ./ \\
            -e "ssh -p \${{ secrets.SSH_PORT || 22 }}" \\
            \${{ secrets.SSH_USER }}@\${{ secrets.SSH_HOST }}:\${{ secrets.SERVER_DIR || '/public_html/' }}
`
    },
    {
        id: 'static',
        name: '静的サイト（ビルド + rsync）',
        description: 'HTML/CSS/JSなどの静的サイトをビルドしてrsyncでデプロイします。変更されたファイルのみを転送するため高速です。',
        secrets: [
            { name: 'SSH_HOST', description: 'SSHサーバーのアドレス（例: example.com または 123.45.67.89）' },
            { name: 'SSH_USER', description: 'SSHユーザー名（例: root または ubuntu）' },
            { name: 'SSH_PORT', description: 'SSHポート番号（オプション、デフォルト: 22）' },
            { name: 'SSH_PRIVATE_KEY', description: 'SSH秘密鍵（-----BEGIN RSA PRIVATE KEY----- から始まる内容全体）' },
            { name: 'SERVER_DIR', description: 'デプロイ先ディレクトリパス（オプション、デフォルト: /public_html/）' }
        ],
        secretsExample: `SSH_HOST: example.com
SSH_USER: root
SSH_PORT: 22
SSH_PRIVATE_KEY: |
  -----BEGIN RSA PRIVATE KEY-----
  MIIEpAIBAAKCAQEA...
  (秘密鍵の内容をそのまま貼り付け)
  -----END RSA PRIVATE KEY-----
SERVER_DIR: /public_html/

※ SSH秘密鍵は以下のコマンドで確認できます:
  cat ~/.ssh/id_rsa

※ 公開鍵（id_rsa.pub）をサーバー側の
  ~/.ssh/authorized_keys に追加してください

※ SSH_PORTとSERVER_DIRは省略可能です（デフォルト: SSH_PORT=22, SERVER_DIR=/public_html/）`,
        content: `name: Deploy to Production

on:
  workflow_dispatch:
    inputs:
      project_id:
        description: 'Project ID'
        required: true
      deploy_log_id:
        description: 'Deploy Log ID'
        required: false

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout repository
        uses: actions/checkout@v4
        with:
          ref: \${{ github.event.inputs.ref || github.ref }}

      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '18'
      
      - name: Install dependencies
        run: npm install
      
      - name: Build
        run: npm run build

      - name: Setup SSH
        uses: webfactory/ssh-agent@v0.9.0
        with:
          ssh-private-key: \${{ secrets.SSH_PRIVATE_KEY }}

      - name: Add known hosts
        run: |
          mkdir -p ~/.ssh
          ssh-keyscan -p \${{ secrets.SSH_PORT || 22 }} \\
            \${{ secrets.SSH_HOST }} >> ~/.ssh/known_hosts

      - name: Deploy via rsync
        run: |
          rsync -avz --delete \\
            --exclude='.git' \\
            --exclude='.gitignore' \\
            --exclude='node_modules' \\
            --exclude='.env' \\
            --exclude='.env.*' \\
            ./ \\
            -e "ssh -p \${{ secrets.SSH_PORT || 22 }}" \\
            \${{ secrets.SSH_USER }}@\${{ secrets.SSH_HOST }}:\${{ secrets.SERVER_DIR || '/public_html/' }}
`
    }
];

const getCurrentTemplate = () => {
    const template = workflowTemplates.find(t => t.id === selectedTemplate.value);
    return template ? template.content : '';
};

const getCurrentTemplateDescription = () => {
    const template = workflowTemplates.find(t => t.id === selectedTemplate.value);
    return template ? template.description : '';
};

const getCurrentTemplateSecrets = () => {
    const template = workflowTemplates.find(t => t.id === selectedTemplate.value);
    return template && template.secrets ? template.secrets : [];
};

const getCurrentTemplateSecretsExample = () => {
    const template = workflowTemplates.find(t => t.id === selectedTemplate.value);
    return template && template.secretsExample ? template.secretsExample : '';
};

const copyTemplate = async () => {
    const template = getCurrentTemplate();
    try {
        await navigator.clipboard.writeText(template);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy:', err);
    }
};
</script>
