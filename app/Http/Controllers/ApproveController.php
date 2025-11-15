<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApproveController extends Controller
{
    public function show(Request $request, string $token): Response
    {
        $project = Project::where('approve_token', $token)->firstOrFail();

        return Inertia::render('Approve', [
            'project' => $project,
            'token' => $token,
        ]);
    }

    public function approve(Request $request, string $token)
    {
        $project = Project::where('approve_token', $token)->firstOrFail();

        // デプロイをトリガー（内部API呼び出し）
        $deployController = new \App\Http\Controllers\DeployController();
        $response = $deployController->trigger($request, $project);

        if ($response->getStatusCode() === 200) {
            return redirect()->route('approve.show', $token)->with('success', 'デプロイが開始されました');
        }

        return redirect()->route('approve.show', $token)->with('error', 'デプロイの開始に失敗しました');
    }
}
