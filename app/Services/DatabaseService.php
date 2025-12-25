<?php

namespace App\Services;

use App\Models\Project;
use App\Models\DatabaseInstance;

class DatabaseService
{
    public function createDatabase(Project $project, $dbType = 'mysql')
    {
        $dbName = 'kbpanel_proj_' . $project->id;
        $dbUser = 'user_' . $project->id;
        $dbPassword = bin2hex(random_bytes(16));

        // TODO: Connect to shared MySQL container
        // TODO: CREATE DATABASE
        // TODO: CREATE USER and GRANT privileges

        return DatabaseInstance::create([
            'project_id' => $project->id,
            'db_type' => $dbType,
            'db_name' => $dbName,
            'db_user' => $dbUser,
            'db_password' => $dbPassword,
            'port' => 3306,
        ]);
    }

    public function launchPhpMyAdmin(DatabaseInstance $dbInstance)
    {
        // Generate secure token for phpMyAdmin access
        $token = bin2hex(random_bytes(32));
        $url = url("/database/phpmyadmin/{$token}");

        $dbInstance->update(['adminer_url' => $url]);

        // TODO: Store token in cache with 1-hour expiration
        // TODO: phpMyAdmin will use this token to auto-login to specific database

        return $url;
    }

    public function exportDatabase(DatabaseInstance $dbInstance)
    {
        // TODO: Execute mysqldump command
        $filename = $dbInstance->db_name . '_' . now()->format('Y-m-d_His') . '.sql';
        return $filename;
    }

    public function importDatabase(DatabaseInstance $dbInstance, $sqlFile)
    {
        // TODO: Execute mysql import command
        return true;
    }

    public function deleteDatabase(DatabaseInstance $dbInstance)
    {
        // TODO: DROP DATABASE
        // TODO: DROP USER
        $dbInstance->delete();
        return true;
    }
}
