<?php

namespace App\Http\Controllers;

use App\Models\DeployLog;
use App\Mail\DeployCompletedNotification;
use App\Services\SlackNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
                    ]);

                    if ($shouldNotify) {
                        // Slack通知
                        app(SlackNotifier::class)->notifyDeployStatus($deployLog, $mappedStatus);
                        
                        // メール通知
                        $this->sendEmailNotification($deployLog);
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

    private function sendEmailNotification(DeployLog $deployLog): void
    {
        try {
            $project = $deployLog->project;
            $user = $project->user;
            
            if (!$user || !$user->email) {
                Log::warning('Cannot send deploy completion notification: user or email not found', [
                    'deploy_log_id' => $deployLog->id,
                    'project_id' => $project->id,
                ]);
                return;
            }
            
            Mail::to($user->email)->send(new DeployCompletedNotification($deployLog));
            
            Log::info('Deploy completion notification sent', [
                'deploy_log_id' => $deployLog->id,
                'project_id' => $project->id,
                'user_email' => $user->email,
                'status' => $deployLog->status,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send deploy completion notification', [
                'deploy_log_id' => $deployLog->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
