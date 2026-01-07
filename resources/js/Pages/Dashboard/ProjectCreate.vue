<template>
    <Head title="プロジェクト作成 - AutoRelease" />
    
    <AuthenticatedLayout>
        <div class="py-12 bg-indigo-50 min-h-screen">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
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
                                <a
                                    :href="route('docs')"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-500 bg-gray-100 rounded-md transition-colors hover:text-gray-700 hover:bg-gray-200"
                                    title="使い方を見る"
                                >
                                    <svg class="mr-1 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    使い方を見る
                                </a>
                            </div>
                            <p class="text-gray-600">
                                クライアントが確認するプロジェクトを登録しましょう。プロジェクト作成後、詳細画面で改善内容を入力して承認URLを生成し、クライアントに共有できます。クライアントが承認すると、自動的に本番環境にデプロイされます。
                            </p>
                        </div>
                        
                        <form @submit.prevent="submit">
                            <div class="space-y-6">
                                <div>
                                    <div class="flex gap-2 items-center">
                                        <InputLabel for="staging_url" value="ステージングURL" />
                                        <div class="relative">
                                            <button
                                                type="button"
                                                @click.stop="toggleTooltip('staging_url', $event)"
                                                class="text-gray-400 transition-colors hover:text-gray-600"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                            <div
                                                v-if="activeTooltip === 'staging_url'"
                                                class="absolute left-0 z-10 p-3 mt-2 w-64 text-sm text-gray-700 bg-white rounded-lg border border-gray-200 shadow-lg"
                                            >
                                                クライアントが確認するテスト環境（ステージング環境）のURLを入力してください。例: https://staging.example.com または https://test.example.com
                                            </div>
                                        </div>
                                </div>
                                    <TextInput
                                        id="staging_url"
                                        v-model="form.staging_url"
                                        type="url"
                                        class="block mt-1 w-full"
                                        placeholder="https://staging.example.com"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.staging_url" />
                                    
                                    <button
                                        type="button"
                                        @click="showSecurityModal = true"
                                        class="mt-2 text-sm text-yellow-600 underline hover:text-yellow-800"
                                    >
                                        重要な推奨事項
                                    </button>
                                </div>

                                <div>
                                    <div class="flex gap-2 items-center">
                                        <InputLabel for="production_url" value="本番URL" />
                                        <div class="relative">
                                            <button
                                                type="button"
                                                @click.stop="toggleTooltip('production_url', $event)"
                                                class="text-gray-400 transition-colors hover:text-gray-600"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                            <div
                                                v-if="activeTooltip === 'production_url'"
                                                class="absolute left-0 z-10 p-3 mt-2 w-64 text-sm text-gray-700 bg-white rounded-lg border border-gray-200 shadow-lg"
                                            >
                                                実際に公開されている本番環境のURLを入力してください。承認後にこのURLにデプロイされます。例: https://example.com
                                            </div>
                                        </div>
                                    </div>
                                    <TextInput
                                        id="production_url"
                                        v-model="form.production_url"
                                        type="url"
                                        class="block mt-1 w-full"
                                        placeholder="https://example.com"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.production_url" />
                                </div>

                                <div>
                                    <InputLabel for="server_dir" value="デプロイ先パス" />
                                    <div class="relative">
                                        <button
                                            type="button"
                                            @click.stop="toggleTooltip('server_dir', $event)"
                                            class="text-gray-400 transition-colors hover:text-gray-600"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                        <div
                                            v-if="activeTooltip === 'server_dir'"
                                            class="absolute left-0 z-10 p-3 mt-2 w-64 text-sm text-gray-700 bg-white rounded-lg border border-gray-200 shadow-lg"
                                        >
                                            FTPサーバー上のデプロイ先ディレクトリパスを入力してください。例: /public_html/ または /home/example.com/public_html/wp-content/themes/mytheme/
                                        </div>
                                    </div>
                                    <TextInput
                                        id="server_dir"
                                        v-model="form.server_dir"
                                        type="text"
                                        class="block mt-1 w-full"
                                        placeholder="/public_html/"
                                    />
                                    <InputError class="mt-2" :message="form.errors.server_dir" />
                                </div>

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

                                <div v-if="selectedOrganization">
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
                                        :disabled="repositories.length === 0"
                                        required
                                    >
                                        <option :value="null" disabled>
                                            {{ repositories.length === 0 ? 'リポジトリを読み込み中...' : 'リポジトリを選択してください' }}
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
                                    
                                    <!-- ワークフローが見つからない場合のガイダンス -->
                                    <div v-if="!loadingWorkflows && workflows.length === 0" class="p-6 mt-1 bg-blue-50 rounded-lg border border-blue-200">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div class="flex-1 ml-3">
                                                <h3 class="mb-2 text-lg font-semibold text-blue-900">
                                                    GitHub Actionsワークフローが見つかりません
                                                </h3>
                                                <p class="mb-4 text-blue-800">
                                                    このリポジトリにはまだワークフローが設定されていません。以下の手順でワークフローを作成してください。
                                                </p>
                                                
                                                <!-- セットアップ手順 -->
                                                <div class="mb-6 space-y-4">
                                                    <div class="p-4 bg-white rounded-lg border border-blue-100">
                                                        <h4 class="mb-3 font-semibold text-blue-900">セットアップ手順</h4>
                                                        <ol class="space-y-2 text-sm list-decimal list-inside text-blue-800">
                                                            <li>GitHubリポジトリで、<code class="px-1.5 py-0.5 font-mono text-xs bg-blue-100 rounded">.github/workflows</code> ディレクトリを作成</li>
                                                            <li>下記のテンプレートから適切なものを選択してコピー</li>
                                                            <li><code class="px-1.5 py-0.5 font-mono text-xs bg-blue-100 rounded">deploy.yml</code> という名前でファイルを作成して貼り付け</li>
                                                            <li>GitHubにコミット&プッシュ</li>
                                                            <li>このページをリロードしてワークフローを選択</li>
                                                        </ol>
                                                    </div>
                                                    
                                                    <!-- テンプレート選択タブ -->
                                                    <div>
                                                        <div class="flex mb-3 space-x-2 border-b border-blue-200">
                                                            <button
                                                                v-for="template in workflowTemplates"
                                                                :key="template.id"
                                                                @click="selectedTemplate = template.id"
                                                                :class="[
                                                                    'px-4 py-2 text-sm font-medium border-b-2 transition',
                                                                    selectedTemplate === template.id
                                                                        ? 'border-blue-500 text-blue-600'
                                                                        : 'border-transparent text-blue-400 hover:text-blue-600'
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
                                                        <p class="mt-2 text-xs text-blue-700">
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
                                                </div>
                                                
                                                <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                                    <p class="text-sm text-yellow-800">
                                                        <strong>注意:</strong> Secretsは一度しか表示されません。コピーして安全に保管してください。また、Secret名は大文字・小文字を区別します。
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- ワークフローが見つかった場合の選択 -->
                                    <select
                                        v-else
                                        id="github_workflow_id"
                                        v-model="form.github_workflow_id"
                                        class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="" disabled>ワークフローを選択してください</option>
                                        <option v-for="workflow in workflows" :key="workflow.id" :value="String(workflow.id)">
                                            {{ workflow.name }} ({{ workflow.path }})
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.github_workflow_id" />
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
                                                デプロイ対象のブランチを選択してください。<code class="px-1 py-0.5 text-xs bg-gray-100 rounded">main</code> または <code class="px-1 py-0.5 text-xs bg-gray-100 rounded">master</code> ブランチが自動的に選択されます。
                                            </div>
                                        </div>
                                    </div>
                                    <select
                                        id="github_branch"
                                        v-model="form.github_branch"
                                        class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="" disabled>ブランチを選択してください</option>
                                        <!-- main/masterブランチのみ表示（存在する場合） -->
                                        <option 
                                            v-for="branch in branches" 
                                            :key="branch.name" 
                                            :value="branch.name"
                                        >
                                            {{ branch.name }}
                                        </option>
                                    </select>
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
    staging_url: '',
    production_url: '',
    server_dir: '/public_html/',
    github_owner: '',
    github_repo: '',
    github_workflow_id: '',
    github_branch: 'main',
});

const organizations = ref([]);
const repositories = ref([]);
const workflows = ref([]);
const branches = ref([]);
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
        name: 'FTPデプロイ',
        description: 'レンタルサーバーなど、FTPでアップロードする場合に使用します。',
        secrets: [
            { name: 'FTP_SERVER', description: 'FTPサーバーのアドレス（例: ftp.example.com）' },
            { name: 'FTP_USERNAME', description: 'FTPユーザー名（例: example.com）' },
            { name: 'FTP_PASSWORD', description: 'FTPパスワード' }
        ],
        secretsExample: `FTP_SERVER: ftp.example.com
FTP_USERNAME: example.com
FTP_PASSWORD: あなたのFTPパスワード

※ レンタルサーバーのコントロールパネルで確認できます`,
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
      server_dir:
        description: 'Server directory path'
        required: false
        default: '/public_html/'

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
        with:
          ref: \${{ github.event.inputs.ref || github.ref }}
      
      - name: Deploy via FTP
        uses: SamKirkland/FTP-Deploy-Action@4.3.0
        with:
          server: \${{ secrets.FTP_SERVER }}
          username: \${{ secrets.FTP_USERNAME }}
          password: \${{ secrets.FTP_PASSWORD }}
          local-dir: ./
          server-dir: \${{ inputs.server_dir || '/public_html/' }}
`
    },
    {
        id: 'ssh',
        name: 'SSHデプロイ',
        description: 'VPSや専用サーバーなど、SSHで接続してデプロイする場合に使用します。',
        secrets: [
            { name: 'SSH_HOST', description: 'SSHサーバーのアドレス（例: example.com または 123.45.67.89）' },
            { name: 'SSH_USER', description: 'SSHユーザー名（例: root または ubuntu）' },
            { name: 'SSH_PRIVATE_KEY', description: 'SSH秘密鍵（-----BEGIN RSA PRIVATE KEY----- から始まる内容全体）' }
        ],
        secretsExample: `SSH_HOST: example.com
SSH_USER: root
SSH_PRIVATE_KEY: |
  -----BEGIN RSA PRIVATE KEY-----
  MIIEpAIBAAKCAQEA...
  (秘密鍵の内容をそのまま貼り付け)
  -----END RSA PRIVATE KEY-----

※ SSH秘密鍵は以下のコマンドで確認できます:
  cat ~/.ssh/id_rsa

※ 公開鍵（id_rsa.pub）をサーバー側の
  ~/.ssh/authorized_keys に追加してください`,
        content: `name: Deploy to Production

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
      
      - name: Deploy via SSH
        uses: appleboy/ssh-action@master
        with:
          host: \${{ secrets.SSH_HOST }}
          username: \${{ secrets.SSH_USER }}
          key: \${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            cd /var/www/html
            git pull origin main
            # 必要に応じて追加のコマンドを記述
            # composer install --no-dev
            # npm run build
`
    },
    {
        id: 'rsync',
        name: 'rsyncデプロイ',
        description: 'rsyncを使ってファイルを同期する場合に使用します。',
        secrets: [
            { name: 'RSYNC_HOST', description: 'rsyncサーバーのアドレス（例: example.com）' },
            { name: 'RSYNC_USER', description: 'rsyncユーザー名（例: root または ubuntu）' },
            { name: 'RSYNC_SSH_KEY', description: 'SSH秘密鍵（-----BEGIN RSA PRIVATE KEY----- から始まる内容全体）' }
        ],
        secretsExample: `RSYNC_HOST: example.com
RSYNC_USER: root
RSYNC_SSH_KEY: |
  -----BEGIN RSA PRIVATE KEY-----
  MIIEpAIBAAKCAQEA...
  (秘密鍵の内容をそのまま貼り付け)
  -----END RSA PRIVATE KEY-----

※ SSH秘密鍵は以下のコマンドで確認できます:
  cat ~/.ssh/id_rsa`,
        content: `name: Deploy to Production

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
      
      - name: Deploy via rsync
        uses: burnett01/rsync-deployments@6.0
        with:
          switches: -avzr --delete
          path: ./
          remote_path: /var/www/html/
          remote_host: \${{ secrets.RSYNC_HOST }}
          remote_user: \${{ secrets.RSYNC_USER }}
          remote_key: \${{ secrets.RSYNC_SSH_KEY }}
`
    },
    {
        id: 'static',
        name: '静的サイト',
        description: 'HTML/CSS/JSなどの静的サイトをビルドしてデプロイする場合に使用します。',
        secrets: [
            { name: 'FTP_SERVER', description: 'FTPサーバーのアドレス（FTPでデプロイする場合）' },
            { name: 'FTP_USERNAME', description: 'FTPユーザー名' },
            { name: 'FTP_PASSWORD', description: 'FTPパスワード' }
        ],
        secretsExample: `※ 静的サイトテンプレートは、ビルド後に
FTP、SSH、rsyncなど任意の方法でデプロイできます。

FTPでデプロイする場合:
FTP_SERVER: ftp.example.com
FTP_USERNAME: example.com
FTP_PASSWORD: あなたのFTPパスワード

SSHでデプロイする場合は、SSH_HOST、SSH_USER、
SSH_PRIVATE_KEY を設定してください。`,
        content: `name: Deploy to Production

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
      
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '18'
      
      - name: Install dependencies
        run: npm install
      
      - name: Build
        run: npm run build
      
      - name: Deploy
        # ここにデプロイコマンドを記述
        # 例: FTP、SSH、rsyncなど
        run: |
          echo "Build completed. Add your deployment commands here."
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
    if (!selectedOrganization.value) return;

    // リセット
    selectedRepository.value = null;
    repositories.value = [];
    workflows.value = [];
    branches.value = [];
    form.github_owner = '';
    form.github_repo = '';
    form.github_workflow_id = '';
    form.github_branch = 'main';

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
    form.github_branch = 'main';
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
        branches.value = branchesResponse.data || [];
        
        console.log('Branches fetched:', branches.value);
        
        // ワークフローが取得できた場合、最初のワークフローを自動選択
        if (workflows.value && workflows.value.length > 0) {
            form.github_workflow_id = String(workflows.value[0].id);
            console.log('Auto-selected workflow:', workflows.value[0].name);
        }
        
        // mainまたはmasterブランチのみを表示（バックエンドでフィルタリング済み）
        if (branches.value && branches.value.length > 0) {
            form.github_branch = branches.value[0].name;
            console.log('Auto-selected branch:', branches.value[0].name);
        } else if (branchesResponse.data && branchesResponse.data.error) {
            console.error('Branch fetch error:', branchesResponse.data.error);
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
    form.post(route('projects.store'));
};
</script>
