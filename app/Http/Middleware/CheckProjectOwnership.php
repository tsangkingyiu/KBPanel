<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Project;

class CheckProjectOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $projectId = $request->route('project');
        
        if (!$projectId) {
            abort(404, 'Project not found');
        }

        $project = Project::find($projectId);
        
        if (!$project) {
            abort(404, 'Project not found');
        }

        // Admins can access all projects
        if (auth()->user()->hasRole('admin')) {
            return $next($request);
        }

        // Check if user owns the project
        if ($project->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this project');
        }

        return $next($request);
    }
}
