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
                        <h2 class="text-xl font-semibold text-gray-900">確認用URLを{{ hasStagingUrl ? '編集' : '登録' }}</h2>
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
                        <p class="mb-6">改善内容の入力時、パス（例: /about）等を入力すると自動的にテスト環境のURLを補完しリンクを生成できます</p>

                        <div>
                            <InputLabel for="staging_url_input" value="確認用のURL" class="font-bold text-gray-800" />
                            <TextInput
                                id="staging_url_input"
                                v-model="form.staging_url"
                                type="url"
                                class="block p-3 mt-1 w-full rounded-xl"
                                placeholder="https://staging.example.com"
                            />
                            <InputError class="mt-2" :message="form.errors.staging_url" />
                        </div>

                        <div class="p-4 bg-yellow-50 rounded-2xl border border-yellow-200">
                            <p class="text-sm text-yellow-800">
                                <strong>重要な推奨事項:</strong> テスト環境には必ずパスワード保護を設定してください。承認URLが漏洩した場合のセキュリティリスクを防ぐためです。
                            </p>
                        </div>

                        <div class="flex gap-3 justify-end pt-4 border-t border-gray-200">
                            <button
                                @click="emit('close')"
                                class="px-4 py-2 text-sm text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
                            >
                                キャンセル
                            </button>
                            <button
                                @click="emit('submit')"
                                :disabled="form.processing"
                                class="flex gap-2 items-center px-4 py-2 text-sm text-white bg-indigo-600 rounded-md hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
                            >
                                <div v-if="form.processing" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                <span>{{ form.processing ? '保存中...' : '保存' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    form: {
        type: Object,
        required: true,
    },
    hasStagingUrl: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'submit']);
</script>
