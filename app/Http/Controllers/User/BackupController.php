<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\BackupService;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    public function __construct(
        private BackupService $backupService
    ) {}

    public function index(Project $project)
    {
        $this->authorize('view', $project);
        $backups = $project->backups()->latest()->paginate(20);
        return view('user.backups.index', compact('project', 'backups'));
    }

    public function create(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'backup_type' => 'required|in:full,database,files',
        ]);

        try {
            $backup = $this->backupService->createBackup(
                $project->id,
                $validated['backup_type']
            );

            return back()->with('success', 'Backup created successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function download(Project $project, $backupId)
    {
        $this->authorize('view', $project);
        
        try {
            return $this->backupService->downloadBackup($backupId);
        } catch (\Exception $e) {
            return back()->with('error', 'Download failed: ' . $e->getMessage());
        }
    }

    public function restore(Request $request, Project $project, $backupId)
    {
        $this->authorize('update', $project);

        try {
            $this->backupService->restoreBackup($project->id, $backupId);
            return back()->with('success', 'Backup restored successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }
}
