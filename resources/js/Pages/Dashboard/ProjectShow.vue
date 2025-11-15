<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                プロジェクト詳細: {{ project.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">プロジェクト名</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ project.name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">ステージングURL</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <a :href="project.staging_url" target="_blank" class="text-blue-600 hover:text-blue-800">
                                        {{ project.staging_url }}
                                    </a>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">本番URL</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <a :href="project.production_url" target="_blank" class="text-blue-600 hover:text-blue-800">
                                        {{ project.production_url }}
                                    </a>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">承認URL</dt>
                                <dd class="mt-1 text-sm text-gray-900 break-all">
                                    <a :href="route('approve.show', project.approve_token)" target="_blank" class="text-blue-600 hover:text-blue-800">
                                        {{ route('approve.show', project.approve_token) }}
                                    </a>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">GitHub Owner</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ project.github_owner }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">GitHub Repo</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ project.github_repo }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">GitHub Workflow ID</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ project.github_workflow_id }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">GitHub Branch</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ project.github_branch }}</dd>
                            </div>
                        </dl>

                        <div class="mt-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">デプロイログ</h3>
                            <div v-if="project.deploy_logs.length === 0" class="text-gray-500">
                                デプロイログがありません
                            </div>
                            <div v-else class="space-y-2">
                                <div
                                    v-for="log in project.deploy_logs"
                                    :key="log.id"
                                    class="border rounded p-4"
                                >
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-medium">ステータス: {{ log.status }}</p>
                                            <p class="text-sm text-gray-600">開始: {{ log.started_at }}</p>
                                            <p v-if="log.finished_at" class="text-sm text-gray-600">終了: {{ log.finished_at }}</p>
                                            <p v-if="log.github_run_id" class="text-sm text-gray-600">Run ID: {{ log.github_run_id }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    project: Object,
});
</script>


