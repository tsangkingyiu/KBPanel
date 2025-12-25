<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FileManagerService;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Admin File Manager Controller
 * Allows admins to manage files for ALL user projects
 */
class FileManagerController extends Controller
{
    protected $fileManager;

    public function __construct(FileManagerService $fileManager)
    {
        $this->fileManager = $fileManager;
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display file manager for all projects
     */
    public function index()
    {
        $projects = Project::with('user')->get();
        
        return view('admin.file-manager.index', [
            'projects' => $projects
        ]);
    }

    /**
     * Browse files for a specific project
     */
    public function browse(Request $request, Project $project)
    {
        $path = $request->input('path', '/');
        
        try {
            $contents = $this->fileManager->listDirectory($project, $path);
            
            return response()->json([
                'success' => true,
                'path' => $path,
                'contents' => $contents,
                'project' => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'owner' => $project->user->name
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to browse directory: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get file contents for editing
     */
    public function getFile(Project $project, Request $request)
    {
        $request->validate([
            'path' => 'required|string'
        ]);

        try {
            $content = $this->fileManager->readFile($project, $request->input('path'));
            
            return response()->json([
                'success' => true,
                'content' => $content,
                'path' => $request->input('path')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to read file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save file contents
     */
    public function saveFile(Project $project, Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'content' => 'required|string'
        ]);

        try {
            $this->fileManager->writeFile(
                $project,
                $request->input('path'),
                $request->input('content')
            );

            return response()->json([
                'success' => true,
                'message' => 'File saved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new file
     */
    public function createFile(Project $project, Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'name' => 'required|string'
        ]);

        try {
            $fullPath = rtrim($request->input('path'), '/') . '/' . $request->input('name');
            $this->fileManager->createFile($project, $fullPath);

            return response()->json([
                'success' => true,
                'message' => 'File created successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new directory
     */
    public function createDirectory(Project $project, Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'name' => 'required|string'
        ]);

        try {
            $fullPath = rtrim($request->input('path'), '/') . '/' . $request->input('name');
            $this->fileManager->createDirectory($project, $fullPath);

            return response()->json([
                'success' => true,
                'message' => 'Directory created successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create directory: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete file or directory
     */
    public function delete(Project $project, Request $request)
    {
        $request->validate([
            'path' => 'required|string'
        ]);

        try {
            $this->fileManager->delete($project, $request->input('path'));

            return response()->json([
                'success' => true,
                'message' => 'Deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rename file or directory
     */
    public function rename(Project $project, Request $request)
    {
        $request->validate([
            'old_path' => 'required|string',
            'new_name' => 'required|string'
        ]);

        try {
            $this->fileManager->rename(
                $project,
                $request->input('old_path'),
                $request->input('new_name')
            );

            return response()->json([
                'success' => true,
                'message' => 'Renamed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to rename: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload files
     */
    public function upload(Project $project, Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'files' => 'required|array',
            'files.*' => 'file|max:51200' // 50MB max
        ]);

        try {
            $uploaded = [];
            foreach ($request->file('files') as $file) {
                $filename = $this->fileManager->uploadFile(
                    $project,
                    $request->input('path'),
                    $file
                );
                $uploaded[] = $filename;
            }

            return response()->json([
                'success' => true,
                'message' => count($uploaded) . ' file(s) uploaded successfully',
                'files' => $uploaded
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload files: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download file
     */
    public function download(Project $project, Request $request)
    {
        $request->validate([
            'path' => 'required|string'
        ]);

        try {
            return $this->fileManager->downloadFile($project, $request->input('path'));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get file/directory permissions
     */
    public function getPermissions(Project $project, Request $request)
    {
        $request->validate([
            'path' => 'required|string'
        ]);

        try {
            $permissions = $this->fileManager->getPermissions($project, $request->input('path'));
            
            return response()->json([
                'success' => true,
                'permissions' => $permissions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get permissions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set file/directory permissions
     */
    public function setPermissions(Project $project, Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'permissions' => 'required|string|regex:/^[0-7]{3}$/'
        ]);

        try {
            $this->fileManager->setPermissions(
                $project,
                $request->input('path'),
                $request->input('permissions')
            );

            return response()->json([
                'success' => true,
                'message' => 'Permissions updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set permissions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search files
     */
    public function search(Project $project, Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'path' => 'nullable|string'
        ]);

        try {
            $results = $this->fileManager->search(
                $project,
                $request->input('query'),
                $request->input('path', '/')
            );

            return response()->json([
                'success' => true,
                'results' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
