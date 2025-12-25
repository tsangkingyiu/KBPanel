<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class DockerService
{
    /**
     * Create and start a Docker container for a project
     */
    public function createProjectContainer(array $config): array
    {
        $composePath = $config['project_path'] . '/docker-compose.yml';
        
        try {
            $result = Process::path($config['project_path'])
                ->run('docker compose up -d');
            
            if ($result->successful()) {
                return [
                    'success' => true,
                    'container_id' => $this->getContainerIdByProject($config['project_name']),
                    'message' => 'Container created successfully'
                ];
            }
            
            return ['success' => false, 'message' => $result->errorOutput()];
        } catch (\Exception $e) {
            Log::error('Docker container creation failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Stop and remove a project container
     */
    public function removeProjectContainer(string $projectPath): bool
    {
        try {
            $result = Process::path($projectPath)
                ->run('docker compose down -v');
            
            return $result->successful();
        } catch (\Exception $e) {
            Log::error('Docker container removal failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get container resource usage stats
     */
    public function getContainerStats(string $containerId): array
    {
        try {
            $result = Process::run("docker stats {$containerId} --no-stream --format json");
            
            if ($result->successful()) {
                $stats = json_decode($result->output(), true);
                return [
                    'cpu_percent' => floatval(rtrim($stats['CPUPerc'] ?? '0%', '%')),
                    'memory_usage' => $stats['MemUsage'] ?? 'N/A',
                    'memory_percent' => floatval(rtrim($stats['MemPerc'] ?? '0%', '%')),
                    'network_io' => $stats['NetIO'] ?? 'N/A',
                    'block_io' => $stats['BlockIO'] ?? 'N/A'
                ];
            }
            
            return [];
        } catch (\Exception $e) {
            Log::error('Failed to get container stats', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get container ID by project name
     */
    private function getContainerIdByProject(string $projectName): ?string
    {
        try {
            $result = Process::run("docker ps -qf name={$projectName}");
            return $result->successful() ? trim($result->output()) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Restart a project container
     */
    public function restartContainer(string $projectPath): bool
    {
        try {
            $result = Process::path($projectPath)
                ->run('docker compose restart');
            
            return $result->successful();
        } catch (\Exception $e) {
            Log::error('Container restart failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Check if Docker is available
     */
    public function checkDockerAvailability(): bool
    {
        try {
            $result = Process::run('docker --version');
            return $result->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
