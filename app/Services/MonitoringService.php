<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ResourceUsage;
use Illuminate\Support\Facades\Log;

class MonitoringService
{
    protected DockerService $dockerService;

    public function __construct(DockerService $dockerService)
    {
        $this->dockerService = $dockerService;
    }

    /**
     * Collect system-wide metrics
     */
    public function collectSystemMetrics(): array
    {
        return [
            'cpu' => $this->getCpuUsage(),
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage(),
            'containers' => $this->getActiveContainersCount()
        ];
    }

    /**
     * Collect metrics for a specific project
     */
    public function collectProjectMetrics(Project $project): array
    {
        if (!$project->docker_container_id) {
            return [];
        }

        $stats = $this->dockerService->getContainerStats($project->docker_container_id);

        // Store in database
        ResourceUsage::create([
            'project_id' => $project->id,
            'user_id' => $project->user_id,
            'cpu_percent' => $stats['CPUPerc'] ?? 0,
            'memory_mb' => $this->parseMemoryValue($stats['MemUsage'] ?? '0'),
            'recorded_at' => now()
        ]);

        return $stats;
    }

    /**
     * Get CPU usage percentage
     */
    protected function getCpuUsage(): float
    {
        $load = sys_getloadavg();
        $cpuCount = $this->getCpuCount();
        return round(($load[0] / $cpuCount) * 100, 2);
    }

    /**
     * Get memory usage
     */
    protected function getMemoryUsage(): array
    {
        $free = shell_exec('free -m');
        $lines = explode("\n", trim($free));
        $mem = preg_split("/[\s]+/", $lines[1]);

        return [
            'total' => (int)$mem[1],
            'used' => (int)$mem[2],
            'free' => (int)$mem[3],
            'percent' => round(((int)$mem[2] / (int)$mem[1]) * 100, 2)
        ];
    }

    /**
     * Get disk usage
     */
    protected function getDiskUsage(): array
    {
        $df = shell_exec('df -h /');
        $lines = explode("\n", trim($df));
        $disk = preg_split("/[\s]+/", $lines[1]);

        return [
            'total' => $disk[1],
            'used' => $disk[2],
            'free' => $disk[3],
            'percent' => (int)rtrim($disk[4], '%')
        ];
    }

    /**
     * Get number of CPU cores
     */
    protected function getCpuCount(): int
    {
        return (int)shell_exec('nproc');
    }

    /**
     * Get active containers count
     */
    protected function getActiveContainersCount(): int
    {
        $output = shell_exec('docker ps -q | wc -l');
        return (int)trim($output);
    }

    /**
     * Parse memory value from Docker stats
     */
    protected function parseMemoryValue(string $memUsage): float
    {
        // Parse format like "123.4MiB / 1.5GiB"
        preg_match('/([\.\d]+)(\w+)/', $memUsage, $matches);
        
        if (count($matches) < 3) {
            return 0;
        }

        $value = (float)$matches[1];
        $unit = $matches[2];

        // Convert to MB
        switch ($unit) {
            case 'GiB':
                return $value * 1024;
            case 'MiB':
                return $value;
            case 'KiB':
                return $value / 1024;
            default:
                return $value;
        }
    }

    /**
     * Get system stats for dashboard
     */
    public static function getSystemStats(): array
    {
        $service = new self(app(DockerService::class));
        return $service->collectSystemMetrics();
    }
}
