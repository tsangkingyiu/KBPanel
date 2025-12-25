<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ResourceUsage;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MonitoringService
{
    /**
     * Collect system-wide metrics
     */
    public function collectSystemMetrics(): array
    {
        try {
            return [
                'cpu' => $this->getCpuUsage(),
                'memory' => $this->getMemoryUsage(),
                'disk' => $this->getDiskUsage(),
                'docker' => $this->getDockerStats(),
                'timestamp' => now()->toDateTimeString()
            ];
        } catch (\Exception $e) {
            Log::error('Failed to collect system metrics', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Collect project-specific metrics
     */
    public function collectProjectMetrics(Project $project): ?array
    {
        if (!$project->docker_container_id) {
            return null;
        }
        
        try {
            $dockerService = app(DockerService::class);
            $stats = $dockerService->getContainerStats($project->docker_container_id);
            
            if (!empty($stats)) {
                // Store in database
                ResourceUsage::create([
                    'project_id' => $project->id,
                    'user_id' => $project->user_id,
                    'cpu_percent' => $stats['cpu_percent'],
                    'memory_mb' => $this->parseMemoryUsage($stats['memory_usage']),
                    'disk_mb' => $this->getProjectDiskUsage($project),
                    'bandwidth_mb' => $this->parseBandwidth($stats['network_io']),
                    'recorded_at' => now()
                ]);
                
                return $stats;
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Failed to collect project metrics', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get user's total resource consumption
     */
    public function getUserMetrics(int $userId): array
    {
        try {
            $projects = Project::where('user_id', $userId)
                ->where('status', 'active')
                ->get();
            
            $totalCpu = 0;
            $totalMemory = 0;
            $totalDisk = 0;
            
            foreach ($projects as $project) {
                $metrics = $this->collectProjectMetrics($project);
                if ($metrics) {
                    $totalCpu += $metrics['cpu_percent'] ?? 0;
                    $totalMemory += $this->parseMemoryUsage($metrics['memory_usage'] ?? '0');
                }
                $totalDisk += $this->getProjectDiskUsage($project);
            }
            
            return [
                'project_count' => $projects->count(),
                'total_cpu_percent' => round($totalCpu, 2),
                'total_memory_mb' => round($totalMemory, 2),
                'total_disk_mb' => round($totalDisk, 2),
                'timestamp' => now()->toDateTimeString()
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get user metrics', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get CPU usage percentage
     */
    protected function getCpuUsage(): float
    {
        try {
            $result = Process::run("top -bn1 | grep 'Cpu(s)' | awk '{print $2}' | cut -d'%' -f1");
            return $result->successful() ? (float) trim($result->output()) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get memory usage
     */
    protected function getMemoryUsage(): array
    {
        try {
            $result = Process::run('free -m');
            if ($result->successful()) {
                $lines = explode("\n", $result->output());
                if (isset($lines[1])) {
                    $parts = preg_split('/\s+/', $lines[1]);
                    $total = (int) ($parts[1] ?? 0);
                    $used = (int) ($parts[2] ?? 0);
                    $free = (int) ($parts[3] ?? 0);
                    
                    return [
                        'total_mb' => $total,
                        'used_mb' => $used,
                        'free_mb' => $free,
                        'percent' => $total > 0 ? round(($used / $total) * 100, 2) : 0
                    ];
                }
            }
            return ['total_mb' => 0, 'used_mb' => 0, 'free_mb' => 0, 'percent' => 0];
        } catch (\Exception $e) {
            return ['total_mb' => 0, 'used_mb' => 0, 'free_mb' => 0, 'percent' => 0];
        }
    }

    /**
     * Get disk usage
     */
    protected function getDiskUsage(): array
    {
        try {
            $result = Process::run("df -h / | tail -1 | awk '{print $2,$3,$4,$5}'");
            if ($result->successful()) {
                $parts = explode(' ', trim($result->output()));
                return [
                    'total' => $parts[0] ?? 'N/A',
                    'used' => $parts[1] ?? 'N/A',
                    'free' => $parts[2] ?? 'N/A',
                    'percent' => rtrim($parts[3] ?? '0%', '%')
                ];
            }
            return ['total' => 'N/A', 'used' => 'N/A', 'free' => 'N/A', 'percent' => 0];
        } catch (\Exception $e) {
            return ['total' => 'N/A', 'used' => 'N/A', 'free' => 'N/A', 'percent' => 0];
        }
    }

    /**
     * Get Docker statistics
     */
    protected function getDockerStats(): array
    {
        try {
            $result = Process::run('docker ps --format "{{.Names}}"');
            $runningContainers = $result->successful() ? 
                count(array_filter(explode("\n", trim($result->output())))) : 0;
            
            return [
                'running_containers' => $runningContainers,
                'total_projects' => Project::where('status', 'active')->count()
            ];
        } catch (\Exception $e) {
            return ['running_containers' => 0, 'total_projects' => 0];
        }
    }

    /**
     * Get project disk usage in MB
     */
    protected function getProjectDiskUsage(Project $project): float
    {
        try {
            $projectPath = storage_path("projects/{$project->user_id}/{$project->id}");
            $result = Process::run("du -sm {$projectPath} | cut -f1");
            return $result->successful() ? (float) trim($result->output()) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Parse memory usage string (e.g., "256.7MiB / 2GiB") to MB
     */
    protected function parseMemoryUsage(string $memoryString): float
    {
        if (preg_match('/(\d+\.?\d*)([A-Za-z]+)/', $memoryString, $matches)) {
            $value = (float) $matches[1];
            $unit = strtoupper($matches[2]);
            
            switch ($unit) {
                case 'KIB':
                case 'KB':
                    return $value / 1024;
                case 'MIB':
                case 'MB':
                    return $value;
                case 'GIB':
                case 'GB':
                    return $value * 1024;
                default:
                    return $value;
            }
        }
        return 0;
    }

    /**
     * Parse bandwidth (e.g., "1.2kB / 0B") to MB
     */
    protected function parseBandwidth(string $bandwidthString): float
    {
        $parts = explode('/', $bandwidthString);
        $inbound = trim($parts[0] ?? '0');
        
        if (preg_match('/(\d+\.?\d*)([A-Za-z]+)/', $inbound, $matches)) {
            $value = (float) $matches[1];
            $unit = strtoupper($matches[2]);
            
            switch ($unit) {
                case 'KB':
                    return $value / 1024;
                case 'MB':
                    return $value;
                case 'GB':
                    return $value * 1024;
                default:
                    return $value;
            }
        }
        return 0;
    }

    /**
     * Get historical metrics for project
     */
    public function getProjectHistory(Project $project, int $hours = 24): array
    {
        return ResourceUsage::where('project_id', $project->id)
            ->where('recorded_at', '>=', now()->subHours($hours))
            ->orderBy('recorded_at', 'asc')
            ->get()
            ->map(function ($record) {
                return [
                    'timestamp' => $record->recorded_at->format('Y-m-d H:i:s'),
                    'cpu_percent' => $record->cpu_percent,
                    'memory_mb' => $record->memory_mb,
                    'disk_mb' => $record->disk_mb,
                    'bandwidth_mb' => $record->bandwidth_mb
                ];
            })
            ->toArray();
    }
}
