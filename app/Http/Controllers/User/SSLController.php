<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\SSLService;
use App\Models\Project;
use App\Models\SSLCertificate;
use App\Models\Domain;
use Illuminate\Http\Request;

/**
 * User SSL Controller
 * Users manage SSL certificates for their own domains
 */
class SSLController extends Controller
{
    protected $sslService;

    public function __construct(SSLService $sslService)
    {
        $this->sslService = $sslService;
        $this->middleware('auth');
    }

    /**
     * Display user's SSL certificates
     */
    public function index()
    {
        $certificates = SSLCertificate::whereHas('domain.project', function($query) {
            $query->where('user_id', auth()->id());
        })
        ->with(['domain.project'])
        ->orderBy('expires_at', 'asc')
        ->get();

        $stats = [
            'total_certs' => $certificates->count(),
            'active_certs' => $certificates->where('status', 'active')->count(),
            'expiring_soon' => $certificates->where('expires_at', '<=', now()->addDays(30))->count()
        ];

        return view('user.ssl.index', [
            'certificates' => $certificates,
            'stats' => $stats
        ]);
    }

    /**
     * Issue Let's Encrypt certificate
     */
    public function issueLetsEncrypt(Request $request)
    {
        $request->validate([
            'domain_id' => 'required|exists:domains,id',
            'email' => 'required|email'
        ]);

        $domain = Domain::findOrFail($request->domain_id);
        
        if ($domain->project->user_id !== auth()->id()) {
            abort(403, 'You can only manage SSL for your own domains');
        }

        try {
            $certificate = $this->sslService->issueLetsEncrypt(
                $domain,
                $request->email
            );

            return response()->json([
                'success' => true,
                'message' => 'SSL certificate issued successfully',
                'certificate' => $certificate
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to issue certificate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload custom SSL certificate
     */
    public function uploadCustom(Request $request)
    {
        $request->validate([
            'domain_id' => 'required|exists:domains,id',
            'certificate' => 'required|string',
            'private_key' => 'required|string',
            'chain' => 'nullable|string'
        ]);

        $domain = Domain::findOrFail($request->domain_id);
        
        if ($domain->project->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $certificate = $this->sslService->uploadCustomCertificate(
                $domain,
                $request->certificate,
                $request->private_key,
                $request->input('chain')
            );

            return response()->json([
                'success' => true,
                'message' => 'Custom certificate uploaded successfully',
                'certificate' => $certificate
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload certificate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Renew certificate
     */
    public function renew(SSLCertificate $certificate)
    {
        if ($certificate->domain->project->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $renewed = $this->sslService->renewCertificate($certificate);

            return response()->json([
                'success' => true,
                'message' => 'Certificate renewed successfully',
                'certificate' => $renewed
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to renew certificate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete certificate
     */
    public function destroy(SSLCertificate $certificate)
    {
        if ($certificate->domain->project->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $this->sslService->deleteCertificate($certificate);

            return response()->json([
                'success' => true,
                'message' => 'Certificate deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete certificate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get certificate details
     */
    public function show(SSLCertificate $certificate)
    {
        if ($certificate->domain->project->user_id !== auth()->id()) {
            abort(403);
        }

        $certificate->load(['domain.project']);

        try {
            $details = $this->sslService->getCertificateDetails($certificate);

            return response()->json([
                'success' => true,
                'certificate' => $certificate,
                'details' => $details
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get certificate details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate certificate
     */
    public function validate(SSLCertificate $certificate)
    {
        if ($certificate->domain->project->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $validation = $this->sslService->validateCertificate($certificate);

            return response()->json([
                'success' => true,
                'validation' => $validation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force HTTPS for domain
     */
    public function forceHttps(Domain $domain, Request $request)
    {
        if ($domain->project->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'force' => 'required|boolean'
        ]);

        try {
            $this->sslService->forceHttps($domain, $request->input('force'));

            return response()->json([
                'success' => true,
                'message' => 'HTTPS enforcement ' . ($request->input('force') ? 'enabled' : 'disabled')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update HTTPS setting: ' . $e->getMessage()
            ], 500);
        }
    }
}
