<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MonitoringService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private MonitoringService $monitoringService
    ) {}

    public function index()
    {
        $systemMetrics = $this->monitoringService->collectSystemMetrics();
        
        return view('admin.dashboard', [
            'stats' => $systemMetrics,
            'totalProjects' => \App\Models\Project::count(),
            'totalUsers' => \App\Models\User::count(),
            'activeContainers' => $systemMetrics['active_containers'] ?? 0,
        ]);
    }
}
