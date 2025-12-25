<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\WordPressService;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Admin WordPress Controller
 * Manage WordPress installations for all projects
 */
class WordPressController extends Controller
{
    protected $wordPressService;

    public function __construct(WordPressService $wordPressService)
    {
        $this->wordPressService = $wordPressService;
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display all WordPress installations
     */
    public function index()
    {
        $wordpressProjects = Project::where('type', 'wordpress')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_wp' => Project::where('type', 'wordpress')->count(),
            'active_wp' => Project::where('type', 'wordpress')->where('status', 'active')->count(),
            'wp_versions' => Project::where('type', 'wordpress')
                ->selectRaw('wordpress_version, COUNT(*) as count')
                ->groupBy('wordpress_version')
                ->get()
        ];

        return view('admin.wordpress.index', [
            'projects' => $wordpressProjects,
            'stats' => $stats
        ]);
    }

    /**
     * Install WordPress for a project
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

        try {
            $project = Project::findOrFail($request->project_id);
            
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

            Log::info('Admin installed WordPress', [
                'admin_id' => auth()->id(),
                'project_id' => $project->id,
                'wp_version' => $installation['version']
            ]);

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
        try {
            $result = $this->wordPressService->updateCore($project);

            Log::info('Admin updated WordPress core', [
                'admin_id' => auth()->id(),
                'project_id' => $project->id,
                'new_version' => $result['new_version']
            ]);

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

            Log::info('Admin installed WordPress plugin', [
                'admin_id' => auth()->id(),
                'project_id' => $project->id,
                'plugin_slug' => $request->plugin_slug
            ]);

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
        $request->validate([
            'plugin_slug' => 'required|string'
        ]);

        try {
            $result = $this->wordPressService->updatePlugin(
                $project,
                $request->plugin_slug
            );

            Log::info('Admin updated WordPress plugin', [
                'admin_id' => auth()->id(),
                'project_id' => $project->id,
                'plugin_slug' => $request->plugin_slug
            ]);

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
        $request->validate([
            'plugin_slug' => 'required|string'
        ]);

        try {
            $this->wordPressService->deletePlugin($project, $request->plugin_slug);

            Log::info('Admin deleted WordPress plugin', [
                'admin_id' => auth()->id(),
                'project_id' => $project->id,
                'plugin_slug' => $request->plugin_slug
            ]);

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

            Log::info('Admin installed WordPress theme', [
                'admin_id' => auth()->id(),
                'project_id' => $project->id,
                'theme_slug' => $request->theme_slug
            ]);

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
        $request->validate([
            'command' => 'required|string'
        ]);

        try {
            $output = $this->wordPressService->runWPCLI(
                $project,
                $request->command
            );

            Log::info('Admin ran WP-CLI command', [
                'admin_id' => auth()->id(),
                'project_id' => $project->id,
                'command' => $request->command
            ]);

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
        try {
            $status = $this->wordPressService->toggleMaintenanceMode($project);

            Log::info('Admin toggled WordPress maintenance mode', [
                'admin_id' => auth()->id(),
                'project_id' => $project->id,
                'maintenance' => $status
            ]);

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
