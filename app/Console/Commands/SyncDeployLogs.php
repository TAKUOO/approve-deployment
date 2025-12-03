<?php

namespace App\Console\Commands;

use App\Models\DeployLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class SyncDeployLogs extends Command
{
    protected $signature = 'deploy:sync-logs {--project-id=}';
    protected $description = 'Sync deploy log statuses from GitHub Actions API';

    public function handle()
    {
        $query = DeployLog::whereIn('status', ['running', 'pending'])
            ->with('project.user');

        if ($this->option('project-id')) {
            $query->where('project_id', $this->option('project-id'));
        }

        $deployLogs = $query->get();

        $this->info("Found {$deployLogs->count()} deploy logs to sync");

        foreach ($deployLogs as $deployLog) {
            $this->line("Processing deploy log ID: {$deployLog->id}");

            $project = $deployLog->project;
            $user = $project->user;

            if (!$user->github_token) {
                $this->warn("  No GitHub token for user {$user->id}");
                continue;
            }

            try {
                $token = Crypt::decryptString($user->github_token);

                // github_run_idがない場合、検索して設定
                if (!$deployLog->github_run_id) {
                    $this->info("  Searching for GitHub run ID...");
                    
                    $response = Http::withToken($token)
                        ->get("https://api.github.com/repos/{$project->github_owner}/{$project->github_repo}/actions/workflows/{$project->github_workflow_id}/runs", [
                            'per_page' => 30,
                        ]);

                    if ($response->successful()) {
                        $runs = $response->json();
                        $targetDeployLogId = (string) $deployLog->id;

                        $this->line("  Found " . count($runs['workflow_runs'] ?? []) . " workflow runs");

                        $matchedRun = null;
                        $minTimeDiff = null;

                        foreach ($runs['workflow_runs'] ?? [] as $run) {
                            $inputs = $run['inputs'] ?? [];
                            $runDeployLogId = $inputs['deploy_log_id'] ?? null;

                            $this->line("    Run ID: {$run['id']}, Status: {$run['status']}, Conclusion: " . ($run['conclusion'] ?? 'null') . ", Deploy Log ID: " . ($runDeployLogId ?? 'null') . ", Created: " . ($run['created_at'] ?? 'null'));

                            // deploy_log_idが一致する場合
                            if ($runDeployLogId === $targetDeployLogId) {
                                $matchedRun = $run;
                                break;
                            }

                            // deploy_log_idがない場合、実行時刻が最も近いものを探す
                            if (!$runDeployLogId && $deployLog->started_at) {
                                $runCreatedAt = isset($run['created_at']) ? new \DateTime($run['created_at']) : null;
                                if ($runCreatedAt) {
                                    $timeDiff = abs($deployLog->started_at->getTimestamp() - $runCreatedAt->getTimestamp());
                                    // 5分以内の実行を候補とする
                                    if ($timeDiff <= 300 && ($minTimeDiff === null || $timeDiff < $minTimeDiff)) {
                                        $minTimeDiff = $timeDiff;
                                        $matchedRun = $run;
                                    }
                                }
                            }
                        }

                        if ($matchedRun) {
                            $deployLog->update([
                                'github_run_id' => (string) $matchedRun['id'],
                            ]);
                            $this->info("  ✓ Found GitHub run ID: {$matchedRun['id']} (matched by " . ($matchedRun['inputs']['deploy_log_id'] ?? 'time') . ")");
                            $deployLog->refresh();
                        }
                    } else {
                        $this->error("  Failed to fetch workflow runs: {$response->status()}");
                        $this->error("  Response: {$response->body()}");
                    }
                }

                // github_run_idがある場合、ステータスを同期
                if ($deployLog->github_run_id) {
                    $this->info("  Syncing status from GitHub run ID: {$deployLog->github_run_id}");
                    
                    $response = Http::withToken($token)
                        ->get("https://api.github.com/repos/{$project->github_owner}/{$project->github_repo}/actions/runs/{$deployLog->github_run_id}");

                    if ($response->successful()) {
                        $runData = $response->json();
                        $status = $runData['status'] ?? null;
                        $conclusion = $runData['conclusion'] ?? null;

                        $mappedStatus = $this->mapStatus($status, $conclusion);

                        if ($mappedStatus !== $deployLog->status) {
                            $deployLog->update([
                                'status' => $mappedStatus,
                                'finished_at' => ($mappedStatus === 'success' || $mappedStatus === 'failed') ? now() : null,
                            ]);
                            $this->info("  Status updated: {$deployLog->getOriginal('status')} -> {$mappedStatus}");
                        } else {
                            $this->line("  Status unchanged: {$mappedStatus}");
                        }
                    } else {
                        $this->error("  Failed to fetch GitHub run: {$response->status()}");
                    }
                } else {
                    $this->warn("  No GitHub run ID found");
                }
            } catch (\Exception $e) {
                $this->error("  Error: {$e->getMessage()}");
            }
        }

        $this->info('Sync completed');
        return 0;
    }

    private function mapStatus(?string $status, ?string $conclusion): string
    {
        if ($conclusion === 'success') {
            return 'success';
        } elseif ($conclusion === 'failure' || $conclusion === 'cancelled' || $conclusion === 'action_required') {
            return 'failed';
        }
        
        if ($status === 'completed') {
            return 'success';
        } elseif ($status === 'in_progress' || $status === 'queued') {
            return 'running';
        }

        return 'pending';
    }
}

