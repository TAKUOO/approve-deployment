<?php

namespace App\Http\Controllers;

use App\Models\DeployLog;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeployController extends Controller
{
    public function trigger(Request $request, Project $project, $approvalMessageId = null)
    {
        $deployLog = DeployLog::create([
            'project_id' => $project->id,
            'status' => 'pending',
            'started_at' => now(),
            'approval_message_id' => $approvalMessageId,
        ]);

        try {
            $token = \Illuminate\Support\Facades\Crypt::decryptString($project->user->github_token);

            $inputs = [
                'project_id' => (string) $project->id,
                'deploy_log_id' => (string) $deployLog->id,
            ];

            // server_dirが設定されている場合は追加（末尾にスラッシュを追加）
            if ($project->server_dir) {
                $serverDir = rtrim($project->server_dir, '/') . '/';
                $inputs['server_dir'] = $serverDir;
            }

            $response = Http::withToken($token)
                ->post("https://api.github.com/repos/{$project->github_owner}/{$project->github_repo}/actions/workflows/{$project->github_workflow_id}/dispatches", [
                    'ref' => $project->github_branch,
                    'inputs' => $inputs,
                ]);

            if ($response->successful()) {
                // workflow_dispatchのレスポンスにはrun_idが含まれていないため、
                // ワークフロー実行一覧から最新の実行を取得
                $runsResponse = Http::withToken($token)
                    ->get("https://api.github.com/repos/{$project->github_owner}/{$project->github_repo}/actions/workflows/{$project->github_workflow_id}/runs", [
                        'per_page' => 1,
                    ]);

                $githubRunId = null;
                if ($runsResponse->successful()) {
                    $runs = $runsResponse->json();
                    if (isset($runs['workflow_runs'][0]['id'])) {
                        $githubRunId = (string) $runs['workflow_runs'][0]['id'];
                    }
                }

                $deployLog->update([
                    'status' => 'running',
                    'github_run_id' => $githubRunId,
                ]);

                Log::info('Deployment triggered', [
                    'deploy_log_id' => $deployLog->id,
                    'github_run_id' => $githubRunId,
                ]);

                return response()->json([
                    'message' => 'Deployment triggered successfully',
                    'deploy_log_id' => $deployLog->id,
                ]);
            } else {
                $deployLog->update([
                    'status' => 'failed',
                    'finished_at' => now(),
                    'raw_log' => $response->body(),
                ]);

                return response()->json([
                    'error' => 'Failed to trigger deployment',
                    'details' => $response->body(),
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Deployment trigger failed', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);

            $deployLog->update([
                'status' => 'failed',
                'finished_at' => now(),
                'raw_log' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to trigger deployment',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
