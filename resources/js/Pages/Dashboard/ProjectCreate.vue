<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                新規プロジェクト作成
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit">
                            <div class="space-y-6">
                                <div>
                                    <InputLabel for="name" value="プロジェクト名" />
                                    <TextInput
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                        autofocus
                                    />
                                    <InputError class="mt-2" :message="form.errors.name" />
                                </div>

                                <div>
                                    <InputLabel for="staging_url" value="ステージングURL" />
                                    <TextInput
                                        id="staging_url"
                                        v-model="form.staging_url"
                                        type="url"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.staging_url" />
                                </div>

                                <div>
                                    <InputLabel for="production_url" value="本番URL" />
                                    <TextInput
                                        id="production_url"
                                        v-model="form.production_url"
                                        type="url"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.production_url" />
                                </div>

                                <div>
                                    <InputLabel for="github_owner" value="GitHub Owner" />
                                    <TextInput
                                        id="github_owner"
                                        v-model="form.github_owner"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.github_owner" />
                                </div>

                                <div>
                                    <InputLabel for="github_repo" value="GitHub Repo" />
                                    <TextInput
                                        id="github_repo"
                                        v-model="form.github_repo"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.github_repo" />
                                </div>

                                <div>
                                    <InputLabel for="github_workflow_id" value="GitHub Workflow ID" />
                                    <TextInput
                                        id="github_workflow_id"
                                        v-model="form.github_workflow_id"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.github_workflow_id" />
                                </div>

                                <div>
                                    <InputLabel for="github_branch" value="GitHub Branch" />
                                    <TextInput
                                        id="github_branch"
                                        v-model="form.github_branch"
                                        type="text"
                                        class="mt-1 block w-full"
                                        placeholder="main"
                                    />
                                    <InputError class="mt-2" :message="form.errors.github_branch" />
                                </div>

                                <div class="flex items-center justify-end">
                                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                        作成
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
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    name: '',
    staging_url: '',
    production_url: '',
    github_owner: '',
    github_repo: '',
    github_workflow_id: '',
    github_branch: 'main',
});

const submit = () => {
    form.post(route('projects.store'));
};
</script>


