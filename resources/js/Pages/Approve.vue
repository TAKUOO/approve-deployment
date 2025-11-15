<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-md">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    デプロイ承認
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    {{ project.name }}
                </p>
            </div>

            <div class="mt-8 space-y-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">プロジェクト情報</h3>
                    <dl class="space-y-2">
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
                    </dl>
                </div>

                <div v-if="$page.props.flash?.success" class="rounded-md bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800">
                        {{ $page.props.flash.success }}
                    </p>
                </div>

                <div v-if="$page.props.flash?.error" class="rounded-md bg-red-50 p-4">
                    <p class="text-sm font-medium text-red-800">
                        {{ $page.props.flash.error }}
                    </p>
                </div>

                <form @submit.prevent="submit" class="mt-8 space-y-6">
                    <div>
                        <PrimaryButton :disabled="processing">
                            承認してデプロイ
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    project: Object,
    token: String,
});

const form = useForm({});

const processing = false;

const submit = () => {
    form.post(route('approve.approve', props.token));
};
</script>


