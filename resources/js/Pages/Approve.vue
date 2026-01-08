<template>
    <Head>
        <title>サイト更新の承認 - AutoRelease</title>
        <meta name="robots" content="noindex, nofollow">
    </Head>
    
    <div class="flex justify-center px-4 py-12 min-h-screen bg-indigo-50 sm:px-6 lg:px-8">
        <div class="p-8 space-y-12 w-full max-w-4xl bg-white rounded-2xl">
            <div>
                <h2 class="mt-6 text-3xl font-extrabold text-center text-gray-900">
                    サイト更新の承認
                </h2>
                <p class="mt-4 text-sm text-center text-gray-600">
                    変更内容を確認して、実際のサイト（公開中のサイト）に反映するかどうかを承認できます。
                </p>
                
                <!-- 重要な注意書き -->
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
                <div v-if="approvalMessage">
                    <div class="overflow-hidden bg-white rounded-lg border border-indigo-500">
                        <div class="px-4 py-3 bg-indigo-500 border-b border-indigo-600">
                            <h3 class="text-sm font-semibold text-white">改善内容</h3>
                        </div>
                        <div class="p-4 bg-white">
                            <div class="text-sm markdown-body" v-html="formattedMessage"></div>
                        </div>
                    </div>
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
                            <PrimaryButton :disabled="processing" class="px-6 py-3 text-base">
                                問題ないので公開する
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
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AppFooter from '@/Components/AppFooter.vue';
import { marked } from 'marked';
import DOMPurify from 'dompurify';
import 'github-markdown-css/github-markdown-light.css';

const props = defineProps({
    project: Object,
    token: String,
    approvalMessage: Object,
});

const form = useForm({
    msg: props.approvalMessage?.id || null,
});

const processing = false;

// カスタムレンダラーを作成してすべてのリンクを別タブで開く
marked.use({
    gfm: true,
    breaks: true,
    renderer: {
        link({ href, title, text }) {
            const titleAttr = title ? ` title="${title}"` : '';
            return `<a href="${href}"${titleAttr} target="_blank" rel="noopener noreferrer" class="text-blue-600 underline hover:text-blue-800">${text}</a>`;
        },
    },
});

// メッセージをフォーマット（改善ページURLを自動リンク化 + GitHub風マークダウン）
const formattedMessage = computed(() => {
    if (!props.approvalMessage || !props.approvalMessage.message) {
        return '';
    }

    let message = props.approvalMessage.message;
    const stagingUrl = props.project.staging_url.replace(/\/$/, ''); // 末尾のスラッシュを削除

    // 改善ページURLのパターンを検出してリンク化
    // 例: "/about" や "改善ページ: /about, /products/item-1"
    message = message.replace(/(?:改善ページ|改善したページ|ページ)[:：]\s*([^\n]+)/g, (match, paths) => {
        const pageLinks = paths.split(',').map(path => {
            const trimmedPath = path.trim();
            if (trimmedPath.startsWith('/')) {
                const fullUrl = stagingUrl + trimmedPath;
                return `<a href="${fullUrl}" target="_blank" class="text-blue-600 underline hover:text-blue-800">${stagingUrl}${trimmedPath}</a>`;
            }
            return trimmedPath;
        }).join(', ');
        return match.replace(paths, pageLinks);
    });

    // 単独のパス（行頭が "/" で始まる）もリンク化
    message = message.replace(/^(\s*)(\/[^\s\n]+)/gm, (match, indent, path) => {
        const fullUrl = stagingUrl + path;
        return `${indent}<a href="${fullUrl}" target="_blank" class="text-blue-600 underline hover:text-blue-800">${stagingUrl}${path}</a>`;
    });

    const html = marked.parse(message);
    // DOMPurifyでtarget="_blank"とrel="noopener noreferrer"を許可
    return DOMPurify.sanitize(html, {
        ADD_ATTR: ['target', 'rel'],
    }); // XSS対策
});

const submit = () => {
    form.post(route('approve.approve', props.token));
};
</script>

<style scoped>
.markdown-body {
    background-color: transparent;
    line-height: 1.75;
}

/* 段落の余白 */
.markdown-body p {
    margin: 1rem 0;
}

/* 見出しの余白 */
.markdown-body h1,
.markdown-body h2,
.markdown-body h3,
.markdown-body h4,
.markdown-body h5,
.markdown-body h6 {
    margin: 1.5rem 0 1rem 0;
    font-weight: 600;
}

.markdown-body h1 {
    font-size: 1.875rem;
}

.markdown-body h2 {
    font-size: 1.5rem;
}

.markdown-body h3 {
    font-size: 1.25rem;
}

.markdown-body h4 {
    font-size: 1.125rem;
}

/* リストの余白 */
.markdown-body ul,
.markdown-body ol {
    list-style-type: disc;
    list-style-position: inside;
    padding-left: 1.25rem;
    margin: 1rem 0;
}

.markdown-body ol {
    list-style-type: decimal;
    padding-left: 1.5rem;
}

.markdown-body li {
    margin: 0.5rem 0;
    line-height: 1.75;
}

/* リスト内の段落 */
.markdown-body li p {
    margin: 0.5rem 0;
}
</style>

<style>
/* コードブロックの背景色を調整（github-markdown-cssを上書き） */
.markdown-body pre {
    background-color: rgb(238, 242, 255) !important; /* indigo-50 */
    border-radius: 0.375rem !important;
    padding: 1rem !important;
    margin: 1.25rem 0 !important;
    overflow-x: auto;
}

.markdown-body code {
    background-color: rgb(238, 242, 255) !important; /* indigo-50 */
    border: 1px solid rgb(165, 180, 252) !important; /* indigo-300 */
    border-radius: 0.25rem !important;
    padding: 0.125rem 0.375rem !important;
    font-size: 0.875em;
    margin: 0 0.125rem;
}

.markdown-body pre code {
    background-color: transparent !important;
    border: none !important;
    padding: 0 !important;
}

/* テーブルのスタイル調整 */
.markdown-body table {
    background-color: white !important;
    border: 1px solid rgb(165, 180, 252) !important; /* indigo-300 */
    border-radius: 0.375rem !important;
    margin: 1.25rem 0 !important;
}

.markdown-body table th,
.markdown-body table td {
    border: 1px solid rgb(165, 180, 252) !important; /* indigo-300 */
}

.markdown-body table th {
    background-color: rgb(238, 242, 255) !important; /* indigo-50 */
}

/* 引用ブロックのスタイル調整 */
.markdown-body blockquote {
    background-color: rgb(238, 242, 255) !important; /* indigo-50 */
    border-left: 4px solid rgb(99, 102, 241) !important; /* indigo-500 */
    padding: 0.75rem 1rem !important;
    margin: 1.25rem 0 !important;
}

/* 水平線の余白 */
.markdown-body hr {
    margin: 1.5rem 0 !important;
    border-color: rgb(199, 210, 254) !important; /* indigo-200 */
}

/* リンクのスタイル */
.markdown-body a {
    margin: 0 0.125rem;
}
</style>

