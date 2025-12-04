<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppHeader from '@/Components/AppHeader.vue';
import AppFooter from '@/Components/AppFooter.vue';
const lastUpdated = '2024年5月30日';
const goBack = () => window.history.back();

const policySections = [
    {
        title: '1. 基本方針',
        description:
            'AutoRelease（以下「本サービス」）は、承認・デプロイ業務に必要な最小限の情報を適切に管理し、利用目的の範囲内で取り扱います。本ポリシーは、本サービスにおける個人情報および関連情報の取扱いを定めるものです。',
    },
    {
        title: '2. 取得する情報',
        bullets: [
            'アカウント情報: GitHubアカウントID、表示名、メールアドレス等、認証に必要な情報',
            'サービス利用情報: 承認・デプロイの履歴、入力されたコメント、操作日時、IPアドレス、ユーザーエージェント',
            'プロジェクト設定情報: テスト/本番URL、GitHubリポジトリやワークフローの識別子、デプロイ先パス等',
            '技術情報: Cookieや類似技術を用いて収集されるセッション情報、ブラウザ情報',
        ],
    },
    {
        title: '3. 利用目的',
        bullets: [
            '本サービスの提供、維持、保守および機能改善',
            '承認・デプロイ履歴の保存、表示、エクスポート',
            '不正利用の防止、セキュリティ対策、障害発生時の調査',
            '利用動向の把握によるUI/UX改善',
            '法令遵守や問い合わせ対応のための本人確認',
        ],
    },
    {
        title: '4. Cookie等の利用',
        description:
            '本サービスは、セッション管理やログイン状態の維持のためにCookie等の技術を利用します。ブラウザの設定によりCookieを無効化できますが、サービスの一部が正常に動作しない場合があります。',
    },
    {
        title: '5. 外部サービスとの連携',
        bullets: [
            'GitHub APIを通じて取得する組織・リポジトリ・ワークフロー情報は、本サービスの設定およびデプロイ実行のために利用します。',
            '外部サービスの障害やポリシー変更により、一部機能が利用できなくなる場合があります。',
            '外部サービス側でのデータ取扱いは、それぞれのサービスが定める規約・ポリシーに従います。',
        ],
    },
    {
        title: '6. 情報の第三者提供',
        description:
            '法令に基づく場合、生命・身体・財産の保護が必要な場合、または本人の同意がある場合を除き、取得情報を第三者に提供しません。',
    },
    {
        title: '7. 安全管理措置',
        bullets: [
            'アクセス権限の最小化とログの取得により、利用者データへの不要なアクセスを防止します。',
            '通信は原則としてTLSで保護します。',
            '外部サービスへのAPI呼び出しに利用するトークン等は、適切な方法で暗号化・保管します。',
        ],
    },
    {
        title: '8. 委託',
        description:
            'システム運用やデータ保管等を外部に委託する場合、委託先を適切に選定し、必要な契約を締結した上で監督を行います。',
    },
    {
        title: '9. 開示・訂正・利用停止等',
        description:
            '利用者からの個人情報の開示、訂正、追加、削除、利用停止等の請求には、法令の範囲内で速やかに対応します。ご希望の際は下記窓口までご連絡ください。',
    },
    {
        title: '10. データの保存期間',
        description:
            '承認履歴・デプロイ履歴等は、利用目的を達成するために必要な期間保存します。不要となったデータは、遅滞なく削除または匿名化します。',
    },
    {
        title: '11. 本ポリシーの変更',
        description:
            '運用実態や法令の変更に応じて、本ポリシーを改定する場合があります。重要な変更がある場合は、本サービス上で通知します。',
    },
];
</script>

<template>
    <Head title="プライバシーポリシー - AutoRelease" />

    <div class="overflow-hidden relative min-h-screen bg-gradient-to-br from-slate-100 via-slate-200 to-indigo-200/50 text-slate-900">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-10 -left-16 w-72 h-72 rounded-full blur-3xl bg-indigo-200/40"></div>
            <div class="absolute bottom-0 right-0 h-[26rem] w-[26rem] rounded-full bg-sky-200/40 blur-[120px]"></div>
        </div>

        <div class="flex relative z-10 flex-col min-h-screen">
            <AppHeader />

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
                        <p class="text-sm font-semibold text-indigo-600 uppercase tracking-wide">Privacy Policy</p>
                        <h1 class="text-4xl font-bold text-slate-900">プライバシーポリシー</h1>
                        <p class="text-slate-600">
                            AutoReleaseにおける情報の収集・利用・管理方法について定めています。安心してご利用いただくため、必ずご確認ください。
                        </p>
                        <p class="text-sm text-slate-500">最終更新日: {{ lastUpdated }}</p>
                    </div>

                    <div class="space-y-8">
                        <section
                            v-for="section in policySections"
                            :key="section.title"
                            class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm"
                        >
                            <h2 class="text-xl font-semibold text-slate-900 mb-3">{{ section.title }}</h2>
                            <p v-if="section.description" class="text-slate-600 leading-relaxed">{{ section.description }}</p>
                            <ul v-if="section.bullets" class="mt-4 space-y-2 text-slate-600 list-disc list-inside">
                                <li v-for="(item, index) in section.bullets" :key="index">{{ item }}</li>
                            </ul>
                        </section>

                        <section class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                            <h2 class="text-xl font-semibold text-slate-900 mb-3">お問い合わせ</h2>
                            <p class="text-slate-700">
                                本ポリシーや情報の取扱いに関するご質問・ご要望は、下記窓口までお知らせください。内容を確認し、速やかに対応いたします。
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

            <footer class="px-6 py-10 space-y-2 text-xs text-center text-slate-500">
                <div class="flex flex-wrap gap-4 justify-center text-sm text-slate-600">
                    <a href="https://rubydesign.jp/" target="_blank" rel="noopener noreferrer" class="underline hover:text-slate-900">運営元</a>
                    <a href="/terms" class="underline hover:text-slate-900">利用規約</a>
                    <a href="/privacy" class="underline hover:text-slate-900">プライバシーポリシー</a>
                </div>
                <div>&copy; {{ new Date().getFullYear() }} AutoRelease. All rights reserved.</div>
            </footer>
        </div>
    </div>
</template>
