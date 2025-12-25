<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DeploymentService;

class DeployLaravelCommand extends Command
{
    protected $signature = 'kbpanel:deploy-laravel {project_id} {--version=} {--php-version=}';
    protected $description = 'Deploy a Laravel project with specified version';

    public function handle(DeploymentService $deploymentService)
    {
        $projectId = $this->argument('project_id');
        $laravelVersion = $this->option('version') ?? '12';
        $phpVersion = $this->option('php-version') ?? '8.2';

        $this->info("Deploying Laravel {$laravelVersion} for project {$projectId}...");
        
        try {
            $result = $deploymentService->deployLaravel($projectId, $laravelVersion, $phpVersion);
            $this->info('Deployment completed successfully!');
            $this->table(['Key', 'Value'], [
                ['Container ID', $result['container_id'] ?? 'N/A'],
                ['Port', $result['port'] ?? 'N/A'],
                ['Status', $result['status'] ?? 'N/A'],
            ]);
            return 0;
        } catch (\Exception $e) {
            $this->error('Deployment failed: ' . $e->getMessage());
            return 1;
        }
    }
}
