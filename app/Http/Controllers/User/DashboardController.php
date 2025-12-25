<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\MonitoringService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        private MonitoringService $monitoringService
    ) {}

    public function index()
    {
        $user = Auth::user();
        $projects = $user->projects()->with('deployments')->get();
        $metrics = $this->monitoringService->collectUserMetrics($user->id);

        return view('user.dashboard', [
            'projects' => $projects,
            'metrics' => $metrics,
            'quota' => [
                'disk_used' => $metrics['disk_usage_mb'] ?? 0,
                'disk_total' => $user->disk_quota,
                'projects_used' => $projects->count(),
                'projects_total' => $user->project_limit,
            ],
        ]);
    }
}
