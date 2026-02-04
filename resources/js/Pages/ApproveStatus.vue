<template>
    <Head>
        <title>承認ステータス - AutoRelease</title>
        <meta name="robots" content="noindex, nofollow">
    </Head>
    
    <div class="flex justify-center px-4 py-12 min-h-screen bg-indigo-50 sm:px-6 lg:px-8">
        <div class="p-8 space-y-8 w-full max-w-4xl bg-white rounded-2xl">
            <div>
                <h2 class="mt-6 text-3xl font-extrabold text-center text-gray-900">
                    承認を受け付けました
                </h2>
                <p class="mt-4 text-sm text-center text-gray-600">
                    リリースされるまでの状況は以下でご確認できます。
                </p>
            </div>

            <!-- デプロイログ詳細 -->
            <div class="mt-8 space-y-6">
                <!-- ステータス表示 -->
                <div class="p-6 text-center rounded-lg border-2" :class="statusClass">
                    <h3 class="text-lg font-semibold" :class="statusTextClass">
                        {{ statusMessage }}
                    </h3>
                    <p v-if="deployLogData.status === 'running'" class="mt-1 text-sm text-gray-600">
                        しばらくお待ちください。完了次第、自動的に更新されます。
                    </p>
                    <div v-if="deployLogData.status === 'running' && (elapsedTime || estimatedTime)" class="flex gap-3 justify-center items-center mt-3 text-xs text-gray-600">
                        <p v-if="elapsedTime">
                            経過時間: <span class="font-medium">{{ elapsedTime }}</span>
                        </p>
                        <p v-if="estimatedTime">
                            予想完了時刻: <span class="font-medium">{{ estimatedTime }}</span>
                        </p>
                        <p v-if="averageDurationSeconds" class="text-gray-500">
                            過去の平均所要時間: 約{{ formatDurationFromSeconds(averageDurationSeconds) }}
                        </p>
                    </div>
                    <p v-else-if="deployLogData.status === 'success'" class="mt-1 text-sm text-gray-600">
                        サイトの更新が完了しました。本番環境に反映されています。
                    </p>
                    <p v-else-if="deployLogData.status === 'failed'" class="mt-1 text-sm text-gray-600">
                        サイトの更新に失敗しました。管理者にお問い合わせください。
                    </p>
                </div>

                <!-- デプロイログ詳細情報 -->
                <div class="bg-gray-100 rounded-2xl border border-gray-200">
                    <div class="flex justify-between items-center p-4 border-b border-gray-200">
                        <h3 class="font-bold text-gray-700 text-md">デプロイ情報</h3>
                    </div>
                    <div class="p-4">
                        <dl class="space-y-3">
                            <div>
                                <dd class="flex gap-3 items-center text-xs font-bold text-gray-500">
                                    <!-- ステータスアイコン -->
                                    <div v-if="deployLogData.status === 'success'" class="flex flex-shrink-0 justify-center items-center w-6 h-6 bg-green-500 rounded-full">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div v-else-if="deployLogData.status === 'failed'" class="flex flex-shrink-0 justify-center items-center w-6 h-6 bg-red-500 rounded-full">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </div>
                                    <div v-else-if="deployLogData.status === 'running'" class="flex-shrink-0 w-6 h-6 rounded-full border-2 border-orange-500"></div>
                                    <div v-else class="flex-shrink-0 w-6 h-6 rounded-full border-2 border-gray-500"></div>
                                    
                                    <p>開始: {{ formatDateTime(deployLogData.started_at) }}</p>
                                    <p v-if="deployLogData.finished_at">終了: {{ formatDateTime(deployLogData.finished_at) }}</p>
                                    <p v-if="deployLogData.finished_at">所要時間: {{ formatDuration(deployLogData.started_at, deployLogData.finished_at) }}</p>
                                    <p v-else-if="deployLogData.started_at">経過時間: {{ formatElapsedTime(deployLogData.started_at) }}</p>
                                    
                                    <!-- 詳細ボタン（右端に配置） -->
                                    <button
                                        v-if="deployLogData.approval_message"
                                        @click="toggleApprovalMessageExpansion"
                                        class="flex gap-1 items-center ml-auto text-xs font-bold text-gray-500 transition-colors hover:text-gray-700"
                                    >
                                        <span>詳細</span>
                                        <svg 
                                            class="w-4 h-4 transition-transform"
                                            :class="{ 'rotate-90': isApprovalMessageExpanded }"
                                            fill="none" 
                                            stroke="currentColor" 
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </dd>
                            </div>
                            <div v-if="deployLogData.approval_message && isApprovalMessageExpanded">
                                <dd class="p-3 mt-1 text-sm text-gray-700 prose prose-sm max-w-none bg-white rounded-2xl border border-gray-200" v-html="formattedApprovalMessage"></dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <AppFooter :show-logo="true" />
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppFooter from '@/Components/AppFooter.vue';
import { marked } from 'marked';
import DOMPurify from 'dompurify';

const props = defineProps({
    project: Object,
    token: String,
    deployLog: Object,
    averageDurationSeconds: Number,
});

const isHtmlMessage = (message) => /<\/?[a-z][\s\S]*>/i.test(message);

const formattedApprovalMessage = computed(() => {
    const message = deployLogData.value?.approval_message?.message;
    if (!message) return '';
    if (isHtmlMessage(message)) {
        return DOMPurify.sanitize(message, {
            ADD_ATTR: ['target', 'rel'],
        });
    }
    const html = marked.parse(message);
    return DOMPurify.sanitize(html, {
        ADD_ATTR: ['target', 'rel'],
    });
});

const deployLogData = ref(props.deployLog);
const averageDurationSeconds = ref(props.averageDurationSeconds);
const isApprovalMessageExpanded = ref(false); // デフォルトで閉じた状態
let pollInterval = null;

// 承認メッセージの開閉を切り替え
const toggleApprovalMessageExpansion = () => {
    isApprovalMessageExpanded.value = !isApprovalMessageExpanded.value;
};

// ステータスに応じたクラスとメッセージ
const statusClass = computed(() => {
    switch (deployLogData.value.status) {
        case 'running':
            return 'bg-blue-50 border-blue-200';
        case 'success':
            return 'bg-green-50 border-green-200';
        case 'failed':
            return 'bg-red-50 border-red-200';
        default:
            return 'bg-gray-50 border-gray-200';
    }
});

const statusTextClass = computed(() => {
    switch (deployLogData.value.status) {
        case 'running':
            return 'text-blue-900';
        case 'success':
            return 'text-green-900';
        case 'failed':
            return 'text-red-900';
        default:
            return 'text-gray-900';
    }
});

const statusBadgeClass = computed(() => {
    switch (deployLogData.value.status) {
        case 'running':
            return 'bg-blue-100 text-blue-800';
        case 'success':
            return 'bg-green-100 text-green-800';
        case 'failed':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
});

const statusLabel = computed(() => {
    const labels = {
        'pending': '待機中',
        'running': '実行中',
        'success': '成功',
        'failed': '失敗',
    };
    return labels[deployLogData.value.status] || deployLogData.value.status;
});

const statusMessage = computed(() => {
    switch (deployLogData.value.status) {
        case 'running':
            return 'サイトの更新を実行中です';
        case 'success':
            return 'サイトの更新が完了しました';
        case 'failed':
            return 'サイトの更新に失敗しました';
        default:
            return 'サイトの更新を待機中です';
    }
});

// 日時フォーマット
const formatDateTime = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}/${month}/${day} ${hours}:${minutes}`;
};

// 所要時間を計算（開始時刻と終了時刻の差分）
const formatDuration = (startedAt, finishedAt) => {
    if (!startedAt || !finishedAt) return '-';
    const start = new Date(startedAt);
    const end = new Date(finishedAt);
    const diffSeconds = Math.floor((end - start) / 1000);
    return formatDurationFromSeconds(diffSeconds);
};

// 経過時間を計算（開始時刻から現在までの差分）
const formatElapsedTime = (startedAt) => {
    if (!startedAt) return '-';
    const start = new Date(startedAt);
    const now = new Date();
    const diffSeconds = Math.floor((now - start) / 1000);
    return formatDurationFromSeconds(diffSeconds);
};

// 秒数を分・秒の形式に変換
const formatDurationFromSeconds = (seconds) => {
    if (!seconds) return '-';
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    if (mins > 0) {
        return `${mins}分${secs}秒`;
    }
    return `${secs}秒`;
};

// 経過時間を計算
const elapsedTime = computed(() => {
    if (!deployLogData.value.started_at || deployLogData.value.status === 'success' || deployLogData.value.status === 'failed') {
        return null;
    }
    const start = new Date(deployLogData.value.started_at);
    const now = new Date();
    const diffSeconds = Math.floor((now - start) / 1000);
    return formatDurationFromSeconds(diffSeconds);
});

// 予想完了時刻を計算
const estimatedTime = computed(() => {
    if (!deployLogData.value.started_at || !averageDurationSeconds.value || deployLogData.value.status !== 'running') {
        return null;
    }
    const start = new Date(deployLogData.value.started_at);
    const estimated = new Date(start.getTime() + averageDurationSeconds.value * 1000);
    return estimated.toLocaleString('ja-JP', {
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
});

// ステータスをポーリングで更新
const pollStatus = async () => {
    try {
        const query = props.deployLog?.access_token
            ? `access_token=${encodeURIComponent(props.deployLog.access_token)}`
            : `token=${encodeURIComponent(props.token)}`;
        const response = await fetch(`/api/deploy-logs/${deployLogData.value.id}?${query}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (response.ok) {
            const data = await response.json();
            
            // デバッグ用ログ
            console.log('Deploy status polled:', {
                id: data.id,
                status: data.status,
                finished_at: data.finished_at,
            });
            
            deployLogData.value = data;
            
            // 平均時間も更新
            if (data.average_duration_seconds) {
                averageDurationSeconds.value = data.average_duration_seconds;
            }
            
            // 成功または失敗した場合はポーリングを停止
            if (data.status === 'success' || data.status === 'failed') {
                console.log('Deployment finished, stopping poll:', data.status);
                if (pollInterval) {
                    clearInterval(pollInterval);
                    pollInterval = null;
                }
                // 経過時間の更新も停止
                if (elapsedInterval) {
                    clearInterval(elapsedInterval);
                    elapsedInterval = null;
                }
            }
        }
    } catch (error) {
        console.error('Failed to poll deploy status:', error);
    }
};

let elapsedInterval = null;
let pollCount = 0;
const MAX_POLL_COUNT = 600; // 最大30分（3秒 × 600回）

onMounted(() => {
    // 実行中または待機中の場合はポーリングを開始
    // 成功や失敗の場合でも、念のため最初の1回はポーリングを実行して最新状態を確認
    if (deployLogData.value.status === 'running' || deployLogData.value.status === 'pending') {
        console.log('Starting poll for deploy log:', deployLogData.value.id, 'Status:', deployLogData.value.status);
        
        // 初回ポーリングを500ms遅延（ページロード直後の負荷を軽減）
        setTimeout(() => {
            pollStatus();
        }, 500);
        
        pollInterval = setInterval(() => {
            pollCount++;
            
            // タイムアウトチェック（30分経過したら停止）
            if (pollCount >= MAX_POLL_COUNT) {
                console.warn('Poll timeout reached, stopping poll');
                if (pollInterval) {
                    clearInterval(pollInterval);
                    pollInterval = null;
                }
                if (elapsedInterval) {
                    clearInterval(elapsedInterval);
                    elapsedInterval = null;
                }
                return;
            }
            
            pollStatus();
        }, 3000); // 3秒ごとに更新（ステータス更新のレスポンスを改善）
        
        // 経過時間を1秒ごとに更新（UI用）
        elapsedInterval = setInterval(() => {
            // リアクティブな更新をトリガー（computedが再計算される）
            if (deployLogData.value.status === 'success' || deployLogData.value.status === 'failed') {
                if (elapsedInterval) {
                    clearInterval(elapsedInterval);
                    elapsedInterval = null;
                }
            }
        }, 1000);
    } else {
        // 成功や失敗の場合でも、念のため最初の1回はポーリングを実行して最新状態を確認
        console.log('Deploy status is', deployLogData.value.status, ', checking once for latest status');
        // 成功/失敗時は即座に実行せず、少し遅延させる
        setTimeout(() => {
            pollStatus();
        }, 500);
    }
});

onUnmounted(() => {
    if (pollInterval) {
        clearInterval(pollInterval);
    }
    if (elapsedInterval) {
        clearInterval(elapsedInterval);
    }
});
</script>
