<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SSHService;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Admin SSH Terminal Controller
 * Provides SSH access to any project container
 */
class SSHTerminalController extends Controller
{
    protected $sshService;

    public function __construct(SSHService $sshService)
    {
        $this->sshService = $sshService;
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display SSH terminal interface for all projects
     */
    public function index()
    {
        $projects = Project::with('user')
            ->where('status', 'active')
            ->get();

        return view('admin.ssh.index', [
            'projects' => $projects
        ]);
    }

    /**
     * Connect to project container via SSH/Docker exec
     */
    public function connect(Project $project)
    {
        try {
            $connection = $this->sshService->createConnection($project);

            Log::info('Admin SSH connection', [
                'admin_id' => auth()->id(),
                'project_id' => $project->id,
                'project_owner' => $project->user_id
            ]);

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
     * Execute command in project container
     */
    public function executeCommand(Project $project, Request $request)
    {
        $request->validate([
            'command' => 'required|string'
        ]);

        try {
            $output = $this->sshService->executeCommand(
                $project,
                $request->input('command')
            );

            Log::info('Admin SSH command executed', [
                'admin_id' => auth()->id(),
                'project_id' => $project->id,
                'command' => $request->input('command')
            ]);

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
     * Get SSH session history for project
     */
    public function getHistory(Project $project)
    {
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
            $this->sshService->closeSession($request->session_id);

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
     * Get active SSH sessions across all projects
     */
    public function getActiveSessions()
    {
        try {
            $sessions = $this->sshService->getActiveSessions();

            return response()->json([
                'success' => true,
                'sessions' => $sessions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get active sessions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restart project container
     */
    public function restartContainer(Project $project)
    {
        try {
            $this->sshService->restartContainer($project);

            Log::info('Admin restarted container', [
                'admin_id' => auth()->id(),
                'project_id' => $project->id
            ]);

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
