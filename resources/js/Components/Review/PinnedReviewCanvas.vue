<template>
    <div class="relative w-full min-h-[70vh]">
        <div class="flex flex-wrap gap-2 justify-between items-center">
            <div class="text-sm text-gray-600">
                <span v-if="!currentReviewUrl">左のパネルでURLを追加するとプレビューが表示されます。</span>
                <span v-else-if="!commentingEnabled">改善内容を作成するとピン留めコメントが使えます。</span>
            </div>
        </div>

        <div class="relative w-full" :style="{ minHeight: frameHeight }">
        <div ref="canvasRef" class="overflow-hidden relative w-full shadow-xl">
            <div v-if="!currentReviewUrl" class="flex justify-center items-center w-full text-sm text-gray-500" :style="{ height: frameHeight }">
                左のパネルでURLを追加して表示してください。
            </div>
            <div v-else>
                <iframe
                    ref="iframeRef"
                    :src="currentReviewUrl"
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
                        <a :href="currentReviewUrl" target="_blank" class="text-indigo-600 underline">新しいタブで開く</a>
                    </div>
                </div>
            </div>

            <div
                v-if="pinMode && currentReviewUrl && commentingEnabled && canCreate"
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
        </div>
    </div>
            <!-- 左端: URL追加 + 一覧（ProjectListサイドバーと同デザイン） -->
            <aside class="flex flex-col absolute top-2 bottom-2 left-2 z-10 w-56 md:w-64 lg:w-72 max-h-[calc(100%-1rem)] bg-white rounded-3xl shadow-lg border border-gray-200 overflow-hidden">
                <!-- ロゴ（上部） -->
                <div class="flex-shrink-0 border-b border-gray-200 px-3 py-4 md:px-4 md:py-5">
                    <Link :href="route('projects.index')" class="flex items-center shrink-0 justify-center">
                        <ApplicationLogo />
                    </Link>
                </div>
                <!-- スロット: ロゴ下・URL入力上（戻る・共有する等） -->
                <div v-if="$slots.headerActions" class="flex-shrink-0 border-b border-gray-200 px-3 py-2 md:px-4 md:py-3">
                    <slot name="headerActions" />
                </div>
                <!-- URL追加 -->
                <div class="flex-shrink-0 border-b border-gray-200 px-3 py-2 md:px-4 md:py-3">
                    <input
                        v-model="newUrlInput"
                        type="url"
                        class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="https://... (Enterで追加)"
                        @keyup.enter="addUrl"
                    />
                </div>
                <!-- URLリスト -->
                <div class="overflow-y-auto flex-1 py-2 min-h-0">
                    <div v-if="urlList.length === 0" class="py-8 text-center px-3 md:px-4">
                        <p class="text-xs md:text-sm text-gray-500">上でURLを追加</p>
                    </div>
                    <div v-else class="px-1.5 md:px-2">
                        <div
                            v-for="(url, idx) in urlList"
                            :key="idx"
                            class="group relative flex items-center rounded-xl cursor-pointer transition-colors mb-1"
                            :class="[
                                'gap-2 md:gap-3 px-2 md:px-3 py-2 md:py-2.5',
                                currentUrlIndex === idx
                                    ? 'bg-indigo-50 text-indigo-700 font-semibold'
                                    : 'text-gray-700 hover:bg-gray-50 font-medium'
                            ]"
                            @click="currentUrlIndex = idx"
                        >
                            <!-- アクティブインジケーター -->
                            <div
                                v-if="currentUrlIndex === idx"
                                class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 md:h-6 bg-indigo-600 rounded-r"
                            ></div>
                            <!-- フォルダアイコン -->
                            <svg
                                class="w-4 h-4 flex-shrink-0"
                                :class="currentUrlIndex === idx ? 'text-indigo-600' : 'text-gray-500 group-hover:text-gray-700'"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                            </svg>
                            <span class="flex-1 min-w-0 text-xs md:text-sm truncate" :title="url">{{ displayUrlLabel(url) }}</span>
                            <button
                                type="button"
                                class="flex-shrink-0 p-1 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition"
                                title="削除"
                                @click.stop="removeUrl(idx)"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </aside>
    <!-- 右端: Figma風コメントパネル（スクロールに付かない） -->
    <aside class="flex overflow-hidden absolute top-0 right-0 bottom-0 z-10 flex-col w-80 max-h-full bg-white border-l border-gray-200 shadow-lg">
        <div class="flex flex-shrink-0 gap-2 justify-between items-center p-3 border-b border-gray-100">
            <span class="text-sm font-semibold text-gray-800">コメント</span>
            <button
                v-if="commentingEnabled && canCreate"
                type="button"
                class="flex gap-1.5 items-center px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50"
                :class="pinMode ? 'ring-2 ring-indigo-400' : ''"
                @click="togglePinMode"
            >
                <span v-if="pinMode">配置中…（終了）</span>
                <span v-else>コメント追加</span>
            </button>
        </div>
        <div v-if="hasNewComments" class="flex-shrink-0 px-3 py-1.5">
            <button
                type="button"
                class="px-2 py-1 w-full text-xs font-semibold text-amber-700 bg-amber-50 rounded border border-amber-200"
                @click="markAllSeen"
            >
                新しいコメント
            </button>
        </div>
        <div class="overflow-y-auto flex-1 p-2 space-y-2 min-h-0">
            <div
                v-for="comment in comments"
                :key="comment.id"
                class="p-3 bg-gray-50 rounded-lg border border-gray-200 transition cursor-pointer hover:border-indigo-200 hover:bg-indigo-50/50"
                :class="{ 'ring-2 ring-indigo-400 border-indigo-300 bg-indigo-50/80': activeCard?.comment?.id === comment.id }"
                @click="openComment(comment)"
            >
                <div class="flex gap-2 items-start">
                    <span class="flex flex-shrink-0 justify-center items-center w-6 h-6 text-xs font-semibold text-white bg-indigo-600 rounded-full">{{ commentIndex(comment) }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap gap-1 items-center">
                            <span class="text-xs font-semibold text-gray-700">{{ comment.author_name || '名無し' }}</span>
                            <span class="text-xs text-gray-400">{{ formatTimestamp(comment.created_at) }}</span>
                            <span v-if="comment.w_ratio != null && comment.h_ratio != null && (comment.w_ratio > 0 || comment.h_ratio > 0)" class="text-[10px] text-amber-600 bg-amber-100 px-1 rounded">範囲</span>
                        </div>
                        <p class="mt-1 text-sm text-gray-700 whitespace-pre-line break-words line-clamp-3" v-html="formatBody(comment.body)"></p>
                    </div>
                </div>
            </div>
            <p v-if="comments.length === 0 && commentingEnabled" class="px-2 py-4 text-xs text-center text-gray-400">コメントはまだありません</p>
            <p v-if="!commentingEnabled" class="px-2 py-4 text-xs text-center text-gray-400">改善内容を作成するとコメントが使えます</p>
        </div>
        <div v-if="errorMessage" class="flex-shrink-0 px-3 py-2 text-xs text-red-600 border-t border-gray-100">{{ errorMessage }}</div>
    </aside>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import axios from 'axios';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

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

const urlList = ref([]);
const currentUrlIndex = ref(0);
const newUrlInput = ref('');

const currentReviewUrl = computed(() => {
    if (urlList.value.length === 0) return props.reviewUrl || '';
    return urlList.value[currentUrlIndex.value] || urlList.value[0] || props.reviewUrl || '';
});

const addableUrl = computed(() => {
    const u = newUrlInput.value?.trim();
    if (!u) return false;
    try {
        new URL(u);
        return true;
    } catch {
        return false;
    }
});

const addUrl = () => {
    const u = newUrlInput.value?.trim();
    if (!addableUrl.value) return;
    urlList.value = [...urlList.value, u];
    currentUrlIndex.value = urlList.value.length - 1;
    newUrlInput.value = '';
    iframeError.value = false;
};

const removeUrl = (idx) => {
    urlList.value = urlList.value.filter((_, i) => i !== idx);
    if (currentUrlIndex.value >= urlList.value.length) {
        currentUrlIndex.value = Math.max(0, urlList.value.length - 1);
    } else if (currentUrlIndex.value > idx) {
        currentUrlIndex.value -= 1;
    }
};

const displayUrlLabel = (url) => {
    if (!url) return '未設定';
    try {
        const u = new URL(url);
        const path = u.pathname && u.pathname !== '/' ? u.pathname : '';
        return u.hostname + path;
    } catch {
        return url.length > 50 ? url.slice(0, 50) + '…' : url;
    }
};

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
    if (!props.canCreate || !commentingEnabled.value || !currentReviewUrl.value) return;
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

watch(() => props.reviewUrl, (url) => {
    iframeError.value = false;
    if (url && urlList.value.length === 0) {
        urlList.value = [url];
        currentUrlIndex.value = 0;
    }
}, { immediate: true });

const commentsWithRect = computed(() => comments.value.filter(c => c.w_ratio != null && c.h_ratio != null && (c.w_ratio > 0 || c.h_ratio > 0)));
</script>
