<template>
    <Head title="プロジェクト一覧 - AutoRelease" />
    
    <AuthenticatedLayout>
        <div class="flex h-[calc(100vh-4rem)]">
            <!-- 左サイドバー（固定） -->
            <div class="flex flex-col w-64 bg-white border-r border-gray-200">
                <!-- サイドバーヘッダー -->
                <div class="p-4">
                    <div class="flex justify-between items-center">
                        <h2 class="text-sm font-semibold text-gray-900">Projects</h2>
                    <Link :href="route('projects.create')">
                            <button class="flex gap-1 items-center px-2 py-1 text-xs font-medium text-white bg-indigo-600 rounded-md border border-transparent transition duration-150 ease-in-out hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                新規作成
                            </button>
                    </Link>
                    </div>
                </div>

                <!-- プロジェクトリスト（スクロール可能） -->
                <div class="overflow-y-auto flex-1">
                    <div v-if="projects.length === 0" class="p-4 text-center">
                        <p class="text-sm text-gray-500">プロジェクトがありません</p>
                        </div>
                    <div v-else class="p-2">
                        <div
                                v-for="project in projects"
                                :key="project.id"
                            @click="selectProject(project.id)"
                            :class="[
                                'px-3 py-2 rounded-md cursor-pointer transition-colors mb-1',
                                selectedProjectId === project.id 
                                    ? 'bg-indigo-50 text-gray-900 font-medium' 
                                    : 'text-gray-700 hover:bg-gray-50'
                            ]"
                        >
                            <div class="flex gap-2 justify-between items-center">
                                <span class="block text-sm truncate">
                                    {{ project.name }}
                                </span>
                                <span
                                    v-if="isProjectSetupIncomplete(project)"
                                    class="flex-shrink-0 px-2 py-0.5 text-xs text-yellow-800 bg-yellow-100 rounded-full"
                                >
                                    未設定
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 右メインコンテンツエリア -->
            <div class="overflow-y-auto flex-1 bg-indigo-50">
                <div v-if="currentProject" class="p-6 mx-auto max-w-6xl">
                    <div>
                        <div class="bg-white rounded-2xl shadow-xl">
                        <div class="p-6 text-gray-900 border-b border-gray-200">
                            <!-- プロジェクト名（タイトル）とアクションボタン -->
                            <div class="flex justify-between items-center mb-1">
                                <div class="flex gap-2 items-center">
                                    <h1 class="text-xl font-bold text-gray-700">{{ currentProject.name }}</h1>
                                </div>
                                <div class="flex gap-1">
                                    <button
                                        @click="openEditModal"
                                        class="p-2 text-gray-500 rounded-md transition-colors hover:text-indigo-600 hover:bg-indigo-50"
                                        title="編集"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button
                                        @click="confirmDelete"
                                        class="p-2 text-gray-500 rounded-md transition-colors hover:text-red-600 hover:bg-red-50"
                                        title="削除"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 mb-6">
                                <span :class="getStatusTagClass(!!currentProject.ssh_configured)">
                                    <svg v-if="currentProject.ssh_configured" class="w-3.5 h-3.5 text-emerald-700" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                        <circle cx="8" cy="8" r="7" />
                                        <path d="M5 8.2l1.9 1.9L11 6" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <svg v-else class="w-3.5 h-3.5 text-yellow-700" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                        <circle cx="8" cy="8" r="7" />
                                        <path d="M8 4.2v5" stroke="#fff" stroke-width="1.5" stroke-linecap="round" />
                                        <circle cx="8" cy="11.8" r="1" fill="#fff" />
                                    </svg>
                                    {{ getSshStatusLabel(currentProject) }}
                                </span>
                                <span :class="getStatusTagClass(!!currentProject.github_workflow_id)">
                                    <svg v-if="currentProject.github_workflow_id" class="w-3.5 h-3.5 text-emerald-700" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                        <circle cx="8" cy="8" r="7" />
                                        <path d="M5 8.2l1.9 1.9L11 6" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <svg v-else class="w-3.5 h-3.5 text-yellow-700" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                        <circle cx="8" cy="8" r="7" />
                                        <path d="M8 4.2v5" stroke="#fff" stroke-width="1.5" stroke-linecap="round" />
                                        <circle cx="8" cy="11.8" r="1" fill="#fff" />
                                    </svg>
                                    {{ getWorkflowStatusLabel(currentProject) }}
                                </span>
                            </div>
                            
                            <!-- その他の情報（横並び） -->
                            <div class="text-sm text-gray-900">
                                <span class="font-medium text-gray-400">本番URL：</span>
                                <a :href="currentProject.production_url" target="_blank" class="text-blue-600 hover:text-blue-800" :title="currentProject.production_url">
                                    {{ currentProject.production_url }}
                                </a>
                                <span class="mx-2 text-gray-400">　</span>
                                <span class="font-medium text-gray-400">テストURL：</span>
                                <a :href="currentProject.staging_url" target="_blank" class="text-blue-600 hover:text-blue-800" :title="currentProject.staging_url">
                                    {{ currentProject.staging_url }}
                                </a>
                            </div>
                        </div>

                        <!-- 改善内容入力と承認URL生成 -->
                        <div
                            v-if="isCurrentProjectSetupComplete"
                            ref="approvalSection"
                            class="px-4 py-4"
                        >
                            <div class="flex gap-1 items-center mb-2">
                                <h2 class="text-2xl font-bold text-gray-800">改善内容の作成</h2>
                                <button
                                    @click="showGuide"
                                    class="p-2 text-gray-400 rounded-md transition-colors hover:text-indigo-600 hover:bg-indigo-50"
                                    title="使い方ガイドを見る"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                            </div>
                            <p class="mb-6 text-sm text-gray-500">改善内容をまとめてクライアントに共有しましょう</p>
                            <div class="space-y-3">
                                <div class="mb-6">
                                    <MdEditor
                                        v-model="approvalMessage"
                                        :preview="true"
                                        :toolbars="markdownToolbars"
                                        language="ja-JP"
                                        placeholder="例：&#10;- トップページのデザインを更新&#10;- 改善ページ: /about, /products/item-1&#10;- 変更点: ヘッダーの色を変更、ボタンの配置を調整"
                                        class="markdown-editor"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">
                                        改善ページURLは「/about」のようにパスだけ入力してください。テスト環境URLと自動的に結合されます。
                                    </p>
                                </div>
                                <div class="flex gap-2 justify-end">
                                    <button
                                        @click="resetApprovalForm"
                                        class="px-6 py-2 text-sm text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
                                    >
                                        {{ generatedApprovalUrl ? 'すべてクリア' : 'キャンセル' }}
                                    </button>
                                    <button
                                        ref="generateButton"
                                        @click="generateApprovalUrl"
                                        :disabled="!approvalMessage || generatingUrl"
                                        class="px-6 py-2 text-sm text-white bg-indigo-600 rounded-md hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
                                    >
                                        {{ generatingUrl ? '生成中...' : (generatedApprovalUrl ? '承認URLを再生成' : '承認URLを生成') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="px-4 py-4">
                            <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                <div class="flex items-start">
                                    <svg class="flex-shrink-0 mt-0.5 w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800">
                                            初期設定が未完了のため、承認URLを作成できません。
                                        </h3>
                                        <p class="mt-1 text-sm text-yellow-700">
                                            以下の項目を設定してください。
                                        </p>
                                        <div class="flex flex-wrap gap-2 mt-2">
                                            <span
                                                v-for="item in missingSetupForCurrentProject"
                                                :key="item"
                                                class="px-2 py-0.5 text-xs text-yellow-800 bg-yellow-100 rounded-full"
                                            >
                                                {{ item }}
                                            </span>
                                        </div>
                                        <div class="mt-3">
                                            <Link 
                                                :href="route('projects.edit', currentProject.id)" 
                                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-yellow-800 bg-yellow-100 rounded-md hover:bg-yellow-200"
                                            >
                                                設定する
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>

                        <!-- サクセスメッセージ（画面下からフェードイン） -->
                        <Transition
                                enter-active-class="transition duration-300 ease-out"
                                enter-from-class="opacity-0 transform translate-y-full"
                                enter-to-class="opacity-100 transform translate-y-0"
                                leave-active-class="transition duration-200 ease-in"
                                leave-from-class="opacity-100 transform translate-y-0"
                                leave-to-class="opacity-0 transform translate-y-full"
                            >
                                <div
                                    v-if="showSuccessMessage"
                                    class="fixed bottom-4 z-50 px-6 max-w-3xl"
                                    style="left: 256px; right: 0; margin: 0 auto;"
                                >
                                    <div class="relative p-3 w-full bg-indigo-100 rounded-2xl border border-indigo-200 shadow-lg">
                                        <div class="flex justify-between items-center mb-2">
                                            <p class="text-sm font-semibold text-blue-800">
                                                {{ isRegenerating ? '承認URLを再生成しました！' : '承認URLを生成しました！' }}
                                            </p>
                                            <button
                                                @click="closeSuccessMessage"
                                                class="flex-shrink-0 text-xs text-blue-700 hover:text-blue-900"
                                            >
                                                閉じる
                                            </button>
                                        </div>
                                        <div class="flex relative gap-2 items-center px-4 py-2 bg-white rounded-xl border border-indigo-200">
                                            <a :href="generatedApprovalUrl" target="_blank" class="flex-1 text-sm text-blue-600 underline truncate hover:text-blue-800">{{ generatedApprovalUrl }}</a>
                                            <button
                                                @click="copyGeneratedApprovalUrl"
                                                class="flex-shrink-0 p-1 text-gray-600 rounded hover:bg-gray-100"
                                                :title="generatedUrlCopied ? 'コピーしました！' : 'コピー'"
                                            >
                                                <svg v-if="!generatedUrlCopied" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                                <svg v-else class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                        </Transition>

                        <div class="mt-8 bg-white rounded-2xl">
                                <div class="flex justify-between items-center p-4 border-b border-gray-200">
                                    <h3 class="font-bold text-gray-700 text-md">リリースログ</h3>
                                    <p class="text-xs font-bold text-gray-600">
                                        ブランチ：<span class="font-medium">{{ currentProject.github_branch || '未設定' }}</span>
                                    </p>
                                </div>
                                <div v-if="!currentProject.deploy_logs || currentProject.deploy_logs.length === 0" class="text-gray-500">
                                    リリースログがありません
                                </div>
                                <div v-else>
                                    <div
                                        v-for="log in currentProject.deploy_logs"
                                        :key="log.id"
                                        class="p-4 border-b border-gray-200 last-of-type:border-none"
                                    >
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <div class="flex gap-3 items-center text-xs font-bold text-gray-500">
                                                    <!-- ステータスアイコン -->
                                                    <div v-if="log.status === 'success'" class="flex flex-shrink-0 justify-center items-center w-6 h-6 bg-green-500 rounded-full">
                                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </div>
                                                    <div v-else-if="log.status === 'failed'" class="flex flex-shrink-0 justify-center items-center w-6 h-6 bg-red-500 rounded-full">
                                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </div>
                                                    <div v-else-if="log.status === 'running'" class="flex-shrink-0 w-6 h-6 rounded-full border-2 border-orange-500"></div>
                                                    <div v-else class="flex-shrink-0 w-6 h-6 rounded-full border-2 border-gray-500"></div>
                                                    
                                                    <p>開始: {{ formatDateTime(log.started_at) }}</p>
                                                    <p v-if="log.finished_at">終了: {{ formatDateTime(log.finished_at) }}</p>
                                                    <p v-if="log.finished_at">所要時間: {{ formatDuration(log.started_at, log.finished_at) }}</p>
                                                    <p v-else-if="log.started_at">経過時間: {{ formatElapsedTime(log.started_at) }}</p>
                                                    
                                                    <!-- 詳細ボタン（右端に配置） -->
                                                    <button
                                                        v-if="log.approval_message"
                                                        @click="toggleLogExpansion(log.id)"
                                                        class="flex gap-1 items-center ml-auto text-xs font-bold text-gray-500 transition-colors hover:text-gray-700"
                                                    >
                                                        <span>詳細</span>
                                                        <svg 
                                                            class="w-4 h-4 transition-transform"
                                                            :class="{ 'rotate-90': expandedLogs[log.id] }"
                                                            fill="none" 
                                                            stroke="currentColor" 
                                                            viewBox="0 0 24 24"
                                                        >
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                <div v-if="log.approval_message && expandedLogs[log.id]" class="p-4 mt-2 bg-white rounded-2xl">
                                                    <div class="text-sm text-gray-600 whitespace-pre-line">{{ log.approval_message.message }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                    
                    <!-- フッター -->
                    <div class="mt-8">
                        <AppFooter />
                    </div>
                </div>
                <div v-else class="flex justify-center items-center h-full">
                    <p class="text-gray-500">プロジェクトを選択してください</p>
                </div>
            </div>
        </div>

        <!-- ガイドオーバーレイ -->
        <div 
            v-if="showGuideOverlay"
            class="flex fixed inset-0 z-50 justify-center items-center bg-black bg-opacity-50"
            @click.self="closeGuide"
        >
            <div class="relative mx-4 w-full max-w-lg bg-white rounded-lg shadow-xl">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-gray-900">
                            使い方ガイド ({{ currentStep + 1 }}/{{ guideSteps.length }})
                        </h3>
                        <button
                            @click="closeGuide"
                            class="p-1 text-gray-400 hover:text-gray-600"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="mb-6">
                        <div class="mb-4">
                            <h4 class="mb-2 text-lg font-semibold text-gray-900">
                                {{ guideSteps[currentStep].title }}
                            </h4>
                            <p class="text-gray-600 whitespace-pre-line">
                                {{ guideSteps[currentStep].description }}
                            </p>
                        </div>
                        <div v-if="guideSteps[currentStep].action" class="p-3 bg-indigo-50 rounded-md border border-indigo-200">
                            <p class="text-sm font-medium text-indigo-700">
                                {{ guideSteps[currentStep].action }}
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <button
                            v-if="currentStep > 0"
                            @click="previousStep"
                            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
                        >
                            前へ
                        </button>
                        <div v-else></div>
                        <div class="flex gap-2">
                            <button
                                v-for="(step, index) in guideSteps"
                                :key="index"
                                @click="currentStep = index"
                                :class="[
                                    'w-2 h-2 rounded-full transition-colors',
                                    currentStep === index ? 'bg-indigo-600' : 'bg-gray-300'
                                ]"
                            ></button>
                        </div>
                        <button
                            v-if="currentStep < guideSteps.length - 1"
                            @click="nextStep"
                            class="px-4 py-2 text-white bg-indigo-600 rounded-md hover:bg-indigo-700"
                        >
                            次へ
                        </button>
                        <button
                            v-else
                            @click="closeGuide"
                            class="px-4 py-2 text-white bg-indigo-600 rounded-md hover:bg-indigo-700"
                        >
                            完了
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 編集モーダル -->
        <div 
            v-if="showEditModal"
            class="flex overflow-y-auto fixed inset-0 z-50 justify-center items-start p-4 bg-black bg-opacity-50"
            @click.self="closeEditModal"
        >
            <div class="relative mx-auto my-8 w-full max-w-3xl bg-white rounded-lg shadow-xl">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-900">プロジェクトを編集する</h3>
                        <button
                            @click="closeEditModal"
                            class="p-1 text-gray-400 hover:text-gray-600"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <form @submit.prevent="submitEditForm" class="space-y-6">
                        <div>
                            <div class="flex gap-2 items-center">
                                <InputLabel for="edit_staging_url" value="テストURL" class="font-bold text-gray-900" />
                                <div class="relative">
                                    <button
                                        type="button"
                                        @click.stop="toggleEditTooltip('staging_url', $event)"
                                        class="text-gray-400 transition-colors hover:text-gray-600"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                    <div
                                        v-if="editActiveTooltip === 'staging_url'"
                                        class="absolute left-0 z-10 p-3 mt-2 w-72 text-sm text-gray-700 bg-white rounded-lg border border-gray-200 shadow-lg"
                                    >
                                        クライアントが確認するテスト環境（ステージング環境）のURLです。
                                    </div>
                                </div>
                            </div>
                            <TextInput
                                id="edit_staging_url"
                                v-model="editForm.staging_url"
                                type="url"
                                class="block mt-1 w-full"
                                placeholder="https://staging.example.com"
                                required
                            />
                            <InputError class="mt-2" :message="editForm.errors.staging_url" />
                        </div>

                        <div>
                            <div class="flex gap-2 items-center">
                                <InputLabel for="edit_production_url" value="本番URL" class="font-bold text-gray-900" />
                                <div class="relative">
                                    <button
                                        type="button"
                                        @click.stop="toggleEditTooltip('production_url', $event)"
                                        class="text-gray-400 transition-colors hover:text-gray-600"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                    <div
                                        v-if="editActiveTooltip === 'production_url'"
                                        class="absolute left-0 z-10 p-3 mt-2 w-72 text-sm text-gray-700 bg-white rounded-lg border border-gray-200 shadow-lg"
                                    >
                                        実際に公開されている本番環境のURLです。
                                    </div>
                                </div>
                            </div>
                            <TextInput
                                id="edit_production_url"
                                v-model="editForm.production_url"
                                type="url"
                                class="block mt-1 w-full"
                                placeholder="https://example.com"
                                required
                            />
                            <InputError class="mt-2" :message="editForm.errors.production_url" />
                        </div>

                        <div>
                            <div class="flex gap-2 items-center">
                                <InputLabel for="edit_server_dir" value="デプロイ先パス" class="font-bold text-gray-900" />
                                <div class="relative">
                                    <button
                                        type="button"
                                        @click.stop="toggleEditTooltip('server_dir', $event)"
                                        class="text-gray-400 transition-colors hover:text-gray-600"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                    <div
                                        v-if="editActiveTooltip === 'server_dir'"
                                        class="absolute left-0 z-10 p-3 mt-2 w-80 text-sm text-gray-700 bg-white rounded-lg border border-gray-200 shadow-lg"
                                    >
                                        サーバー上のデプロイ先ディレクトリパスです。
                                    </div>
                                </div>
                            </div>
                            <TextInput
                                id="edit_server_dir"
                                v-model="editForm.server_dir"
                                type="text"
                                class="block mt-1 w-full"
                                placeholder="/public_html/"
                            />
                            <InputError class="mt-2" :message="editForm.errors.server_dir" />
                        </div>

                        <div>
                            <div class="flex gap-2 items-center">
                                <InputLabel for="edit_slack_webhook_url" value="Slack Webhook URL（任意）" class="font-bold text-gray-900" />
                                <div class="relative">
                                    <button
                                        type="button"
                                        @click.stop="toggleEditTooltip('slack_webhook_url', $event)"
                                        class="text-gray-400 transition-colors hover:text-gray-600"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                    <div
                                        v-if="editActiveTooltip === 'slack_webhook_url'"
                                        class="absolute left-0 z-10 p-3 mt-2 w-80 text-sm text-gray-700 bg-white rounded-lg border border-gray-200 shadow-lg"
                                    >
                                        デプロイ完了時にSlackに通知を送るWebhook URLです。未設定の場合はグローバル設定を使用します。
                                    </div>
                                </div>
                            </div>
                            <TextInput
                                id="edit_slack_webhook_url"
                                v-model="editForm.slack_webhook_url"
                                type="url"
                                class="block mt-1 w-full"
                                placeholder="https://hooks.slack.com/services/xxx/yyy/zzz"
                            />
                            <InputError class="mt-2" :message="editForm.errors.slack_webhook_url" />
                        </div>

                        <div>
                            <div class="flex gap-2 items-center">
                                <InputLabel for="edit_name" value="プロジェクト名" class="font-bold text-gray-900" />
                                <div class="relative">
                                    <button
                                        type="button"
                                        @click.stop="toggleEditTooltip('project_name', $event)"
                                        class="text-gray-400 transition-colors hover:text-gray-600"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                    <div
                                        v-if="editActiveTooltip === 'project_name'"
                                        class="absolute left-0 z-10 p-3 mt-2 w-72 text-sm text-gray-700 bg-white rounded-lg border border-gray-200 shadow-lg"
                                    >
                                        未入力の場合はGitHubリポジトリ名が使用されます。
                                    </div>
                                </div>
                            </div>
                            <TextInput
                                id="edit_name"
                                v-model="editForm.name"
                                type="text"
                                class="block mt-1 w-full"
                                placeholder="プロジェクト名（任意）"
                            />
                            <InputError class="mt-2" :message="editForm.errors.name" />
                        </div>

                        <div>
                            <div class="flex gap-2 items-center">
                                <InputLabel for="edit_organization" value="GitHub 組織" class="font-bold text-gray-900" />
                                <div class="relative">
                                    <button
                                        type="button"
                                        @click.stop="toggleEditTooltip('organization', $event)"
                                        class="text-gray-400 transition-colors hover:text-gray-600"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                    <div
                                        v-if="editActiveTooltip === 'organization'"
                                        class="absolute left-0 z-10 p-3 mt-2 w-64 text-sm text-gray-700 bg-white rounded-lg border border-gray-200 shadow-lg"
                                    >
                                        リポジトリが所属する組織を選択します。
                                    </div>
                                </div>
                            </div>
                            <select
                                id="edit_organization"
                                v-model="editSelectedOrganization"
                                @change="onEditOrganizationChange"
                                class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">後で設定する</option>
                                <option value="personal">個人リポジトリ</option>
                                <option v-for="org in editOrganizations" :key="org.id" :value="org.login">
                                    {{ org.login }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="editForm.errors.github_owner" />
                        </div>

                        <div v-if="editSelectedOrganization">
                            <div class="flex gap-2 items-center">
                                <InputLabel for="edit_repository" value="GitHub リポジトリ" class="font-bold text-gray-900" />
                                <div class="relative">
                                    <button
                                        type="button"
                                        @click.stop="toggleEditTooltip('repository', $event)"
                                        class="text-gray-400 transition-colors hover:text-gray-600"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                    <div
                                        v-if="editActiveTooltip === 'repository'"
                                        class="absolute left-0 z-10 p-3 mt-2 w-72 text-sm text-gray-700 bg-white rounded-lg border border-gray-200 shadow-lg"
                                    >
                                        デプロイ対象のGitHubリポジトリを選択します。
                                    </div>
                                </div>
                            </div>
                            <select
                                id="edit_repository"
                                v-model="editSelectedRepository"
                                @change="onEditRepositoryChange"
                                class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :disabled="editRepositories.length === 0"
                            >
                                <option :value="null" disabled>
                                    {{ editRepositories.length === 0 ? 'リポジトリを読み込み中...' : 'リポジトリを選択してください' }}
                                </option>
                                <option v-for="repo in editRepositories" :key="repo.id" :value="repo">
                                    {{ repo.full_name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="editForm.errors.github_repo" />
                        </div>

                        <div v-if="editSelectedRepository">
                            <div class="flex gap-2 items-center">
                                <InputLabel for="edit_github_workflow_id" value="GitHub ワークフロー" class="font-bold text-gray-900" />
                                <div class="relative">
                                    <button
                                        type="button"
                                        @click.stop="toggleEditTooltip('workflow', $event)"
                                        class="text-gray-400 transition-colors hover:text-gray-600"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                    <div
                                        v-if="editActiveTooltip === 'workflow'"
                                        class="absolute left-0 z-10 p-3 mt-2 w-80 text-sm text-gray-700 bg-white rounded-lg border border-gray-200 shadow-lg"
                                    >
                                        承認時に実行されるGitHub Actionsのワークフローを選択してください。
                                    </div>
                                </div>
                            </div>
                            <select
                                id="edit_github_workflow_id"
                                v-model="editForm.github_workflow_id"
                                class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :disabled="editLoadingWorkflows || editWorkflows.length === 0"
                            >
                                <option value="">後で設定する</option>
                                <option v-for="workflow in editWorkflows" :key="workflow.id" :value="String(workflow.id)">
                                    {{ workflow.name }} ({{ workflow.path }})
                                </option>
                            </select>
                            <p v-if="!editLoadingWorkflows && editWorkflows.length === 0" class="mt-1 text-sm text-yellow-600">
                                ワークフローが見つかりませんでした。GitHub側でワークフローを作成してください。
                            </p>
                            <InputError class="mt-2" :message="editForm.errors.github_workflow_id" />
                        </div>

                        <div v-if="editSelectedRepository">
                            <div class="flex gap-2 items-center">
                                <InputLabel for="edit_github_branch" value="GitHub ブランチ" class="font-bold text-gray-900" />
                                <div class="relative">
                                    <button
                                        type="button"
                                        @click.stop="toggleEditTooltip('branch', $event)"
                                        class="text-gray-400 transition-colors hover:text-gray-600"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                    <div
                                        v-if="editActiveTooltip === 'branch'"
                                        class="absolute left-0 z-10 p-3 mt-2 w-80 text-sm text-gray-700 bg-white rounded-lg border border-gray-200 shadow-lg"
                                    >
                                        本番環境にアップロードするブランチ名です。通常は <code class="px-1 py-0.5 text-xs bg-gray-100 rounded">main</code> を使用します。
                                    </div>
                                </div>
                            </div>
                            <div class="relative">
                                <input
                                    type="text"
                                    id="edit_github_branch"
                                    v-model="editForm.github_branch"
                                    list="edit-branch-list"
                                    class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="main"
                                />
                                <datalist id="edit-branch-list">
                                    <option v-for="branch in editBranches" :key="branch.name" :value="branch.name">
                                        {{ branch.name }}
                                    </option>
                                </datalist>
                            </div>
                            <InputError class="mt-2" :message="editForm.errors.github_branch" />
                        </div>
                        <div v-else>
                            <InputLabel for="edit_github_branch_fallback" value="GitHub ブランチ" class="font-bold text-gray-900" />
                            <TextInput
                                id="edit_github_branch_fallback"
                                v-model="editForm.github_branch"
                                type="text"
                                class="block mt-1 w-full"
                            />
                            <InputError class="mt-2" :message="editForm.errors.github_branch" />
                        </div>

                        <div v-if="!editForm.ssh_configured" class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <label for="edit_ssh_configured" class="flex gap-3 items-start cursor-pointer">
                                <input
                                    id="edit_ssh_configured"
                                    v-model="editForm.ssh_configured"
                                    type="checkbox"
                                    class="mt-1 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500"
                                />
                                <span class="text-sm text-gray-700">
                                    SSH設定済み（GitHub Secretsに登録済み）
                                </span>
                            </label>
                        </div>

                        <div class="flex gap-4 justify-end items-center pt-4 border-t border-gray-200">
                            <button
                                type="button"
                                @click="closeEditModal"
                                class="px-4 py-2 text-sm text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
                            >
                                キャンセル
                            </button>
                            <PrimaryButton :class="{ 'opacity-25': editForm.processing }" :disabled="editForm.processing">
                                更新
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, Transition, onUnmounted } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppFooter from '@/Components/AppFooter.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { MdEditor } from 'md-editor-v3';
import 'md-editor-v3/lib/style.css';
import 'md-editor-v3/lib/preview.css';
import axios from 'axios';

const props = defineProps({
    projects: Array,
    selectedProject: Object,
});

const selectedProjectId = ref(null);
const approvalMessage = ref('');
const generatingUrl = ref(false);
const generatedApprovalUrl = ref('');
const generatedUrlCopied = ref(false);
const showSuccessMessage = ref(false);
const isRegenerating = ref(false);

// md-editor-v3 ツールバー設定
const markdownToolbars = [
    'bold',
    'underline',
    'italic',
    '-',
    'title',
    'strikeThrough',
    'sub',
    'sup',
    'quote',
    'unorderedList',
    'orderedList',
    'task',
    '-',
    'codeRow',
    'code',
    'link',
    'image',
    'table',
    '-',
    'revoke',
    'next',
    'save',
    '=',
    'pageFullscreen',
    'fullscreen',
    'preview',
    'catalog',
];

// ガイド関連
const showGuideOverlay = ref(false);
const currentStep = ref(0);
const approvalSection = ref(null);
const generateButton = ref(null);
const generatedUrlSection = ref(null);

// リリースログの開閉状態管理
const expandedLogs = ref({});

const toggleLogExpansion = (logId) => {
    expandedLogs.value[logId] = !expandedLogs.value[logId];
};

// 編集モーダル関連
const showEditModal = ref(false);
const editForm = useForm({
    name: '',
    staging_url: '',
    production_url: '',
    server_dir: '/public_html/',
    slack_webhook_url: '',
    github_owner: '',
    github_repo: '',
    github_workflow_id: '',
    github_branch: '',
    ssh_configured: false,
});
const editOrganizations = ref([]);
const editRepositories = ref([]);
const editWorkflows = ref([]);
const editBranches = ref([]);
const editSelectedOrganization = ref('');
const editSelectedRepository = ref(null);
const editActiveTooltip = ref(null);
const editLoadingWorkflows = ref(false);

const openEditModal = () => {
    if (!currentProject.value) return;
    
    // フォームに現在のプロジェクト情報を設定
    editForm.name = currentProject.value.name || '';
    editForm.staging_url = currentProject.value.staging_url || '';
    editForm.production_url = currentProject.value.production_url || '';
    editForm.server_dir = currentProject.value.server_dir || '/public_html/';
    editForm.slack_webhook_url = currentProject.value.slack_webhook_url || '';
    editForm.github_owner = currentProject.value.github_owner || '';
    editForm.github_repo = currentProject.value.github_repo || '';
    editForm.github_workflow_id = currentProject.value.github_workflow_id || '';
    editForm.github_branch = currentProject.value.github_branch || '';
    editForm.ssh_configured = currentProject.value.ssh_configured || false;
    
    editSelectedOrganization.value = currentProject.value.github_owner || '';
    editSelectedRepository.value = null;
    editRepositories.value = [];
    editWorkflows.value = [];
    editBranches.value = [];
    
    // モーダルを即座に表示
    showEditModal.value = true;
    document.addEventListener('click', closeEditTooltip);
    
    // データの読み込みは非同期で実行（モーダル表示後に）
    (async () => {
        // 組織一覧を取得
        try {
            const response = await axios.get(route('api.github.organizations'));
            if (response.data && Array.isArray(response.data)) {
                editOrganizations.value = response.data;
                if (
                    editSelectedOrganization.value
                    && !editOrganizations.value.find(org => org.login === editSelectedOrganization.value)
                ) {
                    editSelectedOrganization.value = 'personal';
                }
            }
        } catch (error) {
            console.error('Failed to fetch organizations:', error);
            editOrganizations.value = [];
        }
        
        // 既存のプロジェクトの組織が選択されている場合、リポジトリを取得
        if (editSelectedOrganization.value) {
            await loadEditRepositories();
            // 既存のリポジトリが選択されている場合、ブランチも取得
            if (editSelectedRepository.value) {
                await onEditRepositoryChange();
            }
        }
    })();
};

const closeEditModal = () => {
    showEditModal.value = false;
    editForm.reset();
    editForm.clearErrors();
    editWorkflows.value = [];
    editLoadingWorkflows.value = false;
    document.removeEventListener('click', closeEditTooltip);
};

const loadEditRepositories = async () => {
    if (!editSelectedOrganization.value) return;

    try {
        const response = await axios.get(route('api.github.repositories'), {
            params: {
                organization: editSelectedOrganization.value
            }
        });
        
        if (response.data && Array.isArray(response.data)) {
            editRepositories.value = response.data;
            // 既存のリポジトリを選択
            if (currentProject.value?.github_repo) {
                const existingRepo = editRepositories.value.find(r => r.name === currentProject.value.github_repo);
                if (existingRepo) {
                    editSelectedRepository.value = existingRepo;
                }
            }
        } else {
            editRepositories.value = [];
        }
    } catch (error) {
        console.error('Failed to fetch repositories:', error);
        editRepositories.value = [];
    }
};

const onEditOrganizationChange = async () => {
    if (!editSelectedOrganization.value) {
        editSelectedRepository.value = null;
        editRepositories.value = [];
        editWorkflows.value = [];
        editForm.github_owner = '';
        editForm.github_repo = '';
        editForm.github_workflow_id = '';
        return;
    }

    editSelectedRepository.value = null;
    editRepositories.value = [];
    editWorkflows.value = [];
    editForm.github_owner = '';
    editForm.github_repo = '';
    editForm.github_workflow_id = '';

    await loadEditRepositories();
};

const toggleEditTooltip = (tooltipName, event) => {
    if (event) {
        event.stopPropagation();
    }
    if (editActiveTooltip.value === tooltipName) {
        editActiveTooltip.value = null;
    } else {
        editActiveTooltip.value = tooltipName;
    }
};

const closeEditTooltip = () => {
    editActiveTooltip.value = null;
};

const onEditRepositoryChange = async () => {
    if (!editSelectedRepository.value) {
        editForm.github_owner = '';
        editForm.github_repo = '';
        editForm.github_workflow_id = '';
        editWorkflows.value = [];
        editBranches.value = [];
        return;
    }

    const previousRepo = editForm.github_repo;
    editForm.github_owner = editSelectedRepository.value.owner.login;
    editForm.github_repo = editSelectedRepository.value.name;
    const repoChanged = previousRepo !== editForm.github_repo;
    if (repoChanged) {
        editForm.github_workflow_id = '';
    }
    
    // ワークフローとブランチを取得
    editWorkflows.value = [];
    editBranches.value = [];
    try {
        editLoadingWorkflows.value = true;
        const [workflowsResponse, branchesResponse] = await Promise.all([
            axios.get(route('api.github.workflows'), {
                params: {
                    owner: editForm.github_owner,
                    repo: editForm.github_repo
                }
            }),
            axios.get(route('api.github.branches'), {
                params: {
                    owner: editForm.github_owner,
                    repo: editForm.github_repo
                }
            })
        ]);
        
        editWorkflows.value = workflowsResponse.data.workflows || [];
        if (repoChanged && !editForm.github_workflow_id && editWorkflows.value.length > 0) {
            editForm.github_workflow_id = String(editWorkflows.value[0].id);
        }
        
        editBranches.value = branchesResponse.data.branches || [];
        const defaultBranch = branchesResponse.data.default_branch;
        
        // ブランチの自動選択（優先順位: main -> master -> default_branch -> 最初のブランチ）
        if (editBranches.value && editBranches.value.length > 0) {
            const mainBranch = editBranches.value.find(b => b.name === 'main');
            const masterBranch = editBranches.value.find(b => b.name === 'master');
            const defaultBranchObj = editBranches.value.find(b => b.name === defaultBranch);
            
            if (mainBranch) {
                editForm.github_branch = 'main';
            } else if (masterBranch) {
                editForm.github_branch = 'master';
            } else if (defaultBranchObj) {
                editForm.github_branch = defaultBranch;
            } else {
                editForm.github_branch = editBranches.value[0].name;
            }
        }
    } catch (error) {
        console.error('Failed to fetch repository details:', error);
        editBranches.value = [];
        editWorkflows.value = [];
    } finally {
        editLoadingWorkflows.value = false;
    }
};

const submitEditForm = () => {
    if (!currentProject.value) return;
    
    editForm.patch(route('projects.update', currentProject.value.id), {
        onSuccess: () => {
            closeEditModal();
            // プロジェクト一覧を再読み込み
            router.reload({ only: ['projects', 'selectedProject'] });
        }
    });
};

const guideSteps = [
    {
        title: '改善内容を入力',
        description: '改善したページや変更点を入力してください。\n\n例：\n- トップページのデザインを更新\n- 改善ページ: /about, /products/item-1\n- 変更点: ヘッダーの色を変更',
        action: '「改善内容（マークダウン対応）」のテキストエリアに入力してください'
    },
    {
        title: '承認URLを生成',
        description: '入力した内容を確認し、「承認URLを生成」ボタンをクリックしてください。\n\n改善ページURLは「/about」のようにパスだけ入力すると、テスト環境URLと自動的に結合されます。',
        action: '「承認URLを生成」ボタンをクリックしてください'
    },
    {
        title: 'URLをコピーして共有',
        description: '生成された承認URLをコピーして、クライアントに共有してください。\n\nクライアントがこのURLを開くと、改善内容と改善ページへのリンクが表示されます。',
        action: '「生成されたURLをコピー」ボタンをクリックして、Slackやメールで共有してください'
    },
    {
        title: 'クライアントが承認',
        description: 'クライアントが承認ページで改善内容を確認し、「承認してデプロイ」ボタンをクリックします。\n\n承認と同時にGitHub Actionsが実行され、本番環境にデプロイされます。',
        action: null
    },
    {
        title: 'リリースログで確認',
        description: 'デプロイの結果は「リリースログ」セクションで確認できます。\n\n承認時に共有した内容も記録されるため、過去の改善内容を振り返ることができます。',
        action: null
    }
];

const showGuide = () => {
    showGuideOverlay.value = true;
    currentStep.value = 0;
};

const closeGuide = () => {
    showGuideOverlay.value = false;
    // ガイドを見たことを記録
    localStorage.setItem('approve-deployment-guide-viewed', 'true');
};

const nextStep = () => {
    if (currentStep.value < guideSteps.length - 1) {
        currentStep.value++;
    }
};

const previousStep = () => {
    if (currentStep.value > 0) {
        currentStep.value--;
    }
};

// デプロイログの同期処理（非同期実行）
const syncDeployLogs = async () => {
    try {
        // 実行中または保留中のデプロイログがあるプロジェクトIDを取得
        const projectIds = props.projects
            .filter(project => {
                return project.deploy_logs && project.deploy_logs.some(
                    log => log.status === 'running' || log.status === 'pending'
                );
            })
            .map(project => project.id);

        if (projectIds.length === 0) {
            return; // 同期する必要がない
        }

        const response = await axios.post(route('api.deploy-logs.sync'), {
            project_ids: projectIds,
        });

        if (response.data && response.data.synced_logs) {
            // 同期されたログの情報をログに出力（デバッグ用）
            console.log('Deploy logs synced:', response.data.synced_logs);
            
            // ページを再読み込みして最新の状態を取得（必要に応じて）
            // ただし、頻繁に再読み込みするとUXが悪いので、必要に応じてInertia.reload()を使用
            if (response.data.synced_logs.length > 0) {
                // ステータスが変更された可能性があるので、少し遅延してから再読み込み
                setTimeout(() => {
                    router.reload({ only: ['projects', 'selectedProject'] });
                }, 2000);
            }
        }
    } catch (error) {
        console.error('Failed to sync deploy logs:', error);
        // エラーは無視（ページロードをブロックしない）
    }
};

// デプロイログ同期用のインターバルID
let syncInterval = null;

onMounted(() => {
    // デフォルトで最初のプロジェクトを選択
    if (props.selectedProject) {
        selectedProjectId.value = props.selectedProject.id;
    }
    
    // 初回訪問時にガイドを表示
    nextTick(() => {
        const guideViewed = localStorage.getItem('approve-deployment-guide-viewed');
        if (!guideViewed && props.selectedProject) {
            // 少し遅延させて表示（ページ読み込み後に）
            setTimeout(() => {
                showGuide();
            }, 1000);
        }
    });

    // デプロイログの同期を非同期で実行（ページロードをブロックしない）
    setTimeout(() => {
        syncDeployLogs();
    }, 500); // ページ読み込み後500ms後に実行

    // 定期的にデプロイログを同期（30秒ごと）
    syncInterval = setInterval(() => {
        syncDeployLogs();
    }, 30000);
});

// コンポーネントがアンマウントされたらインターバルをクリア
onUnmounted(() => {
    if (syncInterval) {
        clearInterval(syncInterval);
        syncInterval = null;
    }
});

const currentProject = computed(() => {
    if (!selectedProjectId.value) return props.selectedProject;
    return props.projects.find(p => p.id === selectedProjectId.value) || props.selectedProject;
});

const getMissingSetup = (project) => {
    if (!project) return [];
    const missing = [];
    if (!project.github_owner) missing.push('GitHub組織');
    if (!project.github_repo) missing.push('GitHubリポジトリ');
    if (!project.github_workflow_id) missing.push('ワークフロー');
    if (!project.github_branch) missing.push('ブランチ');
    if (!project.ssh_configured) missing.push('SSH設定');
    return missing;
};

const isProjectSetupIncomplete = (project) => getMissingSetup(project).length > 0;
const missingSetupForCurrentProject = computed(() => getMissingSetup(currentProject.value));
const isCurrentProjectSetupComplete = computed(() => missingSetupForCurrentProject.value.length === 0);

const getStatusTagClass = (isConfigured) => (
    isConfigured
        ? 'inline-flex items-center gap-1 px-2 py-0.5 text-xs text-emerald-700 bg-emerald-100 rounded-full'
        : 'inline-flex items-center gap-1 px-2 py-0.5 text-xs text-yellow-800 bg-yellow-100 rounded-full'
);

const getWorkflowStatusLabel = (project) => (
    project?.github_workflow_id ? 'ワークフロー設定済み' : 'ワークフロー未設定'
);

const getSshStatusLabel = (project) => (
    project?.ssh_configured ? 'SSH設定済み' : 'SSH未設定'
);

const selectProject = (projectId) => {
    selectedProjectId.value = projectId;
};

const generateApprovalUrl = async () => {
    if (!currentProject.value || !approvalMessage.value || !isCurrentProjectSetupComplete.value) return;
    
    // 再生成かどうかを判定
    const wasRegenerating = !!generatedApprovalUrl.value;
    isRegenerating.value = wasRegenerating;
    
    // 再生成の場合は、既存のサクセスメッセージを一度フェードアウト
    if (wasRegenerating && showSuccessMessage.value) {
        showSuccessMessage.value = false;
        // フェードアウトアニメーション完了を待つ（200ms + 少し余裕）
        await new Promise(resolve => setTimeout(resolve, 250));
    }
    
    generatingUrl.value = true;
    try {
        const response = await fetch(route('projects.generate-approval-url', currentProject.value.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                message: approvalMessage.value,
            }),
        });

        if (response.ok) {
            const data = await response.json();
            generatedApprovalUrl.value = data.approval_url;
            generatedUrlCopied.value = false;
            // サクセスメッセージを表示（再生成の場合はフェードイン）
            showSuccessMessage.value = true;
        } else {
            alert('承認URLの生成に失敗しました');
        }
    } catch (error) {
        console.error('Error generating approval URL:', error);
        alert('承認URLの生成に失敗しました');
    } finally {
        generatingUrl.value = false;
    }
};

const resetApprovalForm = () => {
    approvalMessage.value = '';
    generatedApprovalUrl.value = '';
    generatedUrlCopied.value = false;
    showSuccessMessage.value = false;
    isRegenerating.value = false;
};

const copyGeneratedApprovalUrl = async () => {
    if (!generatedApprovalUrl.value) return;
    
    try {
        await navigator.clipboard.writeText(generatedApprovalUrl.value);
        generatedUrlCopied.value = true;
        setTimeout(() => {
            generatedUrlCopied.value = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy:', err);
    }
};

const closeSuccessMessage = () => {
    showSuccessMessage.value = false;
};

const confirmDelete = () => {
    if (!currentProject.value) return;
    if (confirm(`プロジェクト「${currentProject.value.name}」を削除してもよろしいですか？\nこの操作は取り消せません。`)) {
        router.delete(route('projects.destroy', currentProject.value.id), {
            onSuccess: () => {
                // 削除後、最初のプロジェクトを選択
                if (props.projects.length > 1) {
                    const remainingProjects = props.projects.filter(p => p.id !== currentProject.value.id);
                    if (remainingProjects.length > 0) {
                        selectedProjectId.value = remainingProjects[0].id;
                    }
                }
            }
        });
    }
};

// 日時フォーマット関数
const formatDateTime = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}/${month}/${day} ${hours}:${minutes}`;
};

// 所要時間を計算（開始時刻と終了時刻の差分）
const formatDuration = (startedAt, finishedAt) => {
    if (!startedAt || !finishedAt) return '-';
    const start = new Date(startedAt);
    const end = new Date(finishedAt);
    const diffSeconds = Math.floor((end - start) / 1000);
    return formatDurationFromSeconds(diffSeconds);
};

// 経過時間を計算（開始時刻から現在までの差分）
const formatElapsedTime = (startedAt) => {
    if (!startedAt) return '-';
    const start = new Date(startedAt);
    const now = new Date();
    const diffSeconds = Math.floor((now - start) / 1000);
    return formatDurationFromSeconds(diffSeconds);
};

// 秒数を分・秒の形式に変換
const formatDurationFromSeconds = (seconds) => {
    if (!seconds) return '-';
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    if (mins > 0) {
        return `${mins}分${secs}秒`;
    }
    return `${secs}秒`;
};
</script>

<style scoped>
.markdown-editor {
    min-height: 300px;
}

.markdown-editor :deep(.md-editor) {
    border: 1px solid rgb(199, 210, 254);
    border-radius: 0.375rem;
}

.markdown-editor :deep(.md-editor-toolbar) {
    background-color: white;
    border-bottom: 1px solid rgb(199, 210, 254);
}

.markdown-editor :deep(.md-editor-content) {
    background-color: white;
}
</style>
