<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\GitService;
use Illuminate\Http\Request;

class GitController extends Controller
{
    public function __construct(
        private GitService $gitService
    ) {}

    public function repositories(Project $project)
    {
        $this->authorize('view', $project);
        
        $repository = $project->gitRepository;
        return view('shared.git.repositories', compact('project', 'repository'));
    }

    public function connect(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'repository_url' => 'required|url',
            'branch' => 'required|string',
            'access_token' => 'nullable|string',
            'auto_deploy' => 'boolean',
        ]);

        try {
            $this->gitService->connectRepository(
                $project->id,
                $validated['repository_url'],
                $validated['branch'],
                $validated['access_token'] ?? null,
                $validated['auto_deploy'] ?? false
            );

            return back()->with('success', 'Git repository connected successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Connection failed: ' . $e->getMessage());
        }
    }

    public function pull(Project $project)
    {
        $this->authorize('update', $project);

        try {
            $this->gitService->pullLatestChanges($project->id);
            return back()->with('success', 'Repository updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Pull failed: ' . $e->getMessage());
        }
    }

    public function commits(Project $project)
    {
        $this->authorize('view', $project);
        
        $commits = $this->gitService->getCommitHistory($project->id);
        return view('shared.git.commits', compact('project', 'commits'));
    }

    public function deploy(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'commit_hash' => 'nullable|string',
        ]);

        try {
            $this->gitService->deployCommit(
                $project->id,
                $validated['commit_hash'] ?? null
            );

            return back()->with('success', 'Deployment started');
        } catch (\Exception $e) {
            return back()->with('error', 'Deployment failed: ' . $e->getMessage());
        }
    }
}
