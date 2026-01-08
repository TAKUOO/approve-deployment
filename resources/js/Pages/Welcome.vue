<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppHeader from '@/Components/AppHeader.vue';
import AppFooter from '@/Components/AppFooter.vue';

const headerRef = ref(null);

const openLoginModal = () => {
    if (headerRef.value) {
        headerRef.value.openLoginModal();
    }
};

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});

// SEO用のメタ情報
const siteUrl = computed(() => {
    if (typeof window !== 'undefined') {
        return window.location.origin;
    }
    return 'https://autorelease.matsui-dev.net';
});

const pageTitle = 'WEBデザイナー向けリリース自動化システム - AutoRelease';
const pageDescription = 'クライアントがテスト環境を確認し、承認ボタンを押すだけで本番環境へ自動的にアップされます。GitHub Actionsと連携した承認→自動デプロイシステム。Slackで催促する、手作業でアップロードする、といった二重作業をなくします。';
const pageKeywords = 'リリース自動化,デプロイ自動化,承認フロー,クライアント承認,Web制作,デザイナー向け,CI/CD,GitHub Actions,workflow_dispatch,テスト環境,本番環境,デプロイツール';


const openFaqIndex = ref(null);

const toggleFaq = (index) => {
    openFaqIndex.value = openFaqIndex.value === index ? null : index;
};

const features = [
    {
        title: '承認キャンバス',
        description: 'テスト環境へのアクセスと承認ボタンだけを並べたシンプルなビューを生成し、クライアントが迷わず承認できます。',
        detail: '承認URLは自動生成・自動失効。承認と同時に本番アップが走ります。',
        icon: 'note',
        bgGradient: 'from-blue-700 via-indigo-600 to-cyan-700',
        shadowColor: 'shadow-blue-800/60',
        ringColor: 'ring-blue-700/50',
        textColor: 'text-white',
        detailColor: 'text-slate-200',
        iconBg: 'bg-slate-700',
    },
    {
        title: 'GitHub Actions連携',
        description: 'AutoReleaseからworkflow_dispatchを呼び出し、SSH/FTP/Envoyなど好みの手法で本番に反映します。',
        detail: 'Secrets/環境変数をAutoReleaseから安全に注入。ロールバック手順もワンクリック。',
        icon: 'github',
        bgGradient: 'from-slate-800 via-purple-900 to-slate-700',
        shadowColor: 'shadow-purple-900/60',
        ringColor: 'ring-purple-800/50',
        textColor: 'text-white',
        detailColor: 'text-slate-200',
        iconBg: 'bg-slate-700',
    },
    {
        title: '履歴管理',
        description: '承認とデプロイの結果を時系列で保存し、いつ誰がボタンを押したかをすぐに振り返れます。',
        detail: 'CSVとしてダウンロードできる履歴ログで、案件ごとの進捗を共有できます。',
        icon: 'timeline',
        bgGradient: 'from-pink-700 via-purple-700 to-indigo-700',
        shadowColor: 'shadow-pink-800/60',
        ringColor: 'ring-pink-700/50',
        textColor: 'text-white',
        detailColor: 'text-slate-200',
        iconBg: 'bg-slate-700',
    },
];

const workflowSteps = [
    {
        title: '1. Pull Request',
        summary: 'GitHubでレビューが完了',
        detail: 'mainブランチへのマージ待ち。AutoReleaseがテスト環境への承認URLを自動生成します。',
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
        detail: '完了後はAutoReleaseの履歴画面から承認者と実行結果を振り返れます。',
    },
];

const faqs = [
    {
        question: 'AutoReleaseはどんなワークフローに向いていますか？',
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
            'GitHub Actionsの中でSSH/FTP/rsync/Envoyなど好きな手段を指定できるため、レンタルサーバーからクラウドまで幅広く対応します。AutoRelease側はサーバーを持たず、コードも管理しません。',
    },
    {
        question: '利用にあたって推奨される環境はありますか？',
        answer:
            'GitHub Actionsでworkflow_dispatchが使えるGitHubリポジトリと、テスト環境（ステージング）を用意していることが前提です。AutoReleaseからはGitHub ActionsのAPIを呼び出すため、CI/CDをGitHub Actionsで構築しているチームに最適です。',
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
    <Head :title="pageTitle">
        <!-- 基本SEOメタタグ -->
        <meta name="description" :content="pageDescription" />
        <meta name="keywords" :content="pageKeywords" />
        <meta name="robots" content="index, follow" />
        <link rel="canonical" :href="siteUrl" />

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website" />
        <meta property="og:url" :content="siteUrl" />
        <meta property="og:title" :content="pageTitle" />
        <meta property="og:description" :content="pageDescription" />
        <meta property="og:image" :content="`${siteUrl}/images/ogp.jpg`" />
        <meta property="og:image:width" content="1200" />
        <meta property="og:image:height" content="630" />
        <meta property="og:site_name" content="AutoRelease" />
        <meta property="og:locale" content="ja_JP" />

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:url" :content="siteUrl" />
        <meta name="twitter:title" :content="pageTitle" />
        <meta name="twitter:description" :content="pageDescription" />
        <meta name="twitter:image" :content="`${siteUrl}/images/ogp.jpg`" />
    </Head>

    <div class="overflow-hidden relative min-h-screen bg-gradient-to-br from-slate-100 via-slate-200 to-indigo-200/50 text-slate-900">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-10 -left-16 w-72 h-72 rounded-full blur-3xl bg-indigo-200/40"></div>
            <div class="absolute bottom-0 right-0 h-[26rem] w-[26rem] rounded-full bg-sky-200/40 blur-[120px]"></div>
            <div class="absolute inset-x-0 top-1/3 mx-auto w-3/4 h-72 bg-gradient-to-r blur-3xl from-indigo-200/40 via-slate-100 to-purple-200/40"></div>
        </div>

        <div class="flex relative z-10 flex-col min-h-screen">
            <AppHeader ref="headerRef" variant="default" />

            <main class="flex-1">
                <section class="px-4 pt-24 pb-12 sm:px-6 sm:pt-32 md:pt-40 lg:pt-16 lg:pb-26">
                    <div class="mx-auto grid max-w-6xl gap-8 sm:gap-12 lg:grid-cols-[1.05fr_0.95fr] items-center">
                        <div class="space-y-6 sm:space-y-8 lg:space-y-10 text-center lg:text-left">
                            <div>
                                <div class="mb-4 flex justify-center lg:justify-start">
                                    <span class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-semibold text-indigo-700 bg-indigo-100 rounded-full border border-indigo-200">
                                        <svg class="mr-2 w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        ベータ版リリースのため現在無料！
                                    </span>
                                </div>
                                <h1 class="text-3xl font-semibold tracking-tight leading-tight text-slate-900 sm:text-4xl md:text-5xl lg:text-6xl">
                                    <span class="block mb-3 sm:mb-4 lg:mb-5">WEBデザイナー向け</span>
                                    <span class="block text-transparent bg-clip-text bg-gradient-to-r from-indigo-500 via-sky-400 to-purple-500 break-words">リリース自動化システム</span>
                                </h1>
                                <p class="mt-4 sm:mt-6 text-base sm:text-lg leading-relaxed text-slate-600">
                                    クライアントがテスト環境を確認し、承認ボタンを押すだけで本番環境へ自動的にアップされます。Slackで催促する、手作業でアップロードする、といった二重作業をなくします。
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-4 justify-center lg:justify-start">
                                <button
                                    v-if="!$page.props.auth?.user"
                                    @click="openLoginModal"
                                    class="w-full sm:w-auto px-6 py-3 sm:px-8 sm:py-4 text-base sm:text-lg font-semibold text-white bg-indigo-600 rounded-2xl shadow-xl transition shadow-indigo-200 hover:-translate-y-0.5"
                                >
                                    今すぐ試す
                                </button>
                            </div>
                        </div>

                        <div class="hidden md:block relative mt-8 lg:mt-0">
                            <div class="absolute inset-0 rounded-[24px] sm:rounded-[32px] bg-gradient-to-br from-indigo-200/40 via-purple-200/30 to-pink-100/30 blur-[60px]"></div>
                            <div class="relative overflow-hidden rounded-[24px] sm:rounded-[32px] border border-indigo-100 bg-white p-4 sm:p-6 lg:p-8 shadow-[0_30px_80px_rgba(79,70,229,0.12)] ring-1 ring-white/40">
                                <div class="absolute inset-0 bg-gradient-to-br from-white via-indigo-50 to-purple-50 opacity-80"></div>
                                <div class="relative">
                                    <div class="flex justify-between items-center text-xs text-slate-500">
                                        <span>Deploy timeline</span>
                                        <span class="hidden sm:inline">main · production</span>
                                        <span class="sm:hidden">main</span>
                                    </div>
                                    <div class="mt-4 space-y-4 sm:space-y-5">
                                        <div class="p-3 sm:p-4 bg-indigo-50 rounded-2xl border border-indigo-200">
                                            <p class="text-xs sm:text-sm text-indigo-500">承認状況</p>
                                        <p class="text-base sm:text-lg font-semibold text-slate-900">本番反映の準備完了</p>
                                            <p class="text-xs text-slate-500">3 reviewers approved</p>
                                        </div>
                                        <div class="space-y-3 sm:space-y-4">
                                            <div class="flex gap-2 sm:gap-3 items-start" v-for="step in ['テスト環境準備','クライアント承認','GitHub Actions','本番反映']" :key="step">
                                                <span class="mt-1 flex-shrink-0 w-2 h-2 bg-gradient-to-r from-indigo-400 to-purple-400 rounded-full"></span>
                                                <div>
                                                    <p class="text-xs sm:text-sm font-semibold text-slate-900">{{ step }}</p>
                                                    <p class="text-xs text-slate-500">完了済み</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-4 sm:p-5 mt-4 sm:mt-6 text-xs sm:text-sm bg-emerald-50 rounded-2xl border border-emerald-300 text-slate-60">
                                        <p class="font-semibold text-slate-900">Actions ready</p>
                                        <p class="mt-1 text-xs text-slate-500 break-words">workflow_dispatch · secrets synced</p>
                                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 sm:gap-0 mt-3 sm:mt-4">
                                            <span class="text-xl sm:text-2xl font-bold text-emerald-600">00:45</span>
                                            <span class="text-xs font-bold text-emerald-500 break-words">署名済みトークンで安全に実行</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="features" class="relative px-4 py-12 sm:px-6 sm:py-16 lg:py-20">
                    <div class="relative mx-auto space-y-6 sm:space-y-8 lg:space-y-10 max-w-6xl">
                        <div class="space-y-2 sm:space-y-3 text-center">
                            <p class="text-xs uppercase tracking-[0.3em] text-indigo-500">Capabilities</p>
                            <h2 class="text-2xl sm:text-3xl font-semibold text-slate-900">更新時の二重作業を抑え、無駄な時間を排除</h2>
                            <p class="text-sm sm:text-base text-slate-600 px-2">AutoReleaseは、テスト→承認→本番アップを一本化し、同じ作業を繰り返さないための仕組みを標準搭載しています。</p>
                        </div>
                        <div class="grid gap-6 sm:gap-8 md:grid-cols-3">
                            <article
                                v-for="feature in features"
                                :key="feature.title"
                                class="relative overflow-hidden rounded-[20px] sm:rounded-[24px] lg:rounded-[28px] border border-white/50 bg-gradient-to-br p-5 sm:p-6 lg:p-8 shadow-2xl ring-1"
                                :class="[feature.bgGradient, feature.shadowColor, feature.ringColor]"
                            >
                                <div class="relative space-y-3 sm:space-y-4">
                                    <!-- メモのアイコン（承認キャンバス） -->
                                    <img v-if="feature.icon === 'note'" src="/images/note-icon.png" alt="承認キャンバス" class="object-contain w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16" />
                                    <!-- GitHub Octocatアイコン -->
                                    <img v-else-if="feature.icon === 'github'" src="/images/github-icon.png" alt="GitHub Actions連携" class="object-contain w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16" />
                                    <!-- タイムツリーアイコン -->
                                    <img v-else-if="feature.icon === 'timeline'" src="/images/timeline-icon.png" alt="履歴管理" class="object-contain w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16" />
                                    <svg v-else class="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 12V6m4 6V6m-7 6v6m11-6v6M3 3h18" />
                                    </svg>
                                    <h3 class="text-xl sm:text-2xl font-semibold" :class="feature.textColor">{{ feature.title }}</h3>
                                    <p class="text-sm sm:text-base" :class="feature.textColor === 'text-white' ? 'text-slate-100' : 'text-slate-600'">{{ feature.description }}</p>
                                    <p class="text-xs sm:text-sm" :class="feature.detailColor">{{ feature.detail }}</p>
                                </div>
                            </article>
                        </div>
                    </div>
                </section>

                <section id="workflow" class="relative px-4 py-12 sm:px-6 sm:py-16 lg:py-20">
                    <div class="relative mx-auto grid max-w-6xl gap-6 sm:gap-8 lg:gap-10 rounded-[24px] sm:rounded-[32px] lg:rounded-[36px] border border-slate-200 bg-gradient-to-br from-white via-slate-50 to-indigo-50/60 p-4 sm:p-6 lg:p-10 shadow-[0_35px_120px_rgba(79,70,229,0.15)] ring-1 ring-indigo-50 lg:grid-cols-[1.1fr_0.9fr]">
                        <div class="space-y-4 sm:space-y-6">
                            <p class="text-xs uppercase tracking-[0.3em] text-indigo-500">Workflow</p>
                            <h2 class="text-2xl sm:text-3xl font-semibold text-slate-900">4ステップで承認〜自動デプロイ</h2>
                            <p class="text-sm sm:text-base text-slate-600">AutoReleaseはGitHub Actionsと連動し、Pull Requestから承認、デプロイ実行までを一気通貫でトラッキングします。</p>
                            <div class="space-y-3 sm:space-y-4">
                                <div
                                    v-for="step in workflowSteps"
                                    :key="step.title"
                                    class="flex gap-3 sm:gap-4 p-4 sm:p-5 rounded-2xl border border-indigo-100 to-indigo-100/90"
                                >
                                    <div class="flex flex-shrink-0 justify-center items-center w-10 h-10 sm:w-12 sm:h-12 text-xs sm:text-sm font-semibold text-indigo-600 bg-indigo-100 rounded-2xl">
                                        {{ step.title.split('.')[0] }}
                                    </div>
                                    <div>
                                        <p class="text-base sm:text-lg font-semibold text-slate-900">{{ step.title }}</p>
                                        <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-slate-600">{{ step.detail }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="relative overflow-hidden rounded-[24px] sm:rounded-[32px] border border-indigo-100 bg-indigo-100 p-4 sm:p-6 lg:p-8 mt-6 lg:mt-0">
                            <div class="mb-4 sm:mb-6 space-y-2">
                                <p class="text-xs sm:text-sm text-indigo-500">Live status</p>
                                <p class="text-xl sm:text-2xl font-semibold text-slate-900">AutoRelease Bot</p>
                                <p class="text-xs sm:text-sm text-slate-500 break-words">github-actions · production</p>
                            </div>
                            <div class="space-y-4 sm:space-y-6 text-xs sm:text-sm text-slate-600">
                                <div class="p-4 sm:p-5 bg-white rounded-2xl sm:rounded-3xl shadow-sm">
                                    <p class="text-xs uppercase tracking-[0.3em] text-indigo-500">Command</p>
                                    <pre class="overflow-x-auto p-3 sm:p-4 mt-2 sm:mt-3 text-xs sm:text-sm text-emerald-200 rounded-xl sm:rounded-2xl bg-slate-900/90">workflow_dispatch --env=production --signed</pre>
                                    <p class="mt-2 text-xs text-slate-500 break-words">最終承認者: Client Team · IP 203.0.113.4</p>
                                </div>
                                <div class="space-y-3 sm:space-y-4">
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

                <section id="security" class="relative px-4 py-12 sm:px-6 sm:py-16 lg:py-20">
                    <div class="relative mx-auto space-y-6 sm:space-y-8 lg:space-y-10 max-w-6xl">
                        <div class="text-center">
                            <p class="text-xs uppercase tracking-[0.3em] text-indigo-500">Security</p>
                            <h2 class="mt-2 text-2xl sm:text-3xl font-semibold text-slate-900">セキュリティ対策</h2>
                            <p class="mt-3 sm:mt-4 text-sm sm:text-base text-slate-600 px-2">クライアントワークでも安心してご利用いただけるよう、多層的なセキュリティ対策を実装しています。</p>
                        </div>

                        <div class="grid gap-4 sm:gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div class="p-4 sm:p-6 bg-white rounded-2xl border shadow-sm border-slate-200">
                                <div class="flex gap-2 sm:gap-3 items-center mb-3 sm:mb-4">
                                    <div class="flex flex-shrink-0 justify-center items-center w-8 h-8 sm:w-10 sm:h-10 bg-indigo-100 rounded-xl">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-base sm:text-lg font-semibold text-slate-900">有効期限付きトークン</h3>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-600">承認URLは64文字の強力なトークンで保護され、5日間のみ有効です。期限切れ後は自動的に無効化されます。</p>
                            </div>

                            <div class="p-4 sm:p-6 bg-white rounded-2xl border shadow-sm border-slate-200">
                                <div class="flex gap-2 sm:gap-3 items-center mb-3 sm:mb-4">
                                    <div class="flex flex-shrink-0 justify-center items-center w-8 h-8 sm:w-10 sm:h-10 bg-green-100 rounded-xl">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-base sm:text-lg font-semibold text-slate-900">トークンベース認証</h3>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-600">デプロイログAPIは承認トークンによる認証で保護されています。トークンなしでは情報にアクセスできません。</p>
                            </div>

                            <div class="p-4 sm:p-6 bg-white rounded-2xl border shadow-sm border-slate-200">
                                <div class="flex gap-2 sm:gap-3 items-center mb-3 sm:mb-4">
                                    <div class="flex flex-shrink-0 justify-center items-center w-8 h-8 sm:w-10 sm:h-10 bg-yellow-100 rounded-xl">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-base sm:text-lg font-semibold text-slate-900">レート制限</h3>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-600">承認操作は1時間に5回まで、ページアクセスは1分間に30回までに制限されています。不正なアクセスを防止します。</p>
                            </div>

                            <div class="p-4 sm:p-6 bg-white rounded-2xl border shadow-sm border-slate-200">
                                <div class="flex gap-2 sm:gap-3 items-center mb-3 sm:mb-4">
                                    <div class="flex flex-shrink-0 justify-center items-center w-8 h-8 sm:w-10 sm:h-10 bg-purple-100 rounded-xl">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-base sm:text-lg font-semibold text-slate-900">XSS対策</h3>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-600">承認メッセージの表示にはDOMPurifyによるサニタイズ処理を実装し、悪意のあるスクリプトの実行を防止しています。</p>
                            </div>

                            <div class="p-4 sm:p-6 bg-white rounded-2xl border shadow-sm border-slate-200">
                                <div class="flex gap-2 sm:gap-3 items-center mb-3 sm:mb-4">
                                    <div class="flex flex-shrink-0 justify-center items-center w-8 h-8 sm:w-10 sm:h-10 bg-blue-100 rounded-xl">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-base sm:text-lg font-semibold text-slate-900">検索エンジン対策</h3>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-600">承認ページはnoindex設定とrobots.txtで検索エンジンからのインデックスを防止し、URLの漏洩リスクを低減しています。</p>
                            </div>

                            <div class="p-4 sm:p-6 bg-white rounded-2xl border shadow-sm border-slate-200">
                                <div class="flex gap-2 sm:gap-3 items-center mb-3 sm:mb-4">
                                    <div class="flex flex-shrink-0 justify-center items-center w-8 h-8 sm:w-10 sm:h-10 bg-red-100 rounded-xl">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-base sm:text-lg font-semibold text-slate-900">情報の非公開化</h3>
                                </div>
                                <p class="text-xs sm:text-sm text-slate-600">承認ページではGitHubリポジトリ情報などの機密情報を非表示にし、必要最小限の情報のみを表示しています。</p>
                            </div>
                        </div>

                        <div class="p-4 sm:p-6 lg:p-8 mt-6 sm:mt-8 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl border border-indigo-200">
                            <div class="flex gap-3 sm:gap-4 items-start">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 sm:w-7 sm:h-7 lg:w-8 lg:h-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="mb-2 text-lg sm:text-xl font-semibold text-slate-900">クライアントワークでも安心</h3>
                                    <p class="text-sm sm:text-base text-slate-700">
                                        AutoReleaseは、クライアントワークで使用されることを前提に設計されています。承認URLの有効期限管理、トークンベースの認証、レート制限など、多層的なセキュリティ対策により、機密情報の保護と不正アクセスの防止を実現しています。GitHubトークンは暗号化して保存され、承認ページでは必要最小限の情報のみが表示されます。
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="faq" class="relative px-4 pb-12 sm:px-6 sm:pb-16 lg:pb-20">
                    <div class="relative mx-auto space-y-6 sm:space-y-8 lg:space-y-10 max-w-5xl">
                        <div class="text-center">
                            <p class="text-xs uppercase tracking-[0.3em] text-indigo-500">FAQ</p>
                            <h2 class="mt-2 text-2xl sm:text-3xl font-semibold text-slate-900">よくある質問</h2>
                            <p class="mt-3 sm:mt-4 text-sm sm:text-base text-slate-600 px-2">導入前の疑問はすべて公開しています。ほかに質問があればSlackコミュニティへ。</p>
                        </div>

                        <div class="space-y-3 sm:space-y-4">
                            <div
                                v-for="(faq, index) in faqs"
                                :key="faq.question"
                                class="overflow-hidden bg-white rounded-2xl border border-transparent ring-1 ring-slate-100"
                            >
                                <button
                                    @click="toggleFaq(index)"
                                    class="flex justify-between items-center px-4 py-4 sm:px-6 sm:py-5 w-full text-base sm:text-lg font-semibold text-left text-slate-900 gap-2"
                                >
                                    <span class="flex-1 text-left">{{ faq.question }}</span>
                                    <svg
                                        :class="['h-5 w-5 sm:h-6 sm:w-6 flex-shrink-0 transition', openFaqIndex === index ? 'rotate-180 text-indigo-500' : 'text-slate-400']"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                </button>
                                <div v-show="openFaqIndex === index" class="px-4 pb-4 sm:px-6 sm:pb-6 text-xs sm:text-sm leading-relaxed text-slate-600">
                                    {{ faq.answer }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section v-if="!$page.props.auth?.user" class="px-4 pb-16 sm:px-6 sm:pb-20 lg:pb-24">
                    <div class="mx-auto max-w-5xl rounded-[24px] sm:rounded-[32px] lg:rounded-[40px] border border-slate-200 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 p-1">
                        <div class="rounded-[20px] sm:rounded-[28px] lg:rounded-[36px] bg-gradient-to-br from-white via-slate-50 to-indigo-50/50 px-4 py-8 sm:px-6 sm:py-10 lg:px-10 lg:py-12 text-center text-slate-900 shadow-[0_25px_80px_rgba(79,70,229,0.15)]">
                            <p class="text-xs uppercase tracking-[0.3em] text-indigo-500">Get started</p>
                            <h2 class="mt-3 sm:mt-4 text-xl sm:text-2xl lg:text-3xl font-semibold">GitHubアカウントで今日から運用を自動化</h2>
                            <p class="mt-2 sm:mt-3 text-sm sm:text-base text-slate-600">ログインして承認URLを送り、ワンクリックでGitHub Actionsを呼び出すだけ。初期費用はゼロです。</p>
                            <div class="flex flex-wrap gap-4 justify-center mt-6 sm:mt-8">
                                <button
                                    @click="openLoginModal"
                                    class="px-6 py-3 sm:px-8 sm:py-4 lg:px-10 lg:py-4 text-base sm:text-lg font-semibold text-white bg-indigo-600 rounded-2xl shadow-2xl transition shadow-indigo-200 hover:-translate-y-0.5"
                                >
                                    今すぐ試す
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            <AppFooter color-scheme="slate" padding="large" />
        </div>
    </div>
</template>
