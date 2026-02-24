<template>
    <div>
        <div class="flex flex-wrap gap-2 justify-between items-center">
            <div class="text-sm text-gray-600">
                <span v-if="!reviewUrl">URLを入力するとここにサイトが表示されます。</span>
                <span v-else-if="!commentingEnabled">改善内容を作成するとピン留めコメントが使えます。</span>
            </div>
            <div class="flex gap-2 items-center">
                <button
                    v-if="hasNewComments"
                    type="button"
                    class="px-3 py-1.5 text-xs font-semibold text-amber-700 bg-amber-50 rounded-full border border-amber-200"
                    @click="markAllSeen"
                >
                    新しいコメント
                </button>
            </div>
        </div>

        <div ref="canvasRef" class="overflow-hidden relative shadow-xl">
            <div v-if="!reviewUrl" class="flex justify-center items-center w-full text-sm text-gray-500" :style="{ height: frameHeight }">
                右上の入力欄にURLを入力して表示してください。
            </div>
            <div v-else>
                <iframe
                    ref="iframeRef"
                    :src="reviewUrl"
                    class="w-full"
                    :style="{ height: frameHeight }"
                    frameborder="0"
                    sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-popups-to-escape-sandbox"
                    @error="handleIframeError"
                ></iframe>
                <div
                    v-if="iframeError"
                    class="flex absolute inset-0 justify-center items-center text-sm text-gray-600 bg-white/90"
                >
                    <div class="space-y-2 text-center">
                        <div>このサイトは埋め込みを許可していない可能性があります。</div>
                        <a :href="reviewUrl" target="_blank" class="text-indigo-600 underline">新しいタブで開く</a>
                    </div>
                </div>
            </div>

            <div
                v-if="pinMode && reviewUrl && commentingEnabled && canCreate"
                class="absolute inset-0 cursor-crosshair"
                @mousedown.prevent="handlePointerDown"
                @mousemove.prevent="handlePointerMove"
                @mouseup.prevent="handlePointerUp"
                @mouseleave="handlePointerCancel"
            ></div>

            <!-- 選択範囲（保存済み） -->
            <button
                v-for="comment in commentsWithRect"
                :key="`rect_${comment.id}`"
                type="button"
                class="absolute z-[5] rounded-lg bg-amber-200/30 ring-2 ring-amber-400/50 hover:bg-amber-200/40 transition"
                :style="rectStyle(comment)"
                @click.stop="openComment(comment)"
                title="コメントの範囲"
            ></button>

            <!-- 選択範囲（作成中プレビュー） -->
            <div
                v-if="dragPreview"
                class="absolute z-[6] rounded-lg bg-amber-200/25 ring-2 ring-amber-400/60 pointer-events-none"
                :style="dragPreview"
            ></div>

            <button
                v-for="comment in comments"
                :key="comment.id"
                type="button"
                class="flex absolute z-10 justify-center items-center w-6 h-6 text-xs font-semibold text-white bg-indigo-600 rounded-full ring-2 ring-white shadow-lg"
                :style="pinStyle(comment)"
                @click.stop="openComment(comment)"
            >
                {{ commentIndex(comment) }}
            </button>

            <div
                v-if="activeCard"
                class="absolute z-20 p-3 w-64 bg-white rounded-xl border border-gray-200 shadow-xl"
                :style="cardStyle(activeCard.x, activeCard.y)"
            >
                <div class="flex gap-2 justify-between items-start">
                    <div>
                        <div class="text-xs font-semibold text-gray-700">{{ activeCard.comment.author_name }}</div>
                        <div class="text-xs text-gray-400">{{ formatTimestamp(activeCard.comment.created_at) }}</div>
                    </div>
                    <button type="button" class="text-gray-400 hover:text-gray-600" @click="closeActiveCard">×</button>
                </div>
                <div class="mt-2 text-sm text-gray-700 whitespace-pre-line" v-html="formatBody(activeCard.comment.body)"></div>

                <div v-if="canEdit && activeCard.comment.can_edit" class="flex gap-2 mt-3">
                    <button
                        type="button"
                        class="text-xs font-semibold text-indigo-600"
                        @click="startEdit(activeCard.comment)"
                    >
                        編集
                    </button>
                    <button
                        type="button"
                        class="text-xs font-semibold text-red-600"
                        @click="deleteComment(activeCard.comment)"
                    >
                        削除
                    </button>
                </div>
            </div>

            <div
                v-if="draftCard"
                class="absolute z-20 p-3 w-72 bg-white rounded-xl border border-gray-200 shadow-xl"
                :style="cardStyle(draftCard.x, draftCard.y)"
            >
                <div class="flex gap-2 justify-between items-start">
                    <div class="text-xs font-semibold text-gray-700">新しいコメント</div>
                    <button type="button" class="text-gray-400 hover:text-gray-600" @click="cancelDraft">×</button>
                </div>
                <div class="mt-2 space-y-2">
                    <input
                        v-if="requireAuthorName"
                        v-model="draftAuthorName"
                        type="text"
                        class="px-2 py-1 w-full text-xs rounded-md border border-gray-200"
                        placeholder="お名前"
                    />
                    <textarea
                        v-model="draftBody"
                        rows="3"
                        class="px-2 py-1 w-full text-xs rounded-md border border-gray-200"
                        placeholder="コメントを入力"
                    ></textarea>
                </div>
                <div class="flex gap-2 justify-end mt-3">
                    <button type="button" class="text-xs text-gray-500" @click="cancelDraft">キャンセル</button>
                    <button
                        type="button"
                        class="px-2 py-1 text-xs font-semibold text-white bg-indigo-600 rounded-md"
                        :disabled="draftSubmitting || !draftBody.trim() || (requireAuthorName && !draftAuthorName.trim())"
                        @click="submitDraft"
                    >
                        {{ draftSubmitting ? '送信中...' : '追加' }}
                    </button>
                </div>
            </div>

            <div
                v-if="editCard"
                class="absolute z-20 p-3 w-72 bg-white rounded-xl border border-gray-200 shadow-xl"
                :style="cardStyle(editCard.x, editCard.y)"
            >
                <div class="flex gap-2 justify-between items-start">
                    <div class="text-xs font-semibold text-gray-700">コメント編集</div>
                    <button type="button" class="text-gray-400 hover:text-gray-600" @click="cancelEdit">×</button>
                </div>
                <div class="mt-2 space-y-2">
                    <textarea
                        v-model="editBody"
                        rows="3"
                        class="px-2 py-1 w-full text-xs rounded-md border border-gray-200"
                        placeholder="コメントを入力"
                    ></textarea>
                </div>
                <div class="flex gap-2 justify-end mt-3">
                    <button type="button" class="text-xs text-gray-500" @click="cancelEdit">キャンセル</button>
                    <button
                        type="button"
                        class="px-2 py-1 text-xs font-semibold text-white bg-indigo-600 rounded-md"
                        :disabled="editSubmitting || !editBody.trim()"
                        @click="submitEdit"
                    >
                        {{ editSubmitting ? '更新中...' : '更新' }}
                    </button>
                </div>
            </div>
        </div>

        <button
            v-if="commentingEnabled && canCreate"
            type="button"
            class="flex fixed right-6 bottom-6 z-40 gap-2 items-center px-4 py-3 text-sm font-semibold text-white bg-indigo-600 rounded-full shadow-lg transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            :class="pinMode ? 'opacity-100' : ''"
            @click="togglePinMode"
        >
            <span v-if="pinMode">配置中…（終了）</span>
            <span v-else>コメント追加</span>
        </button>

        <div v-if="errorMessage" class="text-xs text-red-600">{{ errorMessage }}</div>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    reviewUrl: {
        type: String,
        default: '',
    },
    approvalMessageId: {
        type: [Number, String],
        default: null,
    },
    listRoute: {
        type: Object,
        required: true,
    },
    createRoute: {
        type: Object,
        required: true,
    },
    updateRoute: {
        type: Object,
        default: null,
    },
    deleteRoute: {
        type: Object,
        default: null,
    },
    canEdit: {
        type: Boolean,
        default: false,
    },
    canCreate: {
        type: Boolean,
        default: true,
    },
    requireAuthorName: {
        type: Boolean,
        default: false,
    },
    defaultAuthorName: {
        type: String,
        default: '',
    },
    pollIntervalMs: {
        type: Number,
        default: 5000,
    },
    frameHeight: {
        type: String,
        default: '70vh',
    },
});

const canvasRef = ref(null);
const iframeRef = ref(null);
const pinMode = ref(false);
const comments = ref([]);
const errorMessage = ref('');
const iframeError = ref(false);

const activeCard = ref(null);
const draftCard = ref(null);
const editCard = ref(null);

const draftBody = ref('');
const draftAuthorName = ref('');
const draftSubmitting = ref(false);

const editBody = ref('');
const editSubmitting = ref(false);

const pollTimer = ref(null);
const hasNewComments = ref(false);
const lastSeenKey = computed(() => props.approvalMessageId ? `review_comments_seen_${props.approvalMessageId}` : null);

const commentingEnabled = computed(() => !!props.approvalMessageId);

const commentIndex = (comment) => {
    const index = comments.value.findIndex(c => c.id === comment.id);
    return index >= 0 ? index + 1 : 1;
};

const buildUrl = (routeInfo, extra = {}) => {
    if (!routeInfo || !routeInfo.name) return '';
    return route(routeInfo.name, { ...(routeInfo.params || {}), ...extra });
};

const pinStyle = (comment) => ({
    left: `${comment.x_ratio * 100}%`,
    top: `${comment.y_ratio * 100}%`,
    transform: 'translate(-50%, -50%)',
});

const rectStyle = (comment) => ({
    left: `${comment.x_ratio * 100}%`,
    top: `${comment.y_ratio * 100}%`,
    width: `${(comment.w_ratio || 0) * 100}%`,
    height: `${(comment.h_ratio || 0) * 100}%`,
});

const cardStyle = (x, y) => ({
    left: `${x}px`,
    top: `${y}px`,
    transform: 'translate(-8px, -100%)',
});

const formatTimestamp = (iso) => {
    if (!iso) return '';
    const date = new Date(iso);
    return date.toLocaleString('ja-JP', { hour: '2-digit', minute: '2-digit', month: '2-digit', day: '2-digit' });
};

const escapeHtml = (str) => str
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

const formatBody = (body) => {
    const escaped = escapeHtml(body || '');
    return escaped.replace(/(@[\p{L}\p{N}_-]{1,32})/gu, '<span class="font-semibold text-indigo-600">$1</span>');
};

const handleIframeError = () => {
    iframeError.value = true;
};

const closeActiveCard = () => {
    activeCard.value = null;
};

const cancelDraft = () => {
    draftCard.value = null;
    draftBody.value = '';
    errorMessage.value = '';
    pinMode.value = false;
};

const cancelEdit = () => {
    editCard.value = null;
    editBody.value = '';
    errorMessage.value = '';
};

const togglePinMode = () => {
    if (!props.canCreate || !commentingEnabled.value || !props.reviewUrl) return;
    pinMode.value = !pinMode.value;
    if (!pinMode.value) {
        cancelDraft();
    }
};

const dragStart = ref(null);
const dragCurrent = ref(null);
const dragPreview = computed(() => {
    if (!dragStart.value || !dragCurrent.value || !canvasRef.value) return null;
    const rect = canvasRef.value.getBoundingClientRect();
    const x1 = Math.max(0, Math.min(rect.width, dragStart.value.x));
    const y1 = Math.max(0, Math.min(rect.height, dragStart.value.y));
    const x2 = Math.max(0, Math.min(rect.width, dragCurrent.value.x));
    const y2 = Math.max(0, Math.min(rect.height, dragCurrent.value.y));
    const left = Math.min(x1, x2);
    const top = Math.min(y1, y2);
    const width = Math.abs(x2 - x1);
    const height = Math.abs(y2 - y1);
    if (width < 2 && height < 2) return null;
    return {
        left: `${left}px`,
        top: `${top}px`,
        width: `${width}px`,
        height: `${height}px`,
    };
});

const handlePointerDown = (event) => {
    if (!pinMode.value) return;
    if (!canvasRef.value) return;
    const rect = canvasRef.value.getBoundingClientRect();
    dragStart.value = { x: event.clientX - rect.left, y: event.clientY - rect.top };
    dragCurrent.value = { ...dragStart.value };
};

const handlePointerMove = (event) => {
    if (!dragStart.value || !canvasRef.value) return;
    const rect = canvasRef.value.getBoundingClientRect();
    dragCurrent.value = { x: event.clientX - rect.left, y: event.clientY - rect.top };
};

const handlePointerCancel = () => {
    dragStart.value = null;
    dragCurrent.value = null;
};

const handlePointerUp = (event) => {
    if (!pinMode.value) return;
    if (!canvasRef.value) return;
    if (!dragStart.value) return;

    const rect = canvasRef.value.getBoundingClientRect();
    const end = { x: event.clientX - rect.left, y: event.clientY - rect.top };

    const x1 = Math.max(0, Math.min(rect.width, dragStart.value.x));
    const y1 = Math.max(0, Math.min(rect.height, dragStart.value.y));
    const x2 = Math.max(0, Math.min(rect.width, end.x));
    const y2 = Math.max(0, Math.min(rect.height, end.y));

    const left = Math.min(x1, x2);
    const top = Math.min(y1, y2);
    const width = Math.abs(x2 - x1);
    const height = Math.abs(y2 - y1);

    const isPoint = width < 8 && height < 8;

    const xRatio = (isPoint ? x2 : left) / rect.width;
    const yRatio = (isPoint ? y2 : top) / rect.height;
    const wRatio = isPoint ? null : width / rect.width;
    const hRatio = isPoint ? null : height / rect.height;

    draftCard.value = { x: x2, y: y2, xRatio, yRatio, wRatio, hRatio };
    draftBody.value = '';
    draftAuthorName.value = props.defaultAuthorName || localStorage.getItem('review_commenter_name') || '';
    activeCard.value = null;
    editCard.value = null;

    handlePointerCancel();
};

const openComment = (comment) => {
    if (!canvasRef.value) return;

    const rect = canvasRef.value.getBoundingClientRect();
    const x = rect.width * comment.x_ratio;
    const y = rect.height * comment.y_ratio;

    activeCard.value = { x, y, comment };
    draftCard.value = null;
    editCard.value = null;
};

const startEdit = (comment) => {
    if (!canvasRef.value) return;
    const rect = canvasRef.value.getBoundingClientRect();
    const x = rect.width * comment.x_ratio;
    const y = rect.height * comment.y_ratio;

    editCard.value = { x, y, comment };
    editBody.value = comment.body;
    activeCard.value = null;
};

const submitDraft = async () => {
    if (!draftCard.value) return;
    if (!commentingEnabled.value) return;

    errorMessage.value = '';
    draftSubmitting.value = true;

    try {
        const payload = {
            msg: props.approvalMessageId,
            body: draftBody.value.trim(),
            x_ratio: draftCard.value.xRatio,
            y_ratio: draftCard.value.yRatio,
        };
        if (draftCard.value.wRatio != null && draftCard.value.hRatio != null) {
            payload.w_ratio = draftCard.value.wRatio;
            payload.h_ratio = draftCard.value.hRatio;
        }

        if (props.requireAuthorName) {
            payload.author_name = draftAuthorName.value.trim();
        } else if (draftAuthorName.value.trim()) {
            payload.author_name = draftAuthorName.value.trim();
        }

        const response = await axios.post(buildUrl(props.createRoute), payload);
        if (response?.data?.comment) {
            comments.value.push(response.data.comment);
            if (props.requireAuthorName && draftAuthorName.value.trim()) {
                localStorage.setItem('review_commenter_name', draftAuthorName.value.trim());
            }
        }
        cancelDraft();
        hasNewComments.value = false;
        markAllSeen();
    } catch (error) {
        errorMessage.value = error?.response?.data?.error || 'コメントの追加に失敗しました。';
    } finally {
        draftSubmitting.value = false;
    }
};

const submitEdit = async () => {
    if (!editCard.value || !props.updateRoute) return;

    errorMessage.value = '';
    editSubmitting.value = true;

    try {
        const response = await axios.patch(buildUrl(props.updateRoute, { comment: editCard.value.comment.id }), {
            body: editBody.value.trim(),
        });

        if (response?.data?.comment) {
            const index = comments.value.findIndex(c => c.id === editCard.value.comment.id);
            if (index >= 0) {
                comments.value[index] = response.data.comment;
            }
        }
        cancelEdit();
    } catch (error) {
        errorMessage.value = error?.response?.data?.error || 'コメントの更新に失敗しました。';
    } finally {
        editSubmitting.value = false;
    }
};

const deleteComment = async (comment) => {
    if (!props.deleteRoute) return;
    if (!confirm('このコメントを削除しますか？')) return;

    errorMessage.value = '';
    try {
        await axios.delete(buildUrl(props.deleteRoute, { comment: comment.id }));
        comments.value = comments.value.filter(c => c.id !== comment.id);
        activeCard.value = null;
    } catch (error) {
        errorMessage.value = error?.response?.data?.error || 'コメントの削除に失敗しました。';
    }
};

const fetchComments = async () => {
    if (!commentingEnabled.value) {
        comments.value = [];
        return;
    }

    try {
        const response = await axios.get(buildUrl(props.listRoute), {
            params: { msg: props.approvalMessageId },
        });

        const data = response?.data?.comments || [];
        const previousIds = new Set(comments.value.map(c => c.id));
        comments.value = data;

        const lastSeen = lastSeenKey.value ? localStorage.getItem(lastSeenKey.value) : null;
        if (lastSeen) {
            const lastSeenTime = new Date(lastSeen).getTime();
            hasNewComments.value = data.some(c => new Date(c.created_at).getTime() > lastSeenTime) && data.some(c => !previousIds.has(c.id));
        } else if (data.length > 0 && previousIds.size > 0) {
            hasNewComments.value = data.some(c => !previousIds.has(c.id));
        }
    } catch (error) {
        errorMessage.value = error?.response?.data?.error || 'コメントの取得に失敗しました。';
    }
};

const markAllSeen = () => {
    if (!lastSeenKey.value) return;
    localStorage.setItem(lastSeenKey.value, new Date().toISOString());
    hasNewComments.value = false;
};

const setupPolling = () => {
    if (pollTimer.value) {
        clearInterval(pollTimer.value);
    }

    pollTimer.value = setInterval(fetchComments, props.pollIntervalMs);
};

onMounted(() => {
    fetchComments();
    setupPolling();
});

onUnmounted(() => {
    if (pollTimer.value) {
        clearInterval(pollTimer.value);
    }
});

watch(() => props.approvalMessageId, () => {
    fetchComments();
    setupPolling();
});

watch(() => props.reviewUrl, () => {
    iframeError.value = false;
});

const commentsWithRect = computed(() => comments.value.filter(c => c.w_ratio != null && c.h_ratio != null && (c.w_ratio > 0 || c.h_ratio > 0)));
</script>
