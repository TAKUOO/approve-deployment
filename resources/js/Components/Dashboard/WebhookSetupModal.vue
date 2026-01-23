<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            class="flex overflow-y-auto fixed inset-0 z-50 justify-center items-start p-4 bg-black bg-opacity-50"
            @click.self="emit('close')"
        >
            <div class="relative mx-auto my-8 w-full max-w-2xl bg-white rounded-3xl shadow-3xl">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-gray-900">GitHub Webhook設定手順</h2>
                        <button
                            @click="emit('close')"
                            class="p-2 text-gray-400 rounded-md transition-colors hover:text-gray-600 hover:bg-gray-100"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="space-y-4 text-gray-700">
                        <div class="p-4 mb-6 bg-blue-50 rounded-2xl border border-blue-200">
                            <p class="text-sm text-blue-800">
                                GitHub Webhookを設定することで、デプロイ完了を自動的に検知できます。設定しない場合、デプロイステータスの更新が遅れる可能性があります。
                            </p>
                        </div>

                        <div>
                            <h3 class="mb-6 font-bold text-gray-500 text-md">設定手順</h3>
                            <ol class="ml-6 space-y-4 list-decimal text-gray-600">
                                <li>
                                    <p class="text-md">GitHubリポジトリの <strong>Settings</strong> → <strong>Webhooks</strong> → <strong>Add webhook</strong> を開く</p>
                                </li>
                                <li>
                                    <p class="text-md">
                                        <strong>Payload URL</strong> に以下を入力：
                                        <code class="block px-4 py-4 mt-1 text-sm text-gray-500 bg-gray-100 rounded-xl">{{ webhookUrl || 'https://yourdomain.com/api/github/webhook' }}</code>
                                    </p>
                                </li>
                                <li>
                                    <p class="text-md"><strong>Content type</strong> を <code class="px-1 py-0.5 text-xs bg-gray-100 rounded">application/json</code> に設定</p>
                                </li>
                                <li>
                                    <p class="text-md"><strong>Events</strong> で <strong>Workflow run</strong> を選択（または <strong>Let me select individual events</strong> を選択して <strong>Workflow run</strong> にチェック）</p>
                                </li>
                                <li>
                                    <p class="text-md"><strong>Active</strong> にチェックを入れて <strong>Add webhook</strong> をクリック</p>
                                </li>
                            </ol>
                        </div>

                        <div class="p-4 bg-yellow-50 rounded-2xl border border-yellow-200">
                            <p class="text-sm text-yellow-800">
                                <strong>注意:</strong> Webhookを設定すると、GitHub Actionsのワークフロー実行が完了した際に自動的にデプロイステータスが更新されます。設定しない場合、手動で同期する必要があります。
                            </p>
                        </div>

                        <div class="flex gap-3 justify-end pt-4 border-t border-gray-200">
                            <button
                                @click="emit('close')"
                                class="px-4 py-2 text-sm text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
                            >
                                閉じる
                            </button>
                            <button
                                v-if="canCheck"
                                @click="emit('check')"
                                class="px-4 py-2 text-sm text-white bg-indigo-600 rounded-md hover:bg-indigo-700"
                            >
                                ステータスを再確認
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    webhookUrl: {
        type: String,
        default: '',
    },
    canCheck: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'check']);
</script>
