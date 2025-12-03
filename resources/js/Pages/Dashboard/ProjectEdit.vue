<template>
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
                                        FTPサーバー上のデプロイ先ディレクトリパスを入力してください。例: /public_html/ または /home/example.com/public_html/wp-content/themes/rubydesign2020
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

                                <div>
                                    <InputLabel for="github_branch" value="GitHub ブランチ" />
                                    <TextInput
                                        id="github_branch"
                                        v-model="form.github_branch"
                                        type="text"
                                        class="block mt-1 w-full"
                                        required
                                    />
                                    <p class="mt-1 text-sm text-gray-500">
                                        デプロイ対象のブランチを入力してください。例: main, master
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
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref, onMounted } from 'vue';
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
    github_branch: props.project.github_branch || 'main',
});

const organizations = ref([]);
const repositories = ref([]);
const selectedOrganization = ref(props.project.github_owner || '');
const selectedRepository = ref(null);

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
    }
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

const onRepositoryChange = () => {
    if (!selectedRepository.value) {
        form.github_owner = '';
        form.github_repo = '';
        return;
    }

    form.github_owner = selectedRepository.value.owner.login;
    form.github_repo = selectedRepository.value.name;
};

const submit = () => {
    form.patch(route('projects.update', props.project.id));
};
</script>

