<?php

namespace App\Services;

use App\Models\DeployLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SlackNotifier
{
    public function notifyDeployStatus(DeployLog $deployLog, string $status): void
    {
        $deployLog->loadMissing('project.user');
        $project = $deployLog->project;
        if (!$project) {
            return;
        }

        // 優先順位: プロジェクト設定 > ユーザー設定 > グローバル設定
        $webhookUrl = $project->slack_webhook_url 
            ?: ($project->user->slack_webhook_url ?? null)
            ?: config('services.slack.webhook_url');
        
        if (!$webhookUrl) {
            return;
        }

        if (!in_array($status, ['success', 'failed'], true)) {
            return;
        }

        $statusLabel = $status === 'success' ? '✅ 本番反映完了' : '❌ デプロイ失敗';
        $statusColor = $status === 'success' ? '#16a34a' : '#dc2626';

        $payload = [
            'text' => $statusLabel,
            'attachments' => [
                [
                    'color' => $statusColor,
                    'fields' => [
                        [
                            'title' => 'プロジェクト',
                            'value' => $project->name,
                            'short' => true,
                        ],
                        [
                            'title' => 'ステータス',
                            'value' => $status === 'success' ? '成功' : '失敗',
                            'short' => true,
                        ],
                        [
                            'title' => 'Deploy Log ID',
                            'value' => (string) $deployLog->id,
                            'short' => true,
                        ],
                        [
                            'title' => 'GitHub Run ID',
                            'value' => $deployLog->github_run_id ?: '-',
                            'short' => true,
                        ],
                    ],
                ],
            ],
        ];

        try {
            Http::timeout(5)->post($webhookUrl, $payload);
        } catch (\Throwable $e) {
            Log::warning('Slack notification failed', [
                'deploy_log_id' => $deployLog->id,
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
