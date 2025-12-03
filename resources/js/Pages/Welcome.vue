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

const openFaqIndex = ref(null);

const toggleFaq = (index) => {
    openFaqIndex.value = openFaqIndex.value === index ? null : index;
};

const features = [
    {
        title: '承認キャンバス',
        description: 'テスト環境へのアクセスと承認ボタンだけを並べたシンプルなビューを生成し、クライアントが迷わず承認できます。',
        detail: '承認URLは自動生成・自動失効。承認と同時に本番アップが走ります。',
        icon: 'sparkles',
        accent: 'from-indigo-200 via-violet-100 to-cyan-200',
    },
    {
        title: 'GitHub Actions連携',
        description: 'ApproveDeployからworkflow_dispatchを呼び出し、SSH/FTP/Envoyなど好みの手法で本番に反映します。',
        detail: 'Secrets/環境変数をApproveDeployから安全に注入。ロールバック手順もワンクリック。',
        icon: 'layers',
        accent: 'from-blue-200 via-sky-100 to-emerald-200',
    },
    {
        title: '履歴管理',
        description: '承認とデプロイの結果を時系列で保存し、いつ誰がボタンを押したかをすぐに振り返れます。',
        detail: 'CSVとしてダウンロードできる履歴ログで、案件ごとの進捗を共有できます。',
        icon: 'shield',
        accent: 'from-purple-200 via-pink-100 to-indigo-200',
    },
];

const workflowSteps = [
    {
        title: '1. Pull Request',
        summary: 'GitHubでレビューが完了',
        detail: 'mainブランチへのマージ待ち。ApproveDeployがテスト環境への承認URLを自動生成します。',
    },
    {
        title: '2. クライアント承認',
        summary: 'URL+1クリック',
        detail: '承認者はコメントを添えてボタンを押すだけ。スマホでも美しく表示されます。',
    },
    {
        title: '3. 自動デプロイ',
        summary: 'workflow_dispatch',
        detail: 'Actionsが起動し、SSH/rsync/FTPなど設定済みのレシピで本番に反映。',
    },
    {
        title: '4. 履歴で確認',
        summary: '承認/デプロイ履歴',
        detail: '完了後はApproveDeployの履歴画面から承認者と実行結果を振り返れます。',
    },
];

const faqs = [
    {
        question: 'ApproveDeployはどんなワークフローに向いていますか？',
        answer:
            'GitHubでコードレビュー→クライアント承認→本番反映という一連の流れを自動化したいWeb制作チームに最適です。静的サイト・WordPress・Laravel・Next.jsなどフレームワークを問いません。',
    },
    {
        question: 'クライアントにアカウント発行は必要ですか？',
        answer:
            '承認URLへアクセスするだけで操作できます。URLは64桁のトークンで保護され、有効期限も任意に設定できるため安全です。コメントや差し戻しも同じ画面から行えます。',
    },
    {
        question: 'サーバー環境は限定されますか？',
        answer:
            'GitHub Actionsの中でSSH/FTP/rsync/Envoyなど好きな手段を指定できるため、レンタルサーバーからクラウドまで幅広く対応します。ApproveDeploy側はサーバーを持たず、コードも管理しません。',
    },
    {
        question: '承認やデプロイの履歴はどれくらい保存されますか？',
        answer:
            '承認者・日時・IP・デプロイログを無期限で保存します。CSVやJSONでダウンロードでき、監査対応も容易です。',
    },
    {
        question: '料金はどれくらいですか？',
        answer:
            '現在はベータ版のため無料で提供中です。正式リリース後も既存ユーザーには移行期間や大幅な割引を予定しています。',
    },
];
</script>

<template>
    <Head title="WEBデザイナー向けデプロイ自動化サービス" />

    <div class="relative min-h-screen overflow-hidden bg-gradient-to-br from-slate-100 via-slate-200 to-indigo-200/50 text-slate-900">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-10 -left-16 w-72 h-72 rounded-full blur-3xl bg-indigo-200/40"></div>
            <div class="absolute bottom-0 right-0 h-[26rem] w-[26rem] rounded-full bg-sky-200/40 blur-[120px]"></div>
            <div class="absolute inset-x-0 top-1/3 mx-auto w-3/4 h-72 bg-gradient-to-r blur-3xl from-indigo-200/40 via-slate-100 to-purple-200/40"></div>
        </div>

        <div class="flex relative z-10 flex-col min-h-screen">
            <header class="mx-auto mt-6 flex w-full max-w-6xl flex-wrap items-center justify-between gap-6 rounded-[32px] border border-white/60 bg-white/80 px-8 py-6 text-slate-600 shadow-xl shadow-indigo-100/70 backdrop-blur">
                <div class="flex gap-4 items-center">
                    <div class="flex justify-center items-center w-12 h-12 bg-white rounded-2xl shadow-lg shadow-indigo-100">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-lg font-semibold tracking-wide text-slate-900">ApproveDeploy</p>
                        <p class="text-xs uppercase text-slate-500">Approve → Auto Deploy</p>
                    </div>
                </div>

                <nav class="flex flex-wrap gap-4 items-center text-sm text-slate-600">
                    <a class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex" href="#features">機能</a>
                    <a class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex" href="#workflow">ワークフロー</a>
                    <a class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex" href="#faq">FAQ</a>

                    <Link
                        v-if="$page.props.auth?.user"
                        :href="route('dashboard')"
                        class="px-5 py-2 rounded-full border transition border-slate-200 text-slate-700 hover:border-slate-400 hover:text-slate-900"
                    >
                        ダッシュボードへ
                    </Link>

                    <template v-else>
                        <button
                            @click="openLoginModal"
                            class="px-5 py-2 rounded-full transition text-slate-600 hover:text-slate-900"
                        >
                            ログイン
                        </button>
                        <button
                            @click="openLoginModal"
                            class="px-6 py-2 font-semibold text-white bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full shadow-lg transition shadow-indigo-200 hover:opacity-90"
                        >
                            無料で始める
                        </button>
                    </template>
                </nav>
            </header>

            <main class="flex-1">
                <section class="px-6 pt-10 pb-20 sm:pt-16 lg:pb-36">
                    <div class="mx-auto grid max-w-6xl gap-12 lg:grid-cols-[1.05fr_0.95fr]">
                        <div class="space-y-10">
                            <div class="inline-flex gap-3 items-center px-4 py-2 text-sm rounded-full shadow-sm bg-white/70 shadow-indigo-100">
                                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-500">新しい承認体験</span>
                                <span class="text-xs text-slate-500">Pushから45秒で本番反映</span>
                            </div>

                            <div>
                                <h1 class="text-4xl font-semibold tracking-tight leading-tight text-slate-900 sm:text-5xl lg:text-6xl">
                                    <span class="block">WEBデザイナー向け</span>
                                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-500 via-sky-400 to-purple-500">デプロイ自動化サービス</span>
                                </h1>
                                <p class="mt-6 text-lg leading-relaxed text-slate-600">
                                    クライアントがテスト環境を確認し、承認ボタンを押すだけで本番環境へ自動的にアップされます。Slackで催促する、手作業でアップロードする、といった二重作業をなくします。
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-4">
                                <button
                                    v-if="!$page.props.auth?.user"
                                    @click="openLoginModal"
                                    class="px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-xl transition shadow-indigo-200 hover:-translate-y-0.5"
                                >
                                    今すぐ試す
                                </button>
                            </div>
                        </div>

                        <div class="relative">
                            <div class="absolute inset-0 rounded-[32px] bg-gradient-to-br from-indigo-200/40 via-purple-200/30 to-pink-100/30 blur-[60px]"></div>
                            <div class="relative overflow-hidden rounded-[32px] border border-indigo-100 bg-white p-8 shadow-[0_30px_80px_rgba(79,70,229,0.12)] ring-1 ring-white/40">
                                <div class="absolute inset-0 bg-gradient-to-br from-white via-indigo-50 to-purple-50 opacity-80"></div>
                                <div class="relative">
                                    <div class="flex justify-between items-center text-xs text-slate-500">
                                        <span>Deploy timeline</span>
                                        <span>main · production</span>
                                    </div>
                                    <div class="mt-4 space-y-5">
                                        <div class="p-4 bg-gradient-to-br rounded-2xl shadow-inner from-slate-50 to-indigo-50/70 shadow-indigo-100/60">
                                            <p class="text-sm text-indigo-500">承認状況</p>
                                            <p class="text-lg font-semibold text-slate-900">Ready for deploy</p>
                                            <p class="text-xs text-slate-500">3 reviewers approved</p>
                                        </div>
                                        <div class="space-y-4">
                                            <div class="flex gap-3 items-start" v-for="step in ['テスト環境準備','クライアント承認','GitHub Actions','本番反映']" :key="step">
                                                <span class="mt-1 w-2 h-2 bg-gradient-to-r from-indigo-400 to-purple-400 rounded-full"></span>
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-900">{{ step }}</p>
                                                    <p class="text-xs text-slate-500">完了済み</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-5 mt-6 text-sm bg-gradient-to-br rounded-2xl border border-indigo-100 shadow-inner from-slate-50 to-emerald-50/50 text-slate-600 shadow-indigo-100/60">
                                        <p class="font-semibold text-slate-900">Actions ready</p>
                                        <p class="mt-1 text-xs text-slate-500">workflow_dispatch · secrets synced</p>
                                        <div class="flex justify-between items-center mt-4">
                                            <span class="text-2xl font-bold text-emerald-500">00:45</span>
                                            <span class="text-xs text-slate-500">署名済みトークンで安全に実行</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="features" class="relative px-6 py-20">
                    <div class="relative mx-auto space-y-10 max-w-6xl">
                        <div class="space-y-3 text-center">
                            <p class="text-xs uppercase tracking-[0.3em] text-indigo-500">Capabilities</p>
                            <h2 class="text-3xl font-semibold text-slate-900">更新時の二重作業を抑え、無駄な時間を排除</h2>
                            <p class="text-slate-600">ApproveDeployは、テスト→承認→本番アップを一本化し、同じ作業を繰り返さないための仕組みを標準搭載しています。</p>
                        </div>
                        <div class="grid gap-8 md:grid-cols-3">
                            <article
                                v-for="feature in features"
                                :key="feature.title"
                                class="relative overflow-hidden rounded-[28px] border border-white/70 bg-gradient-to-br from-white via-white to-indigo-50/70 p-8 shadow-2xl shadow-indigo-100/80 ring-1 ring-indigo-100/80"
                            >
                                <div class="absolute inset-0 bg-gradient-to-br opacity-90 mix-blend-multiply" :class="feature.accent" aria-hidden="true"></div>
                                <div class="relative space-y-4">
                                    <span class="inline-flex justify-center items-center w-12 h-12 bg-white rounded-2xl shadow-inner shadow-slate-100">
                                        <svg v-if="feature.icon === 'sparkles'" class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l2 5 5 2-5 2-2 5-2-5-5-2 5-2zM16 5l1 3 3 1-3 1-1 3-1-3-3-1 3-1zM19 15l1 2 2 1-2 1-1 2-1-2-2-1 2-1z" />
                                        </svg>
                                        <svg v-else-if="feature.icon === 'layers'" class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l8 4-8 4-8-4 8-4zm0 8l8 4-8 4-8-4 8-4z" />
                                        </svg>
                                        <svg v-else-if="feature.icon === 'shield'" class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l8 4v5c0 5.25-3.438 9.795-8 11-4.562-1.205-8-5.75-8-11V7l8-4z" />
                                        </svg>
                                        <svg v-else class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 12V6m4 6V6m-7 6v6m11-6v6M3 3h18" />
                                        </svg>
                                    </span>
                                    <h3 class="text-2xl font-semibold text-slate-900">{{ feature.title }}</h3>
                                    <p class="text-slate-600">{{ feature.description }}</p>
                                    <p class="text-sm text-slate-500">{{ feature.detail }}</p>
                                </div>
                            </article>
                        </div>
                    </div>
                </section>

                <section id="workflow" class="relative px-6 py-20">
                    <div class="relative mx-auto grid max-w-6xl gap-10 rounded-[36px] border border-slate-200 bg-gradient-to-br from-white via-slate-50 to-indigo-50/60 p-10 shadow-[0_35px_120px_rgba(79,70,229,0.15)] ring-1 ring-indigo-50 lg:grid-cols-[1.1fr_0.9fr]">
                        <div class="space-y-6">
                            <p class="text-xs uppercase tracking-[0.3em] text-indigo-500">Workflow</p>
                            <h2 class="text-3xl font-semibold text-slate-900">4ステップで承認〜自動デプロイ</h2>
                            <p class="text-slate-600">ApproveDeployはGitHub Actionsと連動し、Pull Requestから承認、デプロイ実行までを一気通貫でトラッキングします。</p>
                            <div class="space-y-4">
                                <div
                                    v-for="step in workflowSteps"
                                    :key="step.title"
                                    class="flex gap-4 p-5 bg-gradient-to-br from-white via-indigo-50 rounded-3xl border border-indigo-100 shadow-xl to-indigo-100/70 shadow-indigo-100/70"
                                >
                                    <div class="flex justify-center items-center w-12 h-12 text-sm font-semibold text-indigo-600 bg-white rounded-2xl shadow-sm">
                                        {{ step.title.split('.')[0] }}
                                    </div>
                                    <div>
                                        <p class="text-sm uppercase tracking-[0.2em] text-indigo-500">{{ step.summary }}</p>
                                        <p class="text-lg font-semibold text-slate-900">{{ step.title }}</p>
                                        <p class="mt-2 text-sm text-slate-600">{{ step.detail }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="relative overflow-hidden rounded-[32px] border border-indigo-100 bg-gradient-to-br from-white via-slate-50 to-indigo-100/60 p-8 shadow-[0_25px_70px_rgba(79,70,229,0.2)]">
                            <div class="mb-6 space-y-2">
                                <p class="text-sm text-indigo-500">Live status</p>
                                <p class="text-2xl font-semibold text-slate-900">ApproveDeploy Bot</p>
                                <p class="text-sm text-slate-500">github-actions · production</p>
                            </div>
                            <div class="space-y-6 text-sm text-slate-600">
                                <div class="p-5 bg-white rounded-3xl shadow-sm">
                                    <p class="text-xs uppercase tracking-[0.3em] text-indigo-500">Command</p>
                                    <pre class="overflow-x-auto p-4 mt-3 text-emerald-200 rounded-2xl bg-slate-900/90">workflow_dispatch --env=production --signed</pre>
                                    <p class="mt-2 text-xs text-slate-500">最終承認者: Client Team · IP 203.0.113.4</p>
                                </div>
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center">
                                        <span>Secrets Sync</span>
                                        <span class="text-emerald-500">OK</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span>Server Check</span>
                                        <span class="text-emerald-500">Passed</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span>Deploy ETA</span>
                                        <span class="text-slate-900">00:45</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="faq" class="relative px-6 pb-20">
                    <div class="relative mx-auto space-y-10 max-w-5xl">
                        <div class="text-center">
                            <p class="text-xs uppercase tracking-[0.3em] text-indigo-500">FAQ</p>
                            <h2 class="mt-2 text-3xl font-semibold text-slate-900">よくある質問</h2>
                            <p class="mt-4 text-slate-600">導入前の疑問はすべて公開しています。ほかに質問があればSlackコミュニティへ。</p>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="(faq, index) in faqs"
                                :key="faq.question"
                                class="overflow-hidden bg-white rounded-2xl border border-transparent ring-1 shadow-lg shadow-slate-200/80 ring-slate-100"
                            >
                                <button
                                    @click="toggleFaq(index)"
                                    class="flex justify-between items-center px-6 py-5 w-full text-lg font-semibold text-left text-slate-900"
                                >
                                    <span>{{ faq.question }}</span>
                                    <svg
                                        :class="['h-6 w-6 transition', openFaqIndex === index ? 'rotate-180 text-indigo-500' : 'text-slate-400']"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                </button>
                                <div v-show="openFaqIndex === index" class="px-6 pb-6 text-sm leading-relaxed text-slate-600">
                                    {{ faq.answer }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section v-if="!$page.props.auth?.user" class="px-6 pb-24">
                    <div class="mx-auto max-w-5xl rounded-[40px] border border-slate-200 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 p-1">
                        <div class="rounded-[36px] bg-gradient-to-br from-white via-slate-50 to-indigo-50/50 px-10 py-12 text-center text-slate-900 shadow-[0_25px_80px_rgba(79,70,229,0.15)]">
                            <p class="text-xs uppercase tracking-[0.3em] text-indigo-500">Get started</p>
                            <h2 class="mt-4 text-3xl font-semibold">Googleアカウントで今日から運用を自動化</h2>
                            <p class="mt-3 text-slate-600">ログインして承認URLを送り、ワンクリックでGitHub Actionsを呼び出すだけ。初期費用はゼロです。</p>
                            <div class="flex flex-wrap gap-4 justify-center mt-8">
                                <button
                                    @click="openLoginModal"
                                    class="px-10 py-4 text-lg font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-2xl transition shadow-indigo-200 hover:-translate-y-0.5"
                                >
                                    無料で始める
                                </button>
                                <button
                                    @click="openLoginModal"
                                    class="px-10 py-4 text-lg font-semibold rounded-2xl border shadow-lg transition border-white/70 bg-white/80 text-slate-700 shadow-indigo-100/70 hover:-translate-y-0.5"
                                >
                                    デモを見る
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            <footer class="px-6 py-10 text-xs text-center text-slate-500">
                &copy; {{ new Date().getFullYear() }} ApproveDeploy. All rights reserved.
            </footer>
        </div>

        <Modal :show="showLoginModal" @close="closeLoginModal" max-width="md">
            <div class="p-8 bg-white rounded-3xl text-slate-900">
                <div class="mb-6 text-center">
                    <p class="text-sm uppercase tracking-[0.3em] text-indigo-500">Sign in</p>
                    <h2 class="mt-2 text-2xl font-semibold">Googleアカウントでログイン</h2>
                    <p class="mt-2 text-sm text-slate-500">承認→自動デプロイのワークフローを60秒でセットアップできます。</p>
                </div>
                <a
                    :href="route('google.login')"
                    class="flex gap-3 justify-center items-center px-4 py-3 mb-6 text-sm font-semibold bg-white rounded-2xl border shadow-sm transition border-slate-200 text-slate-700 hover:border-indigo-200 hover:text-indigo-600"
                >
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                    </svg>
                    Googleでログイン
                </a>
                <button
                    @click="closeLoginModal"
                    class="block mx-auto text-sm transition text-slate-500 hover:text-slate-700"
                >
                    キャンセル
                </button>
            </div>
        </Modal>
    </div>
</template>
