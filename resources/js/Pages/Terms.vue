<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppFooter from '@/Components/AppFooter.vue';

const page = usePage();
const authUser = computed(() => page.props.auth?.user);
const lastUpdated = '2024年5月30日';
const goBack = () => window.history.back();

const termsSections = [
    {
        title: '1. 本規約の適用',
        description:
            '本規約は、AutoRelease（以下「本サービス」）の提供条件および本サービスの利用に関する当社と利用者との間の権利義務関係を定めるものです。本サービスを利用することで、本規約に同意したものとみなします。',
    },
    {
        title: '2. 定義',
        bullets: [
            '「利用者」: 本サービスにアクセスし、設定・承認・デプロイ等の機能を利用するすべての方',
            '「承認URL」: 利用者が生成する、クライアントが承認操作を行うための限定公開URL',
            '「外部サービス」: GitHub等、本サービスと連携する第三者のサービス',
        ],
    },
    {
        title: '3. 提供内容',
        bullets: [
            '承認画面の生成・配布および承認結果の保存',
            'GitHub Actions等の外部サービスを用いたデプロイ実行のトリガー',
            '承認・デプロイ履歴の表示およびダウンロード',
        ],
        note: 'ベータ提供中につき、機能は予告なく追加・変更・終了する場合があります。',
    },
    {
        title: '4. 利用登録とアカウント',
        bullets: [
            'GitHub等の認証情報を用いてログインする場合、当該認証情報の管理は利用者の責任で行うものとします。',
            '第三者による不正利用が判明した場合、速やかに当社に通知してください。',
        ],
    },
    {
        title: '5. 外部サービスとの連携',
        bullets: [
            '本サービスはGitHub APIを利用して組織・リポジトリ・ワークフロー情報を取得します。',
            '外部サービスの障害・仕様変更・API制限により本サービスの全部または一部が利用できなくなる場合があります。',
            '外部サービスの利用規約やポリシーを順守する責任は利用者が負うものとします。',
        ],
    },
    {
        title: '6. 利用者の責任',
        bullets: [
            '登録情報およびGitHub Secrets等の設定値は正確かつ最新の内容に保ってください。',
            '承認URLの取り扱いには十分注意し、第三者への漏洩防止に努めてください。',
            '本サービス上に入力・保存するデータについて必要なバックアップを行う責任を負います。',
        ],
    },
    {
        title: '7. 禁止事項',
        bullets: [
            '法令または公序良俗に違反する行為',
            '不正アクセス、リバースエンジニアリング、脆弱性の悪用等の行為',
            '第三者の権利を侵害する行為、またはそのおそれのある行為',
            '本サービスの運営を妨害する行為、過度な負荷をかける行為',
            '承認URL等を不正目的で共有・流用する行為',
        ],
    },
    {
        title: '8. サービスの変更・停止',
        bullets: [
            'システム保守、セキュリティ上の必要、外部サービスの不具合等により、事前の予告なくサービスを一時停止・変更する場合があります。',
            '重大な変更を行う場合、可能な範囲で本サービス内の告知等により通知します。',
        ],
    },
    {
        title: '9. 免責',
        bullets: [
            '本サービスの利用または利用不能により生じた損害について、当社に故意または重過失がある場合を除き、責任を負いません。',
            '外部サービス側の障害、設定誤り、認証情報の漏洩等に起因する損害について、当社は責任を負いません。',
            '本サービスで生成・保存されたデータの消失・毀損について、当社は保証しません。必要に応じて利用者自身でバックアップを取得してください。',
        ],
    },
    {
        title: '10. 知的財産権',
        bullets: [
            '本サービスに関する著作権、商標権その他一切の知的財産権は当社または正当な権利者に帰属します。',
            '利用者は、本サービスを本規約の範囲内で非独占的に利用する権利のみを取得します。',
        ],
    },
    {
        title: '11. 料金',
        description:
            'ベータ期間中は無料で提供します。正式リリース時に料金体系を導入する場合、事前に周知の上で新しい条件に同意いただいた後に適用します。',
    },
    {
        title: '12. データの取扱い',
        bullets: [
            '承認履歴・IPアドレス・タイムスタンプ等を、本サービスの提供・品質向上・不正防止のために利用します。',
            '個人情報の取扱いは別途定めるプライバシーポリシーに従います。',
        ],
    },
    {
        title: '13. 通知および連絡',
        description:
            '本サービスに関する通知は、サービス内の表示、メール送信その他当社が適切と判断する方法で行います。利用者からの連絡は、当社が指定する問い合わせ窓口に送付してください。',
    },
    {
        title: '14. 規約の変更',
        description:
            '本規約を改定する場合、変更後の内容を本サービス上に掲示し、合理的な期間の経過をもって効力を生じます。重要な変更については事前に通知します。',
    },
    {
        title: '15. 準拠法・管轄',
        description:
            '本規約は日本法に準拠し解釈されるものとし、本サービスに起因または関連する紛争については、当社の本店所在地を管轄する裁判所を第一審の専属的合意管轄とします。',
    },
];
</script>

<template>
    <Head title="利用規約 - AutoRelease" />

    <div class="overflow-hidden relative min-h-screen bg-gradient-to-br from-slate-100 via-slate-200 to-indigo-200/50 text-slate-900">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-10 -left-16 w-72 h-72 rounded-full blur-3xl bg-indigo-200/40"></div>
            <div class="absolute bottom-0 right-0 h-[26rem] w-[26rem] rounded-full bg-sky-200/40 blur-[120px]"></div>
        </div>

        <div class="flex relative z-10 flex-col min-h-screen">
            <header class="flex flex-wrap gap-6 justify-between items-center mx-auto mt-6 w-full max-w-6xl">
                <div class="flex gap-4 items-center">
                    <Link href="/">
                        <img src="/images/logo.png" alt="AutoRelease" class="h-10 object-contain" />
                    </Link>
                </div>

                <nav class="flex flex-wrap gap-6 items-center font-semibold text-md text-slate-600">
                    <Link href="/" class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex">ホーム</Link>
                    <Link href="/#features" class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex">機能</Link>
                    <Link href="/#workflow" class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex">ワークフロー</Link>
                    <Link href="/#faq" class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex">FAQ</Link>
                    <Link href="/docs" class="hidden transition text-slate-500 hover:text-slate-900 sm:inline-flex">ドキュメント</Link>

                    <Link
                        v-if="authUser"
                        href="/projects"
                        class="px-5 py-2 rounded-full border transition border-slate-200 text-slate-700 hover:border-slate-400 hover:text-slate-900"
                    >
                        プロジェクト一覧へ
                    </Link>

                    <template v-else>
                        <Link
                            href="/auth/github"
                            class="px-6 py-2 font-semibold text-white bg-indigo-600 rounded-full shadow-lg transition shadow-indigo-200 hover:opacity-90"
                        >
                            ログイン
                        </Link>
                    </template>
                </nav>
            </header>

            <main class="flex-1">
                    <div class="mx-auto max-w-5xl px-6 py-12">
                    <div class="mb-10 space-y-4">
                        <button
                            type="button"
                            @click="goBack"
                            class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900"
                        >
                            <svg class="mr-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            戻る
                        </button>
                        <p class="text-sm font-semibold text-indigo-600 uppercase tracking-wide">Terms of Service</p>
                        <h1 class="text-4xl font-bold text-slate-900">利用規約</h1>
                        <p class="text-slate-600">
                            AutoReleaseの利用条件を定めるものです。本サービスをご利用いただく前に必ずお読みください。
                        </p>
                        <p class="text-sm text-slate-500">最終更新日: {{ lastUpdated }}</p>
                    </div>

                    <div class="space-y-8">
                        <section
                            v-for="section in termsSections"
                            :key="section.title"
                            class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm"
                        >
                            <h2 class="text-xl font-semibold text-slate-900 mb-3">{{ section.title }}</h2>
                            <p v-if="section.description" class="text-slate-600 leading-relaxed">{{ section.description }}</p>
                            <ul v-if="section.bullets" class="mt-4 space-y-2 text-slate-600 list-disc list-inside">
                                <li v-for="(item, index) in section.bullets" :key="index">{{ item }}</li>
                            </ul>
                            <div v-if="section.note" class="mt-4 rounded-lg bg-indigo-50 border border-indigo-100 p-4 text-sm text-indigo-800">
                                {{ section.note }}
                            </div>
                        </section>

                        <section class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                            <h2 class="text-xl font-semibold text-slate-900 mb-3">お問い合わせ</h2>
                            <p class="text-slate-700">
                                本規約に関するご質問や、サービス利用に関するお問い合わせは下記へご連絡ください。できるだけ迅速に回答いたします。
                            </p>
                            <div class="mt-4 space-y-1 text-slate-800 text-sm">
                                <p>運営者：RUBYDESIGN.</p>
                                <p>代表者：松井貴史</p>
                                <p>連絡先メールアドレス：<a class="text-indigo-600 hover:text-indigo-800" href="mailto:works@rubydesign.jp">works@rubydesign.jp</a></p>
                            </div>
                        </section>
                    </div>
                </div>
            </main>

            <AppFooter color-scheme="slate" padding="large" />
        </div>
    </div>
</template>
