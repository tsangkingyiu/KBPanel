<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Backup;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class BackupService
{
    protected DatabaseService $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    /**
     * Create project backup
     */
    public function createBackup(Project $project, string $type = 'full'): array
    {
        try {
            $timestamp = now()->format('Y-m-d_His');
            $backupDir = storage_path("backups/{$project->user_id}/{$project->id}");
            File::ensureDirectoryExists($backupDir);
            
            $filename = "{$project->name}_{$type}_{$timestamp}";
            $backupPath = null;
            $size = 0;
            
            switch ($type) {
                case 'full':
                    $backupPath = $this->createFullBackup($project, $backupDir, $filename);
                    break;
                case 'database':
                    $backupPath = $this->createDatabaseBackup($project, $backupDir, $filename);
                    break;
                case 'files':
                    $backupPath = $this->createFilesBackup($project, $backupDir, $filename);
                    break;
                default:
                    throw new \Exception('Invalid backup type');
            }
            
            if ($backupPath && File::exists($backupPath)) {
                $size = File::size($backupPath) / 1024 / 1024; // Convert to MB
                
                $backup = Backup::create([
                    'project_id' => $project->id,
                    'user_id' => $project->user_id,
                    'backup_type' => $type,
                    'file_path' => $backupPath,
                    'size_mb' => round($size, 2),
                    'status' => 'completed'
                ]);
                
                Log::info('Backup created', [
                    'project_id' => $project->id,
                    'type' => $type,
                    'size_mb' => $size
                ]);
                
                return ['success' => true, 'backup' => $backup];
            }
            
            throw new \Exception('Backup file creation failed');
            
        } catch (\Exception $e) {
            Log::error('Backup creation failed', [
                'project_id' => $project->id,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Create full backup (files + database)
     */
    protected function createFullBackup(Project $project, string $backupDir, string $filename): string
    {
        $backupPath = $backupDir . '/' . $filename . '.zip';
        $projectPath = storage_path("projects/{$project->user_id}/{$project->id}/production");
        $dbBackupPath = $backupDir . '/temp_db_' . time() . '.sql';
        
        // Export database
        $this->databaseService->exportDatabase($project->db_name, $dbBackupPath);
        
        // Create ZIP archive
        $zip = new ZipArchive();
        if ($zip->open($backupPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            // Add project files
            $files = File::allFiles($projectPath);
            foreach ($files as $file) {
                $relativePath = str_replace($projectPath . '/', '', $file->getPathname());
                $zip->addFile($file->getPathname(), 'files/' . $relativePath);
            }
            
            // Add database dump
            $zip->addFile($dbBackupPath, 'database.sql');
            
            // Add metadata
            $metadata = [
                'project_name' => $project->name,
                'backup_date' => now()->toDateTimeString(),
                'laravel_version' => $project->laravel_version,
                'php_version' => $project->php_version,
                'database_name' => $project->db_name
            ];
            $zip->addFromString('metadata.json', json_encode($metadata, JSON_PRETTY_PRINT));
            
            $zip->close();
            
            // Clean up temp database file
            File::delete($dbBackupPath);
            
            return $backupPath;
        }
        
        throw new \Exception('Failed to create ZIP archive');
    }

    /**
     * Create database-only backup
     */
    protected function createDatabaseBackup(Project $project, string $backupDir, string $filename): string
    {
        $backupPath = $backupDir . '/' . $filename . '.sql';
        $this->databaseService->exportDatabase($project->db_name, $backupPath);
        return $backupPath;
    }

    /**
     * Create files-only backup
     */
    protected function createFilesBackup(Project $project, string $backupDir, string $filename): string
    {
        $backupPath = $backupDir . '/' . $filename . '.zip';
        $projectPath = storage_path("projects/{$project->user_id}/{$project->id}/production");
        
        $zip = new ZipArchive();
        if ($zip->open($backupPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            $files = File::allFiles($projectPath);
            foreach ($files as $file) {
                $relativePath = str_replace($projectPath . '/', '', $file->getPathname());
                $zip->addFile($file->getPathname(), $relativePath);
            }
            $zip->close();
            return $backupPath;
        }
        
        throw new \Exception('Failed to create ZIP archive');
    }

    /**
     * Restore backup
     */
    public function restoreBackup(Backup $backup): bool
    {
        try {
            $project = $backup->project;
            $projectPath = storage_path("projects/{$project->user_id}/{$project->id}/production");
            
            // Create safety backup before restore
            $this->createBackup($project, 'full');
            
            if ($backup->backup_type === 'full') {
                // Extract ZIP
                $zip = new ZipArchive();
                if ($zip->open($backup->file_path)) {
                    $tempDir = storage_path('backups/temp_restore_' . time());
                    $zip->extractTo($tempDir);
                    $zip->close();
                    
                    // Restore files
                    if (File::exists($tempDir . '/files')) {
                        File::deleteDirectory($projectPath);
                        File::copyDirectory($tempDir . '/files', $projectPath);
                    }
                    
                    // Restore database
                    if (File::exists($tempDir . '/database.sql')) {
                        $this->databaseService->importDatabase(
                            $project->db_name,
                            $tempDir . '/database.sql'
                        );
                    }
                    
                    // Clean up temp directory
                    File::deleteDirectory($tempDir);
                }
            } elseif ($backup->backup_type === 'database') {
                $this->databaseService->importDatabase($project->db_name, $backup->file_path);
            } elseif ($backup->backup_type === 'files') {
                $zip = new ZipArchive();
                if ($zip->open($backup->file_path)) {
                    File::deleteDirectory($projectPath);
                    $zip->extractTo($projectPath);
                    $zip->close();
                }
            }
            
            // Restart container
            app(DockerService::class)->restartContainer($projectPath);
            
            Log::info('Backup restored', [
                'backup_id' => $backup->id,
                'project_id' => $project->id
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Backup restore failed', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Delete backup
     */
    public function deleteBackup(Backup $backup): bool
    {
        try {
            if (File::exists($backup->file_path)) {
                File::delete($backup->file_path);
            }
            $backup->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('Backup deletion failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Clean up old backups based on retention policy
     */
    public function cleanupOldBackups(Project $project, int $daysToKeep = 30): int
    {
        $cutoffDate = now()->subDays($daysToKeep);
        $oldBackups = Backup::where('project_id', $project->id)
            ->where('created_at', '<', $cutoffDate)
            ->get();
        
        $deletedCount = 0;
        foreach ($oldBackups as $backup) {
            if ($this->deleteBackup($backup)) {
                $deletedCount++;
            }
        }
        
        return $deletedCount;
    }
}
