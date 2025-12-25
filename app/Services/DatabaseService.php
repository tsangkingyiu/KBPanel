<?php

namespace App\Services;

use App\Models\Project;
use App\Models\DatabaseInstance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DatabaseService
{
    /**
     * Create a new database for a project
     */
    public function createDatabase(Project $project): DatabaseInstance
    {
        $dbName = 'kbp_' . $project->id . '_' . Str::random(8);
        $dbUser = 'user_' . $project->user_id . '_' . Str::random(6);
        $dbPassword = Str::random(32);

        try {
            // Create database
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // Create user with limited privileges
            DB::statement("CREATE USER IF NOT EXISTS '{$dbUser}'@'%' IDENTIFIED BY '{$dbPassword}'");
            DB::statement("GRANT ALL PRIVILEGES ON `{$dbName}`.* TO '{$dbUser}'@'%'");
            DB::statement("FLUSH PRIVILEGES");

            // Store database instance info
            $dbInstance = DatabaseInstance::create([
                'project_id' => $project->id,
                'db_type' => 'mysql',
                'db_name' => $dbName,
                'db_user' => $dbUser,
                'db_password' => encrypt($dbPassword),
                'db_host' => config('kbpanel.docker.db_host', 'kbpanel_db'),
                'db_port' => config('kbpanel.docker.db_port', 3306),
            ]);

            Log::info('Database created for project: ' . $project->id);

            return $dbInstance;
        } catch (\Exception $e) {
            Log::error('Failed to create database: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a database
     */
    public function deleteDatabase(DatabaseInstance $dbInstance): bool
    {
        try {
            DB::statement("DROP DATABASE IF EXISTS `{$dbInstance->db_name}`");
            DB::statement("DROP USER IF EXISTS '{$dbInstance->db_user}'@'%'");
            DB::statement("FLUSH PRIVILEGES");

            $dbInstance->delete();

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete database: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Export database to SQL file
     */
    public function exportDatabase(DatabaseInstance $dbInstance): string
    {
        $exportPath = storage_path('app/database-exports/' . $dbInstance->db_name . '_' . now()->format('Y-m-d_His') . '.sql');
        $password = decrypt($dbInstance->db_password);

        $command = sprintf(
            'mysqldump -h %s -P %s -u %s -p%s %s > %s',
            $dbInstance->db_host,
            $dbInstance->db_port,
            $dbInstance->db_user,
            $password,
            $dbInstance->db_name,
            $exportPath
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception('Database export failed');
        }

        return $exportPath;
    }

    /**
     * Get database size in MB
     */
    public function getDatabaseSize(string $dbName): float
    {
        $result = DB::select("
            SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
            FROM information_schema.TABLES
            WHERE table_schema = ?
        ", [$dbName]);

        return $result[0]->size_mb ?? 0;
    }

    /**
     * Generate phpMyAdmin access URL
     */
    public function launchPhpMyAdmin(Project $project): string
    {
        // TODO: Implement secure token-based phpMyAdmin access
        $baseUrl = config('kbpanel.phpmyadmin_url', 'http://localhost:8080');
        $token = Str::random(32);

        // Store token in cache for 1 hour
        cache()->put('pma_token_' . $token, [
            'project_id' => $project->id,
            'user_id' => $project->user_id
        ], 3600);

        return $baseUrl . '?token=' . $token;
    }
}
