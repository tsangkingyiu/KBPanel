<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DockerService;

class DockerManagementCommand extends Command
{
    protected $signature = 'kbpanel:docker {action} {container_id?}';
    protected $description = 'Manage Docker containers (start|stop|restart|status|cleanup)';

    public function handle(DockerService $dockerService)
    {
        $action = $this->argument('action');
        $containerId = $this->argument('container_id');

        try {
            switch ($action) {
                case 'status':
                    $status = $dockerService->getContainerStatus($containerId);
                    $this->info(json_encode($status, JSON_PRETTY_PRINT));
                    break;
                case 'start':
                    $dockerService->startContainer($containerId);
                    $this->info("Container {$containerId} started");
                    break;
                case 'stop':
                    $dockerService->stopContainer($containerId);
                    $this->info("Container {$containerId} stopped");
                    break;
                case 'restart':
                    $dockerService->restartContainer($containerId);
                    $this->info("Container {$containerId} restarted");
                    break;
                case 'cleanup':
                    $dockerService->cleanupUnusedContainers();
                    $this->info('Cleanup completed');
                    break;
                default:
                    $this->error('Invalid action');
                    return 1;
            }
            return 0;
        } catch (\Exception $e) {
            $this->error('Docker operation failed: ' . $e->getMessage());
            return 1;
        }
    }
}
