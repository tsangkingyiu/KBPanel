<?php

namespace App\Services;

use App\Models\Project;
use App\Models\GitRepository;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class GitService
{
    /**
     * Clone repository to project
     */
    public function cloneRepository(Project $project, string $repoUrl, string $branch = 'main', ?string $accessToken = null): array
    {
        try {
            $projectPath = storage_path("projects/{$project->user_id}/{$project->id}/production");
            File::ensureDirectoryExists($projectPath);
            
            // Prepare repository URL with token if provided
            $authUrl = $this->prepareAuthUrl($repoUrl, $accessToken);
            
            // Clone repository
            $result = Process::path(dirname($projectPath))
                ->timeout(300)
                ->run("git clone -b {$branch} {$authUrl} " . basename($projectPath));
            
            if (!$result->successful()) {
                throw new \Exception('Git clone failed: ' . $result->errorOutput());
            }
            
            // Get latest commit hash
            $commitHash = $this->getLatestCommitHash($projectPath);
            
            // Save repository information
            $gitRepo = GitRepository::create([
                'project_id' => $project->id,
                'repository_url' => $repoUrl,
                'branch' => $branch,
                'access_token' => $accessToken ? encrypt($accessToken) : null,
                'last_commit_hash' => $commitHash,
                'last_pulled_at' => now()
            ]);
            
            $project->update(['git_repository_id' => $gitRepo->id]);
            
            Log::info('Repository cloned', [
                'project_id' => $project->id,
                'repository' => $repoUrl,
                'branch' => $branch
            ]);
            
            return ['success' => true, 'commit_hash' => $commitHash];
            
        } catch (\Exception $e) {
            Log::error('Repository clone failed', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Pull latest changes from repository
     */
    public function pullLatestChanges(Project $project): array
    {
        try {
            if (!$project->gitRepository) {
                throw new \Exception('No Git repository configured for this project');
            }
            
            $projectPath = storage_path("projects/{$project->user_id}/{$project->id}/production");
            $gitRepo = $project->gitRepository;
            
            // Pull latest changes
            $result = Process::path($projectPath)
                ->run('git pull origin ' . $gitRepo->branch);
            
            if (!$result->successful()) {
                throw new \Exception('Git pull failed: ' . $result->errorOutput());
            }
            
            // Get new commit hash
            $newCommitHash = $this->getLatestCommitHash($projectPath);
            
            // Update repository record
            $gitRepo->update([
                'last_commit_hash' => $newCommitHash,
                'last_pulled_at' => now()
            ]);
            
            Log::info('Repository pulled', [
                'project_id' => $project->id,
                'commit_hash' => $newCommitHash
            ]);
            
            return [
                'success' => true,
                'commit_hash' => $newCommitHash,
                'output' => $result->output()
            ];
            
        } catch (\Exception $e) {
            Log::error('Repository pull failed', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Switch branch
     */
    public function switchBranch(Project $project, string $branch): bool
    {
        try {
            $projectPath = storage_path("projects/{$project->user_id}/{$project->id}/production");
            
            $result = Process::path($projectPath)
                ->run("git checkout {$branch}");
            
            if ($result->successful()) {
                $project->gitRepository->update(['branch' => $branch]);
                Log::info('Branch switched', ['project_id' => $project->id, 'branch' => $branch]);
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error('Branch switch failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * List available branches
     */
    public function listBranches(Project $project): array
    {
        try {
            $projectPath = storage_path("projects/{$project->user_id}/{$project->id}/production");
            
            $result = Process::path($projectPath)
                ->run('git branch -a');
            
            if ($result->successful()) {
                $branches = array_filter(
                    array_map('trim', explode("\n", $result->output()))
                );
                return $branches;
            }
            
            return [];
        } catch (\Exception $e) {
            Log::error('Failed to list branches', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get commit history
     */
    public function getCommitHistory(Project $project, int $limit = 10): array
    {
        try {
            $projectPath = storage_path("projects/{$project->user_id}/{$project->id}/production");
            
            $result = Process::path($projectPath)
                ->run("git log --pretty=format:'%H|%an|%ae|%ad|%s' --date=iso -n {$limit}");
            
            if ($result->successful()) {
                $commits = [];
                $lines = explode("\n", trim($result->output()));
                
                foreach ($lines as $line) {
                    $parts = explode('|', $line);
                    if (count($parts) === 5) {
                        $commits[] = [
                            'hash' => $parts[0],
                            'author_name' => $parts[1],
                            'author_email' => $parts[2],
                            'date' => $parts[3],
                            'message' => $parts[4]
                        ];
                    }
                }
                
                return $commits;
            }
            
            return [];
        } catch (\Exception $e) {
            Log::error('Failed to get commit history', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Deploy from Git (pull + install dependencies + restart)
     */
    public function deployFromGit(Project $project): array
    {
        try {
            $projectPath = storage_path("projects/{$project->user_id}/{$project->id}/production");
            
            // Pull latest changes
            $pullResult = $this->pullLatestChanges($project);
            if (!$pullResult['success']) {
                throw new \Exception($pullResult['message']);
            }
            
            // Install Composer dependencies
            Process::path($projectPath)
                ->timeout(300)
                ->run('composer install --no-dev --optimize-autoloader');
            
            // Install NPM dependencies and build
            Process::path($projectPath)
                ->timeout(300)
                ->run('npm install && npm run build');
            
            // Run migrations
            Process::path($projectPath)
                ->run('php artisan migrate --force');
            
            // Clear and optimize caches
            Process::path($projectPath)
                ->run('php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear');
            
            Process::path($projectPath)
                ->run('php artisan config:cache && php artisan route:cache && php artisan view:cache');
            
            // Restart container
            app(DockerService::class)->restartContainer($projectPath);
            
            Log::info('Deployed from Git', ['project_id' => $project->id]);
            
            return ['success' => true, 'commit_hash' => $pullResult['commit_hash']];
            
        } catch (\Exception $e) {
            Log::error('Git deployment failed', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
            
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get latest commit hash
     */
    protected function getLatestCommitHash(string $projectPath): ?string
    {
        try {
            $result = Process::path($projectPath)
                ->run('git rev-parse HEAD');
            
            return $result->successful() ? trim($result->output()) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Prepare authenticated repository URL
     */
    protected function prepareAuthUrl(string $repoUrl, ?string $accessToken): string
    {
        if (!$accessToken) {
            return $repoUrl;
        }
        
        // For GitHub/GitLab HTTPS URLs, inject token
        if (preg_match('/^https:\/\/([^\/]+)\/(.+)$/', $repoUrl, $matches)) {
            return "https://{$accessToken}@{$matches[1]}/{$matches[2]}";
        }
        
        return $repoUrl;
    }
}
