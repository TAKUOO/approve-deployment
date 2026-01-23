<template>
    <Head title="プロジェクト作成 - AutoRelease" />
    
    <AuthenticatedLayout>
        <div class="py-12 min-h-screen bg-indigo-50">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-xl">
                    <div class="p-8 text-gray-900">
                        <!-- 一覧に戻るボタン -->
                        <div v-if="hasProjects" class="mb-4">
                            <Link :href="route('projects.index')" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                                <svg class="mr-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                一覧に戻る
                            </Link>
                        </div>
                        
                        <!-- タイトルと説明 -->
                        <div class="mb-8">
                            <div class="flex gap-3 items-center mb-2">
                                <h1 class="text-2xl font-semibold text-gray-900">プロジェクトを作成する</h1>
                            </div>
                            <p class="text-gray-600">
                                クライアントが確認するプロジェクトを登録しましょう。プロジェクト作成後、詳細画面で改善内容を入力して承認URLを生成し、クライアントに共有できます。クライアントが承認すると、自動的に本番環境にデプロイされます。
                            </p>
                        </div>
                        
                        <form @submit.prevent="submit">
                            <div class="space-y-6">



                                <div>
                                    <div class="flex gap-2 items-center">
                                        <InputLabel for="organization" value="GitHub 組織" />
                                        <div class="relative">
                                            <button
                                                type="button"
                                                @click.stop="toggleTooltip('organization', $event)"
                                                class="text-gray-400 transition-colors hover:text-gray-600"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                            <div
                                                v-if="activeTooltip === 'organization'"
                                                class="absolute left-0 z-10 p-3 mt-2 w-64 text-sm text-gray-700 bg-white rounded-lg border border-gray-200 shadow-lg"
                                            >
                                                リポジトリを取得する組織を選択してください。
                                            </div>
                                        </div>
                                    </div>
                                    <select
                                        id="organization"
                                        v-model="selectedOrganization"
                                        @change="onOrganizationChange"
                                        class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="" disabled>組織を選択してください</option>
                                        <option value="personal">個人リポジトリ</option>
                                        <option v-for="org in organizations" :key="org.id" :value="org.login">
                                            {{ org.login }}
                                        </option>
                                    </select>
                                    <p v-if="organizations.length === 0" class="mt-1 text-sm text-yellow-600">
                                        ※ 組織が見つかりませんでした。組織に所属している場合、GitHubでアプリの権限を確認してください。
                                    </p>
                                    <InputError class="mt-2" :message="form.errors.github_owner" />
                                </div>

                                <div>
                                    <div class="flex gap-2 items-center">
                                        <InputLabel for="repository" value="GitHub リポジトリ" />
                                        <div class="relative">
                                            <button
                                                type="button"
                                                @click.stop="toggleTooltip('repository', $event)"
                                                class="text-gray-400 transition-colors hover:text-gray-600"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                            <div
                                                v-if="activeTooltip === 'repository'"
                                                class="absolute left-0 z-10 p-3 mt-2 w-64 text-sm text-gray-700 bg-white rounded-lg border border-gray-200 shadow-lg"
                                            >
                                                デプロイ対象のGitHubリポジトリを選択してください。
                                            </div>
                                        </div>
                                    </div>
                                    <select
                                        id="repository"
                                        v-model="selectedRepository"
                                        @change="onRepositoryChange"
                                        class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :disabled="!selectedOrganization || repositories.length === 0"
                                        required
                                    >
                                        <option :value="null" disabled>
                                            {{ !selectedOrganization ? 'まず組織を選択してください' : repositories.length === 0 ? 'リポジトリを読み込み中...' : 'リポジトリを選択してください' }}
                                        </option>
                                        <option v-for="repo in repositories" :key="repo.id" :value="repo">
                                            {{ repo.full_name }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.github_repo" />
                                </div>

                                <div v-if="selectedRepository">
                                    <div class="flex gap-2 items-center">
                                        <InputLabel for="github_workflow_id" value="GitHub ワークフロー" />
                                        <div class="relative">
                                            <button
                                                type="button"
                                                @click.stop="toggleTooltip('workflow', $event)"
                                                class="text-gray-400 transition-colors hover:text-gray-600"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                            <div
                                                v-if="activeTooltip === 'workflow'"
                                                class="absolute left-0 z-10 p-3 mt-2 w-80 text-sm text-gray-700 bg-white rounded-lg border border-gray-200 shadow-lg"
                                            >
                                                承認時に実行されるGitHub Actionsのワークフローを選択してください。リポジトリ内の <code class="px-1 py-0.5 text-xs bg-gray-100 rounded">.github/workflows/</code> ディレクトリに配置されているワークフローが表示されます。
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- ワークフローが見つかった場合の選択 -->
                                    <select
                                        v-if="!loadingWorkflows && workflows.length > 0"
                                        id="github_workflow_id"
                                        v-model="form.github_workflow_id"
                                        class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">後で設定する</option>
                                        <option v-for="workflow in workflows" :key="workflow.id" :value="String(workflow.id)">
                                            {{ workflow.name }} ({{ workflow.path }})
                                        </option>
                                    </select>
                                    <div v-else-if="!loadingWorkflows && workflows.length === 0" class="mt-1 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                        <p class="text-sm text-gray-600">
                                            ワークフローが見つかりませんでした。後で設定できます。
                                            <Link :href="route('settings.index')" target="_blank" class="font-medium text-indigo-600 underline hover:text-indigo-800">
                                                設定マニュアル
                                            </Link>
                                            から設定方法を確認できます。
                                        </p>
                                    </div>
                                    <div v-else class="mt-1 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                        <p class="text-sm text-gray-600">ワークフローを読み込み中...</p>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.github_workflow_id" />
                                    <p class="mt-2 text-sm text-gray-500">
                                        SSH接続の設定が必要です。
                                        <Link :href="route('settings.index')" target="_blank" class="font-medium text-indigo-600 underline transition hover:text-indigo-800">
                                            設定マニュアル
                                        </Link>
                                        から設定方法を確認できます。
                                    </p>
                                </div>

                                <div v-if="selectedRepository">
                                    <div class="flex gap-2 items-center">
                                        <InputLabel for="github_branch" value="GitHub ブランチ" />
                                        <div class="relative">
                                            <button
                                                type="button"
                                                @click.stop="toggleTooltip('branch', $event)"
                                                class="text-gray-400 transition-colors hover:text-gray-600"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                            <div
                                                v-if="activeTooltip === 'branch'"
                                                class="absolute left-0 z-10 p-3 mt-2 w-80 text-sm text-gray-700 bg-white rounded-lg border border-gray-200 shadow-lg"
                                            >
                                                本番環境にアップロードするブランチ名を入力してください。通常は <code class="px-1 py-0.5 text-xs bg-gray-100 rounded">main</code> を使用します。
                                            </div>
                                        </div>
                                    </div>
                                    <div class="relative">
                                        <input
                                            type="text"
                                            id="github_branch"
                                            v-model="form.github_branch"
                                            list="branch-list"
                                            class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="main"
                                        />
                                        <!-- ブランチ候補のdatalist（APIから取得できた場合のみ表示） -->
                                        <datalist id="branch-list">
                                            <option v-for="branch in branches" :key="branch.name" :value="branch.name">
                                                {{ branch.name }}
                                            </option>
                                        </datalist>
                                        <p class="mt-1 text-xs text-gray-500">
                                            本番環境にアップするブランチ名（通常は main）
                                        </p>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.github_branch" />
                                </div>

                                <div class="flex justify-end items-center">
                                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                        プロジェクトを作成
                                    </PrimaryButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- フッター -->
                <div class="mt-8">
                    <AppFooter />
                </div>
            </div>
        </div>

        <!-- 重要な推奨事項モーダル -->
        <Modal :show="showSecurityModal" @close="showSecurityModal = false" max-width="lg">
            <div class="p-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="flex-1 ml-4">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">重要な推奨事項</h3>
                        <div class="space-y-4">
                            <div>
                                <p class="mb-2 text-sm font-medium text-gray-900">
                                    テスト環境（ステージング環境）には必ずパスワード保護を設定してください。
                                </p>
                                <p class="text-sm text-gray-600">
                                    承認URLはテスト環境のURLを表示しますが、テスト環境自体が公開されていると、承認URLが漏洩した場合にセキュリティリスクが発生する可能性があります。
                                </p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="mb-3 text-sm font-semibold text-gray-900">推奨される設定方法：</p>
                                <ul class="space-y-2 text-sm text-gray-700">
                                    <li>• <strong>レンタルサーバー</strong>: サーバーパネルの「アクセス制御」機能、または.htaccessファイルでBasic認証を設定</li>
                                    <li>• <strong>クラウドサービス（AWS、GCP、Azure等）</strong>: 各サービスのアクセス制御機能を利用</li>
                                    <li>• <strong>VPS・専用サーバー</strong>: NginxやApacheの設定でBasic認証を設定</li>
                                    <li>• <strong>その他</strong>: テスト環境へのアクセスを制限できる方法であれば、どの方法でも構いません</li>
                                </ul>
                            </div>
                        </div>
                        <div class="flex justify-end mt-6">
                            <button
                                @click="showSecurityModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
                            >
                                閉じる
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Modal>
        <SshSetupModal :show="showSshModal" @close="showSshModal = false" />
    </AuthenticatedLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppFooter from '@/Components/AppFooter.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import SshSetupModal from '@/Components/SshSetupModal.vue';
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    hasProjects: {
        type: Boolean,
        default: false,
    },
});

const form = useForm({
    name: '',
    server_dir: '/public_html/',
    github_owner: '',
    github_repo: '',
    github_workflow_id: '',
    github_branch: '',
    ssh_configured: false,
});

const organizations = ref([]);
const repositories = ref([]);
const workflows = ref([]);
const branches = ref([]);
const showSshModal = ref(false);
const selectedOrganization = ref('');
const selectedRepository = ref(null);
const selectedTemplate = ref('ftp');
const copied = ref(false);
const loadingWorkflows = ref(false);
const activeTooltip = ref(null);
const showSecurityModal = ref(false);

const toggleTooltip = (tooltipName, event) => {
    if (event) {
        event.stopPropagation();
    }
    if (activeTooltip.value === tooltipName) {
        activeTooltip.value = null;
    } else {
        activeTooltip.value = tooltipName;
    }
};

const closeTooltip = () => {
    activeTooltip.value = null;
};

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
            { name: 'SSH_PRIVATE_KEY', description: 'SSH秘密鍵（-----BEGIN RSA PRIVATE KEY----- から始まる内容全体）' }
        ],
        secretsExample: `SSH_HOST: example.com
SSH_USER: root
SSH_PORT: 22
SSH_PRIVATE_KEY: |
  -----BEGIN RSA PRIVATE KEY-----
  MIIEpAIBAAKCAQEA...
  (秘密鍵の内容をそのまま貼り付け)
  -----END RSA PRIVATE KEY-----

※ SSH秘密鍵は以下のコマンドで確認できます:
  cat ~/.ssh/id_rsa

※ 公開鍵（id_rsa.pub）をサーバー側の
  ~/.ssh/authorized_keys に追加してください

※ SSH_PORTは省略可能です（デフォルト: 22）`,
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

onMounted(async () => {
    try {
        // 組織一覧を取得
        const orgsResponse = await axios.get(route('api.github.organizations'));
        console.log('Organizations response:', orgsResponse.data);
        
        if (orgsResponse.data && Array.isArray(orgsResponse.data)) {
            organizations.value = orgsResponse.data;
            console.log('Organizations loaded:', organizations.value.length);
        } else if (orgsResponse.data && orgsResponse.data.error) {
            console.error('API Error:', orgsResponse.data.error);
            console.error('Error details:', orgsResponse.data.details);
            organizations.value = [];
        } else {
            console.error('Invalid organizations response:', orgsResponse.data);
            organizations.value = [];
        }
    } catch (error) {
        console.error('Failed to fetch organizations:', error);
        if (error.response) {
            console.error('Error response:', error.response.data);
            console.error('Error status:', error.response.status);
        }
        organizations.value = [];
    }

    // 外側をクリックしたときにツールチップを閉じる
    document.addEventListener('click', closeTooltip);
});

onUnmounted(() => {
    document.removeEventListener('click', closeTooltip);
});

const onOrganizationChange = async () => {
    if (!selectedOrganization.value) {
        selectedRepository.value = null;
        repositories.value = [];
        workflows.value = [];
        branches.value = [];
        form.github_owner = '';
        form.github_repo = '';
        form.github_workflow_id = '';
        form.github_branch = '';
        return;
    }

    // リセット
    selectedRepository.value = null;
    repositories.value = [];
    workflows.value = [];
    branches.value = [];
    form.github_owner = '';
    form.github_repo = '';
    form.github_workflow_id = '';
    form.github_branch = '';

    try {
        // 選択した組織のリポジトリを取得
        const response = await axios.get(route('api.github.repositories'), {
            params: {
                organization: selectedOrganization.value
            }
        });
        
        if (response.data && Array.isArray(response.data)) {
        repositories.value = response.data;
        } else if (response.data && response.data.error) {
            console.error('API Error:', response.data.error);
            alert(`リポジトリの取得に失敗しました: ${response.data.error}\n詳細: ${response.data.details || '不明なエラー'}`);
            repositories.value = [];
        } else {
            console.error('Invalid response format:', response.data);
            repositories.value = [];
        }
    } catch (error) {
        console.error('Failed to fetch repositories:', error);
        if (error.response) {
            const errorData = error.response.data;
            alert(`リポジトリの取得に失敗しました: ${errorData.error || 'エラーが発生しました'}\n詳細: ${errorData.details || error.message}`);
        } else {
            alert('リポジトリの取得に失敗しました。ネットワークエラーまたはサーバーエラーの可能性があります。');
        }
        repositories.value = [];
    }
};

const onRepositoryChange = async () => {
    if (!selectedRepository.value) return;

    form.github_owner = selectedRepository.value.owner.login;
    form.github_repo = selectedRepository.value.name;
    // レポジトリ名を自動的にプロジェクト名として設定
    form.name = `${selectedRepository.value.owner.login}/${selectedRepository.value.name}`;
    
    // Reset dependent fields
    form.github_workflow_id = '';
    form.github_branch = '';
    workflows.value = [];
    branches.value = [];
    loadingWorkflows.value = true;

    try {
        const [workflowsResponse, branchesResponse] = await Promise.all([
            axios.get(route('api.github.workflows'), {
                params: {
                    owner: form.github_owner,
                    repo: form.github_repo
                }
            }),
            axios.get(route('api.github.branches'), {
                params: {
                    owner: form.github_owner,
                    repo: form.github_repo
                }
            })
        ]);

        // GitHub APIのレスポンス構造: { total_count: X, workflows: [...] }
        workflows.value = workflowsResponse.data.workflows || [];
        
        // ブランチを取得（すべてのブランチがソートされて返される）
        branches.value = branchesResponse.data.branches || [];
        const defaultBranch = branchesResponse.data.default_branch;
        
        // ワークフローが取得できた場合、最初のワークフローを自動選択
        if (workflows.value && workflows.value.length > 0) {
            form.github_workflow_id = String(workflows.value[0].id);
        }
        
        // ブランチの自動選択（優先順位: main -> master -> default_branch -> 最初のブランチ）
        if (branches.value && branches.value.length > 0) {
            const mainBranch = branches.value.find(b => b.name === 'main');
            const masterBranch = branches.value.find(b => b.name === 'master');
            const defaultBranchObj = branches.value.find(b => b.name === defaultBranch);
            
            if (mainBranch) {
                form.github_branch = mainBranch.name;
            } else if (masterBranch) {
                form.github_branch = masterBranch.name;
            } else if (defaultBranchObj) {
                form.github_branch = defaultBranchObj.name;
            } else {
                form.github_branch = branches.value[0].name;
            }
        } else if (branchesResponse.data && branchesResponse.data.error) {
            alert(`ブランチの取得に失敗しました: ${branchesResponse.data.error}`);
        }
    } catch (error) {
        console.error('Failed to fetch repository details:', error);
    } finally {
        loadingWorkflows.value = false;
    }
};

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

const submit = () => {
    // GitHub組織とリポジトリの必須チェック
    if (!selectedOrganization.value) {
        alert('GitHub組織を選択してください。');
        return;
    }
    
    if (!selectedRepository.value) {
        alert('GitHubリポジトリを選択してください。');
        return;
    }
    
    form.post(route('projects.store'));
};
</script>
