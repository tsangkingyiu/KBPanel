<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\DatabaseService;
use Illuminate\Http\Request;

class DatabaseManagerController extends Controller
{
    public function __construct(
        private DatabaseService $databaseService
    ) {}

    public function index(Project $project)
    {
        $this->authorize('view', $project);
        
        $databases = $project->databaseInstances;
        return view('shared.database.manager', compact('project', 'databases'));
    }

    public function createDatabase(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'db_type' => 'required|in:mysql,mariadb',
            'db_name' => 'required|string|alpha_dash',
        ]);

        try {
            $database = $this->databaseService->createDatabase(
                $project->id,
                $validated['db_type'],
                $validated['db_name']
            );

            return back()->with('success', 'Database created successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Database creation failed: ' . $e->getMessage());
        }
    }

    public function launchPhpMyAdmin(Project $project)
    {
        $this->authorize('view', $project);

        try {
            $url = $this->databaseService->launchPhpMyAdmin($project->id);
            return redirect($url);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to launch phpMyAdmin: ' . $e->getMessage());
        }
    }

    public function export(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        try {
            return $this->databaseService->exportDatabase($project->id);
        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    public function import(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'sql_file' => 'required|file|mimes:sql,txt',
        ]);

        try {
            $this->databaseService->importDatabase(
                $project->id,
                $validated['sql_file']
            );

            return back()->with('success', 'Database imported successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
