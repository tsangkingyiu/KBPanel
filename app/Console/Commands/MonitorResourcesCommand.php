<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MonitoringService;

class MonitorResourcesCommand extends Command
{
    protected $signature = 'kbpanel:monitor {type=system} {--user-id=} {--project-id=}';
    protected $description = 'Monitor system, user, or project resources';

    public function handle(MonitoringService $monitoringService)
    {
        $type = $this->argument('type');
        
        try {
            switch ($type) {
                case 'system':
                    $metrics = $monitoringService->collectSystemMetrics();
                    $this->info('=== System Metrics ===');
                    break;
                case 'user':
                    $userId = $this->option('user-id');
                    $metrics = $monitoringService->collectUserMetrics($userId);
                    $this->info("=== User {$userId} Metrics ===");
                    break;
                case 'project':
                    $projectId = $this->option('project-id');
                    $metrics = $monitoringService->collectProjectMetrics($projectId);
                    $this->info("=== Project {$projectId} Metrics ===");
                    break;
                default:
                    $this->error('Invalid type');
                    return 1;
            }
            
            $this->table(['Metric', 'Value'], collect($metrics)->map(fn($v, $k) => [$k, $v])->toArray());
            return 0;
        } catch (\Exception $e) {
            $this->error('Monitoring failed: ' . $e->getMessage());
            return 1;
        }
    }
}
