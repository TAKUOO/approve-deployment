<template>
    <Head title="プロジェクト一覧 - AutoRelease" />
    
    <AuthenticatedLayout>
        <div class="flex h-screen bg-indigo-50 relative">
            <!-- オーバーレイ（メニューが開いている時のみ表示） -->
            <div
                v-if="isMenuOpen"
                @click="toggleMenu"
                class="fixed inset-0 z-40 bg-black bg-opacity-50 md:hidden transition-opacity"
            ></div>

            <!-- ハンバーガーメニューボタン（モバイルのみ表示） -->
            <button
                @click="toggleMenu"
                class="fixed top-4 left-4 z-50 p-2 bg-white rounded-xl shadow-lg border border-gray-200 md:hidden transition-transform"
                :class="{ 'rotate-90': isMenuOpen }"
            >
                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path v-if="!isMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- 左サイドバー（フローティングカード風） -->
            <div
                @dblclick="toggleCollapse"
                :class="[
                    'fixed md:relative flex flex-col m-2 md:m-4 bg-white rounded-3xl shadow-lg border border-gray-200 flex-shrink-0 z-40 md:z-auto',
                    'transition-all duration-300 ease-in-out',
                    'top-20 md:top-auto h-[calc(100vh-5.5rem)] md:h-auto',
                    isMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
                    isCollapsed ? 'w-16 md:w-16' : 'w-56 md:w-64 lg:w-72'
                ]"
            >
                <!-- ロゴ（上部） -->
                <div @dblclick.stop :class="['border-b border-gray-200', isCollapsed ? 'px-2 py-4' : 'px-3 py-4 md:px-4 md:py-5']">
                    <Link :href="route('projects.index')" class="flex items-center shrink-0 justify-center">
                        <ApplicationLogo v-if="!isCollapsed" />
                        <div v-else class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                            <span class="text-xs font-bold text-indigo-600">A</span>
                        </div>
                    </Link>
                </div>

                <!-- 新規プロジェクトボタン -->
                <div @dblclick.stop :class="['border-b border-gray-200', isCollapsed ? 'px-2 py-2' : 'px-3 py-2 md:px-4 md:py-3']">
                    <button 
                        @click.stop="() => { openCreateModal(); if (window.innerWidth < 768) isMenuOpen.value = false; }"
                        :class="[
                            'flex items-center justify-center w-full font-semibold text-white rounded-xl bg-indigo-600 transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
                            isCollapsed ? 'px-2 py-2' : 'gap-2 px-3 py-2 md:px-4 md:py-2.5 text-xs md:text-sm'
                        ]"
                    >
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span v-if="!isCollapsed" class="truncate">新規プロジェクト</span>
                    </button>
                </div>

                <!-- プロジェクトリスト（スクロール可能） -->
                <div class="overflow-y-auto flex-1 py-2">
                    <div v-if="projects.length === 0" :class="['py-8 text-center', isCollapsed ? 'px-2' : 'px-3 md:px-4']">
                        <p v-if="!isCollapsed" class="text-xs md:text-sm text-gray-500">プロジェクトがありません</p>
                        <svg v-else class="w-5 h-5 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </div>
                    <div v-else :class="isCollapsed ? 'px-1.5' : 'px-1.5 md:px-2'">
                        <div
                            v-for="project in projects"
                            :key="project.id"
                            @click.stop="selectProjectWithMenuClose(project.id)"
                            @dblclick.stop
                            :class="[
                                'group relative flex items-center rounded-xl cursor-pointer transition-colors mb-1',
                                isCollapsed ? 'justify-center px-2 py-2.5' : 'gap-2 md:gap-3 px-2 md:px-3 py-2 md:py-2.5',
                                selectedProjectId === project.id 
                                    ? 'bg-indigo-50 text-indigo-700 font-semibold' 
                                    : 'text-gray-700 hover:bg-gray-50 font-medium'
                            ]"
                            :title="isCollapsed ? project.name : ''"
                        >
                            <!-- アクティブインジケーター -->
                            <div 
                                v-if="selectedProjectId === project.id && !isCollapsed"
                                class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 md:h-6 bg-indigo-600 rounded-r"
                            ></div>
                            
                            <!-- プロジェクトアイコン -->
                            <svg 
                                class="w-4 h-4 flex-shrink-0" 
                                :class="selectedProjectId === project.id ? 'text-indigo-600' : 'text-gray-500 group-hover:text-gray-700'"
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                            </svg>
                            
                            <!-- プロジェクト名 -->
                            <div v-if="!isCollapsed" class="flex-1 min-w-0">
                                <div class="flex gap-1.5 md:gap-2 items-center">
                                    <span class="block text-xs md:text-sm truncate">
                                        {{ project.name }}
                                    </span>
                                    <span
                                        v-if="isProjectSetupIncomplete(project)"
                                        class="flex-shrink-0 w-1.5 h-1.5 bg-yellow-400 rounded-full"
                                        title="設定未完了"
                                    ></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ユーザー設定（下部固定） -->
                <div @dblclick.stop class="flex flex-col flex-shrink-0 border-t border-gray-200">
                    <!-- 設定マニュアルリンク -->
                    <Link
                        :href="route('settings.index')"
                        @click.stop="() => { if (window.innerWidth < 768) isMenuOpen.value = false; }"
                        :class="[
                            'flex items-center font-medium text-gray-600 transition-colors hover:text-gray-900 hover:bg-gray-50 rounded-xl',
                            isCollapsed ? 'justify-center px-2 py-3' : 'gap-2 md:gap-3 px-3 py-2 md:px-4 md:py-3 text-xs md:text-sm mx-1 md:mx-0'
                        ]"
                        :title="isCollapsed ? '設定マニュアル' : ''"
                    >
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span v-if="!isCollapsed" class="truncate">設定マニュアル</span>
                    </Link>
                    
                    <!-- ユーザードロップダウン -->
                    <div class="relative w-full">
                        <Dropdown align="right" width="48" :bottom="true">
                            <template #trigger>
                                <button
                                    type="button"
                                    :class="[
                                        'flex items-center w-full font-medium text-gray-600 transition-colors hover:text-gray-900 hover:bg-gray-50 focus:outline-none rounded-xl',
                                        isCollapsed ? 'justify-center px-2 py-3' : 'justify-between px-3 py-2 md:px-4 md:py-3 text-xs md:text-sm mx-1 md:mx-0'
                                    ]"
                                    :title="isCollapsed ? $page.props.auth.user.name : ''"
                                >
                                    <span :class="['flex items-center', isCollapsed ? 'justify-center' : 'gap-2 md:gap-3 min-w-0']">
                                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span v-if="!isCollapsed" class="truncate">{{ $page.props.auth.user.name }}</span>
                                    </span>
                                    <svg
                                        v-if="!isCollapsed"
                                        class="w-3.5 h-3.5 md:w-4 md:h-4 flex-shrink-0 ml-1 md:ml-2"
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
            <div class="overflow-y-auto flex-1">
                <!-- プロジェクト選択中のローディング表示 -->
                <div v-if="selectingProject" class="flex justify-center items-center h-full">
                    <div class="text-center">
                        <div class="inline-block w-8 h-8 rounded-full border-4 border-indigo-600 animate-spin border-t-transparent"></div>
                        <p class="mt-4 text-sm text-gray-500">プロジェクトを読み込み中...</p>
                    </div>
                </div>
                <div v-else-if="currentProject" class="p-4 md:p-6 lg:p-10 mx-auto max-w-6xl">
                    <div>
                        <div class="pb-6">
                            <!-- プロジェクト名（タイトル）とアクションボタン -->
                            <div class="flex justify-between items-center mb-4">
                                <div class="flex gap-2 items-center">
                                    <h1 class="text-2xl font-semibold text-gray-900">{{ currentProject.name }}</h1>
                                </div>
                                <div class="flex gap-1">
                                    <button
                                        @click="openEditModal"
                                        :disabled="editForm.processing"
                                        class="p-2 text-gray-500 rounded-lg transition-colors hover:text-gray-700 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                        title="編集"
                                    >
                                        <svg v-if="!editForm.processing" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <div v-else class="w-5 h-5 rounded-full border-2 border-indigo-600 animate-spin border-t-transparent"></div>
                                    </button>
                                    <button
                                        @click="confirmDelete"
                                        :disabled="deletingProject"
                                        class="p-2 text-gray-500 rounded-lg transition-colors hover:text-red-600 hover:bg-red-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                        title="削除"
                                    >
                                        <svg v-if="!deletingProject" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <div v-else class="w-5 h-5 rounded-full border-2 border-red-600 animate-spin border-t-transparent"></div>
                                    </button>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
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
                            
                        </div>
                        <div class="bg-white rounded-3xl shadow-sm border border-gray-200">
                        <div class="p-6">
                            <div class="space-y-2">
                                <div class="mb-2 text-sm font-bold text-gray-700">URLを入れるだけでWebサイトを読み込み！コメントを付けてすぐに共有</div>
                                <div class="flex overflow-hidden rounded-2xl border border-indigo-300 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500">
                                    <input
                                        v-model="stagingUrlForm.staging_url"
                                        type="url"
                                        class="flex-1 px-3 py-3 min-w-0 bg-transparent border-0 text-md focus:ring-0 focus:outline-none"
                                        placeholder="https://example.com"
                                        @keyup.enter="submitImproveUrl"
                                    />
                                    <button
                                        v-if="currentProject?.id"
                                        type="button"
                                        class="flex-shrink-0 px-6 py-3 m-1.5 font-semibold text-white bg-indigo-500 rounded-xl transition-colors text-md hover:bg-indigo-600 disabled:opacity-60"
                                        :disabled="stagingUrlForm.processing"
                                        @click="goToImprovePage"
                                    >
                                        すぐ作る
                                    </button>
                                </div>
                                <p class="text-xs text-gray-400">Enterキーでも遷移します。</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="currentProject.deploy_logs && currentProject.deploy_logs.length > 0" class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200">
                                <div class="flex justify-between items-center p-4 border-b border-gray-200">
                                    <h3 class="font-semibold text-gray-900 text-sm">リリースログ</h3>
                                    <p class="text-xs font-medium text-gray-600">
                                        ブランチ：<span class="font-normal">{{ currentProject.github_branch || '未設定' }}</span>
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
                                                <div v-if="log.approval_message && expandedLogs[log.id]" class="p-4 mt-2 bg-gray-50 rounded-lg">
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
                    <div class="text-center">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                        <p class="text-gray-400 text-sm">プロジェクトを選択してください</p>
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
import { ref, computed, onMounted, onUnmounted } from 'vue';
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
import { marked } from 'marked';
import DOMPurify from 'dompurify';
import axios from 'axios';

const props = defineProps({
    projects: Array,
    selectedProject: Object,
});

const selectedProjectId = ref(null);
const selectingProject = ref(false); // プロジェクト選択中フラグ
const deletingProject = ref(false); // プロジェクト削除中フラグ
const expandedLogs = ref({});

// メニューの開閉状態（モバイルではデフォルトで閉じている）
const isMenuOpen = ref(false);

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
};

// メニューの折りたたみ状態（デスクトップではデフォルトで展開）
const isCollapsed = ref(false);

const toggleCollapse = () => {
    isCollapsed.value = !isCollapsed.value;
};

// プロジェクト選択時にメニューを閉じる（モバイルのみ）
const selectProjectWithMenuClose = async (projectId) => {
    selectingProject.value = true;
    try {
        selectedProjectId.value = projectId;
        // プロジェクト選択時にwebhookステータスを確認
        const project = props.projects.find(p => p.id === projectId);
        if (project && project.github_owner && project.github_repo) {
            await checkWebhookStatus(project);
        }
        // 少し遅延を入れてローディング表示を見せる（UX向上）
        await new Promise(resolve => setTimeout(resolve, 200));
        // モバイルサイズの場合のみメニューを閉じる
        if (window.innerWidth < 768) {
            isMenuOpen.value = false;
        }
    } finally {
        selectingProject.value = false;
    }
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
        onStart: () => {
            // フォーム送信開始時はform.processingが自動的にtrueになる
        },
        onSuccess: () => {
            closeEditModal();
            // プロジェクト一覧を再読み込み
            router.reload({ only: ['projects', 'selectedProject'] });
        },
        onError: () => {
            // エラー時も処理は継続
        }
    });
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

const toggleLogExpansion = (logId) => {
    expandedLogs.value[logId] = !expandedLogs.value[logId];
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
        onStart: () => {
            // フォーム送信開始時はform.processingが自動的にtrueになる
        },
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
        onStart: () => {
            // フォーム送信開始時はform.processingが自動的にtrueになる
        },
        onSuccess: () => {
            showStagingUrlModal.value = false;
            stagingUrlForm.reset();
            // プロジェクト一覧を再読み込み
            router.reload({ only: ['projects', 'selectedProject'] });
        }
    });
};

const submitImproveUrl = () => {
    if (!currentProject.value) return;
    goToImprovePage();
};

const goToImprovePage = () => {
    if (!currentProject.value) return;
    const nextUrl = (stagingUrlForm.staging_url || '').trim();
    if (!nextUrl) {
        alert('改善内容URLを入力してください。');
        return;
    }

    stagingUrlForm.staging_url = nextUrl;
    stagingUrlForm.patch(route('projects.update', currentProject.value.id), {
        onSuccess: () => {
            router.visit(route('projects.improve', currentProject.value.id));
        },
    });
};

const selectProject = async (projectId) => {
    selectingProject.value = true;
    try {
        selectedProjectId.value = projectId;
        // プロジェクト選択時にwebhookステータスを確認
        const project = props.projects.find(p => p.id === projectId);
        if (project && project.github_owner && project.github_repo) {
            await checkWebhookStatus(project);
        }
        // 少し遅延を入れてローディング表示を見せる（UX向上）
        await new Promise(resolve => setTimeout(resolve, 200));
    } finally {
        selectingProject.value = false;
    }
};

const confirmDelete = () => {
    if (!currentProject.value) return;
    if (confirm(`プロジェクト「${currentProject.value.name}」を削除してもよろしいですか？\nこの操作は取り消せません。`)) {
        deletingProject.value = true;
        router.delete(route('projects.destroy', currentProject.value.id), {
            onStart: () => {
                deletingProject.value = true;
            },
            onSuccess: () => {
                // 削除後、最初のプロジェクトを選択
                if (props.projects.length > 1) {
                    const remainingProjects = props.projects.filter(p => p.id !== currentProject.value.id);
                    if (remainingProjects.length > 0) {
                        selectedProjectId.value = remainingProjects[0].id;
                    }
                }
            },
            onFinish: () => {
                deletingProject.value = false;
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
