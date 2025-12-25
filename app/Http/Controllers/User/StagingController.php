<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\StagingService;
use Illuminate\Http\Request;

class StagingController extends Controller
{
    public function __construct(
        private StagingService $stagingService
    ) {}

    public function index(Project $project)
    {
        $this->authorize('view', $project);
        $staging = $project->stagingEnvironments;
        return view('user.staging.index', compact('project', 'staging'));
    }

    public function create(Project $project)
    {
        $this->authorize('update', $project);

        try {
            $staging = $this->stagingService->createStagingEnvironment($project->id);
            return back()->with('success', 'Staging environment created');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create staging: ' . $e->getMessage());
        }
    }

    public function syncFromProduction(Project $project)
    {
        $this->authorize('update', $project);

        try {
            $this->stagingService->syncProductionToStaging($project->id);
            return back()->with('success', 'Staging synchronized from production');
        } catch (\Exception $e) {
            return back()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    public function promoteToProduction(Project $project)
    {
        $this->authorize('update', $project);

        try {
            $this->stagingService->syncStagingToProduction($project->id);
            return back()->with('success', 'Staging promoted to production');
        } catch (\Exception $e) {
            return back()->with('error', 'Promotion failed: ' . $e->getMessage());
        }
    }
}
