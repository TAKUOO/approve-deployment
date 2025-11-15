<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});

const showLoginModal = ref(false);

const openLoginModal = () => {
    showLoginModal.value = true;
};

const closeLoginModal = () => {
    showLoginModal.value = false;
};
</script>

<template>
    <Head title="ApproveDeploy - 承認→自動デプロイ" />
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50">
        <div class="flex relative flex-col justify-center items-center px-6 py-12 min-h-screen">
            <!-- ヘッダー -->
            <header class="flex absolute top-0 right-0 left-0 justify-between items-center px-6 py-6 lg:px-12">
                <div class="flex gap-2 items-center">
                    <div class="flex justify-center items-center w-10 h-10 bg-indigo-600 rounded-lg">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900">ApproveDeploy</span>
                </div>

                <nav v-if="canLogin" class="flex gap-4 items-center">
                    <Link
                        v-if="$page.props.auth?.user"
                        :href="route('dashboard')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 rounded-md hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        ダッシュボード
                    </Link>

                    <template v-else>
                        <button
                            @click="openLoginModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 rounded-md hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            ログイン
                        </button>

                        <button
                            v-if="canRegister"
                            @click="openLoginModal"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            無料で始める
                        </button>
                    </template>
                </nav>
            </header>

            <!-- メインコンテンツ -->
            <main class="mx-auto max-w-7xl text-center">
                <!-- ヒーローセクション -->
                <div class="mb-16">
                    <div class="mb-4">
                        <span class="inline-block px-4 py-2 text-sm font-semibold text-indigo-600 bg-indigo-100 rounded-full">
                            Web製作者のための
                        </span>
                    </div>
                    <h1 class="mb-6 text-5xl font-bold tracking-tight text-gray-900 sm:text-6xl lg:text-7xl">
                        <span class="block">承認から</span>
                        <span class="block text-indigo-600">自動アップまで</span>
                    </h1>
                    <p class="mx-auto mb-8 max-w-2xl text-xl text-gray-600 sm:text-2xl">
                        クライアントがテスト環境を確認し、承認ボタンを押すだけで<br />
                        本番環境へ自動的にアップされます
                    </p>
                    <div v-if="canRegister && !$page.props.auth?.user" class="flex flex-col gap-4 justify-center items-center sm:flex-row">
                        <button
                            @click="openLoginModal"
                            class="px-8 py-4 text-lg font-semibold text-white bg-indigo-600 rounded-lg shadow-lg transition shadow-indigo-500/50 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            今すぐ始める
                        </button>
                        <button
                            @click="openLoginModal"
                            class="px-8 py-4 text-lg font-semibold text-gray-700 bg-white rounded-lg border-2 border-gray-300 transition hover:border-gray-400 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            ログイン
                        </button>
                    </div>
                </div>

                <!-- 課題と解決策セクション -->
                <div class="mb-16">
                    <h2 class="mb-12 text-3xl font-bold text-gray-900">こんな課題、ありませんか？</h2>
                    <div class="grid gap-8 md:grid-cols-2">
                        <!-- Before: 課題 -->
                        <div class="p-8 bg-red-50 rounded-xl border-2 border-red-200">
                            <div class="flex items-center mb-4">
                                <div class="flex justify-center items-center w-10 h-10 bg-red-500 rounded-lg mr-3">
                                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-red-900">従来の課題</h3>
                            </div>
                            <ul class="space-y-3 text-left text-gray-700">
                                <li class="flex items-start">
                                    <span class="text-red-500 mr-2">×</span>
                                    <span>クライアントからの承認待ちで本番環境へのアップが遅れる</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-red-500 mr-2">×</span>
                                    <span>メールやチャットでの承認プロセスが煩雑</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-red-500 mr-2">×</span>
                                    <span>手動でのアップ作業でミスが発生する可能性</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-red-500 mr-2">×</span>
                                    <span>承認から本番環境へのアップまでの時間がかかる</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-red-500 mr-2">×</span>
                                    <span>クライアントにログインを求める必要がある</span>
                                </li>
                            </ul>
                        </div>

                        <!-- After: 解決策 -->
                        <div class="p-8 bg-green-50 rounded-xl border-2 border-green-200">
                            <div class="flex items-center mb-4">
                                <div class="flex justify-center items-center w-10 h-10 bg-green-500 rounded-lg mr-3">
                                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-green-900">ApproveDeployで解決</h3>
                            </div>
                            <ul class="space-y-3 text-left text-gray-700">
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span>承認URLを送るだけで即座に承認可能</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span>シンプルな承認フローでスムーズに進行</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span>自動アップでミスゼロ、時間短縮</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span>承認から本番環境へのアップまで数秒で完了</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span>クライアントはログイン不要で簡単</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- 機能紹介セクション -->
                <div class="grid gap-8 mb-16 md:grid-cols-3">
                    <div class="p-8 bg-white rounded-xl shadow-lg">
                        <div class="flex justify-center items-center mb-4 w-12 h-12 bg-indigo-100 rounded-lg">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-xl font-semibold text-gray-900">簡単な承認フロー</h3>
                        <p class="text-gray-600">
                            クライアントはログイン不要で、承認URLにアクセスするだけでテスト環境を確認できます。
                        </p>
                    </div>

                    <div class="p-8 bg-white rounded-xl shadow-lg">
                        <div class="flex justify-center items-center mb-4 w-12 h-12 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-xl font-semibold text-gray-900">自動アップ</h3>
                        <p class="text-gray-600">
                            承認ボタンを押すだけで、本番環境へ自動的にアップされます。
                        </p>
                    </div>

                    <div class="p-8 bg-white rounded-xl shadow-lg">
                        <div class="flex justify-center items-center mb-4 w-12 h-12 bg-purple-100 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-xl font-semibold text-gray-900">アップ履歴管理</h3>
                        <p class="text-gray-600">
                            すべてのアップ履歴を記録し、ステータスをリアルタイムで確認できます。
                        </p>
                    </div>
                </div>

                <!-- ワークフロー説明 -->
                <div class="p-12 mb-16 bg-white rounded-2xl shadow-xl">
                    <h2 class="mb-8 text-3xl font-bold text-gray-900">シンプルな3ステップ</h2>
                    <div class="grid gap-8 md:grid-cols-3">
                        <div class="relative">
                            <div class="flex justify-center items-center mb-4 w-16 h-16 text-2xl font-bold text-white bg-indigo-600 rounded-full">
                                1
                            </div>
                            <h3 class="mb-2 text-xl font-semibold text-gray-900">プロジェクト作成</h3>
                            <p class="text-gray-600">
                                GitHub情報とデプロイ設定を登録し、承認URLを生成します。
                            </p>
                            <div v-if="!$page.props.auth?.user" class="hidden absolute -right-4 top-8 md:block">
                                <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </div>
                        </div>

                        <div class="relative">
                            <div class="flex justify-center items-center mb-4 w-16 h-16 text-2xl font-bold text-white bg-green-600 rounded-full">
                                2
                            </div>
                            <h3 class="mb-2 text-xl font-semibold text-gray-900">クライアント承認</h3>
                            <p class="text-gray-600">
                                クライアントが承認URLにアクセスし、テスト環境を確認して承認します。
                            </p>
                            <div v-if="!$page.props.auth?.user" class="hidden absolute -right-4 top-8 md:block">
                                <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-center items-center mb-4 w-16 h-16 text-2xl font-bold text-white bg-purple-600 rounded-full">
                                3
                            </div>
                            <h3 class="mb-2 text-xl font-semibold text-gray-900">自動アップ</h3>
                            <p class="text-gray-600">
                                本番環境へ自動的にアップ作業を実行します。
                            </p>
                        </div>
                    </div>
                </div>

                <!-- CTAセクション -->
                <div v-if="canRegister && !$page.props.auth?.user" class="p-12 text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl">
                    <h2 class="mb-4 text-3xl font-bold">今すぐ始めましょう</h2>
                    <p class="mb-8 text-xl text-indigo-100">
                        Googleアカウントでログインして、承認→自動アップのワークフローを体験してください
                    </p>
                    <button
                        @click="openLoginModal"
                        class="inline-block px-8 py-4 text-lg font-semibold text-indigo-600 bg-white rounded-lg shadow-lg transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-600"
                    >
                        無料で始める
                    </button>
                </div>
            </main>

            <!-- フッター -->
            <footer class="absolute right-0 bottom-0 left-0 py-6 text-sm text-center text-gray-500">
                <p>&copy; {{ new Date().getFullYear() }} ApproveDeploy. All rights reserved.</p>
            </footer>
        </div>

        <!-- ログインモーダル -->
        <Modal :show="showLoginModal" @close="closeLoginModal" max-width="md">
            <div class="p-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Googleでログイン</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Googleアカウントでログインして、承認→自動アップのワークフローを始めましょう
                    </p>
                </div>

                <!-- Googleログインボタン -->
                <div class="mb-4">
                    <a
                        :href="route('google.login')"
                        class="flex justify-center items-center px-4 py-3 w-full text-sm font-medium text-gray-700 bg-white rounded-md border border-gray-300 shadow-sm transition-colors hover:bg-gray-50"
                    >
                        <svg class="mr-3 w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        Googleでログイン
                    </a>
                </div>

                <div class="text-center">
                    <button
                        @click="closeLoginModal"
                        class="text-sm text-gray-500 hover:text-gray-700"
                    >
                        キャンセル
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>
