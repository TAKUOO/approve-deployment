<?php

namespace App\Http\Controllers;

use App\Models\DeployLog;
use App\Services\SlackNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GitHubWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        // GitHub Actions workflow_run イベントの処理
        if (isset($payload['workflow_run'])) {
            $workflowRun = $payload['workflow_run'];
            $runId = $workflowRun['id'] ?? null;
            $status = $workflowRun['status'] ?? null;
            $conclusion = $workflowRun['conclusion'] ?? null;

            // inputsからproject_idとdeploy_log_idを取得
            $inputs = $workflowRun['inputs'] ?? [];
            $deployLogId = $inputs['deploy_log_id'] ?? null;

            if ($deployLogId) {
                $deployLog = DeployLog::with(['project.user', 'approvalMessage'])->find($deployLogId);

                if ($deployLog) {
                    $oldStatus = $deployLog->status;
                    $mappedStatus = $this->mapStatus($status, $conclusion);
                    $shouldNotify = in_array($mappedStatus, ['success', 'failed'], true)
                        && $deployLog->finished_at === null
                        && $deployLog->status !== $mappedStatus;

                    $deployLog->update([
                        'github_run_id' => $runId,
                        'status' => $mappedStatus,
                        'finished_at' => $conclusion ? now() : null,
                        'raw_log' => json_encode($workflowRun),
                        'pr_title' => $deployLog->pr_title ?: ($workflowRun['display_title'] ?? null),
                    ]);

                    if ($shouldNotify) {
                        // Slack通知
                        app(SlackNotifier::class)->notifyDeployStatus($deployLog, $mappedStatus);
                    }
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

    private function mapStatus(?string $status, ?string $conclusion): string
    {
        // conclusionが設定されている場合は、それを優先
        if ($conclusion === 'success') {
            Log::info('Deploy status mapped to success', [
                'status' => $status,
                'conclusion' => $conclusion,
            ]);
            return 'success';
        } elseif ($conclusion === 'failure' || $conclusion === 'cancelled' || $conclusion === 'action_required') {
            Log::info('Deploy status mapped to failed', [
                'status' => $status,
                'conclusion' => $conclusion,
            ]);
            return 'failed';
        }
        
        // conclusionがない場合は、statusで判定
        if ($status === 'completed') {
            // completedだがconclusionがない場合は、成功とみなす
            Log::info('Deploy status mapped to success (completed without conclusion)', [
                'status' => $status,
                'conclusion' => $conclusion,
            ]);
            return 'success';
        } elseif ($status === 'in_progress' || $status === 'queued') {
            return 'running';
        }

        Log::warning('Deploy status mapped to pending (unknown status)', [
            'status' => $status,
            'conclusion' => $conclusion,
        ]);
        return 'pending';
    }

}
