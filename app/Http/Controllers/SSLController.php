<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\SSLService;
use Illuminate\Http\Request;

class SSLController extends Controller
{
    public function __construct(
        private SSLService $sslService
    ) {}

    public function index(Project $project)
    {
        $this->authorize('view', $project);
        
        $certificates = $project->sslCertificates;
        return view('shared.ssl.index', compact('project', 'certificates'));
    }

    public function generate(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'domain' => 'required|string',
            'staging' => 'boolean',
        ]);

        try {
            $certificate = $this->sslService->generateCertificate(
                $validated['domain'],
                $validated['staging'] ?? false
            );

            return back()->with('success', 'SSL certificate generated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'SSL generation failed: ' . $e->getMessage());
        }
    }

    public function renew(Project $project, $certificateId)
    {
        $this->authorize('update', $project);

        try {
            $this->sslService->renewCertificate($certificateId);
            return back()->with('success', 'Certificate renewed successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Renewal failed: ' . $e->getMessage());
        }
    }
}
