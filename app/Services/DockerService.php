<?php

namespace App\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;

class DockerService
{
    /**
     * Check if Docker daemon is running
     */
    public function isDockerRunning(): bool
    {
        try {
            $process = new Process(['docker', 'info']);
            $process->run();
            return $process->isSuccessful();
        } catch (\Exception $e) {
            Log::error('Docker status check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a Docker container for a project
     */
    public function createContainer(array $config): array
    {
        $composePath = $config['compose_path'];
        $projectName = $config['project_name'];

        try {
            $process = new Process([
                'docker', 'compose',
                '-f', $composePath,
                '-p', $projectName,
                'up', '-d'
            ]);
            $process->setTimeout(300);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            return [
                'success' => true,
                'output' => $process->getOutput(),
                'container_id' => $this->getContainerId($projectName)
            ];
        } catch (\Exception $e) {
            Log::error('Container creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Stop and remove a Docker container
     */
    public function removeContainer(string $projectName): bool
    {
        try {
            $process = new Process([
                'docker', 'compose',
                '-p', $projectName,
                'down', '-v'
            ]);
            $process->run();

            return $process->isSuccessful();
        } catch (\Exception $e) {
            Log::error('Container removal failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get container ID for a project
     */
    protected function getContainerId(string $projectName): ?string
    {
        try {
            $process = new Process([
                'docker', 'ps',
                '--filter', 'name=' . $projectName,
                '--format', '{{.ID}}'
            ]);
            $process->run();

            return trim($process->getOutput());
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get container stats
     */
    public function getContainerStats(string $containerId): array
    {
        try {
            $process = new Process([
                'docker', 'stats',
                '--no-stream',
                '--format', '{{json .}}',
                $containerId
            ]);
            $process->run();

            $output = $process->getOutput();
            return json_decode($output, true) ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to get container stats: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Execute command in container
     */
    public function execInContainer(string $containerId, array $command): array
    {
        try {
            $fullCommand = array_merge(['docker', 'exec', $containerId], $command);
            $process = new Process($fullCommand);
            $process->run();

            return [
                'success' => $process->isSuccessful(),
                'output' => $process->getOutput(),
                'error' => $process->getErrorOutput()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
