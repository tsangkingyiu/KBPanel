<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DatabaseService
{
    /**
     * Create a new database and user for a project on shared MySQL instance
     */
    public function createProjectDatabase(string $projectName, int $userId): array
    {
        $dbName = $this->sanitizeDatabaseName($projectName);
        $dbUser = 'user_' . $userId . '_' . Str::random(6);
        $dbPassword = Str::random(24);
        
        try {
            // Connect to shared MySQL container
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Create user with limited privileges (only to their database)
            DB::statement("CREATE USER IF NOT EXISTS '{$dbUser}'@'%' IDENTIFIED BY '{$dbPassword}'");
            DB::statement("GRANT ALL PRIVILEGES ON `{$dbName}`.* TO '{$dbUser}'@'%'");
            DB::statement("FLUSH PRIVILEGES");
            
            Log::info('Project database created', [
                'db_name' => $dbName,
                'db_user' => $dbUser,
                'user_id' => $userId
            ]);
            
            return [
                'success' => true,
                'db_name' => $dbName,
                'db_user' => $dbUser,
                'db_password' => $dbPassword,
                'db_host' => config('kbpanel.docker.db_host'),
                'db_port' => config('kbpanel.docker.db_port')
            ];
            
        } catch (\Exception $e) {
            Log::error('Database creation failed', [
                'error' => $e->getMessage(),
                'project' => $projectName
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to create database: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete project database and user
     */
    public function deleteProjectDatabase(string $dbName, string $dbUser): bool
    {
        try {
            DB::statement("DROP DATABASE IF EXISTS `{$dbName}`");
            DB::statement("DROP USER IF EXISTS '{$dbUser}'@'%'");
            DB::statement("FLUSH PRIVILEGES");
            
            Log::info('Project database deleted', ['db_name' => $dbName]);
            return true;
            
        } catch (\Exception $e) {
            Log::error('Database deletion failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Export database to SQL file
     */
    public function exportDatabase(string $dbName, string $exportPath): bool
    {
        $dbHost = config('kbpanel.docker.db_host');
        $dbUser = env('DB_USERNAME');
        $dbPassword = env('DB_PASSWORD');
        
        $command = "docker exec kbpanel_db mysqldump -h {$dbHost} -u {$dbUser} -p{$dbPassword} {$dbName} > {$exportPath}";
        
        try {
            exec($command, $output, $returnCode);
            return $returnCode === 0;
        } catch (\Exception $e) {
            Log::error('Database export failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Import database from SQL file
     */
    public function importDatabase(string $dbName, string $importPath): bool
    {
        $dbHost = config('kbpanel.docker.db_host');
        $dbUser = env('DB_USERNAME');
        $dbPassword = env('DB_PASSWORD');
        
        $command = "docker exec -i kbpanel_db mysql -h {$dbHost} -u {$dbUser} -p{$dbPassword} {$dbName} < {$importPath}";
        
        try {
            exec($command, $output, $returnCode);
            return $returnCode === 0;
        } catch (\Exception $e) {
            Log::error('Database import failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get database size in MB
     */
    public function getDatabaseSize(string $dbName): float
    {
        try {
            $result = DB::selectOne("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.TABLES
                WHERE table_schema = ?
            ", [$dbName]);
            
            return $result->size_mb ?? 0;
        } catch (\Exception $e) {
            Log::error('Failed to get database size', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Generate phpMyAdmin access URL with token
     */
    public function generatePhpMyAdminUrl(string $dbName, string $dbUser): string
    {
        // Generate time-limited token for phpMyAdmin access
        $token = Str::random(32);
        $expiresAt = now()->addHour();
        
        // Store token in cache for validation
        cache()->put("pma_token_{$token}", [
            'db_name' => $dbName,
            'db_user' => $dbUser,
            'expires_at' => $expiresAt
        ], $expiresAt);
        
        return route('phpmyadmin.proxy', ['token' => $token]);
    }

    /**
     * Sanitize database name for MySQL naming rules
     */
    private function sanitizeDatabaseName(string $name): string
    {
        // Remove special characters, keep only alphanumeric and underscores
        $sanitized = preg_replace('/[^a-zA-Z0-9_]/', '_', $name);
        
        // Ensure it starts with a letter
        if (!preg_match('/^[a-zA-Z]/', $sanitized)) {
            $sanitized = 'db_' . $sanitized;
        }
        
        // Limit length to 64 characters (MySQL limit)
        return substr($sanitized, 0, 64);
    }
}
