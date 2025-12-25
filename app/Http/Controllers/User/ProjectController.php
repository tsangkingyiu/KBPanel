<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\DeploymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function __construct(
        private DeploymentService $deploymentService
    ) {}

    public function index()
    {
        $projects = Auth::user()->projects()->latest()->paginate(10);
        return view('user.projects.index', compact('projects'));
    }

    public function create()
    {
        return view('user.projects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|unique:projects',
            'type' => 'required|in:laravel,wordpress',
            'laravel_version' => 'required_if:type,laravel',
            'php_version' => 'required|in:7.4,8.0,8.1,8.2,8.3',
            'webserver' => 'required|in:nginx,apache',
        ]);

        // Check user limits
        $user = Auth::user();
        if ($user->projects()->count() >= $user->project_limit) {
            return back()->with('error', 'Project limit reached');
        }

        $project = $user->projects()->create($validated);

        // Deploy project asynchronously
        \Illuminate\Support\Facades\Artisan::queue('kbpanel:deploy-laravel', [
            'project_id' => $project->id,
            '--version' => $validated['laravel_version'] ?? '12',
            '--php-version' => $validated['php_version'],
        ]);

        return redirect()->route('user.projects.show', $project)
            ->with('success', 'Project created and deployment started');
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);
        $project->load(['deployments', 'stagingEnvironments', 'gitRepository']);
        
        return view('user.projects.show', compact('project'));
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        
        // Stop and remove Docker container
        if ($project->docker_container_id) {
            $this->deploymentService->removeProject($project->id);
        }

        $project->delete();

        return redirect()->route('user.projects.index')
            ->with('success', 'Project deleted successfully');
    }
}
