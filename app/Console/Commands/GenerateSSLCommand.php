<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SSLService;

class GenerateSSLCommand extends Command
{
    protected $signature = 'kbpanel:ssl-generate {domain} {--staging}';
    protected $description = 'Generate SSL certificate using Let\'s Encrypt';

    public function handle(SSLService $sslService)
    {
        $domain = $this->argument('domain');
        $isStaging = $this->option('staging');

        $this->info("Generating SSL certificate for {$domain}...");
        
        try {
            $result = $sslService->generateCertificate($domain, $isStaging);
            $this->info('SSL certificate generated successfully!');
            $this->info("Certificate path: {$result['cert_path']}");
            $this->info("Expires at: {$result['expires_at']}");
            return 0;
        } catch (\Exception $e) {
            $this->error('SSL generation failed: ' . $e->getMessage());
            return 1;
        }
    }
}
