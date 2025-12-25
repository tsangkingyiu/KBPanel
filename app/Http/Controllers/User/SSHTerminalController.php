<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\SSHService;
use App\Models\Project;
use Illuminate\Http\Request;

/**
 * User SSH Terminal Controller
 * Users can only access SSH to their own projects
 */
class SSHTerminalController extends Controller
{
    protected $sshService;

    public function __construct(SSHService $sshService)
    {
        $this->sshService = $sshService;
        $this->middleware('auth');
    }

    /**
     * Display SSH terminal interface for user's projects
     */
    public function index()
    {
        $projects = auth()->user()->projects()
            ->where('status', 'active')
            ->get();

        return view('user.ssh.index', [
            'projects' => $projects
        ]);
    }

    /**
     * Connect to user's project container
     */
    public function connect(Project $project)
    {
        if ($project->user_id !== auth()->id()) {
            abort(403, 'You can only access your own projects');
        }

        try {
            $connection = $this->sshService->createConnection($project);

            return response()->json([
                'success' => true,
                'session_id' => $connection['session_id'],
                'ws_url' => $connection['ws_url'],
                'container' => $project->docker_container_id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to establish SSH connection: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Execute command in user's project container
     */
    public function executeCommand(Project $project, Request $request)
    {
        if ($project->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'command' => 'required|string'
        ]);

        try {
            $output = $this->sshService->executeCommand(
                $project,
                $request->input('command')
            );

            return response()->json([
                'success' => true,
                'output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Command execution failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get container terminal environment info
     */
    public function getEnvironment(Project $project)
    {
        if ($project->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $env = $this->sshService->getContainerEnvironment($project);

            return response()->json([
                'success' => true,
                'environment' => $env
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get environment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get SSH session history for user's project
     */
    public function getHistory(Project $project)
    {
        if ($project->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $history = $this->sshService->getSessionHistory($project);

            return response()->json([
                'success' => true,
                'history' => $history
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disconnect SSH session
     */
    public function disconnect(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string'
        ]);

        try {
            // Verify session belongs to user before closing
            $this->sshService->closeSession($request->session_id, auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Session closed'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to close session: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restart user's project container
     */
    public function restartContainer(Project $project)
    {
        if ($project->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $this->sshService->restartContainer($project);

            return response()->json([
                'success' => true,
                'message' => 'Container restarted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restart container: ' . $e->getMessage()
            ], 500);
        }
    }
}
