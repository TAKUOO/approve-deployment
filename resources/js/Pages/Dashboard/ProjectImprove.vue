<template>
    <Head title="改善内容画面" />
    <AuthenticatedLayout>
        <div class="relative w-full min-h-screen bg-indigo-100">
            <div class="mx-auto max-w-7xl">
                <PinnedReviewCanvas
                    :review-url="reviewUrl"
                    :approval-message-id="approvalMessageId"
                    :list-route="{ name: 'projects.comments.index', params: { project: project.id } }"
                    :create-route="{ name: 'projects.comments.store', params: { project: project.id } }"
                    :update-route="{ name: 'projects.comments.update', params: { project: project.id } }"
                    :delete-route="{ name: 'projects.comments.destroy', params: { project: project.id } }"
                    :can-edit="true"
                    :can-create="true"
                    :require-author-name="false"
                    :poll-interval-ms="5000"
                    frame-height="100vh"
                >
                    <template #headerActions>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                class="flex-shrink-0 px-3 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded-xl transition hover:bg-gray-200"
                                @click="goBack"
                            >
                                戻る
                            </button>
                            <button
                                type="button"
                                class="flex-1 min-w-0 px-3 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-xl transition hover:bg-indigo-700 disabled:opacity-60"
                                :disabled="generatingUrl"
                                @click="generateApprovalUrl"
                            >
                                {{ generatingUrl ? '生成中...' : '共有する' }}
                            </button>
                        </div>
                    </template>
                </PinnedReviewCanvas>
            </div>

            <!-- 共有URL生成時のフローティング通知 -->
            <div
                v-if="generatedApprovalUrl"
                class="fixed left-1/2 bottom-6 z-50 w-full max-w-md -translate-x-1/2 p-4 mx-4 bg-white rounded-xl border border-indigo-200 shadow-lg"
            >
                <div class="flex justify-between gap-2 items-start">
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap gap-2 items-center">
                            <span class="text-sm font-medium text-indigo-700">共有URLを生成しました</span>
                            <button
                                type="button"
                                class="px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 rounded-full hover:bg-indigo-700"
                                @click="copyGeneratedApprovalUrl"
                            >
                                {{ generatedUrlCopied ? 'コピーしました' : 'URLをコピー' }}
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-gray-600 break-all">{{ generatedApprovalUrl }}</p>
                    </div>
                    <button
                        type="button"
                        class="flex-shrink-0 p-1 rounded text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
                        title="閉じる"
                        @click="generatedApprovalUrl = ''"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppFooter from '@/Components/AppFooter.vue';
import PinnedReviewCanvas from '@/Components/Review/PinnedReviewCanvas.vue';

const props = defineProps({
    project: Object,
    approvalMessageId: [Number, String, null],
});

const reviewUrl = ref(props.project?.staging_url || '');
const generatingUrl = ref(false);
const generatedApprovalUrl = ref('');
const generatedUrlCopied = ref(false);
const approvalMessageId = ref(props.approvalMessageId ? Number(props.approvalMessageId) : null);

const reviewMessageStorageKey = (projectId) => `project_review_msg_${projectId}`;

const ensureApprovalMessage = async () => {
    if (approvalMessageId.value || !props.project?.id) return;

    const response = await fetch(route('projects.approval-messages.store', props.project.id), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify({
            message: '',
        }),
    });

    if (response.ok) {
        const data = await response.json();
        approvalMessageId.value = data.message_id;
        if (props.project?.id) {
            localStorage.setItem(reviewMessageStorageKey(props.project.id), String(data.message_id));
        }
    }
};

const generateApprovalUrl = async () => {
    if (!props.project?.id) return;

    generatingUrl.value = true;
    try {
        if (!approvalMessageId.value) {
            await ensureApprovalMessage();
        }

        const response = await fetch(route('projects.generate-approval-url', props.project.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                message: '',
                message_id: approvalMessageId.value,
            }),
        });

        if (response.ok) {
            const data = await response.json();
            generatedApprovalUrl.value = data.approval_url;
            generatedUrlCopied.value = false;
        } else {
            alert('共有URLの生成に失敗しました');
        }
    } catch (error) {
        console.error('Error generating approval URL:', error);
        alert('共有URLの生成に失敗しました');
    } finally {
        generatingUrl.value = false;
    }
};

const copyGeneratedApprovalUrl = async () => {
    if (!generatedApprovalUrl.value) return;
    try {
        await navigator.clipboard.writeText(generatedApprovalUrl.value);
        generatedUrlCopied.value = true;
        setTimeout(() => {
            generatedUrlCopied.value = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy:', err);
    }
};

const goBack = () => {
    router.visit(route('projects.index'));
};

onMounted(() => {
    if (!approvalMessageId.value && props.project?.id) {
        const storedId = localStorage.getItem(reviewMessageStorageKey(props.project.id));
        if (storedId) {
            approvalMessageId.value = Number(storedId);
        }
    }

    ensureApprovalMessage();
});
</script>
