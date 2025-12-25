<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GitService;
use App\Models\Project;
use App\Models\GitRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Admin Git Controller
 * Manage Git repositories for all projects
 */
class GitController extends Controller
{
    protected $gitService;

    public function __construct(GitService $gitService)
    {
        $this->gitService = $gitService;
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display all Git repositories
     */
    public function index()
    {
        $repositories = GitRepository::with(['project.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_repos' => GitRepository::count(),
            'auto_deploy_enabled' => GitRepository::where('auto_deploy', true)->count(),
            'recent_pulls' => GitRepository::where('last_pulled_at', '>=', now()->subHours(24))->count()
        ];

        return view('admin.git.index', [
            'repositories' => $repositories,
            'stats' => $stats
        ]);
    }

    /**
     * Connect repository to project
     */
    public function connect(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'repository_url' => 'required|url',
            'branch' => 'nullable|string',
            'access_token' => 'nullable|string'
        ]);

        try {
            $project = Project::findOrFail($request->project_id);
            
            $repository = $this->gitService->connectRepository(
                $project,
                $request->repository_url,
                $request->input('branch', 'main'),
                $request->input('access_token')
            );

            Log::info('Admin connected Git repository', [
                'admin_id' => auth()->id(),
                'project_id' => $project->id,
                'repository_url' => $request->repository_url
            ]);

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
        try {
            $result = $this->gitService->pullLatestChanges($repository);

            Log::info('Admin pulled Git changes', [
                'admin_id' => auth()->id(),
                'repository_id' => $repository->id
            ]);

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
        $request->validate([
            'branch' => 'required|string'
        ]);

        try {
            $this->gitService->switchBranch($repository, $request->branch);

            Log::info('Admin switched Git branch', [
                'admin_id' => auth()->id(),
                'repository_id' => $repository->id,
                'branch' => $request->branch
            ]);

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
        try {
            $repository->auto_deploy = !$repository->auto_deploy;
            $repository->save();

            if ($repository->auto_deploy) {
                $webhookUrl = $this->gitService->setupWebhook($repository);
            }

            Log::info('Admin toggled auto-deploy', [
                'admin_id' => auth()->id(),
                'repository_id' => $repository->id,
                'auto_deploy' => $repository->auto_deploy
            ]);

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
        $request->validate([
            'commit_hash' => 'required|string'
        ]);

        try {
            $result = $this->gitService->deployCommit(
                $repository,
                $request->commit_hash
            );

            Log::info('Admin deployed specific commit', [
                'admin_id' => auth()->id(),
                'repository_id' => $repository->id,
                'commit_hash' => $request->commit_hash
            ]);

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
        try {
            $this->gitService->disconnectRepository($repository);

            Log::info('Admin disconnected Git repository', [
                'admin_id' => auth()->id(),
                'repository_id' => $repository->id
            ]);

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
