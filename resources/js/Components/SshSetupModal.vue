<template>
    <Modal :show="show" @close="close" max-width="2xl">
        <div class="p-6 bg-white text-slate-900">
            <div class="flex justify-between items-start gap-4">
                <div>
                    <h2 class="text-2xl font-semibold">SSH設定の詳細</h2>
                    <p class="mt-2 text-sm text-slate-600">
                        デプロイのためにGitHub Secretsとサーバー側のSSH設定が必要です。
                    </p>
                </div>
                <button
                    type="button"
                    @click="close"
                    class="p-2 text-slate-400 rounded-md transition hover:text-slate-600 hover:bg-slate-100"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mt-6 space-y-6 text-sm text-slate-700">
                <div class="p-4 bg-slate-50 rounded-lg border border-slate-200">
                    <h3 class="text-base font-semibold text-slate-900">1. GitHub Secretsに登録</h3>
                    <p class="mt-2">
                        リポジトリの <strong>Settings → Secrets and variables → Actions</strong> に、以下を登録します。
                    </p>
                    <ul class="mt-3 space-y-2">
                        <li><code class="px-2 py-0.5 bg-white rounded border">SSH_HOST</code> SSHサーバーのアドレス</li>
                        <li><code class="px-2 py-0.5 bg-white rounded border">SSH_USER</code> SSHユーザー名</li>
                        <li><code class="px-2 py-0.5 bg-white rounded border">SSH_PORT</code> ポート番号（省略可、通常22）</li>
                        <li><code class="px-2 py-0.5 bg-white rounded border">SSH_PRIVATE_KEY</code> 秘密鍵の内容</li>
                        <li><code class="px-2 py-0.5 bg-white rounded border">SERVER_DIR</code> デプロイ先ディレクトリパス（省略可、デフォルト: /public_html/）</li>
                    </ul>
                </div>

                <div class="p-4 bg-slate-50 rounded-lg border border-slate-200">
                    <h3 class="text-base font-semibold text-slate-900">2. 秘密鍵の確認</h3>
                    <p class="mt-2">秘密鍵の内容を <code>SSH_PRIVATE_KEY</code> に設定します。</p>
                    <pre class="mt-3 p-3 rounded bg-slate-900 text-slate-100 text-xs"><code>cat ~/.ssh/id_rsa</code></pre>
                    <p class="mt-2 text-xs text-slate-500">
                        出力された <code>-----BEGIN RSA PRIVATE KEY-----</code> から最後までをコピーしてください。
                    </p>
                </div>

                <div class="p-4 bg-slate-50 rounded-lg border border-slate-200">
                    <h3 class="text-base font-semibold text-slate-900">3. 公開鍵をサーバーに追加</h3>
                    <p class="mt-2">公開鍵をサーバー側の <code>~/.ssh/authorized_keys</code> に追加します。</p>
                    <pre class="mt-3 p-3 rounded bg-slate-900 text-slate-100 text-xs"><code>cat ~/.ssh/id_rsa.pub</code></pre>
                </div>

                <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200 text-yellow-900">
                    <p class="text-xs">
                        <strong>注意:</strong> Secrets名は大文字・小文字を区別します。SSH接続できない場合は、サーバーのアクセス権限とファイアウォール設定も確認してください。
                    </p>
                </div>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import Modal from '@/Components/Modal.vue';

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const close = () => {
    emit('close');
};
</script>
