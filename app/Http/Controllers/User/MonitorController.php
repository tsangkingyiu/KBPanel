<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\MonitoringService;

class MonitorController extends Controller
{
    public function __construct(
        private MonitoringService $monitoringService
    ) {}

    public function index(Project $project)
    {
        $this->authorize('view', $project);
        
        $metrics = $this->monitoringService->collectProjectMetrics($project->id);
        $history = $project->resourceUsage()
            ->where('recorded_at', '>=', now()->subDay())
            ->orderBy('recorded_at')
            ->get();

        return view('user.monitoring.index', compact('project', 'metrics', 'history'));
    }
}
