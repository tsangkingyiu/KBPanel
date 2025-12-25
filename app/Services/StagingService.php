<?php

namespace App\Services;

use App\Models\Project;
use App\Models\StagingEnvironment;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class StagingService
{
    protected DatabaseService $databaseService;
    protected DockerService $dockerService;

    public function __construct(
        DatabaseService $databaseService,
        DockerService $dockerService
    ) {
        $this->databaseService = $databaseService;
        $this->dockerService = $dockerService;
    }

    /**
     * Create staging environment from production
     */
    public function createStaging(Project $project): array
    {
        try {
            $productionPath = storage_path("projects/{$project->user_id}/{$project->id}/production");
            $stagingPath = storage_path("projects/{$project->user_id}/{$project->id}/staging");
            
            // Create staging directory
            File::ensureDirectoryExists($stagingPath);
            
            // Copy production files to staging
            File::copyDirectory($productionPath, $stagingPath);
            
            // Create staging database
            $stagingDbName = $project->db_name . '_staging';
            $dbResult = $this->databaseService->createProjectDatabase(
                $stagingDbName,
                $project->user_id
            );
            
            if (!$dbResult['success']) {
                throw new \Exception('Staging database creation failed');
            }
            
            // Export production database and import to staging
            $exportPath = storage_path('backups/temp_staging_' . time() . '.sql');
            $this->databaseService->exportDatabase($project->db_name, $exportPath);
            $this->databaseService->importDatabase($dbResult['db_name'], $exportPath);
            File::delete($exportPath);
            
            // Update staging .env file
            $this->updateStagingEnv($stagingPath, $dbResult, $project);
            
            // Create staging subdomain configuration
            $subdomain = 'staging.' . $project->domain;
            $port = $this->getAvailablePort();
            
            // Update docker-compose for staging
            $this->updateStagingDockerCompose($stagingPath, $port);
            
            // Start staging container
            $containerResult = $this->dockerService->createProjectContainer([
                'project_path' => $stagingPath,
                'project_name' => $project->name . '_staging'
            ]);
            
            if ($containerResult['success']) {
                $staging = StagingEnvironment::create([
                    'project_id' => $project->id,
                    'subdomain' => $subdomain,
                    'docker_container_id' => $containerResult['container_id'],
                    'port' => $port,
                    'status' => 'active',
                    'db_name' => $dbResult['db_name'],
                    'db_user' => $dbResult['db_user'],
                    'db_password' => encrypt($dbResult['db_password'])
                ]);
                
                $project->update(['has_staging' => true]);
                
                Log::info('Staging environment created', [
                    'project_id' => $project->id,
                    'subdomain' => $subdomain
                ]);
                
                return ['success' => true, 'staging' => $staging];
            }
            
            throw new \Exception('Staging container creation failed');
            
        } catch (\Exception $e) {
            Log::error('Staging creation failed', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Sync production to staging
     */
    public function syncFromProduction(StagingEnvironment $staging): bool
    {
        try {
            $project = $staging->project;
            $productionPath = storage_path("projects/{$project->user_id}/{$project->id}/production");
            $stagingPath = storage_path("projects/{$project->user_id}/{$project->id}/staging");
            
            // Stop staging container
            $this->dockerService->removeProjectContainer($stagingPath);
            
            // Clear staging directory (except .env)
            $envBackup = File::get($stagingPath . '/.env');
            File::deleteDirectory($stagingPath);
            File::ensureDirectoryExists($stagingPath);
            
            // Copy production files
            File::copyDirectory($productionPath, $stagingPath);
            File::put($stagingPath . '/.env', $envBackup);
            
            // Sync database
            $exportPath = storage_path('backups/temp_sync_' . time() . '.sql');
            $this->databaseService->exportDatabase($project->db_name, $exportPath);
            $this->databaseService->importDatabase($staging->db_name, $exportPath);
            File::delete($exportPath);
            
            // Restart staging container
            $this->dockerService->createProjectContainer([
                'project_path' => $stagingPath,
                'project_name' => $project->name . '_staging'
            ]);
            
            $staging->update(['synced_at' => now()]);
            
            Log::info('Staging synced from production', ['project_id' => $project->id]);
            return true;
            
        } catch (\Exception $e) {
            Log::error('Staging sync failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Promote staging to production
     */
    public function promoteToProduction(StagingEnvironment $staging): bool
    {
        try {
            $project = $staging->project;
            $productionPath = storage_path("projects/{$project->user_id}/{$project->id}/production");
            $stagingPath = storage_path("projects/{$project->user_id}/{$project->id}/staging");
            
            // Create production backup first
            app(BackupService::class)->createBackup($project, 'full');
            
            // Stop production container
            $this->dockerService->removeProjectContainer($productionPath);
            
            // Backup production .env
            $envBackup = File::get($productionPath . '/.env');
            
            // Replace production with staging files
            File::deleteDirectory($productionPath);
            File::copyDirectory($stagingPath, $productionPath);
            File::put($productionPath . '/.env', $envBackup);
            
            // Sync staging database to production
            $exportPath = storage_path('backups/temp_promote_' . time() . '.sql');
            $this->databaseService->exportDatabase($staging->db_name, $exportPath);
            $this->databaseService->importDatabase($project->db_name, $exportPath);
            File::delete($exportPath);
            
            // Restart production container
            $this->dockerService->createProjectContainer([
                'project_path' => $productionPath,
                'project_name' => $project->name
            ]);
            
            Log::info('Staging promoted to production', ['project_id' => $project->id]);
            return true;
            
        } catch (\Exception $e) {
            Log::error('Staging promotion failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Delete staging environment
     */
    public function deleteStaging(StagingEnvironment $staging): bool
    {
        try {
            $project = $staging->project;
            $stagingPath = storage_path("projects/{$project->user_id}/{$project->id}/staging");
            
            // Remove container
            $this->dockerService->removeProjectContainer($stagingPath);
            
            // Delete database
            $this->databaseService->deleteProjectDatabase(
                $staging->db_name,
                $staging->db_user
            );
            
            // Delete files
            File::deleteDirectory($stagingPath);
            
            // Update records
            $staging->delete();
            $project->update(['has_staging' => false]);
            
            Log::info('Staging environment deleted', ['project_id' => $project->id]);
            return true;
            
        } catch (\Exception $e) {
            Log::error('Staging deletion failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Update staging environment file
     */
    protected function updateStagingEnv(string $stagingPath, array $dbConfig, Project $project): void
    {
        $envPath = $stagingPath . '/.env';
        $envContent = File::get($envPath);
        
        $envContent = preg_replace('/APP_ENV=.*/','APP_ENV=staging', $envContent);
        $envContent = preg_replace('/APP_DEBUG=.*/','APP_DEBUG=true', $envContent);
        $envContent = preg_replace('/DB_DATABASE=.*/','DB_DATABASE=' . $dbConfig['db_name'], $envContent);
        $envContent = preg_replace('/DB_USERNAME=.*/','DB_USERNAME=' . $dbConfig['db_user'], $envContent);
        $envContent = preg_replace('/DB_PASSWORD=.*/','DB_PASSWORD=' . $dbConfig['db_password'], $envContent);
        $envContent = preg_replace('/APP_URL=.*/','APP_URL=https://staging.' . $project->domain, $envContent);
        
        File::put($envPath, $envContent);
    }

    /**
     * Update Docker Compose for staging
     */
    protected function updateStagingDockerCompose(string $stagingPath, int $port): void
    {
        $composePath = $stagingPath . '/docker-compose.yml';
        $content = File::get($composePath);
        
        // Update port mapping
        $content = preg_replace('/(- ")\d+(:\d+")/', "$1{$port}$2", $content);
        
        File::put($composePath, $content);
    }

    /**
     * Get available port for staging
     */
    protected function getAvailablePort(): int
    {
        $lastStaging = StagingEnvironment::orderBy('port', 'desc')->first();
        $lastProject = Project::orderBy('port', 'desc')->first();
        
        $maxPort = max(
            $lastStaging ? $lastStaging->port : 8000,
            $lastProject ? $lastProject->port : 8000
        );
        
        return $maxPort + 1;
    }
}
