<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                プロジェクト一覧
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-4">
                    <Link :href="route('projects.create')">
                        <PrimaryButton>
                            新規プロジェクト作成
                        </PrimaryButton>
                    </Link>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="projects.length === 0" class="text-center py-8">
                            <p class="text-gray-500">プロジェクトがありません</p>
                        </div>

                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div
                                v-for="project in projects"
                                :key="project.id"
                                class="border rounded-lg p-4 hover:shadow-md transition-shadow"
                            >
                                <h3 class="text-lg font-semibold mb-2">
                                    <a :href="route('projects.show', project.id)" class="text-blue-600 hover:text-blue-800">
                                        {{ project.name }}
                                    </a>
                                </h3>
                                <p class="text-sm text-gray-600 mb-2">
                                    ステージング: 
                                    <a :href="project.staging_url" target="_blank" class="text-blue-500">
                                        {{ project.staging_url }}
                                    </a>
                                </p>
                                <p class="text-sm text-gray-600 mb-4">
                                    本番: 
                                    <a :href="project.production_url" target="_blank" class="text-blue-500">
                                        {{ project.production_url }}
                                    </a>
                                </p>
                                <div class="text-xs text-gray-500">
                                    <p>承認URL: {{ route('approve.show', project.approve_token) }}</p>
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
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    projects: Array,
});
</script>


