<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\DatabaseService;
use App\Models\Project;
use App\Models\DatabaseInstance;
use Illuminate\Http\Request;

/**
 * User Database Manager Controller
 * Users can only manage their own project databases
 */
class DatabaseManagerController extends Controller
{
    protected $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
        $this->middleware('auth');
    }

    /**
     * Display user's databases
     */
    public function index()
    {
        $databases = DatabaseInstance::whereHas('project', function($query) {
            $query->where('user_id', auth()->id());
        })
        ->with('project')
        ->orderBy('created_at', 'desc')
        ->get();

        $stats = [
            'total_databases' => $databases->count(),
            'total_size_mb' => $databases->sum('size_mb')
        ];

        return view('user.database.index', [
            'databases' => $databases,
            'stats' => $stats
        ]);
    }

    /**
     * Create new database for user's project
     */
    public function create(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'db_name' => 'required|string|alpha_dash|max:64',
            'db_type' => 'required|in:mysql,mariadb',
            'db_user' => 'required|string|alpha_dash|max:32',
            'db_password' => 'required|string|min:8'
        ]);

        $project = Project::findOrFail($request->project_id);
        
        if ($project->user_id !== auth()->id()) {
            abort(403, 'You can only create databases for your own projects');
        }

        try {
            $database = $this->databaseService->createDatabase(
                $project,
                $request->db_name,
                $request->db_user,
                $request->db_password,
                $request->db_type
            );

            return response()->json([
                'success' => true,
                'message' => 'Database created successfully',
                'database' => $database
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get database details
     */
    public function show(DatabaseInstance $database)
    {
        if ($database->project->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this database');
        }

        $database->load('project');
        
        try {
            $stats = $this->databaseService->getDatabaseStats($database);
            
            return response()->json([
                'success' => true,
                'database' => $database,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get database info: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update database user password
     */
    public function updatePassword(DatabaseInstance $database, Request $request)
    {
        if ($database->project->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'new_password' => 'required|string|min:8'
        ]);

        try {
            $this->databaseService->updateDatabasePassword(
                $database,
                $request->new_password
            );

            return response()->json([
                'success' => true,
                'message' => 'Database password updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update password: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export database
     */
    public function export(DatabaseInstance $database, Request $request)
    {
        if ($database->project->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'format' => 'required|in:sql,gzip',
            'structure_only' => 'boolean',
            'data_only' => 'boolean'
        ]);

        try {
            $file = $this->databaseService->exportDatabase(
                $database,
                $request->format,
                $request->input('structure_only', false),
                $request->input('data_only', false)
            );

            return response()->download($file);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import database from SQL file
     */
    public function import(DatabaseInstance $database, Request $request)
    {
        if ($database->project->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'sql_file' => 'required|file|mimes:sql,gz|max:512000'
        ]);

        try {
            $result = $this->databaseService->importDatabase(
                $database,
                $request->file('sql_file')
            );

            return response()->json([
                'success' => true,
                'message' => 'Database imported successfully',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to import database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete database
     */
    public function destroy(DatabaseInstance $database)
    {
        if ($database->project->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $this->databaseService->deleteDatabase($database);

            return response()->json([
                'success' => true,
                'message' => 'Database deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Launch phpMyAdmin for database
     */
    public function launchPhpMyAdmin(DatabaseInstance $database)
    {
        if ($database->project->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $url = $this->databaseService->launchPhpMyAdmin($database);

            return response()->json([
                'success' => true,
                'url' => $url,
                'message' => 'phpMyAdmin session created'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to launch phpMyAdmin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get database connection info
     */
    public function getConnectionInfo(DatabaseInstance $database)
    {
        if ($database->project->user_id !== auth()->id()) {
            abort(403);
        }

        return response()->json([
            'success' => true,
            'connection' => [
                'host' => config('kbpanel.db.host'),
                'port' => $database->port,
                'database' => $database->db_name,
                'username' => $database->db_user,
                'password' => '***hidden***'
            ]
        ]);
    }

    /**
     * List all tables in database
     */
    public function listTables(DatabaseInstance $database)
    {
        if ($database->project->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $tables = $this->databaseService->listTables($database);

            return response()->json([
                'success' => true,
                'tables' => $tables
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to list tables: ' . $e->getMessage()
            ], 500);
        }
    }
}
