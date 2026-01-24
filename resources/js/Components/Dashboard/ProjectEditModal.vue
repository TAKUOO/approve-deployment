<template>
    <div
        v-if="show"
        class="flex overflow-y-auto fixed inset-0 z-50 justify-center items-start p-4 bg-black bg-opacity-50"
        @click.self="emit('close')"
    >
        <div class="relative mx-auto my-8 w-full max-w-3xl bg-white rounded-lg shadow-xl">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-900">プロジェクトを編集する</h3>
                    <button
                        @click="emit('close')"
                        class="p-1 text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form @submit.prevent="emit('submit')" class="space-y-6">
                    <div>
                        <div class="flex gap-2 items-center">
                            <InputLabel for="edit_name" value="プロジェクト名" class="font-bold text-gray-900" />
                            <div class="relative">
                                <button
                                    type="button"
                                    @click.stop="emit('toggleTooltip', 'project_name', $event)"
                                    class="text-gray-400 transition-colors hover:text-gray-600"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                                <div
                                    v-if="activeTooltip === 'project_name'"
                                    class="absolute left-0 z-10 p-3 mt-2 w-72 text-sm text-gray-700 bg-white rounded-lg border border-gray-200 shadow-lg"
                                >
                                    未入力の場合はGitHubリポジトリ名が使用されます。
                                </div>
                            </div>
                        </div>
                        <TextInput
                            id="edit_name"
                            v-model="form.name"
                            type="text"
                            class="block mt-1 w-full"
                            placeholder="プロジェクト名（任意）"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <div class="flex gap-2 items-center">
                            <InputLabel for="edit_organization" value="GitHub 組織" class="font-bold text-gray-500" />
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
                                    プロジェクト作成後に変更することはできません。
                                </div>
                            </div>
                        </div>
                        <select
                            id="edit_organization"
                            v-model="selectedOrganizationModel"
                            disabled
                            class="block mt-1 w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-gray-500 cursor-not-allowed"
                        >
                            <option value="">後で設定する</option>
                            <option value="personal">個人リポジトリ</option>
                            <option v-for="org in organizations" :key="org.id" :value="org.login">
                                {{ org.login }}
                            </option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">プロジェクト作成後に変更することはできません</p>
                        <InputError class="mt-2" :message="form.errors.github_owner" />
                    </div>

                    <div v-if="selectedOrganization">
                        <div class="flex gap-2 items-center">
                            <InputLabel for="edit_repository" value="GitHub リポジトリ" class="font-bold text-gray-500" />
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
                                    class="absolute left-0 z-10 p-3 mt-2 w-72 text-sm text-gray-700 bg-white rounded-lg border border-gray-200 shadow-lg"
                                >
                                    プロジェクト作成後に変更することはできません。
                                </div>
                            </div>
                        </div>
                        <select
                            id="edit_repository"
                            v-model="selectedRepositoryModel"
                            disabled
                            class="block mt-1 w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-gray-500 cursor-not-allowed"
                        >
                            <option :value="null" disabled>
                                {{ repositories.length === 0 ? 'リポジトリを読み込み中...' : 'リポジトリを選択してください' }}
                            </option>
                            <option v-for="repo in repositories" :key="repo.id" :value="repo">
                                {{ repo.full_name }}
                            </option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">プロジェクト作成後に変更することはできません</p>
                        <InputError class="mt-2" :message="form.errors.github_repo" />
                    </div>

                    <div v-if="selectedRepository">
                        <div class="flex gap-2 items-center">
                            <InputLabel for="edit_github_workflow_id" value="GitHub ワークフロー" class="font-bold text-gray-900" />
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
                            id="edit_github_workflow_id"
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
                            <InputLabel for="edit_github_branch" value="GitHub ブランチ" class="font-bold text-gray-900" />
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
                        <div class="relative">
                            <input
                                type="text"
                                id="edit_github_branch"
                                v-model="form.github_branch"
                                list="edit-branch-list"
                                class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="main"
                            />
                            <datalist id="edit-branch-list">
                                <option v-for="branch in branches" :key="branch.name" :value="branch.name">
                                    {{ branch.name }}
                                </option>
                            </datalist>
                        </div>
                        <InputError class="mt-2" :message="form.errors.github_branch" />
                    </div>
                    <div v-else>
                        <InputLabel for="edit_github_branch_fallback" value="GitHub ブランチ" class="font-bold text-gray-900" />
                        <TextInput
                            id="edit_github_branch_fallback"
                            v-model="form.github_branch"
                            type="text"
                            class="block mt-1 w-full"
                        />
                        <InputError class="mt-2" :message="form.errors.github_branch" />
                    </div>

                    <div v-if="!form.ssh_configured" class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <label for="edit_ssh_configured" class="flex gap-3 items-start cursor-pointer">
                            <input
                                id="edit_ssh_configured"
                                v-model="form.ssh_configured"
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
                            @click="emit('close')"
                            class="px-4 py-2 text-sm text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
                        >
                            キャンセル
                        </button>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            <span v-if="form.processing" class="flex gap-2 items-center">
                                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                <span>更新中...</span>
                            </span>
                            <span v-else>更新</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, watch } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

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
