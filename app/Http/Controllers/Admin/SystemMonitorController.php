<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MonitoringService;
use App\Models\User;
use Illuminate\Http\Request;

class SystemMonitorController extends Controller
{
    public function __construct(
        private MonitoringService $monitoringService
    ) {}

    public function system()
    {
        $metrics = $this->monitoringService->collectSystemMetrics();
        return view('admin.monitoring.system', compact('metrics'));
    }

    public function users()
    {
        $users = User::with('projects')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'projects_count' => $user->projects->count(),
                'metrics' => $this->monitoringService->collectUserMetrics($user->id),
            ];
        });

        return view('admin.monitoring.users', compact('users'));
    }
}
