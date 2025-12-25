<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\SSHService;
use Illuminate\Http\Request;

class SSHTerminalController extends Controller
{
    public function __construct(
        private SSHService $sshService
    ) {}

    public function index(Project $project)
    {
        $this->authorize('view', $project);
        
        // Generate secure WebSocket token
        $token = $this->sshService->generateTerminalToken($project->id);

        return view('shared.terminal.ssh', compact('project', 'token'));
    }

    public function execute(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'command' => 'required|string',
        ]);

        try {
            $output = $this->sshService->executeCommand(
                $project->id,
                $validated['command']
            );

            return response()->json(['output' => $output]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
