<?php

namespace App\Http\Controllers;

use App\Models\DeployLog;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeployController extends Controller
{
    public function trigger(Request $request, Project $project)
    {
        $deployLog = DeployLog::create([
            'project_id' => $project->id,
            'status' => 'pending',
            'started_at' => now(),
        ]);

        try {
            $response = Http::withToken(config('services.github.token'))
                ->post("https://api.github.com/repos/{$project->github_owner}/{$project->github_repo}/actions/workflows/{$project->github_workflow_id}/dispatches", [
                    'ref' => $project->github_branch,
                    'inputs' => [
                        'project_id' => $project->id,
                        'deploy_log_id' => $deployLog->id,
                    ],
                ]);

            if ($response->successful()) {
                $deployLog->update([
                    'status' => 'running',
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
