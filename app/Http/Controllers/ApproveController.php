<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Approval;
use App\Models\ApprovalMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ApproveController extends Controller
{
    public function show(Request $request, string $token): Response
    {
        $project = Project::where('approve_token', $token)
            ->where(function ($query) {
                $query->whereNull('approve_token_expires_at')
                      ->orWhere('approve_token_expires_at', '>', now());
            })
            ->firstOrFail();
        
        $approvalMessage = null;
        if ($request->has('msg')) {
            $approvalMessage = ApprovalMessage::where('id', $request->query('msg'))
                ->where('project_id', $project->id)
                ->first();
        }

        // 承認ページに必要な情報のみを渡す（GitHub情報は除外）
        return Inertia::render('Approve', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'staging_url' => $project->staging_url,
                'production_url' => $project->production_url,
            ],
            'token' => $token,
            'approvalMessage' => $approvalMessage,
        ]);
    }

    public function approve(Request $request, string $token)
    {
        $project = Project::where('approve_token', $token)
            ->where(function ($query) {
                $query->whereNull('approve_token_expires_at')
                      ->orWhere('approve_token_expires_at', '>', now());
            })
            ->firstOrFail();

        // レート制限チェック（10分間に30回まで）
        $recentApprovals = Approval::where('project_id', $project->id)
            ->where('approved_at', '>', now()->subMinutes(10))
            ->count();

        if ($recentApprovals >= 30) {
            Log::warning('Approval rate limit exceeded', [
                'project_id' => $project->id,
                'ip_address' => $request->ip(),
                'token' => $token,
            ]);

            return redirect()->route('approve.show', $token)
                ->with('error', '承認回数が上限に達しました。しばらく時間をおいてから再度お試しください。');
        }

        // 承認履歴を記録
        Approval::create([
            'project_id' => $project->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'approved_at' => now(),
        ]);

        // メッセージIDを取得（クエリパラメータまたはフォームデータから）
        $approvalMessageId = $request->query('msg') ?? $request->input('msg');

        // デプロイをトリガー（内部API呼び出し）
        $deployController = new \App\Http\Controllers\DeployController();
        $response = $deployController->trigger($request, $project, $approvalMessageId);

        // レスポンスからdeploy_log_idを取得（成功・失敗どちらの場合でも）
        $responseData = json_decode($response->getContent(), true);
        $deployLogId = $responseData['deploy_log_id'] ?? null;

        if ($response->getStatusCode() === 200) {
            Log::info('Deployment approved', [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'ip_address' => $request->ip(),
                'deploy_log_id' => $deployLogId,
            ]);
        } else {
            Log::error('Deployment trigger failed', [
                'project_id' => $project->id,
                'ip_address' => $request->ip(),
                'deploy_log_id' => $deployLogId,
                'response_status' => $response->getStatusCode(),
                'response_body' => $response->getContent(),
            ]);
        }

        // deploy_log_idが取得できた場合は、成功・失敗に関わらずステータスページにリダイレクト
        if ($deployLogId) {
            return redirect()->route('approve.status', ['token' => $token, 'deployLog' => $deployLogId]);
        }

        // deploy_log_idが取得できない場合のみ、承認画面に戻る
        return redirect()->route('approve.show', $token)
            ->with('error', $response->getStatusCode() === 200 
                ? 'サイトの更新を開始しましたが、ステータスを確認できませんでした'
                : 'サイトの更新に失敗しました');
    }

    public function status(Request $request, string $token, $deployLog)
    {
        $project = Project::where('approve_token', $token)
            ->where(function ($query) {
                $query->whereNull('approve_token_expires_at')
                      ->orWhere('approve_token_expires_at', '>', now());
            })
            ->firstOrFail();
        
        // デプロイログを取得（プロジェクトに紐づいているか確認）
        $deployLog = \App\Models\DeployLog::where('id', $deployLog)
            ->where('project_id', $project->id)
            ->with('approvalMessage')
            ->firstOrFail();

        // 過去の成功したデプロイログから平均時間を計算
        $averageDuration = \App\Models\DeployLog::where('project_id', $project->id)
            ->where('status', 'success')
            ->whereNotNull('started_at')
            ->whereNotNull('finished_at')
            ->get()
            ->map(function ($log) {
                return $log->started_at->diffInSeconds($log->finished_at);
            })
            ->avg();

        // 承認ステータスページに必要な情報のみを渡す（GitHub情報は除外）
        return Inertia::render('ApproveStatus', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'production_url' => $project->production_url,
            ],
            'token' => $token,
            'deployLog' => $deployLog,
            'averageDurationSeconds' => $averageDuration ? (int) $averageDuration : null,
        ]);
    }
}
