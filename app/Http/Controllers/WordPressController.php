<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\WordPressService;
use Illuminate\Http\Request;

class WordPressController extends Controller
{
    public function __construct(
        private WordPressService $wordPressService
    ) {}

    public function deploy(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'site_title' => 'required|string',
            'admin_user' => 'required|string',
            'admin_password' => 'required|string|min:8',
            'admin_email' => 'required|email',
        ]);

        try {
            $this->wordPressService->deployWordPress(
                $project->id,
                $validated
            );

            return back()->with('success', 'WordPress deployed successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Deployment failed: ' . $e->getMessage());
        }
    }
}
