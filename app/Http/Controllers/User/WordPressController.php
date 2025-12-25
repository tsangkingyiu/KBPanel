<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\WordPressService;
use App\Models\Project;
use Illuminate\Http\Request;

/**
 * User WordPress Controller
 * Users manage WordPress installations for their own projects
 */
class WordPressController extends Controller
{
    protected $wordPressService;

    public function __construct(WordPressService $wordPressService)
    {
        $this->wordPressService = $wordPressService;
        $this->middleware('auth');
    }

    /**
     * Display user's WordPress installations
     */
    public function index()
    {
        $wordpressProjects = auth()->user()->projects()
            ->where('type', 'wordpress')
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total_wp' => $wordpressProjects->count(),
            'active_wp' => $wordpressProjects->where('status', 'active')->count()
        ];

        return view('user.wordpress.index', [
            'projects' => $wordpressProjects,
            'stats' => $stats
        ]);
    }

    /**
     * Install WordPress for user's project
     */
    public function install(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'wp_admin_user' => 'required|string',
            'wp_admin_password' => 'required|string|min:8',
            'wp_admin_email' => 'required|email',
            'site_title' => 'required|string',
            'wp_version' => 'nullable|string'
        ]);

        $project = Project::findOrFail($request->project_id);
        
        if ($project->user_id !== auth()->id()) {
            abort(403, 'You can only install WordPress on your own projects');
        }

        try {
            $installation = $this->wordPressService->installWordPress(
                $project,
                [
                    'admin_user' => $request->wp_admin_user,
                    'admin_password' => $request->wp_admin_password,
                    'admin_email' => $request->wp_admin_email,
                    'site_title' => $request->site_title,
                    'version' => $request->input('wp_version', 'latest')
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'WordPress installed successfully',
                'installation' => $installation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to install WordPress: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update WordPress core
     */
    public function updateCore(Project $project)
    {
        if ($project->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $result = $this->wordPressService->updateCore($project);

            return response()->json([
                'success' => true,
                'message' => 'WordPress updated successfully',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update WordPress: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * List plugins
     */
    public function listPlugins(Project $project)
    {
        if ($project->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $plugins = $this->wordPressService->listPlugins($project);

            return response()->json([
                'success' => true,
                'plugins' => $plugins
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to list plugins: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Install plugin
     */
    public function installPlugin(Project $project, Request $request)
    {
        if ($project->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'plugin_slug' => 'required|string',
            'activate' => 'boolean'
        ]);

        try {
            $result = $this->wordPressService->installPlugin(
                $project,
                $request->plugin_slug,
                $request->input('activate', true)
            );

            return response()->json([
                'success' => true,
                'message' => 'Plugin installed successfully',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to install plugin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update plugin
     */
    public function updatePlugin(Project $project, Request $request)
    {
        if ($project->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'plugin_slug' => 'required|string'
        ]);

        try {
            $result = $this->wordPressService->updatePlugin(
                $project,
                $request->plugin_slug
            );

            return response()->json([
                'success' => true,
                'message' => 'Plugin updated successfully',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update plugin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete plugin
     */
    public function deletePlugin(Project $project, Request $request)
    {
        if ($project->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'plugin_slug' => 'required|string'
        ]);

        try {
            $this->wordPressService->deletePlugin($project, $request->plugin_slug);

            return response()->json([
                'success' => true,
                'message' => 'Plugin deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete plugin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * List themes
     */
    public function listThemes(Project $project)
    {
        if ($project->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $themes = $this->wordPressService->listThemes($project);

            return response()->json([
                'success' => true,
                'themes' => $themes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to list themes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Install theme
     */
    public function installTheme(Project $project, Request $request)
    {
        if ($project->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'theme_slug' => 'required|string',
            'activate' => 'boolean'
        ]);

        try {
            $result = $this->wordPressService->installTheme(
                $project,
                $request->theme_slug,
                $request->input('activate', false)
            );

            return response()->json([
                'success' => true,
                'message' => 'Theme installed successfully',
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to install theme: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Run WP-CLI command
     */
    public function runWPCLI(Project $project, Request $request)
    {
        if ($project->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'command' => 'required|string'
        ]);

        try {
            $output = $this->wordPressService->runWPCLI(
                $project,
                $request->command
            );

            return response()->json([
                'success' => true,
                'output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Command failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get WordPress info
     */
    public function getInfo(Project $project)
    {
        if ($project->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $info = $this->wordPressService->getWordPressInfo($project);

            return response()->json([
                'success' => true,
                'info' => $info
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get info: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enable/disable maintenance mode
     */
    public function toggleMaintenance(Project $project)
    {
        if ($project->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $status = $this->wordPressService->toggleMaintenanceMode($project);

            return response()->json([
                'success' => true,
                'message' => 'Maintenance mode ' . ($status ? 'enabled' : 'disabled'),
                'maintenance_mode' => $status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle maintenance mode: ' . $e->getMessage()
            ], 500);
        }
    }
}
