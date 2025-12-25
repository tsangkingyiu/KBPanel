<?php

namespace App\Traits;

trait HasResourceLimits
{
    public function canCreateProject()
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->projects()->count() < ($this->project_limit ?? 10);
    }

    public function hasExceededDiskQuota()
    {
        if ($this->isAdmin()) {
            return false;
        }

        $totalDiskUsage = $this->projects()->sum('disk_usage');
        return $totalDiskUsage >= ($this->disk_quota ?? 1000);
    }

    public function getRemainingDiskQuota()
    {
        $totalDiskUsage = $this->projects()->sum('disk_usage');
        return max(0, ($this->disk_quota ?? 1000) - $totalDiskUsage);
    }

    public function getRemainingProjects()
    {
        return max(0, ($this->project_limit ?? 10) - $this->projects()->count());
    }
}
