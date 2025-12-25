<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\GitService;
use App\Models\Project;
use App\Models\GitRepository;
use Illuminate\Http\Request;

/**
 * User Git Controller
 * Users manage Git repositories for their own projects
 */
class GitController extends Controller
{
    protected $gitService;

    public function __construct(GitService $gitService)
    {
        $this->gitService = $gitService;
        $this->middleware('auth');
    }

    /**
     * Display user's Git repositories
     */
    public function index()
    {
        $repositories = GitRepository::whereHas('project', function($query) {
            $query->where('user_id', auth()->id());
        })
        ->with('project')
        ->orderBy('created_at', 'desc')
        ->get();

        $stats = [
            'total_repos' => $repositories->count(),
            'auto_deploy_enabled' => $repositories->where('auto_deploy', true)->count()
        ];

        return view('user.git.index', [
            'repositories' => $repositories,
            'stats' => $stats
        ]);
    }

    /**
     * Connect repository to user's project
     */
    public function connect(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'repository_url' => 'required|url',
            'branch' => 'nullable|string',
            'access_token' => 'nullable|string'
        ]);

        $project = Project::findOrFail($request->project_id);
        
        if ($project->user_id !== auth()->id()) {
            abort(403, 'You can only connect repositories to your own projects');
        }

        try {
            $repository = $this->gitService->connectRepository(
                $project,
                $request->repository_url,
                $request->input('branch', 'main'),
                $request->input('access_token')
            );

            return response()->json([
                'success' => true,
                'message' => 'Repository connected successfully',
                'repository' => $repository
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to connect repository: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pull latest changes
     */
    public function pull(GitRepository $repository)
    {
        if ($repository->project->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $result = $this->gitService->pullLatestChanges($repository);

            return response()->json([
                'success' => true,
                'message' => 'Changes pulled successfully',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to pull changes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get commit history
     */
    public function getCommits(GitRepository $repository, Request $request)
    {
        if ($repository->project->user_id !== auth()->id()) {
            abort(403);
        }

        $limit = $request->input('limit', 50);

        try {
            $commits = $this->gitService->getCommitHistory($repository, $limit);

            return response()->json([
                'success' => true,
                'commits' => $commits
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get commits: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * List branches
     */
    public function listBranches(GitRepository $repository)
    {
        if ($repository->project->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $branches = $this->gitService->listBranches($repository);

            return response()->json([
                'success' => true,
                'branches' => $branches
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to list branches: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Switch branch
     */
    public function switchBranch(GitRepository $repository, Request $request)
    {
        if ($repository->project->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'branch' => 'required|string'
        ]);

        try {
            $this->gitService->switchBranch($repository, $request->branch);

            return response()->json([
                'success' => true,
                'message' => 'Switched to branch: ' . $request->branch
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to switch branch: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Compare changes (diff)
     */
    public function compareChanges(GitRepository $repository, Request $request)
    {
        if ($repository->project->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'from' => 'required|string',
            'to' => 'required|string'
        ]);

        try {
            $diff = $this->gitService->compareChanges(
                $repository,
                $request->from,
                $request->to
            );

            return response()->json([
                'success' => true,
                'diff' => $diff
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to compare changes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enable/disable auto-deploy
     */
    public function toggleAutoDeploy(GitRepository $repository)
    {
        if ($repository->project->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $repository->auto_deploy = !$repository->auto_deploy;
            $repository->save();

            if ($repository->auto_deploy) {
                $webhookUrl = $this->gitService->setupWebhook($repository);
            }

            return response()->json([
                'success' => true,
                'message' => 'Auto-deploy ' . ($repository->auto_deploy ? 'enabled' : 'disabled'),
                'auto_deploy' => $repository->auto_deploy,
                'webhook_url' => $repository->auto_deploy ? ($webhookUrl ?? null) : null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle auto-deploy: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deploy specific commit
     */
    public function deployCommit(GitRepository $repository, Request $request)
    {
        if ($repository->project->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'commit_hash' => 'required|string'
        ]);

        try {
            $result = $this->gitService->deployCommit(
                $repository,
                $request->commit_hash
            );

            return response()->json([
                'success' => true,
                'message' => 'Commit deployed successfully',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deploy commit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disconnect repository
     */
    public function disconnect(GitRepository $repository)
    {
        if ($repository->project->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $this->gitService->disconnectRepository($repository);

            return response()->json([
                'success' => true,
                'message' => 'Repository disconnected successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to disconnect repository: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get webhook events log
     */
    public function getWebhookLog(GitRepository $repository)
    {
        if ($repository->project->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $log = $this->gitService->getWebhookLog($repository);

            return response()->json([
                'success' => true,
                'log' => $log
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get webhook log: ' . $e->getMessage()
            ], 500);
        }
    }
}
