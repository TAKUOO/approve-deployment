<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(): Response
    {
        $projects = Project::where('user_id', Auth::id())
            ->with('deployLogs')
            ->latest()
            ->get();

        return Inertia::render('Dashboard/ProjectList', [
            'projects' => $projects,
        ]);
    }

    public function show(Project $project): Response
    {
        $project->load('deployLogs');

        return Inertia::render('Dashboard/ProjectShow', [
            'project' => $project,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Dashboard/ProjectCreate');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'staging_url' => 'required|url',
            'production_url' => 'required|url',
            'github_owner' => 'required|string',
            'github_repo' => 'required|string',
            'github_workflow_id' => 'required|string',
            'github_branch' => 'nullable|string|default:main',
        ]);

        $approveToken = hash_hmac(
            'sha256',
            Auth::id() . time(),
            config('app.key')
        );

        $project = Project::create([
            ...$validated,
            'user_id' => Auth::id(),
            'approve_token' => $approveToken,
            'github_branch' => $validated['github_branch'] ?? 'main',
        ]);

        return redirect()->route('projects.show', $project);
    }
}
