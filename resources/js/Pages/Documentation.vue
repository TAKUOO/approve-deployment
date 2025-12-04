<template>
    <Head title="ドキュメント - AutoRelease" />

    <div class="overflow-hidden relative min-h-screen bg-gradient-to-br from-slate-100 via-slate-200 to-indigo-200/50 text-slate-900">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-10 -left-16 w-72 h-72 rounded-full blur-3xl bg-indigo-200/40"></div>
            <div class="absolute bottom-0 right-0 h-[26rem] w-[26rem] rounded-full bg-sky-200/40 blur-[120px]"></div>
        </div>

        <div class="flex relative z-10 flex-col min-h-screen">
            <header class="flex flex-wrap gap-6 justify-between items-center mx-auto mt-6 w-full max-w-6xl">
                <div class="flex gap-4 items-center">
                    <Link href="/">
                        <img src="/images/logo.png" alt="AutoRelease" class="object-contain h-10" />
                    </Link>
                </div>

                <nav class="flex flex-wrap gap-6 items-center font-semibold text-md text-slate-600">
                    <Link href="/" class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex">ホーム</Link>
                    <Link href="/#features" class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex">機能</Link>
                    <Link href="/#workflow" class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex">ワークフロー</Link>
                    <Link href="/#faq" class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex">FAQ</Link>

                    <Link
                        v-if="authUser"
                        href="/projects"
                        class="px-5 py-2 rounded-full border transition border-slate-200 text-slate-700 hover:border-slate-400 hover:text-slate-900"
                    >
                        プロジェクト一覧へ
                    </Link>

                    <template v-else>
                        <Link
                            href="/auth/github"
                            class="px-6 py-2 font-semibold text-white bg-indigo-600 rounded-full shadow-lg transition shadow-indigo-200 hover:opacity-90"
                        >
                            ログイン
                        </Link>
                    </template>
                </nav>
            </header>

            <main class="flex-1">
                <div class="px-6 py-12 mx-auto max-w-4xl">
                    <div class="mb-12">
                        <h1 class="mb-4 text-4xl font-bold text-slate-900">ドキュメント</h1>
                        <p class="text-lg text-slate-600">AutoReleaseの設定方法と使い方を詳しく説明します。</p>
                    </div>

                    <div class="space-y-12">
                        <!-- 目次 -->
                        <div class="p-6 bg-white rounded-2xl border shadow-sm border-slate-200">
                            <h2 class="mb-4 text-2xl font-semibold text-slate-900">目次</h2>
                            <nav class="space-y-2">
                                <a href="#getting-started" class="block text-indigo-600 hover:text-indigo-800">1. はじめに</a>
                                <a href="#project-setup" class="block text-indigo-600 hover:text-indigo-800">2. プロジェクトの作成</a>
                                <a href="#github-secrets" class="block text-indigo-600 hover:text-indigo-800">3. GitHub Secretsの設定</a>
                                <a href="#workflow-setup" class="block text-indigo-600 hover:text-indigo-800">4. ワークフローファイルの設定</a>
                                <a href="#deploy-path" class="block text-indigo-600 hover:text-indigo-800">5. デプロイ先パスの設定</a>
                                <a href="#webhook" class="block text-indigo-600 hover:text-indigo-800">6. Webhookの設定</a>
                                <a href="#troubleshooting" class="block text-indigo-600 hover:text-indigo-800">7. トラブルシューティング</a>
                            </nav>
                        </div>

                        <!-- 1. はじめに -->
                        <section id="getting-started" class="p-8 bg-white rounded-2xl border shadow-sm border-slate-200">
                            <h2 class="mb-4 text-2xl font-semibold text-slate-900">1. はじめに</h2>
                            <div class="max-w-none prose prose-slate">
                                <p class="mb-4 text-slate-600">
                                    AutoReleaseを使用するには、以下の準備が必要です：
                                </p>
                                <ul class="mb-4 space-y-2 list-disc list-inside text-slate-600">
                                    <li>GitHubアカウント</li>
                                    <li>GitHub Actionsが使用できるリポジトリ</li>
                                    <li>テスト環境（ステージング環境）と本番環境のFTPサーバー情報</li>
                                </ul>
                                <p class="text-slate-600">
                                    まずはGitHubアカウントでログインし、プロジェクトを作成しましょう。
                                </p>
                            </div>
                        </section>

                        <!-- 2. プロジェクトの作成 -->
                        <section id="project-setup" class="p-8 bg-white rounded-2xl border shadow-sm border-slate-200">
                            <h2 class="mb-4 text-2xl font-semibold text-slate-900">2. プロジェクトの作成</h2>
                            <div class="max-w-none prose prose-slate">
                                <h3 class="mb-3 text-xl font-semibold text-slate-900">2.1 基本情報の入力</h3>
                                <p class="mb-4 text-slate-600">
                                    プロジェクト作成画面で、以下の情報を入力します：
                                </p>
                                <ul class="mb-4 space-y-2 list-disc list-inside text-slate-600">
                                    <li><strong>プロジェクト名</strong>: 任意のプロジェクト名</li>
                                    <li><strong>テストURL</strong>: クライアントが確認するテスト環境のURL（例: https://staging.example.com）</li>
                                    <li><strong>本番URL</strong>: 実際に公開されている本番環境のURL（例: https://example.com）</li>
                                    <li><strong>デプロイ先パス</strong>: FTPサーバー上のデプロイ先ディレクトリパス（例: /public_html/ または /rubydesign.jp/public_html/wp-content/themes/rubydesign2020/）</li>
                                </ul>

                                <h3 class="mt-6 mb-3 text-xl font-semibold text-slate-900">2.2 GitHub情報の設定</h3>
                                <p class="mb-4 text-slate-600">
                                    GitHub組織、リポジトリ、ワークフロー、ブランチを選択します：
                                </p>
                                <ul class="mb-4 space-y-2 list-disc list-inside text-slate-600">
                                    <li><strong>GitHub組織</strong>: リポジトリが所属する組織を選択</li>
                                    <li><strong>GitHubリポジトリ</strong>: デプロイ対象のリポジトリを選択</li>
                                    <li><strong>GitHubワークフロー</strong>: デプロイに使用するワークフローを選択（自動的に最初のワークフローが選択されます）</li>
                                    <li><strong>GitHubブランチ</strong>: デプロイ元のブランチを選択（main または master）</li>
                                </ul>
                            </div>
                        </section>

                        <!-- 3. GitHub Secretsの設定 -->
                        <section id="github-secrets" class="p-8 bg-white rounded-2xl border shadow-sm border-slate-200">
                            <h2 class="mb-4 text-2xl font-semibold text-slate-900">3. GitHub Secretsの設定</h2>
                            <div class="max-w-none prose prose-slate">
                                <p class="mb-4 text-slate-600">
                                    GitHubリポジトリのSettings → Secrets and variables → Actionsで、以下のSecretsを設定します：
                                </p>

                                <h3 class="mb-3 text-xl font-semibold text-slate-900">3.1 FTPデプロイの場合</h3>
                                <div class="p-4 mb-4 rounded-lg bg-slate-50">
                                    <ul class="space-y-2 list-disc list-inside text-slate-700">
                                        <li><strong>FTP_SERVER</strong>: FTPサーバーのアドレス（例: sv10201.xserver.jp）</li>
                                        <li><strong>FTP_USERNAME</strong>: FTPユーザー名（例: example.com）</li>
                                        <li><strong>FTP_PASSWORD</strong>: FTPパスワード</li>
                                    </ul>
                                </div>

                                <h4 class="mb-2 text-lg font-semibold text-slate-900">設定手順</h4>
                                <ol class="mb-4 space-y-2 list-decimal list-inside text-slate-600">
                                    <li>GitHubリポジトリの「Settings」タブを開く</li>
                                    <li>左メニューの「Secrets and variables」→「Actions」をクリック</li>
                                    <li>「New repository secret」ボタンをクリック</li>
                                    <li>Nameに「FTP_SERVER」、SecretにFTPサーバーアドレスを入力して「Add secret」をクリック</li>
                                    <li>同様に「FTP_USERNAME」と「FTP_PASSWORD」も追加（合計3つのSecrets）</li>
                                </ol>

                                <div class="p-4 mb-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                    <p class="text-sm text-yellow-800">
                                        <strong>注意:</strong> Secrets名は大文字小文字を含めて正確に入力してください（FTP_SERVER、FTP_USERNAME、FTP_PASSWORD）。各Secretは個別に追加する必要があります。
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- 4. ワークフローファイルの設定 -->
                        <section id="workflow-setup" class="p-8 bg-white rounded-2xl border shadow-sm border-slate-200">
                            <h2 class="mb-4 text-2xl font-semibold text-slate-900">4. ワークフローファイルの設定</h2>
                            <div class="max-w-none prose prose-slate">
                                <p class="mb-4 text-slate-600">
                                    GitHubリポジトリの<code class="px-2 py-1 rounded bg-slate-100">.github/workflows/deploy.yml</code>に、以下のワークフローファイルを配置します：
                                </p>

                                <div class="overflow-x-auto p-4 mb-4 rounded-lg bg-slate-900" v-pre>
                                    <pre class="text-sm text-slate-100"><code>name: Deploy to Production

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
      - uses: actions/checkout@v3
      
      - name: Deploy via FTP
        uses: SamKirkland/FTP-Deploy-Action@4.3.0
        with:
          server: ${{ secrets.FTP_SERVER }}
          username: ${{ secrets.FTP_USERNAME }}
          password: ${{ secrets.FTP_PASSWORD }}
          local-dir: ./
          server-dir: ${{ inputs.server_dir || '/public_html/' }}</code></pre>
                                </div>

                                <p class="mb-4 text-slate-600">
                                    このワークフローファイルをリポジトリにコミット・プッシュしてください。
                                </p>
                            </div>
                        </section>

                        <!-- 5. デプロイ先パスの設定 -->
                        <section id="deploy-path" class="p-8 bg-white rounded-2xl border shadow-sm border-slate-200">
                            <h2 class="mb-4 text-2xl font-semibold text-slate-900">5. デプロイ先パスの設定</h2>
                            <div class="max-w-none prose prose-slate">
                                <p class="mb-4 text-slate-600">
                                    プロジェクト作成・編集画面で「デプロイ先パス」を設定します。パスは必ず末尾に<code class="px-2 py-1 rounded bg-slate-100">/</code>を付けてください。
                                </p>

                                <h3 class="mb-3 text-xl font-semibold text-slate-900">5.1 パスの確認方法</h3>
                                <p class="mb-4 text-slate-600">
                                    FTPクライアントまたはファイルマネージャーで、FTPサーバーのルートディレクトリを確認します：
                                </p>
                                <ul class="mb-4 space-y-2 list-disc list-inside text-slate-600">
                                    <li>FTPサーバーのルートが<code class="px-2 py-1 rounded bg-slate-100">/</code>から始まる場合: <code class="px-2 py-1 rounded bg-slate-100">/rubydesign.jp/public_html/wp-content/themes/rubydesign2020/</code></li>
                                    <li>FTPサーバーのルートが<code class="px-2 py-1 rounded bg-slate-100">/rubydesign.jp/</code>から始まる場合: <code class="px-2 py-1 rounded bg-slate-100">/public_html/wp-content/themes/rubydesign2020/</code></li>
                                </ul>

                                <h3 class="mb-3 text-xl font-semibold text-slate-900">5.2 よくあるパス例</h3>
                                <div class="p-4 mb-4 rounded-lg bg-slate-50">
                                    <ul class="space-y-2 list-disc list-inside text-slate-700">
                                        <li>通常のサイト: <code class="px-2 py-1 bg-white rounded">/public_html/</code></li>
                                        <li>WordPressテーマ: <code class="px-2 py-1 bg-white rounded">/public_html/wp-content/themes/テーマ名/</code></li>
                                        <li>Xserverの場合: <code class="px-2 py-1 bg-white rounded">/rubydesign.jp/public_html/wp-content/themes/rubydesign2020/</code></li>
                                    </ul>
                                </div>

                                <div class="p-4 mb-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                    <p class="text-sm text-yellow-800">
                                        <strong>重要:</strong> パスの末尾には必ず<code class="px-1 py-0.5 bg-yellow-100 rounded">/</code>を付けてください。例: <code class="px-1 py-0.5 bg-yellow-100 rounded">/public_html/</code>（正）、<code class="px-1 py-0.5 bg-yellow-100 rounded">/public_html</code>（誤）
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- 6. Webhookの設定 -->
                        <section id="webhook" class="p-8 bg-white rounded-2xl border shadow-sm border-slate-200">
                            <h2 class="mb-4 text-2xl font-semibold text-slate-900">6. Webhookの設定</h2>
                            <div class="max-w-none prose prose-slate">
                                <p class="mb-4 text-slate-600">
                                    GitHub Actionsの実行状況を自動的に更新するために、Webhookを設定します：
                                </p>

                                <h3 class="mb-3 text-xl font-semibold text-slate-900">設定手順</h3>
                                <ol class="mb-4 space-y-2 list-decimal list-inside text-slate-600">
                                    <li>GitHubリポジトリの「Settings」タブを開く</li>
                                    <li>左メニューの「Webhooks」をクリック</li>
                                    <li>「Add webhook」ボタンをクリック</li>
                                    <li>以下の情報を入力：
                                        <ul class="mt-2 ml-6 space-y-1 list-disc list-inside">
                                            <li><strong>Payload URL</strong>: <code class="px-2 py-1 rounded bg-slate-100">https://your-domain.com/api/github/webhook</code></li>
                                            <li><strong>Content type</strong>: <code class="px-2 py-1 rounded bg-slate-100">application/json</code></li>
                                            <li><strong>Events</strong>: <code class="px-2 py-1 rounded bg-slate-100">workflow_run</code>にチェック</li>
                                        </ul>
                                    </li>
                                    <li>「Active」にチェックを入れる</li>
                                    <li>「Add webhook」をクリック</li>
                                </ol>

                                <p class="mb-4 text-slate-600">
                                    Webhookが設定されると、GitHub Actionsの実行が完了した際に自動的にステータスが更新されます。
                                </p>
                            </div>
                        </section>

                        <!-- 7. トラブルシューティング -->
                        <section id="troubleshooting" class="p-8 bg-white rounded-2xl border shadow-sm border-slate-200">
                            <h2 class="mb-4 text-2xl font-semibold text-slate-900">7. トラブルシューティング</h2>
                            <div class="max-w-none prose prose-slate">
                                <h3 class="mb-3 text-xl font-semibold text-slate-900">7.1 よくあるエラーと解決方法</h3>

                                <div class="mb-4 space-y-4">
                                    <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                                        <h4 class="mb-2 font-semibold text-red-900">エラー: Input required and not supplied: server</h4>
                                        <p class="mb-2 text-sm text-red-800">
                                            <strong>原因:</strong> GitHub Secretsの<code class="px-1 py-0.5 bg-red-100 rounded">FTP_SERVER</code>が設定されていない、または名前が間違っています。
                                        </p>
                                        <p class="text-sm text-red-800">
                                            <strong>解決方法:</strong> GitHub Secretsで<code class="px-1 py-0.5 bg-red-100 rounded">FTP_SERVER</code>、<code class="px-1 py-0.5 bg-red-100 rounded">FTP_USERNAME</code>、<code class="px-1 py-0.5 bg-red-100 rounded">FTP_PASSWORD</code>が正しく設定されているか確認してください。
                                        </p>
                                    </div>

                                    <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                                        <h4 class="mb-2 font-semibold text-red-900">エラー: server-dir should be a folder (must end with /)</h4>
                                        <p class="mb-2 text-sm text-red-800">
                                            <strong>原因:</strong> デプロイ先パスの末尾に<code class="px-1 py-0.5 bg-red-100 rounded">/</code>が付いていません。
                                        </p>
                                        <p class="text-sm text-red-800">
                                            <strong>解決方法:</strong> プロジェクトの「デプロイ先パス」の末尾に<code class="px-1 py-0.5 bg-red-100 rounded">/</code>を追加してください。例: <code class="px-1 py-0.5 bg-red-100 rounded">/public_html/</code>
                                        </p>
                                    </div>

                                    <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                                        <h4 class="mb-2 font-semibold text-red-900">デプロイは成功したが、本番環境に反映されない</h4>
                                        <p class="mb-2 text-sm text-red-800">
                                            <strong>原因:</strong> GitHub SecretsのFTPサーバー設定が本番環境を指していない、またはデプロイ先パスが間違っています。
                                        </p>
                                        <p class="text-sm text-red-800">
                                            <strong>解決方法:</strong> GitHub SecretsのFTPサーバー情報が本番環境のものか確認し、デプロイ先パスが本番環境の正しいパスか確認してください。
                                        </p>
                                    </div>

                                    <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                                        <h4 class="mb-2 font-semibold text-red-900">ステータスが「running」のまま更新されない</h4>
                                        <p class="mb-2 text-sm text-red-800">
                                            <strong>原因:</strong> Webhookが設定されていない、または正しく動作していません。
                                        </p>
                                        <p class="text-sm text-red-800">
                                            <strong>解決方法:</strong> GitHubリポジトリのSettings → Webhooksで、Webhookが正しく設定されているか確認してください。イベントは<code class="px-1 py-0.5 bg-red-100 rounded">workflow_run</code>を選択してください。
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </main>

            <AppFooter color-scheme="slate" padding="large" />
        </div>
    </div>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppFooter from '@/Components/AppFooter.vue';

const page = usePage();
const authUser = computed(() => page.props.auth?.user);
</script>

