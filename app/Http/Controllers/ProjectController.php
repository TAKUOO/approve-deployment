<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::where('user_id', Auth::id())
            ->with(['deployLogs.approvalMessage'])
            ->latest()
            ->get();

        // プロジェクトがない場合は作成画面にリダイレクト
        if ($projects->isEmpty()) {
            return redirect()->route('projects.create');
        }

        // 実行中のデプロイログのステータスをGitHub Actions APIから同期
        foreach ($projects as $project) {
            foreach ($project->deployLogs as $deployLog) {
                if (($deployLog->status === 'running' || $deployLog->status === 'pending')) {
                    try {
                        // github_run_idがない場合、まず検索して設定
                        if (!$deployLog->github_run_id) {
                            $this->findAndSetGitHubRunId($deployLog);
                            $deployLog->refresh();
                        }
                        
                        // github_run_idがある場合、ステータスを同期
                        if ($deployLog->github_run_id) {
                            $this->syncDeployLogStatus($deployLog);
                            $deployLog->refresh();
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::warning('Failed to sync deploy log', [
                            'deploy_log_id' => $deployLog->id,
                            'github_run_id' => $deployLog->github_run_id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        // 再取得
        $projects = Project::where('user_id', Auth::id())
            ->with(['deployLogs.approvalMessage'])
            ->latest()
            ->get();

        // デフォルトで最初のプロジェクトを選択
        $selectedProject = $projects->first();

        return Inertia::render('Dashboard/ProjectList', [
            'projects' => $projects,
            'selectedProject' => $selectedProject,
        ]);
    }

    private function findAndSetGitHubRunId(\App\Models\DeployLog $deployLog)
    {
        $project = $deployLog->project;
        $user = $project->user;

        if (!$user->github_token) {
            \Illuminate\Support\Facades\Log::warning('No GitHub token for user', [
                'deploy_log_id' => $deployLog->id,
                'user_id' => $user->id,
            ]);
            return;
        }

        try {
            $token = \Illuminate\Support\Facades\Crypt::decryptString($user->github_token);
            
            \Illuminate\Support\Facades\Log::info('Searching for GitHub run ID', [
                'deploy_log_id' => $deployLog->id,
                'project_id' => $project->id,
                'owner' => $project->github_owner,
                'repo' => $project->github_repo,
                'workflow_id' => $project->github_workflow_id,
            ]);
            
            // ワークフロー実行一覧から、deploy_log_idが一致するものを検索
            $response = \Illuminate\Support\Facades\Http::withToken($token)
                ->get("https://api.github.com/repos/{$project->github_owner}/{$project->github_repo}/actions/workflows/{$project->github_workflow_id}/runs", [
                    'per_page' => 30,
                ]);

            if ($response->successful()) {
                $runs = $response->json();
                $targetDeployLogId = (string) $deployLog->id;

                \Illuminate\Support\Facades\Log::info('GitHub API response received', [
                    'deploy_log_id' => $deployLog->id,
                    'total_runs' => count($runs['workflow_runs'] ?? []),
                ]);

                $matchedRun = null;
                $minTimeDiff = null;

                foreach ($runs['workflow_runs'] ?? [] as $run) {
                    $inputs = $run['inputs'] ?? [];
                    $runDeployLogId = $inputs['deploy_log_id'] ?? null;

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

                    \Illuminate\Support\Facades\Log::info('GitHub run ID found and set', [
                        'deploy_log_id' => $deployLog->id,
                        'github_run_id' => $matchedRun['id'],
                        'matched_by' => $matchedRun['inputs']['deploy_log_id'] ?? 'time',
                        'run_status' => $matchedRun['status'] ?? null,
                        'run_conclusion' => $matchedRun['conclusion'] ?? null,
                    ]);
                    return;
                }
                
                \Illuminate\Support\Facades\Log::warning('GitHub run ID not found', [
                    'deploy_log_id' => $deployLog->id,
                    'searched_runs' => count($runs['workflow_runs'] ?? []),
                ]);
            } else {
                \Illuminate\Support\Facades\Log::error('GitHub API request failed', [
                    'deploy_log_id' => $deployLog->id,
                    'status_code' => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to find GitHub run ID', [
                'deploy_log_id' => $deployLog->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function syncDeployLogStatus(\App\Models\DeployLog $deployLog)
    {
        $project = $deployLog->project;
        $user = $project->user;

        if (!$user->github_token || !$deployLog->github_run_id) {
            return;
        }

        try {
            $token = \Illuminate\Support\Facades\Crypt::decryptString($user->github_token);
            
            // GitHub Actions APIからワークフロー実行の詳細を取得
            $response = \Illuminate\Support\Facades\Http::withToken($token)
                ->get("https://api.github.com/repos/{$project->github_owner}/{$project->github_repo}/actions/runs/{$deployLog->github_run_id}");

            if ($response->successful()) {
                $runData = $response->json();
                $status = $runData['status'] ?? null;
                $conclusion = $runData['conclusion'] ?? null;

                // ステータスをマッピング
                $mappedStatus = $this->mapDeployStatus($status, $conclusion);

                // ステータスが変更された場合のみ更新
                if ($mappedStatus !== $deployLog->status) {
                    $deployLog->update([
                        'status' => $mappedStatus,
                        'finished_at' => ($mappedStatus === 'success' || $mappedStatus === 'failed') ? now() : null,
                    ]);

                    \Illuminate\Support\Facades\Log::info('Deploy log status synced from GitHub', [
                        'deploy_log_id' => $deployLog->id,
                        'old_status' => $deployLog->getOriginal('status'),
                        'new_status' => $mappedStatus,
                        'github_status' => $status,
                        'github_conclusion' => $conclusion,
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to sync deploy log status from GitHub API', [
                'deploy_log_id' => $deployLog->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function mapDeployStatus(?string $status, ?string $conclusion): string
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

    public function create(): Response
    {
        $hasProjects = Project::where('user_id', Auth::id())->exists();
        
        return Inertia::render('Dashboard/ProjectCreate', [
            'hasProjects' => $hasProjects,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'staging_url' => 'required|url',
            'production_url' => 'required|url',
            'server_dir' => 'nullable|string|max:255',
            'github_owner' => 'required|string',
            'github_repo' => 'required|string',
            'github_workflow_id' => 'required|string',
            'github_branch' => 'required|string',
        ]);
        
        // github_branchが空の場合は'main'をデフォルト値として設定
        if (empty($validated['github_branch'])) {
            $validated['github_branch'] = 'main';
        }

        // プロジェクト名が未指定の場合は、レポジトリ名を使用
        $name = $validated['name'] ?? "{$validated['github_owner']}/{$validated['github_repo']}";

        // より強力なトークン生成（64文字のランダム文字列）
        $approveToken = bin2hex(random_bytes(32));

        $project = Project::create([
            ...$validated,
            'name' => $name,
            'user_id' => Auth::id(),
            'approve_token' => $approveToken,
            'github_branch' => $validated['github_branch'] ?? 'main',
        ]);

        return redirect()->route('projects.index');
    }

    public function edit(Project $project): Response
    {
        // 所有者チェック
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('Dashboard/ProjectEdit', [
            'project' => $project,
        ]);
    }

    public function update(Request $request, Project $project)
    {
        // 所有者チェック
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'staging_url' => 'required|url',
            'production_url' => 'required|url',
            'server_dir' => 'nullable|string|max:255',
            'github_owner' => 'required|string',
            'github_repo' => 'required|string',
            'github_workflow_id' => 'required|string',
            'github_branch' => 'required|string',
        ]);
        
        // github_branchが空の場合は'main'をデフォルト値として設定
        if (empty($validated['github_branch'])) {
            $validated['github_branch'] = 'main';
        }

        // プロジェクト名が未指定の場合は、レポジトリ名を使用
        $name = $validated['name'] ?? "{$validated['github_owner']}/{$validated['github_repo']}";

        $project->update([
            ...$validated,
            'name' => $name,
            'github_branch' => $validated['github_branch'] ?? 'main',
        ]);

        return redirect()->route('projects.index');
    }

    public function destroy(Project $project)
    {
        // 所有者チェック
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        $project->delete();

        return redirect()->route('projects.index');
    }

    public function getOrganizations()
    {
        $user = Auth::user();
        try {
            $token = \Illuminate\Support\Facades\Crypt::decryptString($user->github_token);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Token decryption failed'], 500);
        }

        // まず組織一覧を取得
        $orgsResponse = \Illuminate\Support\Facades\Http::withToken($token)->get('https://api.github.com/user/orgs');
        
        $orgs = [];
        if ($orgsResponse->successful()) {
            $orgs = $orgsResponse->json();
        } else {
            \Log::warning('GitHub API Error - Organizations', [
                'status' => $orgsResponse->status(),
                'body' => $orgsResponse->body()
            ]);
            
            // 組織一覧が取得できない場合、リポジトリから組織を抽出
            $reposResponse = \Illuminate\Support\Facades\Http::withToken($token)->get('https://api.github.com/user/repos', [
                'sort' => 'updated',
                'per_page' => 100,
                'affiliation' => 'owner,organization_member',
            ]);
            
            if ($reposResponse->successful()) {
                $repos = $reposResponse->json();
                $orgNames = [];
                foreach ($repos as $repo) {
                    if (isset($repo['owner']['type']) && $repo['owner']['type'] === 'Organization') {
                        $orgName = $repo['owner']['login'];
                        if (!in_array($orgName, $orgNames)) {
                            $orgNames[] = $orgName;
                            $orgs[] = [
                                'id' => $repo['owner']['id'],
                                'login' => $orgName,
                                'avatar_url' => $repo['owner']['avatar_url'] ?? null,
                            ];
                        }
                    }
                }
                \Log::info('GitHub Organizations extracted from repositories', [
                    'count' => count($orgs),
                    'orgs' => $orgs
                ]);
            }
        }

        \Log::info('GitHub Organizations final result', [
            'count' => count($orgs),
            'orgs' => $orgs
        ]);

        return response()->json($orgs);
    }

    public function getRepositories(Request $request)
    {
        $user = Auth::user();
        try {
            $token = \Illuminate\Support\Facades\Crypt::decryptString($user->github_token);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Token decryption failed'], 500);
        }

        $organization = $request->query('organization');

        if ($organization && $organization !== 'personal') {
            // 組織のリポジトリを取得
            // まず /user/repos で全リポジトリを取得し、組織でフィルタリング
            $response = \Illuminate\Support\Facades\Http::withToken($token)->get('https://api.github.com/user/repos', [
                'sort' => 'updated',
                'per_page' => 100,
                'affiliation' => 'owner,organization_member', // 所有または組織メンバーとしてのリポジトリ
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'error' => 'Failed to fetch repositories',
                    'details' => $response->body(),
                    'status' => $response->status()
                ], $response->status());
            }

            // 組織名でフィルタリング
            $allRepos = $response->json();
            $filteredRepos = array_filter($allRepos, function($repo) use ($organization) {
                return isset($repo['owner']['login']) && $repo['owner']['login'] === $organization;
            });

            return response()->json(array_values($filteredRepos));
        } else {
            // 個人リポジトリを取得（個人が所有するリポジトリのみ）
        $response = \Illuminate\Support\Facades\Http::withToken($token)->get('https://api.github.com/user/repos', [
            'sort' => 'updated',
            'per_page' => 100,
                'affiliation' => 'owner', // 個人が所有するリポジトリのみ（typeは指定しない）
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'error' => 'Failed to fetch personal repositories',
                    'details' => $response->body(),
                    'status' => $response->status()
                ], $response->status());
            }

            return response()->json($response->json());
        }

        return response()->json($response->json());
    }

    public function getWorkflows(Request $request)
    {
        $user = Auth::user();
        try {
            $token = \Illuminate\Support\Facades\Crypt::decryptString($user->github_token);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Token decryption failed'], 500);
        }
        
        $owner = $request->query('owner');
        $repo = $request->query('repo');

        $response = \Illuminate\Support\Facades\Http::withToken($token)->get("https://api.github.com/repos/{$owner}/{$repo}/actions/workflows");

        return response()->json($response->json());
    }

    public function getBranches(Request $request)
    {
        $user = Auth::user();
        try {
            $token = \Illuminate\Support\Facades\Crypt::decryptString($user->github_token);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Token decryption failed'], 500);
        }

        $owner = $request->query('owner');
        $repo = $request->query('repo');

        // リポジトリ情報を取得してデフォルトブランチを確認
        $repoResponse = \Illuminate\Support\Facades\Http::withToken($token)->get("https://api.github.com/repos/{$owner}/{$repo}");
        $defaultBranch = null;
        if ($repoResponse->successful()) {
            $repoData = $repoResponse->json();
            $defaultBranch = $repoData['default_branch'] ?? null;
        }

        // mainまたはmasterブランチのみを取得
        // まずmainブランチを試す
        $mainBranchResponse = \Illuminate\Support\Facades\Http::withToken($token)->get("https://api.github.com/repos/{$owner}/{$repo}/branches/main");
        if ($mainBranchResponse->successful()) {
            return response()->json([$mainBranchResponse->json()]);
        }

        // mainブランチがない場合はmasterブランチを試す
        $masterBranchResponse = \Illuminate\Support\Facades\Http::withToken($token)->get("https://api.github.com/repos/{$owner}/{$repo}/branches/master");
        if ($masterBranchResponse->successful()) {
            return response()->json([$masterBranchResponse->json()]);
        }

        // main/masterブランチが存在しない場合は、デフォルトブランチを取得
        if ($defaultBranch) {
            $defaultBranchResponse = \Illuminate\Support\Facades\Http::withToken($token)->get("https://api.github.com/repos/{$owner}/{$repo}/branches/{$defaultBranch}");
            if ($defaultBranchResponse->successful()) {
                return response()->json([$defaultBranchResponse->json()]);
            }
        }

        // どれも取得できない場合はエラー
        return response()->json([
            'error' => 'main or master branch not found',
            'default_branch' => $defaultBranch
        ], 404);
    }

    public function generateApprovalUrl(Request $request, Project $project)
    {
        // プロジェクトの所有権を確認
        if ($project->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        // メッセージを保存
        $approvalMessage = \App\Models\ApprovalMessage::create([
            'project_id' => $project->id,
            'message' => $validated['message'],
        ]);

        // 承認URLを生成（短いIDを含める）
        $approvalUrl = route('approve.show', [
            'token' => $project->approve_token,
            'msg' => $approvalMessage->id,
        ]);

        return response()->json([
            'approval_url' => $approvalUrl,
            'message_id' => $approvalMessage->id,
        ]);
    }
}
