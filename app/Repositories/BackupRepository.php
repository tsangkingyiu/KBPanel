<?php

namespace App\Repositories;

use App\Models\Backup;

class BackupRepository
{
    public function findByProject($projectId)
    {
        return Backup::where('project_id', $projectId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findByUser($userId)
    {
        return Backup::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getTotalBackupSize($projectId = null, $userId = null)
    {
        $query = Backup::query();
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return $query->sum('size_mb');
    }

    public function deleteOldBackups($projectId, $keepCount = 10)
    {
        $backupsToDelete = Backup::where('project_id', $projectId)
            ->orderBy('created_at', 'desc')
            ->skip($keepCount)
            ->get();
        
        foreach ($backupsToDelete as $backup) {
            $backup->delete();
        }
        
        return $backupsToDelete->count();
    }
}
