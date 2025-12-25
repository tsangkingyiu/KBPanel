<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SSLService;
use App\Models\Project;
use App\Models\SSLCertificate;
use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Admin SSL Controller
 * Manage SSL certificates for all domains and projects
 */
class SSLController extends Controller
{
    protected $sslService;

    public function __construct(SSLService $sslService)
    {
        $this->sslService = $sslService;
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display all SSL certificates
     */
    public function index()
    {
        $certificates = SSLCertificate::with(['domain.project.user'])
            ->orderBy('expires_at', 'asc')
            ->paginate(20);

        $stats = [
            'total_certs' => SSLCertificate::count(),
            'active_certs' => SSLCertificate::where('status', 'active')->count(),
            'expiring_soon' => SSLCertificate::where('expires_at', '<=', now()->addDays(30))->count(),
            'expired_certs' => SSLCertificate::where('expires_at', '<', now())->count()
        ];

        return view('admin.ssl.index', [
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

        try {
            $domain = Domain::findOrFail($request->domain_id);
            
            $certificate = $this->sslService->issueLetsEncrypt(
                $domain,
                $request->email
            );

            Log::info('Admin issued Let\'s Encrypt certificate', [
                'admin_id' => auth()->id(),
                'domain_id' => $domain->id,
                'domain_name' => $domain->name
            ]);

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

        try {
            $domain = Domain::findOrFail($request->domain_id);
            
            $certificate = $this->sslService->uploadCustomCertificate(
                $domain,
                $request->certificate,
                $request->private_key,
                $request->input('chain')
            );

            Log::info('Admin uploaded custom SSL certificate', [
                'admin_id' => auth()->id(),
                'domain_id' => $domain->id
            ]);

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
        try {
            $renewed = $this->sslService->renewCertificate($certificate);

            Log::info('Admin renewed SSL certificate', [
                'admin_id' => auth()->id(),
                'certificate_id' => $certificate->id
            ]);

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
     * Auto-renew expiring certificates
     */
    public function autoRenew()
    {
        try {
            $renewed = $this->sslService->autoRenewExpiring();

            Log::info('Admin triggered auto-renewal', [
                'admin_id' => auth()->id(),
                'renewed_count' => count($renewed)
            ]);

            return response()->json([
                'success' => true,
                'message' => count($renewed) . ' certificate(s) renewed',
                'renewed' => $renewed
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Auto-renewal failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete certificate
     */
    public function destroy(SSLCertificate $certificate)
    {
        try {
            $this->sslService->deleteCertificate($certificate);

            Log::info('Admin deleted SSL certificate', [
                'admin_id' => auth()->id(),
                'certificate_id' => $certificate->id
            ]);

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
        $certificate->load(['domain.project.user']);

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
        $request->validate([
            'force' => 'required|boolean'
        ]);

        try {
            $this->sslService->forceHttps($domain, $request->input('force'));

            Log::info('Admin changed force HTTPS setting', [
                'admin_id' => auth()->id(),
                'domain_id' => $domain->id,
                'force_https' => $request->input('force')
            ]);

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

    /**
     * Get Let's Encrypt account info
     */
    public function getLetsEncryptInfo()
    {
        try {
            $info = $this->sslService->getLetsEncryptAccountInfo();

            return response()->json([
                'success' => true,
                'info' => $info
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get account info: ' . $e->getMessage()
            ], 500);
        }
    }
}
