<template>
    <Head title="改善内容画面" />
    <AuthenticatedLayout>
        <div class="w-full min-h-screen bg-indigo-100">
            <div class="sticky top-0 z-30 bg-gray-100/95">
                <div class="flex flex-wrap gap-3 justify-between items-center px-6 py-2 mx-auto">
                    <div class="gap-4 text-sm">
                        <span class="font-semibold text-gray-500">URL：</span>
                        <a
                            v-if="reviewUrl"
                            :href="reviewUrl"
                            target="_blank"
                            class="text-indigo-600 underline break-all"
                        >
                            {{ reviewUrl }}
                        </a>
                        <span v-else class="text-gray-400">未設定</span>
                    </div>
                    <div class="flex gap-2 items-center">
                        <button
                            type="button"
                            class="px-3 py-2 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
                            @click="goBack"
                        >
                            戻る
                        </button>
                        <button
                            type="button"
                            class="px-3 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg disabled:opacity-60"
                            :disabled="generatingUrl"
                            @click="generateApprovalUrl"
                        >
                            {{ generatingUrl ? '生成中...' : '共有する' }}
                        </button>
                    </div>
                </div>
            </div>


            <div class="mx-auto max-w-7xl">
                <div v-if="generatedApprovalUrl" class="p-3 mt-4 bg-indigo-50 rounded-xl border border-indigo-200">
                    <div class="flex flex-wrap gap-2 items-center">
                        <div class="text-sm text-indigo-700">共有URLを生成しました。</div>
                        <button
                            type="button"
                            class="px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 rounded-full"
                            @click="copyGeneratedApprovalUrl"
                        >
                            {{ generatedUrlCopied ? 'コピーしました' : 'URLをコピー' }}
                        </button>
                    </div>
                    <div class="mt-2 text-xs text-indigo-700 break-all">{{ generatedApprovalUrl }}</div>
                </div>

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
                />
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
