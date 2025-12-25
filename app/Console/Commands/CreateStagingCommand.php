<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\StagingService;

class CreateStagingCommand extends Command
{
    protected $signature = 'kbpanel:staging-create {project_id}';
    protected $description = 'Create staging environment for a project';

    public function handle(StagingService $stagingService)
    {
        $projectId = $this->argument('project_id');

        $this->info("Creating staging environment for project {$projectId}...");
        
        try {
            $result = $stagingService->createStagingEnvironment($projectId);
            $this->info('Staging environment created successfully!');
            $this->table(['Key', 'Value'], [
                ['Staging URL', $result['url'] ?? 'N/A'],
                ['Container ID', $result['container_id'] ?? 'N/A'],
                ['Database', $result['database'] ?? 'N/A'],
            ]);
            return 0;
        } catch (\Exception $e) {
            $this->error('Staging creation failed: ' . $e->getMessage());
            return 1;
        }
    }
}
