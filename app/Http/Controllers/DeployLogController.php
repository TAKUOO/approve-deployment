<?php

namespace App\Http\Controllers;

use App\Models\DeployLog;
use App\Models\Project;
use App\Services\SlackNotifier;
use Illuminate\Http\Request;

class DeployLogController extends Controller
{
    public function show(Request $request, DeployLog $deployLog)
    {
        // 承認トークンで保護
        $token = $request->query('token');
        if (!$token) {
            return response()->json(['error' => 'Token required'], 401);
        }
        
        $project = Project::where('approve_token', $token)
            ->where(function ($query) {
                $query->whereNull('approve_token_expires_at')
                      ->orWhere('approve_token_expires_at', '>', now());
            })
            ->first();
        if (!$project || $deployLog->project_id !== $project->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // デプロイログと関連データを取得
        $deployLog->load('approvalMessage', 'project');

        // 実行中でGitHub Run IDがある場合、GitHub Actions APIから直接ステータスを確認
        // ただし、ポーリング頻度を考慮して、最後の同期から10秒以上経過している場合のみ同期する
        $lastSyncedAt = $deployLog->updated_at;
        $shouldSync = $lastSyncedAt === null || $lastSyncedAt->diffInSeconds(now()) >= 10;
        
        if (($deployLog->status === 'running' || $deployLog->status === 'pending') && $deployLog->github_run_id && $shouldSync) {
            try {
                $this->syncStatusFromGitHub($deployLog);
                // 再取得
                $deployLog->refresh();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to sync status from GitHub', [
                    'deploy_log_id' => $deployLog->id,
                    'github_run_id' => $deployLog->github_run_id,
                    'error' => $e->getMessage(),
                ]);
            }
        } elseif (($deployLog->status === 'running' || $deployLog->status === 'pending') && !$deployLog->github_run_id) {
            // github_run_idがない場合、最新のワークフロー実行を検索して設定
            try {
                if (method_exists($this, 'findAndSetGitHubRunId')) {
                    $this->findAndSetGitHubRunId($deployLog);
                    $deployLog->refresh();
                    
                    // github_run_idが設定されたら、ステータスを同期
                    if ($deployLog->github_run_id) {
                        $this->syncStatusFromGitHub($deployLog);
                        $deployLog->refresh();
                    }
                } else {
                    \Illuminate\Support\Facades\Log::error('findAndSetGitHubRunId method not found', [
                        'deploy_log_id' => $deployLog->id,
                    ]);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to find GitHub run ID', [
                    'deploy_log_id' => $deployLog->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // 過去の成功したデプロイログから平均時間を計算（データベース側で計算してパフォーマンスを最適化）
        $dbDriver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        
        if ($dbDriver === 'sqlite') {
            // SQLite用のクエリ（julianday関数を使用）
            $averageDuration = \App\Models\DeployLog::where('project_id', $deployLog->project_id)
                ->where('status', 'success')
                ->whereNotNull('started_at')
                ->whereNotNull('finished_at')
                ->selectRaw('AVG((julianday(finished_at) - julianday(started_at)) * 86400) as avg_seconds')
                ->value('avg_seconds');
        } else {
            // MySQL/MariaDB用のクエリ
            $averageDuration = \App\Models\DeployLog::where('project_id', $deployLog->project_id)
                ->where('status', 'success')
                ->whereNotNull('started_at')
                ->whereNotNull('finished_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_at, finished_at)) as avg_seconds')
                ->value('avg_seconds');
        }

        return response()->json([
            'id' => $deployLog->id,
            'status' => $deployLog->status,
            'github_run_id' => $deployLog->github_run_id,
            'started_at' => $deployLog->started_at?->toIso8601String(),
            'finished_at' => $deployLog->finished_at?->toIso8601String(),
            'approval_message' => $deployLog->approvalMessage ? [
                'id' => $deployLog->approvalMessage->id,
                'message' => $deployLog->approvalMessage->message,
            ] : null,
            'average_duration_seconds' => $averageDuration ? (int) $averageDuration : null,
        ]);
    }

    private function findAndSetGitHubRunId(DeployLog $deployLog)
    {
        $project = $deployLog->project;
        $user = $project->user;

        if (!$user->github_token) {
            return;
        }

        try {
            $token = \Illuminate\Support\Facades\Crypt::decryptString($user->github_token);
            
            // ワークフロー実行一覧から、deploy_log_idが一致するものを検索
            $response = \Illuminate\Support\Facades\Http::withToken($token)
                ->get("https://api.github.com/repos/{$project->github_owner}/{$project->github_repo}/actions/workflows/{$project->github_workflow_id}/runs", [
                    'per_page' => 20,
                ]);

            if ($response->successful()) {
                $runs = $response->json();
                $targetDeployLogId = (string) $deployLog->id;

                foreach ($runs['workflow_runs'] ?? [] as $run) {
                    $inputs = $run['inputs'] ?? [];
                    $runDeployLogId = $inputs['deploy_log_id'] ?? null;

                    if ($runDeployLogId === $targetDeployLogId) {
                        $deployLog->update([
                            'github_run_id' => (string) $run['id'],
                        ]);

                        \Illuminate\Support\Facades\Log::info('GitHub run ID found and set', [
                            'deploy_log_id' => $deployLog->id,
                            'github_run_id' => $run['id'],
                        ]);
                        return;
                    }
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to find GitHub run ID', [
                'deploy_log_id' => $deployLog->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function syncStatusFromGitHub(DeployLog $deployLog)
    {
        $project = $deployLog->project;
        $user = $project->user;

        if (!$user->github_token) {
            return;
        }

        try {
            $token = \Illuminate\Support\Facades\Crypt::decryptString($user->github_token);
            
            // GitHub Actions APIからワークフロー実行の詳細を取得（タイムアウトを5秒に設定）
            $response = \Illuminate\Support\Facades\Http::withToken($token)
                ->timeout(5)
                ->get("https://api.github.com/repos/{$project->github_owner}/{$project->github_repo}/actions/runs/{$deployLog->github_run_id}");

            if ($response->successful()) {
                $runData = $response->json();
                $status = $runData['status'] ?? null;
                $conclusion = $runData['conclusion'] ?? null;

                \Illuminate\Support\Facades\Log::info('GitHub API response', [
                    'deploy_log_id' => $deployLog->id,
                    'github_run_id' => $deployLog->github_run_id,
                    'github_status' => $status,
                    'github_conclusion' => $conclusion,
                ]);

                // ステータスをマッピング
                $mappedStatus = $this->mapStatus($status, $conclusion);

                // ステータスが変更された場合のみ更新
                if ($mappedStatus !== $deployLog->status) {
                    $shouldNotify = in_array($mappedStatus, ['success', 'failed'], true)
                        && $deployLog->finished_at === null;

                    $deployLog->update([
                        'status' => $mappedStatus,
                        'finished_at' => ($mappedStatus === 'success' || $mappedStatus === 'failed') ? now() : null,
                    ]);

                    \Illuminate\Support\Facades\Log::info('Deploy status synced from GitHub', [
                        'deploy_log_id' => $deployLog->id,
                        'old_status' => $deployLog->getOriginal('status'),
                        'new_status' => $mappedStatus,
                        'github_status' => $status,
                        'github_conclusion' => $conclusion,
                    ]);

                    if ($shouldNotify) {
                        app(SlackNotifier::class)->notifyDeployStatus($deployLog, $mappedStatus);
                    }
                } else {
                    \Illuminate\Support\Facades\Log::debug('Deploy status unchanged', [
                        'deploy_log_id' => $deployLog->id,
                        'current_status' => $deployLog->status,
                        'github_status' => $status,
                        'github_conclusion' => $conclusion,
                    ]);
                }
            } else {
                \Illuminate\Support\Facades\Log::warning('GitHub API request failed', [
                    'deploy_log_id' => $deployLog->id,
                    'github_run_id' => $deployLog->github_run_id,
                    'status_code' => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to sync status from GitHub API', [
                'deploy_log_id' => $deployLog->id,
                'error' => $e->getMessage(),
            ]);
            // エラーが発生しても処理を継続（タイムアウトやネットワークエラー時もレスポンスを返す）
            // throw $e; を削除して、エラー時でも既存のデータを返すようにする
        }
    }

    private function mapStatus(?string $status, ?string $conclusion): string
    {
        // conclusionが設定されている場合は、それを優先
        if ($conclusion === 'success') {
            return 'success';
        } elseif ($conclusion === 'failure' || $conclusion === 'cancelled' || $conclusion === 'action_required') {
            return 'failed';
        }
        
        // conclusionがない場合は、statusで判定
        if ($status === 'completed') {
            return 'success';
        } elseif ($status === 'in_progress' || $status === 'queued') {
            return 'running';
        }

        return 'pending';
    }
}
