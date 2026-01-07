<template>
    <Head title="プロジェクト編集 - AutoRelease" />
    
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white">
                    <div class="text-gray-900">
                        <!-- 一覧に戻るボタン -->
                        <div class="mb-4">
                            <Link :href="route('projects.index')" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                                <svg class="mr-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                一覧に戻る
                            </Link>
                        </div>
                        
                        <!-- タイトルと使い方ボタン -->
                        <div class="flex gap-3 items-center mb-6">
                            <h1 class="text-2xl font-semibold text-gray-900">プロジェクトを編集する</h1>
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
                        
                        <form @submit.prevent="submit">
                            <div class="space-y-6">
                                <div>
                                    <InputLabel for="staging_url" value="テストURL" />
                                    <TextInput
                                        id="staging_url"
                                        v-model="form.staging_url"
                                        type="url"
                                        class="block mt-1 w-full"
                                        placeholder="https://staging.example.com"
                                        required
                                    />
                                    <p class="mt-1 text-sm text-gray-500">
                                        クライアントが確認するテスト環境（ステージング環境）のURLを入力してください。例: https://staging.example.com または https://test.example.com
                                    </p>
                                    <InputError class="mt-2" :message="form.errors.staging_url" />
                                </div>

                                <div>
                                    <InputLabel for="production_url" value="本番URL" />
                                    <TextInput
                                        id="production_url"
                                        v-model="form.production_url"
                                        type="url"
                                        class="block mt-1 w-full"
                                        placeholder="https://example.com"
                                        required
                                    />
                                    <p class="mt-1 text-sm text-gray-500">
                                        実際に公開されている本番環境のURLを入力してください。承認後にこのURLにデプロイされます。例: https://example.com
                                    </p>
                                    <InputError class="mt-2" :message="form.errors.production_url" />
                                </div>

                                <div>
                                    <InputLabel for="server_dir" value="デプロイ先パス" />
                                    <TextInput
                                        id="server_dir"
                                        v-model="form.server_dir"
                                        type="text"
                                        class="block mt-1 w-full"
                                        placeholder="/public_html/"
                                    />
                                    <p class="mt-1 text-sm text-gray-500">
                                        FTPサーバー上のデプロイ先ディレクトリパスを入力してください。例: /public_html/ または /home/example.com/public_html/wp-content/themes/mytheme/
                                    </p>
                                    <InputError class="mt-2" :message="form.errors.server_dir" />
                                </div>

                                <div>
                                    <InputLabel for="name" value="プロジェクト名" />
                                    <TextInput
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        class="block mt-1 w-full"
                                        placeholder="プロジェクト名（任意）"
                                    />
                                    <p class="mt-1 text-sm text-gray-500">
                                        プロジェクト名を入力してください。未入力の場合は、GitHubリポジトリ名が使用されます。
                                    </p>
                                    <InputError class="mt-2" :message="form.errors.name" />
                                </div>

                                <div>
                                    <InputLabel for="organization" value="GitHub 組織" />
                                    <select
                                        id="organization"
                                        v-model="selectedOrganization"
                                        @change="onOrganizationChange"
                                        class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="" :selected="!project.github_owner">組織を選択してください</option>
                                        <option value="personal" :selected="project.github_owner && !organizations.find(o => o.login === project.github_owner)">個人リポジトリ</option>
                                        <option v-for="org in organizations" :key="org.id" :value="org.login" :selected="project.github_owner === org.login">
                                            {{ org.login }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.github_owner" />
                                </div>

                                <div v-if="selectedOrganization">
                                    <InputLabel for="repository" value="GitHub リポジトリ" />
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
                                            required
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
                                <div v-else>
                                    <InputLabel for="github_branch" value="GitHub ブランチ" />
                                    <TextInput
                                        id="github_branch"
                                        v-model="form.github_branch"
                                        type="text"
                                        class="block mt-1 w-full"
                                        required
                                    />
                                    <p class="mt-1 text-sm text-gray-500">
                                        本番環境にアップするブランチ名（通常は main）
                                    </p>
                                    <InputError class="mt-2" :message="form.errors.github_branch" />
                                </div>

                                <div class="flex justify-end items-center gap-4">
                                    <Link :href="route('projects.index')" class="text-sm text-gray-600 hover:text-gray-900">
                                        キャンセル
                                    </Link>
                                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                        更新
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
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    project: Object,
});

const form = useForm({
    name: props.project.name || '',
    staging_url: props.project.staging_url || '',
    production_url: props.project.production_url || '',
    server_dir: props.project.server_dir || '/public_html/',
    github_owner: props.project.github_owner || '',
    github_repo: props.project.github_repo || '',
    github_workflow_id: props.project.github_workflow_id || '',
    github_branch: props.project.github_branch || 'main',
});

const organizations = ref([]);
const repositories = ref([]);
const branches = ref([]);
const selectedOrganization = ref(props.project.github_owner || '');
const selectedRepository = ref(null);
const activeTooltip = ref(null);

onMounted(async () => {
    // 組織一覧を取得
    try {
        const response = await axios.get(route('api.github.organizations'));
        if (response.data && Array.isArray(response.data)) {
            organizations.value = response.data;
        }
    } catch (error) {
        console.error('Failed to fetch organizations:', error);
        organizations.value = [];
    }

    // 既存のプロジェクトの組織が選択されている場合、リポジトリを取得
    if (selectedOrganization.value) {
        await loadRepositories();
        // 既存のリポジトリが選択されている場合、ブランチも取得
        if (selectedRepository.value) {
            await onRepositoryChange();
        }
    }
    
    // ツールチップをクリック外で閉じる
    document.addEventListener('click', closeTooltip);
});

onUnmounted(() => {
    document.removeEventListener('click', closeTooltip);
});

const loadRepositories = async () => {
    if (!selectedOrganization.value) return;

    try {
        const response = await axios.get(route('api.github.repositories'), {
            params: {
                organization: selectedOrganization.value
            }
        });
        
        if (response.data && Array.isArray(response.data)) {
            repositories.value = response.data;
            // 既存のリポジトリを選択
            if (props.project.github_repo) {
                const existingRepo = repositories.value.find(r => r.name === props.project.github_repo);
                if (existingRepo) {
                    selectedRepository.value = existingRepo;
                }
            }
        } else {
            repositories.value = [];
        }
    } catch (error) {
        console.error('Failed to fetch repositories:', error);
        repositories.value = [];
    }
};

const onOrganizationChange = async () => {
    if (!selectedOrganization.value) {
        selectedRepository.value = null;
        repositories.value = [];
        form.github_owner = '';
        form.github_repo = '';
        return;
    }

    selectedRepository.value = null;
    repositories.value = [];
    form.github_owner = '';
    form.github_repo = '';

    await loadRepositories();
};

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

const onRepositoryChange = async () => {
    if (!selectedRepository.value) {
        form.github_owner = '';
        form.github_repo = '';
        branches.value = [];
        return;
    }

    form.github_owner = selectedRepository.value.owner.login;
    form.github_repo = selectedRepository.value.name;
    
    // ブランチを取得
    branches.value = [];
    try {
        const branchesResponse = await axios.get(route('api.github.branches'), {
            params: {
                owner: form.github_owner,
                repo: form.github_repo
            }
        });
        
        // ブランチを取得（すべてのブランチがソートされて返される）
        branches.value = branchesResponse.data.branches || [];
        const defaultBranch = branchesResponse.data.default_branch;
        
        // ブランチの自動選択（優先順位: main -> master -> default_branch -> 最初のブランチ）
        if (branches.value && branches.value.length > 0) {
            const mainBranch = branches.value.find(b => b.name === 'main');
            const masterBranch = branches.value.find(b => b.name === 'master');
            const defaultBranchObj = branches.value.find(b => b.name === defaultBranch);
            
            if (mainBranch) {
                form.github_branch = 'main';
            } else if (masterBranch) {
                form.github_branch = 'master';
            } else if (defaultBranchObj) {
                form.github_branch = defaultBranch;
            } else {
                form.github_branch = branches.value[0].name;
            }
        }
    } catch (error) {
        console.error('Failed to fetch branches:', error);
        branches.value = [];
    }
};

const submit = () => {
    form.patch(route('projects.update', props.project.id));
};
</script>

