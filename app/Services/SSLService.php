<?php

namespace App\Services;

use App\Models\Project;
use App\Models\SSLCertificate;

class SSLService
{
    public function generateSelfSignedCertificate(Project $project)
    {
        // TODO: Generate self-signed SSL certificate
        $certPath = "/etc/ssl/kbpanel/{$project->domain}.crt";
        $keyPath = "/etc/ssl/kbpanel/{$project->domain}.key";

        return SSLCertificate::create([
            'project_id' => $project->id,
            'certificate_path' => $certPath,
            'private_key_path' => $keyPath,
            'issuer' => 'self-signed',
            'expires_at' => now()->addYear(),
            'status' => 'active',
        ]);
    }

    public function generateLetsEncryptCertificate(Project $project)
    {
        // TODO: Use Certbot to generate Let's Encrypt certificate
        // certbot certonly --webroot -w /var/www/project -d domain.com
        return null;
    }

    public function renewCertificate(SSLCertificate $certificate)
    {
        // TODO: Renew SSL certificate
        return true;
    }

    public function checkExpiringCertificates($days = 30)
    {
        return SSLCertificate::where('expires_at', '<=', now()->addDays($days))
            ->where('auto_renew', true)
            ->get();
    }
}
