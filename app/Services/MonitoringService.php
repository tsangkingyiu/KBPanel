<?php

namespace App\Services;

use App\Models\ResourceUsage;
use App\Models\Project;
use App\Models\User;

class MonitoringService
{
    public static function getSystemStats()
    {
        // TODO: Collect system-wide metrics
        return [
            'cpu_usage' => 0.0,
            'memory_usage' => 0.0,
            'disk_usage' => 0.0,
            'total_projects' => Project::count(),
            'active_containers' => 0,
            'total_users' => User::count(),
        ];
    }

    public function collectProjectMetrics(Project $project)
    {
        // TODO: Get container stats via DockerService
        return [
            'cpu_percent' => 0.0,
            'memory_mb' => 0.0,
            'disk_mb' => $project->disk_usage ?? 0,
        ];
    }

    public function collectUserMetrics(User $user)
    {
        $projects = $user->projects;
        
        return [
            'total_projects' => $projects->count(),
            'total_disk_usage' => $projects->sum('disk_usage'),
            'disk_quota' => $user->disk_quota,
            'remaining_quota' => $user->getRemainingDiskQuota(),
        ];
    }

    public function recordMetrics()
    {
        // TODO: This would be called by scheduler every X minutes
        $projects = Project::where('status', 'active')->get();

        foreach ($projects as $project) {
            $metrics = $this->collectProjectMetrics($project);
            
            ResourceUsage::create([
                'project_id' => $project->id,
                'user_id' => $project->user_id,
                'cpu_percent' => $metrics['cpu_percent'],
                'memory_mb' => $metrics['memory_mb'],
                'disk_mb' => $metrics['disk_mb'],
                'recorded_at' => now(),
            ]);
        }
    }
}
