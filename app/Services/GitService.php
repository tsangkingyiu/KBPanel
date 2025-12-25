<?php

namespace App\Services;

use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;

class GitService
{
    /**
     * Clone a Git repository
     */
    public function cloneRepository(string $url, string $destination, string $branch = 'main'): bool
    {
        try {
            $process = new Process([
                'git', 'clone',
                '--branch', $branch,
                '--single-branch',
                $url,
                $destination
            ]);
            $process->setTimeout(300);
            $process->run();

            return $process->isSuccessful();
        } catch (\Exception $e) {
            Log::error('Git clone failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Pull latest changes from repository
     */
    public function pullLatestChanges(string $repoPath): array
    {
        try {
            $process = new Process(['git', 'pull'], $repoPath);
            $process->run();

            return [
                'success' => $process->isSuccessful(),
                'output' => $process->getOutput()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get commit history
     */
    public function getCommitHistory(string $repoPath, int $limit = 10): array
    {
        try {
            $process = new Process([
                'git', 'log',
                '--pretty=format:%H|%an|%ae|%at|%s',
                '-' . $limit
            ], $repoPath);
            $process->run();

            if (!$process->isSuccessful()) {
                return [];
            }

            $commits = [];
            $lines = explode("\n", trim($process->getOutput()));

            foreach ($lines as $line) {
                $parts = explode('|', $line);
                if (count($parts) === 5) {
                    $commits[] = [
                        'hash' => $parts[0],
                        'author' => $parts[1],
                        'email' => $parts[2],
                        'timestamp' => (int)$parts[3],
                        'message' => $parts[4]
                    ];
                }
            }

            return $commits;
        } catch (\Exception $e) {
            Log::error('Failed to get commit history: ' . $e->getMessage());
            return [];
        }
    }
}
