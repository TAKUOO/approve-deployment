<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppHeader from '@/Components/AppHeader.vue';
import AppFooter from '@/Components/AppFooter.vue';

const props = defineProps({
    status: {
        type: Number,
        default: 500,
    },
    message: {
        type: String,
        default: '',
    },
});

const errorMessages = {
    404: {
        title: 'ページが見つかりません',
        description: 'お探しのページは存在しないか、移動または削除された可能性があります。',
        icon: 'M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    405: {
        title: 'メソッドが許可されていません',
        description: 'このページは、リクエストされたHTTPメソッドをサポートしていません。',
        icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    },
    403: {
        title: 'アクセスが拒否されました',
        description: 'このページにアクセスする権限がありません。',
        icon: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
    },
    500: {
        title: 'サーバーエラー',
        description: 'サーバーでエラーが発生しました。しばらくしてから再度お試しください。',
        icon: 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
};

const errorInfo = errorMessages[props.status] || errorMessages[500];
const displayMessage = props.message || errorInfo.description;

const goBack = () => {
    // ブラウザの履歴を使って前のページに戻る
    try {
        if (typeof window !== 'undefined' && window.history) {
            window.history.back();
        } else {
            router.visit('/');
        }
    } catch (error) {
        // エラーが発生した場合はホームにリダイレクト
        router.visit('/');
    }
};
</script>

<template>
    <Head :title="`${status} - ${errorInfo.title}`" />

    <div class="overflow-hidden relative min-h-screen bg-gradient-to-br from-slate-100 via-slate-200 to-indigo-200/50 text-slate-900">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-10 -left-16 w-72 h-72 rounded-full blur-3xl bg-indigo-200/40"></div>
            <div class="absolute bottom-0 right-0 h-[26rem] w-[26rem] rounded-full bg-sky-200/40 blur-[120px]"></div>
        </div>

        <div class="flex relative z-10 flex-col min-h-screen">
            <AppHeader />

            <main class="flex-1 flex items-center justify-center px-6 py-20">
                <div class="mx-auto max-w-2xl text-center">
                    <div class="mb-8">
                        <div class="inline-flex items-center justify-center w-24 h-24 mb-6 bg-red-100 rounded-full">
                            <svg class="w-12 h-12 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="errorInfo.icon" />
                            </svg>
                        </div>
                        <h1 class="text-6xl font-bold text-slate-900 mb-4">{{ status }}</h1>
                        <h2 class="text-2xl font-semibold text-slate-700 mb-4">{{ errorInfo.title }}</h2>
                        <p class="text-lg text-slate-600 mb-8">{{ displayMessage }}</p>
                    </div>

                    <div class="flex flex-wrap gap-4 justify-center">
                        <Link
                            href="/"
                            class="px-6 py-3 text-lg font-semibold text-white bg-indigo-600 rounded-2xl shadow-lg transition shadow-indigo-200 hover:opacity-90"
                        >
                            ホームに戻る
                        </Link>
                        <button
                            @click="goBack"
                            class="px-6 py-3 text-lg font-semibold text-slate-700 bg-white rounded-2xl border shadow-sm transition border-slate-200 hover:border-slate-400"
                        >
                            前のページに戻る
                        </button>
                    </div>

                    <div v-if="status === 404" class="mt-12 p-6 bg-white rounded-2xl border border-slate-200 shadow-sm">
                        <h3 class="mb-4 text-lg font-semibold text-slate-900">よくアクセスされるページ</h3>
                        <div class="flex flex-wrap gap-3 justify-center">
                            <Link href="/" class="px-4 py-2 text-sm text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition">
                                ホーム
                            </Link>
                            <Link :href="route('docs')" class="px-4 py-2 text-sm text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition">
                                ドキュメント
                            </Link>
                            <Link href="/#features" class="px-4 py-2 text-sm text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition">
                                機能
                            </Link>
                            <Link href="/#faq" class="px-4 py-2 text-sm text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition">
                                FAQ
                            </Link>
                        </div>
                    </div>
                </div>
            </main>

            <AppFooter color-scheme="slate" padding="large" />
        </div>
    </div>
</template>

