<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\DeploymentService;
use Illuminate\Http\Request;

class DeploymentController extends Controller
{
    public function __construct(
        private DeploymentService $deploymentService
    ) {}

    public function laravel(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'laravel_version' => 'required|string',
            'php_version' => 'required|in:7.4,8.0,8.1,8.2,8.3',
        ]);

        try {
            $this->deploymentService->deployLaravel(
                $project->id,
                $validated['laravel_version'],
                $validated['php_version']
            );

            return back()->with('success', 'Laravel deployment started');
        } catch (\Exception $e) {
            return back()->with('error', 'Deployment failed: ' . $e->getMessage());
        }
    }

    public function wordpress(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        try {
            $this->deploymentService->deployWordPress($project->id);
            return back()->with('success', 'WordPress deployment started');
        } catch (\Exception $e) {
            return back()->with('error', 'Deployment failed: ' . $e->getMessage());
        }
    }
}
