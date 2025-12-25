<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\FileManagerService;
use Illuminate\Http\Request;

class FileManagerController extends Controller
{
    public function __construct(
        private FileManagerService $fileManagerService
    ) {}

    public function index(Project $project)
    {
        $this->authorize('view', $project);
        
        $path = request()->query('path', '/');
        $files = $this->fileManagerService->listFiles($project->id, $path);

        return view('shared.file-manager.index', compact('project', 'files', 'path'));
    }

    public function upload(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'file' => 'required|file|max:102400', // 100MB
            'path' => 'required|string',
        ]);

        try {
            $this->fileManagerService->uploadFile(
                $project->id,
                $validated['file'],
                $validated['path']
            );

            return back()->with('success', 'File uploaded successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    public function delete(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'path' => 'required|string',
        ]);

        try {
            $this->fileManagerService->deleteFile($project->id, $validated['path']);
            return back()->with('success', 'File deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    public function edit(Request $request, Project $project)
    {
        $this->authorize('view', $project);
        
        $path = $request->query('path');
        $content = $this->fileManagerService->getFileContent($project->id, $path);

        return view('shared.file-manager.edit', compact('project', 'path', 'content'));
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'path' => 'required|string',
            'content' => 'required|string',
        ]);

        try {
            $this->fileManagerService->updateFileContent(
                $project->id,
                $validated['path'],
                $validated['content']
            );

            return back()->with('success', 'File updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }
}
