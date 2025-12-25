<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Deployment;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class DeploymentService
{
    protected DockerService $dockerService;
    protected DatabaseService $databaseService;

    public function __construct(
        DockerService $dockerService,
        DatabaseService $databaseService
    ) {
        $this->dockerService = $dockerService;
        $this->databaseService = $databaseService;
    }

    /**
     * Deploy a new Laravel project
     */
    public function deployLaravel(array $data): array
    {
        $project = $this->createProject($data);
        $projectPath = storage_path("projects/{$data['user_id']}/{$project->id}/production");
        
        try {
            // Create project directory
            File::ensureDirectoryExists($projectPath);
            
            // Create database
            $dbResult = $this->databaseService->createProjectDatabase(
                $project->name,
                $data['user_id']
            );
            
            if (!$dbResult['success']) {
                throw new \Exception('Database creation failed');
            }
            
            // Update project with database credentials
            $project->update([
                'db_name' => $dbResult['db_name'],
                'db_user' => $dbResult['db_user'],
                'db_password' => encrypt($dbResult['db_password'])
            ]);
            
            // Create Laravel project
            $this->installLaravel($projectPath, $data['laravel_version'] ?? '12.*');
            
            // Configure environment
            $this->configureEnvironment($projectPath, $project, $dbResult);
            
            // Generate Docker Compose configuration
            $this->generateDockerCompose($projectPath, $project, $data);
            
            // Start Docker container
            $containerResult = $this->dockerService->createProjectContainer([
                'project_path' => $projectPath,
                'project_name' => $project->name
            ]);
            
            if ($containerResult['success']) {
                $project->update([
                    'docker_container_id' => $containerResult['container_id'],
                    'status' => 'active'
                ]);
                
                // Run migrations
                $this->runMigrations($projectPath);
                
                // Create deployment record
                Deployment::create([
                    'project_id' => $project->id,
                    'type' => 'initial',
                    'status' => 'success',
                    'deployed_at' => now()
                ]);
                
                return ['success' => true, 'project' => $project];
            }
            
            throw new \Exception('Container creation failed');
            
        } catch (\Exception $e) {
            Log::error('Laravel deployment failed', [
                'error' => $e->getMessage(),
                'project' => $data['name'] ?? 'unknown'
            ]);
            
            // Cleanup on failure
            if (isset($project)) {
                $this->rollbackDeployment($project);
            }
            
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Install Laravel using Composer
     */
    protected function installLaravel(string $path, string $version): void
    {
        $parentPath = dirname($path);
        $dirName = basename($path);
        
        $result = Process::path($parentPath)
            ->timeout(300)
            ->run("composer create-project laravel/laravel {$dirName} \"{$version}\" --prefer-dist");
        
        if (!$result->successful()) {
            throw new \Exception('Laravel installation failed: ' . $result->errorOutput());
        }
    }

    /**
     * Configure Laravel .env file
     */
    protected function configureEnvironment(string $projectPath, Project $project, array $dbConfig): void
    {
        $envPath = $projectPath . '/.env';
        $envContent = File::get($envPath);
        
        // Update database configuration
        $envContent = preg_replace('/DB_CONNECTION=.*/','DB_CONNECTION=mysql', $envContent);
        $envContent = preg_replace('/DB_HOST=.*/','DB_HOST=' . $dbConfig['db_host'], $envContent);
        $envContent = preg_replace('/DB_PORT=.*/','DB_PORT=' . $dbConfig['db_port'], $envContent);
        $envContent = preg_replace('/DB_DATABASE=.*/','DB_DATABASE=' . $dbConfig['db_name'], $envContent);
        $envContent = preg_replace('/DB_USERNAME=.*/','DB_USERNAME=' . $dbConfig['db_user'], $envContent);
        $envContent = preg_replace('/DB_PASSWORD=.*/','DB_PASSWORD=' . $dbConfig['db_password'], $envContent);
        
        // Update app configuration
        $envContent = preg_replace('/APP_NAME=.*/','APP_NAME="' . $project->name . '"', $envContent);
        $envContent = preg_replace('/APP_URL=.*/','APP_URL=https://' . $project->domain, $envContent);
        
        File::put($envPath, $envContent);
    }

    /**
     * Generate Docker Compose file for project
     */
    protected function generateDockerCompose(string $projectPath, Project $project, array $config): void
    {
        $template = File::get(base_path('docker/templates/laravel.yml'));
        
        $compose = str_replace(
            ['${PHP_VERSION}', '${USER_ID}', '${GROUP_ID}', '${PORT}'],
            [$config['php_version'] ?? '8.2', 33, 33, $project->port],
            $template
        );
        
        File::put($projectPath . '/docker-compose.yml', $compose);
    }

    /**
     * Run Laravel migrations
     */
    protected function runMigrations(string $projectPath): void
    {
        Process::path($projectPath)
            ->run('php artisan migrate --force');
    }

    /**
     * Create project record
     */
    protected function createProject(array $data): Project
    {
        return Project::create([
            'user_id' => $data['user_id'],
            'name' => $data['name'],
            'domain' => $data['domain'],
            'type' => 'laravel',
            'laravel_version' => $data['laravel_version'] ?? '12',
            'php_version' => $data['php_version'] ?? '8.2',
            'web_server' => $data['web_server'] ?? 'nginx',
            'port' => $data['port'] ?? $this->getAvailablePort(),
            'status' => 'deploying'
        ]);
    }

    /**
     * Rollback failed deployment
     */
    protected function rollbackDeployment(Project $project): void
    {
        $projectPath = storage_path("projects/{$project->user_id}/{$project->id}");
        
        // Remove Docker container
        if ($project->docker_container_id) {
            $this->dockerService->removeProjectContainer($projectPath . '/production');
        }
        
        // Remove database
        if ($project->db_name && $project->db_user) {
            $this->databaseService->deleteProjectDatabase(
                $project->db_name,
                $project->db_user
            );
        }
        
        // Remove project files
        File::deleteDirectory($projectPath);
        
        // Update project status
        $project->update(['status' => 'failed']);
    }

    /**
     * Get next available port for project
     */
    protected function getAvailablePort(): int
    {
        $lastProject = Project::orderBy('port', 'desc')->first();
        return $lastProject ? $lastProject->port + 1 : 8000;
    }
}
