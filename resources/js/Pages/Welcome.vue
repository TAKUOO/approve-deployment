<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import AppFooter from '@/Components/AppFooter.vue';

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
    <Head title="WEBデザイナー向けリリース自動化システム - AutoRelease" />

    <div class="overflow-hidden relative min-h-screen bg-gradient-to-br from-slate-100 via-slate-200 to-indigo-200/50 text-slate-900">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-10 -left-16 w-72 h-72 rounded-full blur-3xl bg-indigo-200/40"></div>
            <div class="absolute bottom-0 right-0 h-[26rem] w-[26rem] rounded-full bg-sky-200/40 blur-[120px]"></div>
            <div class="absolute inset-x-0 top-1/3 mx-auto w-3/4 h-72 bg-gradient-to-r blur-3xl from-indigo-200/40 via-slate-100 to-purple-200/40"></div>
        </div>

        <div class="flex relative z-10 flex-col min-h-screen">
            <header class="flex flex-wrap gap-6 justify-between items-center mx-auto mt-6 w-full max-w-6xl">
                <div class="flex gap-4 items-center">
                    <div>
                        <img src="/images/logo.png" alt="AutoRelease" class="object-contain h-10" />
                    </div>
                </div>

                <nav class="flex flex-wrap gap-6 items-center font-semibold text-md text-slate-600">
                    <a class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex" href="#features">機能</a>
                    <a class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex" href="#workflow">ワークフロー</a>
                    <a class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex" href="#security">セキュリティ</a>
                    <a class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex" href="#faq">FAQ</a>
                    <Link :href="route('docs')" class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex">ドキュメント</Link>

                    <Link
                        v-if="$page.props.auth?.user"
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

            <main class="flex-1">
                <section class="px-6 pt-60 pb-20 sm:pt-16 lg:pb-26">
                    <div class="mx-auto grid max-w-6xl gap-12 lg:grid-cols-[1.05fr_0.95fr] items-center">
                        <div class="space-y-10">
                            <div>
                                <h1 class="text-4xl font-semibold tracking-tight leading-tight text-slate-900 sm:text-5xl lg:text-6xl">
                                    <span class="block mb-5">WEBデザイナー向け</span>
                                    <span class="block text-transparent whitespace-nowrap bg-clip-text bg-gradient-to-r from-indigo-500 via-sky-400 to-purple-500">リリース自動化システム</span>
                                </h1>
                                <p class="mt-6 text-lg leading-relaxed text-slate-600">
                                    クライアントがテスト環境を確認し、承認ボタンを押すだけで本番環境へ自動的にアップされます。Slackで催促する、手作業でアップロードする、といった二重作業をなくします。
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-4">
                                <button
                                    v-if="!$page.props.auth?.user"
                                    @click="openLoginModal"
                                    class="px-8 py-4 text-lg font-semibold text-white bg-indigo-600 rounded-2xl shadow-xl transition shadow-indigo-200 hover:-translate-y-0.5"
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
                                        <div class="p-4 bg-indigo-50 rounded-2xl border border-indigo-200">
                                            <p class="text-sm text-indigo-500">承認状況</p>
                                        <p class="text-lg font-semibold text-slate-900">本番反映の準備完了</p>
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
                                    <div class="p-5 mt-6 text-sm bg-emerald-50 rounded-2xl border border-emerald-300 text-slate-60">
                                        <p class="font-semibold text-slate-900">Actions ready</p>
                                        <p class="mt-1 text-xs text-slate-500">workflow_dispatch · secrets synced</p>
                                        <div class="flex justify-between items-center mt-4">
                                            <span class="text-2xl font-bold text-emerald-600">00:45</span>
                                            <span class="text-xs font-bold text-emerald-500">署名済みトークンで安全に実行</span>
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
                            <p class="text-slate-600">AutoReleaseは、テスト→承認→本番アップを一本化し、同じ作業を繰り返さないための仕組みを標準搭載しています。</p>
                        </div>
                        <div class="grid gap-8 md:grid-cols-3">
                            <article
                                v-for="feature in features"
                                :key="feature.title"
                                class="relative overflow-hidden rounded-[28px] border border-white/50 bg-gradient-to-br p-8 shadow-2xl ring-1"
                                :class="[feature.bgGradient, feature.shadowColor, feature.ringColor]"
                            >
                                <div class="relative space-y-4">
                                    <!-- メモのアイコン（承認キャンバス） -->
                                    <img v-if="feature.icon === 'note'" src="/images/note-icon.png" alt="承認キャンバス" class="object-contain w-16 h-16" />
                                    <!-- GitHub Octocatアイコン -->
                                    <img v-else-if="feature.icon === 'github'" src="/images/github-icon.png" alt="GitHub Actions連携" class="object-contain w-16 h-16" />
                                    <!-- タイムツリーアイコン -->
                                    <img v-else-if="feature.icon === 'timeline'" src="/images/timeline-icon.png" alt="履歴管理" class="object-contain w-16 h-16" />
                                    <svg v-else class="w-16 h-16 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 12V6m4 6V6m-7 6v6m11-6v6M3 3h18" />
                                    </svg>
                                    <h3 class="text-2xl font-semibold" :class="feature.textColor">{{ feature.title }}</h3>
                                    <p :class="feature.textColor === 'text-white' ? 'text-slate-100' : 'text-slate-600'">{{ feature.description }}</p>
                                    <p class="text-sm" :class="feature.detailColor">{{ feature.detail }}</p>
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
                            <p class="text-slate-600">AutoReleaseはGitHub Actionsと連動し、Pull Requestから承認、デプロイ実行までを一気通貫でトラッキングします。</p>
                            <div class="space-y-4">
                                <div
                                    v-for="step in workflowSteps"
                                    :key="step.title"
                                    class="flex gap-4 p-5 rounded-2xl border border-indigo-100 to-indigo-100/90"
                                >
                                    <div class="flex justify-center items-center w-12 h-12 text-sm font-semibold text-indigo-600 bg-indigo-100 rounded-2xl">
                                        {{ step.title.split('.')[0] }}
                                    </div>
                                    <div>
                                        <p class="text-lg font-semibold text-slate-900">{{ step.title }}</p>
                                        <p class="mt-2 text-sm text-slate-600">{{ step.detail }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="relative overflow-hidden rounded-[32px] border border-indigo-100 bg-indigo-100 p-8">
                            <div class="mb-6 space-y-2">
                                <p class="text-sm text-indigo-500">Live status</p>
                                <p class="text-2xl font-semibold text-slate-900">AutoRelease Bot</p>
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

                <section id="security" class="relative px-6 py-20">
                    <div class="relative mx-auto space-y-10 max-w-6xl">
                        <div class="text-center">
                            <p class="text-xs uppercase tracking-[0.3em] text-indigo-500">Security</p>
                            <h2 class="mt-2 text-3xl font-semibold text-slate-900">セキュリティ対策</h2>
                            <p class="mt-4 text-slate-600">クライアントワークでも安心してご利用いただけるよう、多層的なセキュリティ対策を実装しています。</p>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div class="p-6 bg-white rounded-2xl border shadow-sm border-slate-200">
                                <div class="flex gap-3 items-center mb-4">
                                    <div class="flex justify-center items-center w-10 h-10 bg-indigo-100 rounded-xl">
                                        <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-slate-900">有効期限付きトークン</h3>
                                </div>
                                <p class="text-sm text-slate-600">承認URLは64文字の強力なトークンで保護され、7日間のみ有効です。期限切れ後は自動的に無効化されます。</p>
                            </div>

                            <div class="p-6 bg-white rounded-2xl border shadow-sm border-slate-200">
                                <div class="flex gap-3 items-center mb-4">
                                    <div class="flex justify-center items-center w-10 h-10 bg-green-100 rounded-xl">
                                        <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-slate-900">トークンベース認証</h3>
                                </div>
                                <p class="text-sm text-slate-600">デプロイログAPIは承認トークンによる認証で保護されています。トークンなしでは情報にアクセスできません。</p>
                            </div>

                            <div class="p-6 bg-white rounded-2xl border shadow-sm border-slate-200">
                                <div class="flex gap-3 items-center mb-4">
                                    <div class="flex justify-center items-center w-10 h-10 bg-yellow-100 rounded-xl">
                                        <svg class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-slate-900">レート制限</h3>
                                </div>
                                <p class="text-sm text-slate-600">承認操作は1時間に5回まで、ページアクセスは1分間に30回までに制限されています。不正なアクセスを防止します。</p>
                            </div>

                            <div class="p-6 bg-white rounded-2xl border shadow-sm border-slate-200">
                                <div class="flex gap-3 items-center mb-4">
                                    <div class="flex justify-center items-center w-10 h-10 bg-purple-100 rounded-xl">
                                        <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-slate-900">XSS対策</h3>
                                </div>
                                <p class="text-sm text-slate-600">承認メッセージの表示にはDOMPurifyによるサニタイズ処理を実装し、悪意のあるスクリプトの実行を防止しています。</p>
                            </div>

                            <div class="p-6 bg-white rounded-2xl border shadow-sm border-slate-200">
                                <div class="flex gap-3 items-center mb-4">
                                    <div class="flex justify-center items-center w-10 h-10 bg-blue-100 rounded-xl">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-slate-900">検索エンジン対策</h3>
                                </div>
                                <p class="text-sm text-slate-600">承認ページはnoindex設定とrobots.txtで検索エンジンからのインデックスを防止し、URLの漏洩リスクを低減しています。</p>
                            </div>

                            <div class="p-6 bg-white rounded-2xl border shadow-sm border-slate-200">
                                <div class="flex gap-3 items-center mb-4">
                                    <div class="flex justify-center items-center w-10 h-10 bg-red-100 rounded-xl">
                                        <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-slate-900">情報の非公開化</h3>
                                </div>
                                <p class="text-sm text-slate-600">承認ページではGitHubリポジトリ情報などの機密情報を非表示にし、必要最小限の情報のみを表示しています。</p>
                            </div>
                        </div>

                        <div class="p-8 mt-8 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl border border-indigo-200">
                            <div class="flex gap-4 items-start">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="mb-2 text-xl font-semibold text-slate-900">クライアントワークでも安心</h3>
                                    <p class="text-slate-700">
                                        AutoReleaseは、クライアントワークで使用されることを前提に設計されています。承認URLの有効期限管理、トークンベースの認証、レート制限など、多層的なセキュリティ対策により、機密情報の保護と不正アクセスの防止を実現しています。GitHubトークンは暗号化して保存され、承認ページでは必要最小限の情報のみが表示されます。
                                    </p>
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
                                class="overflow-hidden bg-white rounded-2xl border border-transparent ring-1 ring-slate-100"
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
                            <h2 class="mt-4 text-3xl font-semibold">GitHubアカウントで今日から運用を自動化</h2>
                            <p class="mt-3 text-slate-600">ログインして承認URLを送り、ワンクリックでGitHub Actionsを呼び出すだけ。初期費用はゼロです。</p>
                            <div class="flex flex-wrap gap-4 justify-center mt-8">
                                <button
                                    @click="openLoginModal"
                                    class="px-10 py-4 text-lg font-semibold text-white bg-indigo-600 rounded-2xl shadow-2xl transition shadow-indigo-200 hover:-translate-y-0.5"
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
    </div>
</template>
