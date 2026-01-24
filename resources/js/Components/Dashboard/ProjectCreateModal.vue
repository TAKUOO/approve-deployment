<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            class="flex overflow-y-auto fixed inset-0 z-50 justify-center items-start p-4 bg-black bg-opacity-50"
            @click.self="emit('close')"
        >
            <div class="relative mx-auto my-8 w-full max-w-3xl bg-white rounded-3xl shadow-3xl">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">プロジェクトを作成する</h2>
                        <button
                            @click="emit('close')"
                            class="p-2 text-gray-400 rounded-md transition-colors hover:text-gray-600 hover:bg-gray-100"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <form @submit.prevent="emit('submit')" class="space-y-6">
                        <div>
                            <div class="flex gap-2 items-center">
                                <InputLabel for="create_organization" value="GitHub 組織" class="font-bold text-gray-900" />
                                <div class="relative">
                                    <button
                                        type="button"
                                        @click.stop="emit('toggleTooltip', 'organization', $event)"
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
                                id="create_organization"
                                v-model="selectedOrganizationModel"
                                class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option value="" disabled>組織を選択してください</option>
                                <option value="personal">個人リポジトリ</option>
                                <option v-for="org in organizations" :key="org.id" :value="org.login">
                                    {{ org.login }}
                                </option>
                            </select>
                            <p v-if="organizations.length === 0 && !loadingOrganizations" class="mt-1 text-sm text-yellow-600">
                                ※ 組織が見つかりませんでした。組織に所属している場合、GitHubでアプリの権限を確認してください。
                            </p>
                            <InputError class="mt-2" :message="form.errors.github_owner" />
                        </div>

                        <div>
                            <div class="flex gap-2 items-center">
                                <InputLabel for="create_repository" value="GitHub リポジトリ" class="font-bold text-gray-900" />
                                <div class="relative">
                                    <button
                                        type="button"
                                        @click.stop="emit('toggleTooltip', 'repository', $event)"
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
                                id="create_repository"
                                v-model="selectedRepositoryModel"
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
                                <InputLabel for="create_github_workflow_id" value="GitHub ワークフロー" class="font-bold text-gray-900" />
                                <div class="relative">
                                    <button
                                        type="button"
                                        @click.stop="emit('toggleTooltip', 'workflow', $event)"
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
                                        承認時に実行されるGitHub Actionsのワークフローを選択してください。
                                    </div>
                                </div>
                            </div>
                            <select
                                id="create_github_workflow_id"
                                v-model="form.github_workflow_id"
                                class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :disabled="loadingWorkflows || workflows.length === 0"
                            >
                                <option value="">後で設定する</option>
                                <option v-for="workflow in workflows" :key="workflow.id" :value="String(workflow.id)">
                                    {{ workflow.name }} ({{ workflow.path }})
                                </option>
                            </select>
                            <p v-if="!loadingWorkflows && workflows.length === 0" class="mt-1 text-sm text-yellow-600">
                                ワークフローが見つかりませんでした。GitHub側でワークフローを作成してください。
                            </p>
                            <InputError class="mt-2" :message="form.errors.github_workflow_id" />
                        </div>

                        <div v-if="selectedRepository">
                            <div class="flex gap-2 items-center">
                                <InputLabel for="create_github_branch" value="GitHub ブランチ" class="font-bold text-gray-900" />
                                <div class="relative">
                                    <button
                                        type="button"
                                        @click.stop="emit('toggleTooltip', 'branch', $event)"
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
                                        本番環境にアップロードするブランチ名です。通常は <code class="px-1 py-0.5 text-xs bg-gray-100 rounded">main</code> を使用します。
                                    </div>
                                </div>
                            </div>
                            <input
                                type="text"
                                id="create_github_branch"
                                v-model="form.github_branch"
                                list="create-branch-list"
                                class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="main"
                            />
                            <datalist id="create-branch-list">
                                <option v-for="branch in branches" :key="branch.name" :value="branch.name">
                                    {{ branch.name }}
                                </option>
                            </datalist>
                            <InputError class="mt-2" :message="form.errors.github_branch" />
                        </div>

                        <div class="flex gap-3 justify-end pt-4 border-t border-gray-200">
                            <button
                                type="button"
                                @click="emit('close')"
                                class="px-4 py-2 text-sm text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
                            >
                                キャンセル
                            </button>
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                <span v-if="form.processing" class="flex gap-2 items-center">
                                    <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                    <span>作成中...</span>
                                </span>
                                <span v-else>プロジェクトを作成</span>
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { computed, watch } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    form: {
        type: Object,
        required: true,
    },
    selectedOrganization: {
        type: String,
        default: '',
    },
    organizations: {
        type: Array,
        default: () => [],
    },
    selectedRepository: {
        type: Object,
        default: null,
    },
    repositories: {
        type: Array,
        default: () => [],
    },
    workflows: {
        type: Array,
        default: () => [],
    },
    branches: {
        type: Array,
        default: () => [],
    },
    activeTooltip: {
        type: String,
        default: null,
    },
    loadingWorkflows: {
        type: Boolean,
        default: false,
    },
    loadingOrganizations: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits([
    'close',
    'submit',
    'toggleTooltip',
    'organizationChange',
    'repositoryChange',
    'update:selectedOrganization',
    'update:selectedRepository',
]);

const selectedOrganizationModel = computed({
    get: () => (typeof props.selectedOrganization === 'string' ? props.selectedOrganization : ''),
    set: (value) => emit('update:selectedOrganization', value),
});

const selectedRepositoryModel = computed({
    get: () => props.selectedRepository,
    set: (value) => emit('update:selectedRepository', value),
});

// 組織が変更されたときにリポジトリを読み込む
watch(() => props.selectedOrganization, (newValue, oldValue) => {
    if (newValue !== oldValue && newValue) {
        emit('organizationChange');
    }
});

// リポジトリが変更されたときにワークフローとブランチを読み込む
watch(() => props.selectedRepository, (newValue, oldValue) => {
    if (newValue !== oldValue && newValue) {
        emit('repositoryChange');
    }
});
</script>
