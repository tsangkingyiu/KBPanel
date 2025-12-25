<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Deployment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DeploymentService
{
    protected DockerService $dockerService;
    protected GitService $gitService;

    public function __construct(DockerService $dockerService, GitService $gitService)
    {
        $this->dockerService = $dockerService;
        $this->gitService = $gitService;
    }

    /**
     * Deploy a Laravel project
     */
    public function deployLaravel(Project $project, array $config): Deployment
    {
        $deployment = Deployment::create([
            'project_id' => $project->id,
            'type' => 'laravel',
            'status' => 'pending',
            'config' => $config
        ]);

        try {
            // Update deployment status
            $deployment->update(['status' => 'deploying']);

            // Create project directory
            $projectPath = $this->createProjectDirectory($project);

            // Clone repository if Git URL provided
            if (!empty($config['git_url'])) {
                $this->gitService->cloneRepository(
                    $config['git_url'],
                    $projectPath,
                    $config['branch'] ?? 'main'
                );
            }

            // Generate Docker Compose file
            $composePath = $this->generateDockerCompose($project, $config);

            // Start Docker containers
            $result = $this->dockerService->createContainer([
                'compose_path' => $composePath,
                'project_name' => 'kbpanel_' . $project->id
            ]);

            if (!$result['success']) {
                throw new \Exception('Failed to create Docker container');
            }

            // Run Laravel setup commands
            $this->setupLaravel($result['container_id'], $projectPath);

            $deployment->update([
                'status' => 'completed',
                'deployed_at' => now()
            ]);

            return $deployment;
        } catch (\Exception $e) {
            Log::error('Deployment failed: ' . $e->getMessage());
            $deployment->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create project directory
     */
    protected function createProjectDirectory(Project $project): string
    {
        $basePath = config('kbpanel.storage_path', '/var/www/kbpanel/storage');
        $projectPath = $basePath . '/projects/' . $project->user_id . '/' . $project->id . '/production';

        if (!file_exists($projectPath)) {
            mkdir($projectPath, 0755, true);
        }

        return $projectPath;
    }

    /**
     * Generate Docker Compose configuration
     */
    protected function generateDockerCompose(Project $project, array $config): string
    {
        $templatePath = base_path('docker/templates/laravel.yml');
        $template = file_get_contents($templatePath);

        // Replace placeholders
        $replacements = [
            '${PHP_VERSION}' => $config['php_version'] ?? '8.2',
            '${PORT}' => $project->port,
            '${USER_ID}' => $project->user_id,
            '${GROUP_ID}' => $project->user_id,
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $template);

        $composePath = $this->createProjectDirectory($project) . '/docker-compose.yml';
        file_put_contents($composePath, $content);

        return $composePath;
    }

    /**
     * Setup Laravel application
     */
    protected function setupLaravel(string $containerId, string $projectPath): void
    {
        $commands = [
            ['composer', 'install', '--no-dev', '--optimize-autoloader'],
            ['php', 'artisan', 'key:generate', '--force'],
            ['php', 'artisan', 'migrate', '--force'],
            ['php', 'artisan', 'storage:link'],
            ['php', 'artisan', 'config:cache'],
            ['php', 'artisan', 'route:cache'],
            ['php', 'artisan', 'view:cache'],
        ];

        foreach ($commands as $command) {
            $result = $this->dockerService->execInContainer($containerId, $command);
            if (!$result['success']) {
                Log::warning('Command failed: ' . implode(' ', $command));
            }
        }
    }
}
