<template>
    <Head>
        <title>無効な承認URL - AutoRelease</title>
        <meta name="robots" content="noindex, nofollow">
    </Head>
    
    <div class="flex justify-center px-4 py-12 min-h-screen bg-indigo-50 sm:px-6 lg:px-8">
        <div class="p-8 space-y-8 w-full max-w-4xl bg-white rounded-2xl">
            <div class="text-center">
                <div class="flex justify-center mb-6">
                    <div class="flex justify-center items-center w-20 h-20 bg-red-100 rounded-full">
                        <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>
                
                <h2 class="mt-6 text-3xl font-extrabold text-center text-gray-900">
                    承認URLが無効です
                </h2>
                
                <div class="mt-6 space-y-4">
                    <div v-if="reason === 'expired_or_invalid'" class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                        <p class="text-sm text-yellow-800">
                            <strong>この承認URLは既に使用済みか、期限切れです。</strong>
                        </p>
                        <p class="mt-2 text-sm text-yellow-700">
                            承認URLは一度使用すると自動的に無効化されます。また、有効期限は5日間です。
                        </p>
                    </div>
                    
                    <div v-else-if="reason === 'invalid'" class="p-4 bg-red-50 rounded-lg border border-red-200">
                        <p class="text-sm text-red-800">
                            <strong>この承認URLは無効です。</strong>
                        </p>
                        <p class="mt-2 text-sm text-red-700">
                            承認URLが正しくないか、既に使用済みです。新しい承認URLを発行してください。
                        </p>
                    </div>
                    
                    <div v-else class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <p class="text-sm text-gray-800">
                            <strong>この承認URLは使用できません。</strong>
                        </p>
                        <p class="mt-2 text-sm text-gray-700">
                            承認URLが無効になっています。新しい承認URLを発行してください。
                        </p>
                    </div>
                </div>
                
                <div class="mt-8 space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">対処方法</h3>
                    <ul class="text-left space-y-2 text-sm text-gray-600 max-w-2xl mx-auto">
                        <li class="flex gap-2 items-start">
                            <span class="flex-shrink-0 mt-0.5">•</span>
                            <span>承認URLは一度使用すると自動的に無効化されます。これはセキュリティ上の理由によるものです。</span>
                        </li>
                        <li class="flex gap-2 items-start">
                            <span class="flex-shrink-0 mt-0.5">•</span>
                            <span>承認URLの有効期限は5日間です。期限切れの場合は、新しい承認URLを発行してください。</span>
                        </li>
                        <li class="flex gap-2 items-start">
                            <span class="flex-shrink-0 mt-0.5">•</span>
                            <span>新しい承認URLが必要な場合は、プロジェクト管理者にお問い合わせください。</span>
                        </li>
                    </ul>
                </div>
            </div>

            <AppFooter :show-logo="true" />
        </div>
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AppFooter from '@/Components/AppFooter.vue';

const props = defineProps({
    reason: {
        type: String,
        default: 'invalid',
        validator: (value) => ['expired_or_invalid', 'invalid', 'expired'].includes(value),
    },
});
</script>

