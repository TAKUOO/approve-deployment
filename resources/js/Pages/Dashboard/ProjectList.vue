<template>
    <Head title="プロジェクト一覧 - AutoRelease" />
    
    <AuthenticatedLayout>
        <div class="flex h-screen">
            <!-- 左サイドバー（固定） -->
            <div class="flex flex-col bg-white border-r border-gray-200 w-70">
                <!-- ロゴ（上部） -->
                <div class="px-4 py-6">
                    <Link :href="route('projects.index')" class="flex items-center shrink-0">
                        <ApplicationLogo />
                    </Link>
                </div>

                <!-- サイドバーヘッダー -->
                <div class="px-4 py-2">
                    <button 
                        @click="openCreateModal"
                        class="flex gap-1 justify-center items-center px-4 py-3 w-full text-sm font-bold text-indigo-600 rounded-3xl border border-indigo-100 border-solid transition duration-150 ease-in-out hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-400"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        プロジェクトの新規追加
                    </button>
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
                                'px-4 py-2 rounded-xl cursor-pointer transition-colors mb-1',
                                selectedProjectId === project.id 
                                    ? 'bg-indigo-50 text-gray-800 font-bold' 
                                    : 'text-gray-600 hover:bg-gray-50 font-semibold'
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

                <!-- ユーザー設定（下部固定） -->
                <div class="flex flex-shrink-0 justify-between items-center border-t border-gray-200">
                    <div class="relative w-full">
                        <Dropdown align="right" width="48" :bottom="true">
                            <template #trigger>
                                <button
                                        type="button"
                                        class="flex justify-between items-center px-2 py-4 w-full text-sm font-semibold leading-4 text-gray-500 bg-white rounded-md border border-transparent transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                    >
                                        {{ $page.props.auth.user.name }}
                                        <svg
                                            class="w-4 h-4 -me-0.5 ms-2"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>
                            </template>
                            <template #content>
                                <DropdownLink :href="route('profile.edit')">
                                    プロフィール
                                </DropdownLink>
                                <DropdownLink :href="route('settings.index')">
                                    設定マニュアル
                                </DropdownLink>
                                <DropdownLink
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                >
                                    ログアウト
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </div>

            <!-- 右メインコンテンツエリア -->
            <div class="overflow-y-auto flex-1 bg-indigo-50">
                <div v-if="currentProject" class="p-10 mx-auto max-w-6xl">
                    <div>
                        <div class="bg-white rounded-3xl">
                        <div class="px-6 py-4 text-gray-900">
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
                                <span 
                                    v-if="currentProject.github_owner && currentProject.github_repo"
                                    :class="getStatusTagClass(getWebhookStatus(currentProject).configured)"
                                    @click.stop="showWebhookModal = true"
                                    class="cursor-pointer"
                                    title="クリックして設定手順を確認"
                                >
                                    <svg v-if="getWebhookStatus(currentProject).configured" class="w-3.5 h-3.5 text-emerald-700" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                        <circle cx="8" cy="8" r="7" />
                                        <path d="M5 8.2l1.9 1.9L11 6" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <svg v-else class="w-3.5 h-3.5 text-yellow-700" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                        <circle cx="8" cy="8" r="7" />
                                        <path d="M8 4.2v5" stroke="#fff" stroke-width="1.5" stroke-linecap="round" />
                                        <circle cx="8" cy="11.8" r="1" fill="#fff" />
                                    </svg>
                                    {{ getWebhookStatusLabel(currentProject) }}
                                </span>
                            </div>
                            
                            <!-- その他の情報（横並び） -->
                            <div class="text-sm text-gray-900">
                                <span class="font-semibold text-gray-400">確認用URL：</span>
                                <a 
                                    v-if="currentProject.staging_url"
                                    :href="currentProject.staging_url" 
                                    target="_blank" 
                                    class="text-blue-600 hover:text-blue-800" 
                                    :title="currentProject.staging_url"
                                >
                                    {{ currentProject.staging_url }}
                                </a>
                                <button
                                    @click="openStagingUrlModal"
                                    class="ml-1 font-medium text-gray-500"
                                >
                                    {{ currentProject.staging_url ? '(編集)' : '登録されていません' }}
                                </button>
                            </div>
                        </div>

                        <!-- 改善内容入力と承認URL生成 -->
                        <div
                            v-if="isCurrentProjectSetupComplete"
                            ref="approvalSection"
                            class="px-4 py-4"
                        >
                            <!-- <div class="flex items-center mb-2">
                                <h2 class="text-xl font-bold text-gray-600">改善内容</h2>
                                <button
                                    @click="showGuide"
                                    class="p-2 text-gray-400 rounded-md transition-colors hover:text-indigo-600 hover:bg-indigo-50"
                                    title="使い方ガイドを見る"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                            </div> -->
                            <div class="space-y-3">
                                <div class="mb-4">
                                    <div class="tiptap-editor">
                                        <div class="tiptap-toolbar">
                                            <button type="button" @click="toggleHeading(1)" :class="{ 'is-active': editor?.isActive('heading', { level: 1 }) }">H1</button>
                                            <button type="button" @click="toggleHeading(2)" :class="{ 'is-active': editor?.isActive('heading', { level: 2 }) }">H2</button>
                                            <button type="button" @click="toggleHeading(3)" :class="{ 'is-active': editor?.isActive('heading', { level: 3 }) }">H3</button>
                                            <span class="divider"></span>
                                            <button type="button" @click="toggleBold" :class="{ 'is-active': editor?.isActive('bold') }"><strong>B</strong></button>
                                            <button type="button" @click="toggleItalic" :class="{ 'is-active': editor?.isActive('italic') }"><em>I</em></button>
                                            <button type="button" @click="toggleUnderline" :class="{ 'is-active': editor?.isActive('underline') }"><u>U</u></button>
                                            <button type="button" @click="toggleStrike" :class="{ 'is-active': editor?.isActive('strike') }"><s>S</s></button>
                                            <span class="divider"></span>
                                            <button type="button" @click="toggleBulletList" :class="{ 'is-active': editor?.isActive('bulletList') }">•</button>
                                            <button type="button" @click="toggleOrderedList" :class="{ 'is-active': editor?.isActive('orderedList') }">1.</button>
                                            <button type="button" @click="toggleBlockquote" :class="{ 'is-active': editor?.isActive('blockquote') }">“”</button>
                                            <button type="button" @click="toggleCodeBlock" :class="{ 'is-active': editor?.isActive('codeBlock') }">&lt;/&gt;</button>
                                            <button type="button" @click="toggleCode" :class="{ 'is-active': editor?.isActive('code') }">`</button>
                                            <button type="button" @click="setHorizontalRule">HR</button>
                                            <span class="divider"></span>
                                            <button type="button" @click="setLink" :class="{ 'is-active': editor?.isActive('link') }">Link</button>
                                            <button type="button" @click="addImage">Image</button>
                                            <span class="divider"></span>
                                            <button type="button" @click="undo" :disabled="!editor?.can().undo()">↶</button>
                                            <button type="button" @click="redo" :disabled="!editor?.can().redo()">↷</button>
                                        </div>
                                        <EditorContent :editor="editor" class="tiptap-content" />
                                    </div>
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
                                        :disabled="isApprovalMessageEmpty || generatingUrl"
                                        class="px-6 py-2 text-sm text-white bg-indigo-600 rounded-md hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
                                    >
                                        {{ generatingUrl ? '生成中...' : (generatedApprovalUrl ? '承認URLを再生成' : '承認URLを生成') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="px-4 py-4">
                            <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                <div class="flex justify-between">
                                        <h3 class="text-sm font-bold text-yellow-800">
                                            <span
                                                v-for="item in missingSetupForCurrentProject"
                                                :key="item"
                                            >
                                            {{ item }}/</span>が未完了のため、本機能をご利用できません。
                                        </h3>
                                        <button
                                                v-if="missingSetupForCurrentProject.includes('GitHub Webhook') && currentProject.github_owner && currentProject.github_repo"
                                                @click="showWebhookModal = true"
                                                class="inline-flex gap-1 items-center text-xs font-bold text-yellow-800 hover:text-yellow-900"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                設定マニュアルへ
                                        </button>
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

                        <div v-if="currentProject.deploy_logs && currentProject.deploy_logs.length > 0" class="mt-8 bg-white rounded-2xl">
                                <div class="flex justify-between items-center p-4 border-b border-gray-200">
                                    <h3 class="font-bold text-gray-700 text-md">リリースログ</h3>
                                    <p class="text-xs font-bold text-gray-600">
                                        ブランチ：<span class="font-medium">{{ currentProject.github_branch || '未設定' }}</span>
                                    </p>
                                </div>
                                <div>
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
                                                    <div class="max-w-none text-sm text-gray-600 prose prose-sm" v-html="formatApprovalMessage(log.approval_message.message)"></div>
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

        <ProjectEditModal
            :show="showEditModal"
            :form="editForm"
            v-model:selected-organization="editSelectedOrganization"
            :organizations="editOrganizations"
            v-model:selected-repository="editSelectedRepository"
            :repositories="editRepositories"
            :workflows="editWorkflows"
            :branches="editBranches"
            :active-tooltip="editActiveTooltip"
            :loading-workflows="editLoadingWorkflows"
            @close="closeEditModal"
            @submit="submitEditForm"
            @toggle-tooltip="toggleEditTooltip"
            @organization-change="onEditOrganizationChange"
            @repository-change="onEditRepositoryChange"
        />
    </AuthenticatedLayout>

    <WebhookSetupModal
        :show="showWebhookModal"
        :webhook-url="getWebhookStatus(currentProject).webhook_url"
        :can-check="!!(currentProject && currentProject.github_owner && currentProject.github_repo)"
        @close="showWebhookModal = false"
        @check="checkWebhookStatus(currentProject); showWebhookModal = false"
    />

    <StagingUrlModal
        :show="showStagingUrlModal"
        :form="stagingUrlForm"
        :has-staging-url="!!(currentProject && currentProject.staging_url)"
        @close="showStagingUrlModal = false"
        @submit="submitStagingUrl"
    />

    <ProjectCreateModal
        :show="showCreateModal"
        :form="createForm"
        v-model:selected-organization="createSelectedOrganization"
        :organizations="createOrganizations"
        v-model:selected-repository="createSelectedRepository"
        :repositories="createRepositories"
        :workflows="createWorkflows"
        :branches="createBranches"
        :active-tooltip="createActiveTooltip"
        :loading-workflows="createLoadingWorkflows"
        :loading-organizations="createLoadingOrganizations"
        @close="closeCreateModal"
        @submit="submitCreateForm"
        @toggle-tooltip="toggleCreateTooltip"
        @organization-change="onCreateOrganizationChange"
        @repository-change="onCreateRepositoryChange"
    />
</template>

<script setup>
import { ref, computed, onMounted, nextTick, Transition, onUnmounted } from 'vue';
import { Head, router, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppFooter from '@/Components/AppFooter.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ProjectEditModal from '@/Components/Dashboard/ProjectEditModal.vue';
import ProjectCreateModal from '@/Components/Dashboard/ProjectCreateModal.vue';
import WebhookSetupModal from '@/Components/Dashboard/WebhookSetupModal.vue';
import StagingUrlModal from '@/Components/Dashboard/StagingUrlModal.vue';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import LinkExtension from '@tiptap/extension-link';
import Image from '@tiptap/extension-image';
import Underline from '@tiptap/extension-underline';
import Placeholder from '@tiptap/extension-placeholder';
import { marked } from 'marked';
import DOMPurify from 'dompurify';
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
const isConvertingPaths = ref(false); // パス変換中フラグ

const editor = useEditor({
    extensions: [
        StarterKit.configure({
            heading: {
                levels: [1, 2, 3],
            },
            bulletList: {
                keepMarks: true,
            },
            orderedList: {
                keepMarks: true,
            },
        }),
        Underline,
        LinkExtension.configure({
            openOnClick: false,
            autolink: true,
            linkOnPaste: true,
            HTMLAttributes: {
                target: '_blank',
                rel: 'noopener noreferrer',
            },
        }),
        Image.configure({
            inline: false,
        }),
        Placeholder.configure({
            placeholder: '改善内容の入力...',
        }),
    ],
    content: '',
    onUpdate: ({ editor: editorInstance }) => {
        approvalMessage.value = editorInstance.getHTML();
        
        // パスの自動リンク化処理（無限ループを避けるため、変換中はスキップ）
        if (isConvertingPaths.value || !currentProject.value || !currentProject.value.staging_url) return;
        
        const stagingUrl = currentProject.value.staging_url.replace(/\/$/, ''); // 末尾のスラッシュを削除
        
        // エディター全体を走査してパスを検出
        const pathsToConvert = [];
        
        editorInstance.state.doc.descendants((node, pos) => {
            if (node.isText && node.text) {
                const text = node.text;
                // パスパターンを検出（/で始まり、空白、改行、句読点、または行末まで）
                // 例: /top, /about, /products/item-1
                const pathPattern = /(?:^|[\s\n,。、])(\/[\w\-/]+(?:\?[\w\-=&]+)?(?:#[\w\-]+)?)(?=[\s\n,。、]|$)/g;
                
                let match;
                while ((match = pathPattern.exec(text)) !== null) {
                    const path = match[1];
                    const matchIndex = match.index + (match[0].startsWith('/') ? 0 : 1); // 前の文字を考慮
                    const absoluteFrom = pos + matchIndex;
                    const absoluteTo = absoluteFrom + path.length;
                    
                    // 既にリンクになっているかチェック
                    const marksAt = node.marks;
                    const isAlreadyLink = marksAt.some(mark => mark.type.name === 'link');
                    
                    if (!isAlreadyLink) {
                        pathsToConvert.push({
                            path,
                            from: absoluteFrom,
                            to: absoluteTo,
                        });
                    }
                }
            }
        });
        
        // パスが見つかった場合、リンクに変換（逆順にソートして後ろから変換）
        if (pathsToConvert.length > 0) {
            isConvertingPaths.value = true;
            pathsToConvert.sort((a, b) => b.from - a.from);
            
            // 一度にすべてのパスを変換するのではなく、次のフレームで処理
            nextTick(() => {
                pathsToConvert.forEach(({ path, from: pathFrom, to: pathTo }) => {
                    const fullUrl = stagingUrl + path;
                    const displayText = stagingUrl + path; // 表示テキスト: ステージングURL + パス
                    
                    // 既にリンクになっていないか再確認
                    const marksAt = editorInstance.state.doc.resolve(pathFrom).marks();
                    const isAlreadyLink = marksAt.some(mark => mark.type.name === 'link');
                    
                    if (!isAlreadyLink) {
                        // テキストを「ステージングURL + パス」に置き換えてリンクを設定
                        const linkHtml = `<a href="${fullUrl}" target="_blank" rel="noopener noreferrer">${displayText}</a>`;
                        editorInstance.chain()
                            .setTextSelection({ from: pathFrom, to: pathTo })
                            .deleteSelection()
                            .insertContent(linkHtml)
                            .run();
                    }
                });
                
                // 変換完了後、フラグをリセット
                setTimeout(() => {
                    isConvertingPaths.value = false;
                }, 100);
            });
        }
    },
});

const isApprovalMessageEmpty = computed(() => !editor.value || editor.value.isEmpty);

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

const toggleHeading = (level) => {
    if (!editor.value) return;
    editor.value.chain().focus().toggleHeading({ level }).run();
};

const toggleBold = () => {
    if (!editor.value) return;
    editor.value.chain().focus().toggleBold().run();
};

const toggleItalic = () => {
    if (!editor.value) return;
    editor.value.chain().focus().toggleItalic().run();
};

const toggleUnderline = () => {
    if (!editor.value) return;
    editor.value.chain().focus().toggleUnderline().run();
};

const toggleStrike = () => {
    if (!editor.value) return;
    editor.value.chain().focus().toggleStrike().run();
};

const toggleBulletList = () => {
    if (!editor.value) return;
    editor.value.chain().focus().toggleBulletList().run();
};

const toggleOrderedList = () => {
    if (!editor.value) return;
    editor.value.chain().focus().toggleOrderedList().run();
};

const toggleBlockquote = () => {
    if (!editor.value) return;
    editor.value.chain().focus().toggleBlockquote().run();
};

const toggleCodeBlock = () => {
    if (!editor.value) return;
    editor.value.chain().focus().toggleCodeBlock().run();
};

const toggleCode = () => {
    if (!editor.value) return;
    editor.value.chain().focus().toggleCode().run();
};

const setHorizontalRule = () => {
    if (!editor.value) return;
    editor.value.chain().focus().setHorizontalRule().run();
};

const setLink = () => {
    if (!editor.value) return;
    const previousUrl = editor.value.getAttributes('link').href;
    const url = window.prompt('リンクURLを入力してください', previousUrl || '');
    if (url === null) return;
    if (url === '') {
        editor.value.chain().focus().unsetLink().run();
        return;
    }
    editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
};

const addImage = () => {
    if (!editor.value) return;
    const url = window.prompt('画像URLを入力してください');
    if (!url) return;
    editor.value.chain().focus().setImage({ src: url }).run();
};

const undo = () => {
    if (!editor.value) return;
    editor.value.chain().focus().undo().run();
};

const redo = () => {
    if (!editor.value) return;
    editor.value.chain().focus().redo().run();
};

// 編集モーダル関連
const showEditModal = ref(false);
const editForm = useForm({
    name: '',
    server_dir: '/public_html/',
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
    editForm.server_dir = currentProject.value.server_dir || '/public_html/';
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

    // 初期プロジェクトのwebhookステータスを確認
    if (props.selectedProject && props.selectedProject.github_owner && props.selectedProject.github_repo) {
        setTimeout(() => {
            checkWebhookStatus(props.selectedProject);
        }, 1000); // ページ読み込み後1秒後に実行
    }
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
    // webhookのチェック（GitHubリポジトリが設定されている場合のみ）
    if (project.github_owner && project.github_repo) {
        const webhookStatus = getWebhookStatus(project);
        if (!webhookStatus.configured) {
            missing.push('GitHub Webhook');
        }
    }
    return missing;
};

const isProjectSetupIncomplete = (project) => getMissingSetup(project).length > 0;
const missingSetupForCurrentProject = computed(() => getMissingSetup(currentProject.value));

// 承認URL生成に必要な必須項目のみをチェック（webhookは除外）
const getMissingCriticalSetup = (project) => {
    if (!project) return [];
    const missing = [];
    if (!project.github_owner) missing.push('GitHub組織');
    if (!project.github_repo) missing.push('GitHubリポジトリ');
    if (!project.github_workflow_id) missing.push('ワークフロー');
    if (!project.github_branch) missing.push('ブランチ');
    if (!project.ssh_configured) missing.push('SSH設定');
    // webhookはクリティカルではないため除外
    return missing;
};

const isCurrentProjectSetupComplete = computed(() => {
    const criticalMissing = getMissingCriticalSetup(currentProject.value);
    return criticalMissing.length === 0;
});

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

const isHtmlMessage = (message) => /<\/?[a-z][\s\S]*>/i.test(message);

const formatApprovalMessage = (message) => {
    if (!message) return '';
    if (isHtmlMessage(message)) {
        return DOMPurify.sanitize(message, {
            ADD_ATTR: ['target', 'rel'],
        });
    }
    const html = marked.parse(message);
    return DOMPurify.sanitize(html, {
        ADD_ATTR: ['target', 'rel'],
    });
};

// Webhookステータス管理
const webhookStatus = ref({});
const checkingWebhook = ref(false);
const showWebhookModal = ref(false);

// ステージングURL登録モーダル
const showStagingUrlModal = ref(false);
const stagingUrlForm = useForm({
    staging_url: '',
});

// プロジェクト作成モーダル
const showCreateModal = ref(false);
const createForm = useForm({
    name: '',
    server_dir: '/public_html/',
    github_owner: '',
    github_repo: '',
    github_workflow_id: '',
    github_branch: '',
    ssh_configured: false,
});
const createOrganizations = ref([]);
const createRepositories = ref([]);
const createWorkflows = ref([]);
const createBranches = ref([]);
const createSelectedOrganization = ref('');
const createSelectedRepository = ref(null);
const createActiveTooltip = ref(null);
const createLoadingWorkflows = ref(false);
const createLoadingOrganizations = ref(false);

const openCreateModal = async () => {
    showCreateModal.value = true;
    document.addEventListener('click', closeCreateTooltip);
    
    // 組織一覧を取得
    createLoadingOrganizations.value = true;
    try {
        const response = await axios.get(route('api.github.organizations'));
        if (response.data && Array.isArray(response.data)) {
            createOrganizations.value = response.data;
        } else {
            createOrganizations.value = [];
        }
    } catch (error) {
        console.error('Failed to fetch organizations:', error);
        createOrganizations.value = [];
    } finally {
        createLoadingOrganizations.value = false;
    }
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    createForm.reset();
    createForm.clearErrors();
    createSelectedOrganization.value = '';
    createSelectedRepository.value = null;
    createRepositories.value = [];
    createWorkflows.value = [];
    createBranches.value = [];
    document.removeEventListener('click', closeCreateTooltip);
};

const toggleCreateTooltip = (tooltipName, event) => {
    if (event) {
        event.stopPropagation();
    }
    if (createActiveTooltip.value === tooltipName) {
        createActiveTooltip.value = null;
    } else {
        createActiveTooltip.value = tooltipName;
    }
};

const closeCreateTooltip = () => {
    createActiveTooltip.value = null;
};

const loadCreateRepositories = async () => {
    if (!createSelectedOrganization.value) return;

    try {
        const response = await axios.get(route('api.github.repositories'), {
            params: {
                organization: createSelectedOrganization.value
            }
        });
        
        if (response.data && Array.isArray(response.data)) {
            createRepositories.value = response.data;
        } else {
            createRepositories.value = [];
        }
    } catch (error) {
        console.error('Failed to fetch repositories:', error);
        createRepositories.value = [];
    }
};

const onCreateOrganizationChange = async () => {
    if (!createSelectedOrganization.value) {
        createSelectedRepository.value = null;
        createRepositories.value = [];
        createWorkflows.value = [];
        createBranches.value = [];
        createForm.github_owner = '';
        createForm.github_repo = '';
        createForm.github_workflow_id = '';
        createForm.github_branch = '';
        return;
    }

    createSelectedRepository.value = null;
    createRepositories.value = [];
    createWorkflows.value = [];
    createBranches.value = [];
    createForm.github_owner = '';
    createForm.github_repo = '';
    createForm.github_workflow_id = '';
    createForm.github_branch = '';

    await loadCreateRepositories();
};

const onCreateRepositoryChange = async () => {
    if (!createSelectedRepository.value) return;

    createForm.github_owner = createSelectedRepository.value.owner.login;
    createForm.github_repo = createSelectedRepository.value.name;
    createForm.name = `${createSelectedRepository.value.owner.login}/${createSelectedRepository.value.name}`;
    
    createForm.github_workflow_id = '';
    createForm.github_branch = '';
    createWorkflows.value = [];
    createBranches.value = [];
    createLoadingWorkflows.value = true;

    try {
        const [workflowsResponse, branchesResponse] = await Promise.all([
            axios.get(route('api.github.workflows'), {
                params: {
                    owner: createForm.github_owner,
                    repo: createForm.github_repo
                }
            }),
            axios.get(route('api.github.branches'), {
                params: {
                    owner: createForm.github_owner,
                    repo: createForm.github_repo
                }
            })
        ]);

        createWorkflows.value = workflowsResponse.data.workflows || [];
        createBranches.value = branchesResponse.data.branches || [];
        const defaultBranch = branchesResponse.data.default_branch;
        
        if (createWorkflows.value && createWorkflows.value.length > 0) {
            createForm.github_workflow_id = String(createWorkflows.value[0].id);
        }
        
        if (createBranches.value && createBranches.value.length > 0) {
            const mainBranch = createBranches.value.find(b => b.name === 'main');
            const masterBranch = createBranches.value.find(b => b.name === 'master');
            const defaultBranchObj = createBranches.value.find(b => b.name === defaultBranch);
            
            if (mainBranch) {
                createForm.github_branch = mainBranch.name;
            } else if (masterBranch) {
                createForm.github_branch = masterBranch.name;
            } else if (defaultBranchObj) {
                createForm.github_branch = defaultBranchObj.name;
            } else {
                createForm.github_branch = createBranches.value[0].name;
            }
        }
    } catch (error) {
        console.error('Failed to fetch repository details:', error);
    } finally {
        createLoadingWorkflows.value = false;
    }
};

const submitCreateForm = () => {
    if (!createSelectedOrganization.value) {
        alert('GitHub組織を選択してください。');
        return;
    }
    
    if (!createSelectedRepository.value) {
        alert('GitHubリポジトリを選択してください。');
        return;
    }
    
    createForm.post(route('projects.store'), {
        preserveState: false,
        preserveScroll: false,
        onSuccess: () => {
            // モーダルを閉じる（リダイレクトはInertiaが自動的に処理）
            closeCreateModal();
        },
        onError: (errors) => {
            console.error('Project creation errors:', errors);
        }
    });
};

const checkWebhookStatus = async (project) => {
    if (!project || !project.github_owner || !project.github_repo) {
        webhookStatus.value[project?.id] = { configured: false, error: 'GitHub repository not configured' };
        return;
    }

    checkingWebhook.value = true;
    try {
        const response = await axios.get(route('api.github.webhooks.check', project.id));
        webhookStatus.value[project.id] = {
            configured: response.data.configured || false,
            webhook_url: response.data.webhook_url || '',
            error: response.data.error || null,
        };
    } catch (error) {
        console.error('Failed to check webhook status:', error);
        webhookStatus.value[project.id] = {
            configured: false,
            error: error.response?.data?.error || 'Failed to check webhook status',
        };
    } finally {
        checkingWebhook.value = false;
    }
};

const getWebhookStatus = (project) => {
    if (!project) return { configured: false };
    return webhookStatus.value[project.id] || { configured: false };
};

const getWebhookStatusLabel = (project) => {
    const status = getWebhookStatus(project);
    return status.configured ? '完了検知設定済み' : '完了検知未設定';
};

const openStagingUrlModal = () => {
    if (!currentProject.value) return;
    stagingUrlForm.staging_url = currentProject.value.staging_url || '';
    showStagingUrlModal.value = true;
};

const submitStagingUrl = () => {
    if (!currentProject.value) return;
    
    stagingUrlForm.patch(route('projects.update', currentProject.value.id), {
        onSuccess: () => {
            showStagingUrlModal.value = false;
            stagingUrlForm.reset();
            // プロジェクト一覧を再読み込み
            router.reload({ only: ['projects', 'selectedProject'] });
        }
    });
};

const selectProject = (projectId) => {
    selectedProjectId.value = projectId;
    // プロジェクト選択時にwebhookステータスを確認
    const project = props.projects.find(p => p.id === projectId);
    if (project && project.github_owner && project.github_repo) {
        checkWebhookStatus(project);
    }
};

const generateApprovalUrl = async () => {
    if (!currentProject.value || isApprovalMessageEmpty.value || !isCurrentProjectSetupComplete.value) return;
    
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
    if (editor.value) {
        editor.value.commands.clearContent(true);
    }
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
.tiptap-editor {
    border: 1px solid rgb(199, 210, 254);
    border-radius: 0.5rem;
    background-color: white;
    overflow: hidden;
}

.tiptap-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    padding: 0.5rem;
    border-bottom: 1px solid rgb(199, 210, 254);
    background-color: white;
}

.tiptap-toolbar button {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    color: #475569;
    border-radius: 0.375rem;
    border: 1px solid transparent;
}

.tiptap-toolbar button.is-active {
    background-color: #e0e7ff;
    color: #4338ca;
    border-color: #c7d2fe;
}

.tiptap-toolbar button:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.tiptap-toolbar .divider {
    width: 1px;
    height: 1.25rem;
    background-color: #e2e8f0;
    margin: 0 0.25rem;
}

.tiptap-content :deep(.ProseMirror) {
    min-height: 260px;
    padding: 0.75rem;
    outline: none;
}

.tiptap-content :deep(.ProseMirror p.is-editor-empty:first-child::before) {
    color: #94a3b8;
    content: attr(data-placeholder);
    float: left;
    height: 0;
    pointer-events: none;
    white-space: pre-wrap;
}
</style>
