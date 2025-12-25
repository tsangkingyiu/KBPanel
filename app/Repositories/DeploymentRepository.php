<?php

namespace App\Repositories;

use App\Models\Deployment;

class DeploymentRepository
{
    public function findByProject($projectId)
    {
        return Deployment::where('project_id', $projectId)
            ->orderBy('deployed_at', 'desc')
            ->get();
    }

    public function getLatestDeployment($projectId)
    {
        return Deployment::where('project_id', $projectId)
            ->latest('deployed_at')
            ->first();
    }

    public function getSuccessfulDeployments($projectId)
    {
        return Deployment::where('project_id', $projectId)
            ->where('status', 'success')
            ->orderBy('deployed_at', 'desc')
            ->get();
    }

    public function getFailedDeployments($projectId)
    {
        return Deployment::where('project_id', $projectId)
            ->where('status', 'failed')
            ->orderBy('deployed_at', 'desc')
            ->get();
    }
}
