<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Modal from '@/Components/Modal.vue';

const page = usePage();
const authUser = computed(() => page.props.auth?.user);

const showLoginModal = ref(false);

const openLoginModal = () => {
    showLoginModal.value = true;
};

const closeLoginModal = () => {
    showLoginModal.value = false;
};

// 親コンポーネントから呼び出せるように公開
defineExpose({
    openLoginModal,
});

defineProps({
    variant: {
        type: String,
        default: 'default', // 'default' or 'docs'
    },
});
</script>

<template>
    <header class="flex flex-wrap gap-6 justify-between items-center mx-auto mt-6 w-full max-w-6xl">
        <div class="flex gap-4 items-center">
            <Link href="/">
                <img src="/images/logo.png" alt="AutoRelease" class="object-contain h-10" />
            </Link>
        </div>

        <nav class="flex flex-wrap gap-6 items-center font-semibold text-md text-slate-600">
            <!-- Welcomeページ用のナビゲーション -->
            <template v-if="variant === 'default'">
                <a class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex" href="#features">機能</a>
                <a class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex" href="#workflow">ワークフロー</a>
                <a class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex" href="#security">セキュリティ</a>
                <a class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex" href="#faq">FAQ</a>
                <Link :href="route('docs')" class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex">ドキュメント</Link>
            </template>

            <!-- ドキュメントページ用のナビゲーション -->
            <template v-else-if="variant === 'docs'">
                <Link href="/" class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex">ホーム</Link>
                <Link href="/#features" class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex">機能</Link>
                <Link href="/#workflow" class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex">ワークフロー</Link>
                <Link href="/#faq" class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex">FAQ</Link>
            </template>

            <!-- その他のページ用のナビゲーション -->
            <template v-else>
                <Link href="/" class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex">ホーム</Link>
                <Link :href="route('docs')" class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex">ドキュメント</Link>
            </template>

            <Link
                v-if="authUser"
                :href="route('projects.index')"
                class="px-5 py-2 rounded-full border transition border-slate-200 text-slate-700 hover:border-slate-400 hover:text-slate-900"
            >
                プロジェクト一覧へ
            </Link>

            <template v-else>
                <button
                    @click="openLoginModal"
                    class="px-6 py-2 font-semibold text-white bg-indigo-600 rounded-full shadow-lg transition shadow-indigo-200 hover:opacity-90"
                >
                    ログイン
                </button>
            </template>
        </nav>
    </header>

    <Modal :show="showLoginModal" @close="closeLoginModal" max-width="md">
        <div class="p-8 bg-white rounded-3xl text-slate-900">
            <div class="mb-6 text-center">
                <h2 class="mt-2 text-2xl font-semibold">GitHubアカウントでログイン</h2>
                <p class="mt-3 text-sm font-medium text-slate-500">AutoReleaseはWEBデザイナー向けのリリース自動化システムです。承認ボタンで自動的にサーバーへアップするワークフローを60秒でセットアップできます。</p>
            </div>
            <a
                :href="route('github.login')"
                class="flex gap-3 justify-center items-center px-4 py-3 mb-6 text-sm font-semibold bg-white rounded-2xl border shadow-sm transition border-slate-200 text-slate-700 hover:border-indigo-200 hover:text-indigo-600"
            >
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />
                </svg>
                GitHubでログイン
            </a>
            <button
                @click="closeLoginModal"
                class="block mx-auto text-sm transition text-slate-500 hover:text-slate-700"
            >
                キャンセル
            </button>
        </div>
    </Modal>
</template>

