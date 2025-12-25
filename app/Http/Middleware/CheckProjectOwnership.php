<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Project;
use Symfony\Component\HttpFoundation\Response;

class CheckProjectOwnership
{
    public function handle(Request $request, Closure $next): Response
    {
        $project = Project::findOrFail($request->route('project'));

        // Admin can access all projects
        if (auth()->user()->role->name === 'admin') {
            return $next($request);
        }

        // Check ownership
        if ($project->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this project');
        }

        return $next($request);
    }
}
