<template>
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
                <div class="p-6 rounded-lg border-2" :class="statusClass">
                    <div class="flex items-center gap-3">
                        <div v-if="deployLog.status === 'running'" class="flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <div v-else-if="deployLog.status === 'success'" class="flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div v-else-if="deployLog.status === 'failed'" class="flex-shrink-0">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <div v-else class="flex-shrink-0">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold" :class="statusTextClass">
                                {{ statusMessage }}
                            </h3>
                            <p v-if="deployLog.status === 'running'" class="mt-1 text-sm text-gray-600">
                                しばらくお待ちください。完了次第、自動的に更新されます。
                            </p>
                            <div v-if="deployLog.status === 'running' && (elapsedTime || estimatedTime)" class="mt-3 space-y-1">
                                <p v-if="elapsedTime" class="text-sm text-gray-600">
                                    経過時間: <span class="font-medium">{{ elapsedTime }}</span>
                                </p>
                                <p v-if="estimatedTime" class="text-sm text-gray-600">
                                    予想完了時刻: <span class="font-medium">{{ estimatedTime }}</span>
                                </p>
                                <p v-if="averageDurationSeconds" class="text-xs text-gray-500">
                                    過去の平均所要時間: 約{{ formatDuration(averageDurationSeconds) }}
                                </p>
                            </div>
                            <p v-else-if="deployLog.status === 'success'" class="mt-1 text-sm text-gray-600">
                                サイトの更新が完了しました。本番環境に反映されています。
                            </p>
                            <p v-else-if="deployLog.status === 'failed'" class="mt-1 text-sm text-gray-600">
                                サイトの更新に失敗しました。管理者にお問い合わせください。
                            </p>
                        </div>
                    </div>
                </div>

                <!-- デプロイログ詳細情報 -->
                <div class="p-6 bg-gray-50 rounded-lg border border-gray-200">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">デプロイ情報</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ステータス</dt>
                            <dd class="mt-1">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="statusBadgeClass">
                                    {{ statusLabel }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">開始日時</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ formatDateTime(deployLog.started_at) }}
                            </dd>
                        </div>
                        <div v-if="deployLog.finished_at">
                            <dt class="text-sm font-medium text-gray-500">終了日時</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ formatDateTime(deployLog.finished_at) }}
                            </dd>
                        </div>
                        <div v-if="deployLog.finished_at">
                            <dt class="text-sm font-medium text-gray-500">所要時間</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ formatDuration(deployLog.started_at, deployLog.finished_at) }}
                            </dd>
                        </div>
                        <div v-else-if="deployLog.started_at">
                            <dt class="text-sm font-medium text-gray-500">経過時間</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ formatElapsedTime(deployLog.started_at) }}
                            </dd>
                        </div>
                        <div v-if="deployLog.approval_message">
                            <dt class="text-sm font-medium text-gray-500">承認時に共有した内容</dt>
                            <dd class="mt-1 p-3 text-sm text-gray-700 bg-white rounded border border-gray-200 whitespace-pre-line">
                                {{ deployLog.approval_message.message }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- 本番URLへのリンク（成功時のみ表示） -->
                <div v-if="deployLog.status === 'success'" class="p-6 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-green-800">
                                更新されたサイトを確認する
                            </p>
                            <a 
                                :href="project.production_url" 
                                target="_blank" 
                                rel="noopener noreferrer"
                                class="mt-2 inline-block text-sm text-green-700 underline hover:text-green-900"
                            >
                                {{ project.production_url }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="space-y-2 text-xs text-center text-gray-500">
                <!-- サービスロゴ（フッター上） -->
                <div class="pt-8 mt-12 border-t border-gray-200">
                    <div class="flex justify-center mb-10">
                        <a href="/" class="text-3xl font-bold tracking-wide transition text-slate-900 hover:text-slate-700">
                            Quicknee
                        </a>
                    </div>
                </div>
                
                <div class="flex flex-wrap gap-4 justify-center text-sm text-gray-600">
                    <span>運営元：Quicknee</span>
                    <a href="/terms" class="underline hover:text-gray-900">利用規約</a>
                    <a href="/privacy" class="underline hover:text-gray-900">プライバシーポリシー</a>
                </div>
                <div>&copy; {{ new Date().getFullYear() }} Quicknee. All rights reserved.</div>
            </footer>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    project: Object,
    token: String,
    deployLog: Object,
    averageDurationSeconds: Number,
});

const deployLogData = ref(props.deployLog);
const averageDurationSeconds = ref(props.averageDurationSeconds);
let pollInterval = null;

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
    return formatDuration(diffSeconds);
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

// 秒数を分・秒の形式に変換
const formatDuration = (seconds) => {
    if (!seconds) return '-';
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    if (mins > 0) {
        return `${mins}分${secs}秒`;
    }
    return `${secs}秒`;
};

// ステータスをポーリングで更新
const pollStatus = async () => {
    try {
        const response = await fetch(`/api/deploy-logs/${deployLogData.value.id}`, {
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
    // 実行中の場合のみポーリングを開始
    if (deployLogData.value.status === 'running' || deployLogData.value.status === 'pending') {
        console.log('Starting poll for deploy log:', deployLogData.value.id, 'Status:', deployLogData.value.status);
        
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
        }, 3000); // 3秒ごとに更新
        
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
        console.log('Deploy already finished, no poll needed. Status:', deployLogData.value.status);
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

