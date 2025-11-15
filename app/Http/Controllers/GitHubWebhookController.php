<?php

namespace App\Http\Controllers;

use App\Models\DeployLog;
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
                $deployLog = DeployLog::find($deployLogId);

                if ($deployLog) {
                    $deployLog->update([
                        'github_run_id' => $runId,
                        'status' => $this->mapStatus($status, $conclusion),
                        'finished_at' => $conclusion ? now() : null,
                        'raw_log' => json_encode($workflowRun),
                    ]);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

    private function mapStatus(string $status, ?string $conclusion): string
    {
        if ($conclusion === 'success') {
            return 'success';
        } elseif ($conclusion === 'failure' || $conclusion === 'cancelled') {
            return 'failed';
        } elseif ($status === 'in_progress' || $status === 'queued') {
            return 'running';
        }

        return 'pending';
    }
}
