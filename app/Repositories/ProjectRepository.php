<?php

namespace App\Repositories;

use App\Models\Project;

class ProjectRepository
{
    public function findByUser($userId)
    {
        return Project::where('user_id', $userId)->get();
    }

    public function findActive()
    {
        return Project::where('status', 'active')->get();
    }

    public function findByDomain($domain)
    {
        return Project::where('domain', $domain)->first();
    }

    public function getTotalDiskUsage($userId = null)
    {
        $query = Project::query();
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return $query->sum('disk_usage');
    }

    public function countByUser($userId)
    {
        return Project::where('user_id', $userId)->count();
    }
}
