<template>
    <Head>
        <title>承認依頼 - AutoRelease</title>
        <meta name="robots" content="noindex, nofollow">
    </Head>

    <div class="flex justify-center p-0 min-h-screen bg-indigo-50 sm:px-4 sm:py-12 lg:px-8">
        <div class="p-4 space-y-12 w-full max-w-4xl bg-white rounded-3xl sm:p-8">
            <div>
                <h2 class="mt-4 text-3xl font-extrabold text-center text-gray-900 sm:mt-6">
                    承認依頼
                </h2>
                <p class="mt-4 text-sm text-center text-gray-600">
                    変更内容を確認して、実際のサイト（公開中のサイト）に反映するかどうかを承認できます。
                </p>

                <div class="p-4 mt-6 bg-yellow-50 rounded-md border-l-4 border-yellow-400">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-yellow-800">
                                重要：このURLは5日間のみ有効です。承認後、本番環境（公開中のサイト）に自動的に変更が反映されます。
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 space-y-16">
                <div class="space-y-3">
                    <h3 class="text-sm font-semibold text-gray-700">改善内容</h3>
                    <div class="text-sm">
                        <a
                            v-if="project?.staging_url"
                            :href="project.staging_url"
                            target="_blank"
                            class="text-indigo-600 underline break-all"
                        >
                            {{ project.staging_url }}
                        </a>
                        <span v-else class="text-gray-400">URLが未設定です。</span>
                    </div>
                    <PinnedReviewCanvas
                        :review-url="project?.staging_url || ''"
                        :approval-message-id="approvalMessage?.id || null"
                        :list-route="{ name: 'approve.comments.index', params: { token } }"
                        :create-route="{ name: 'approve.comments.store', params: { token } }"
                        :can-edit="false"
                        :can-create="true"
                        :require-author-name="true"
                        :poll-interval-ms="5000"
                        frame-height="calc(100vh - 320px)"
                    />
                </div>

                <div v-if="$page.props.flash?.success" class="p-4 bg-green-50 rounded-md">
                    <p class="text-sm font-medium text-green-800">
                        {{ $page.props.flash.success }}
                    </p>
                </div>

                <div v-if="$page.props.flash?.error" class="p-4 bg-red-50 rounded-md">
                    <p class="text-sm font-medium text-red-800">
                        {{ $page.props.flash.error }}
                    </p>
                </div>

                <div>
                    <p class="mb-6 text-sm font-semibold text-center text-gray-700">問題がなければボタンをクリックしてください</p>
                    <form @submit.prevent="submit">
                        <div class="flex justify-center">
                            <PrimaryButton :disabled="form.processing" class="flex gap-2 items-center px-6 py-3 text-base">
                                <div v-if="form.processing" class="w-4 h-4 rounded-full border-2 border-white animate-spin border-t-transparent"></div>
                                <span>{{ form.processing ? '処理中...' : '問題ないので公開する' }}</span>
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>

            <AppFooter :show-logo="true" />
        </div>
    </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AppFooter from '@/Components/AppFooter.vue';
import PinnedReviewCanvas from '@/Components/Review/PinnedReviewCanvas.vue';

const props = defineProps({
    project: Object,
    token: String,
    approvalMessage: Object,
});

const form = useForm({
    msg: props.approvalMessage?.id || null,
});

const submit = () => {
    form.post(route('approve.approve', props.token));
};
</script>
