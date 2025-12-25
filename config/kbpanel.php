<?php

return [

    /*
    |--------------------------------------------------------------------------
    | KBPanel Version
    |--------------------------------------------------------------------------
    |
    | Current version of KBPanel installation.
    |
    */

    'version' => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | Docker Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for Docker container management and networking.
    |
    */

    'docker' => [
        'network' => env('KBPANEL_DOCKER_NETWORK', 'kbpanel_net'),
        'db_host' => env('KBPANEL_DB_HOST', 'kbpanel_db'),
        'db_port' => env('KBPANEL_DB_PORT', 3306),
        'redis_host' => env('KBPANEL_REDIS_HOST', 'kbpanel_redis'),
        'redis_port' => env('KBPANEL_REDIS_PORT', 6379),
        'phpmyadmin_port' => env('KBPANEL_PMA_PORT', 8080),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Project Settings
    |--------------------------------------------------------------------------
    |
    | Default configuration values for new projects.
    |
    */

    'defaults' => [
        'php_version' => env('KBPANEL_DEFAULT_PHP', '8.2'),
        'web_server' => env('KBPANEL_DEFAULT_SERVER', 'nginx'),
        'db_quota_mb' => env('KBPANEL_DB_QUOTA_MB', 500),
        'disk_quota_mb' => env('KBPANEL_DISK_QUOTA_MB', 5000),
        'starting_port' => env('KBPANEL_PORT_START', 8000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported PHP Versions
    |--------------------------------------------------------------------------
    |
    | PHP versions available for project deployment.
    |
    */

    'php_versions' => [
        '7.4' => 'PHP 7.4',
        '8.0' => 'PHP 8.0',
        '8.1' => 'PHP 8.1',
        '8.2' => 'PHP 8.2',
        '8.3' => 'PHP 8.3',
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Laravel Versions
    |--------------------------------------------------------------------------
    |
    | Laravel versions available for deployment.
    |
    */

    'laravel_versions' => [
        '8' => 'Laravel 8.x LTS',
        '9' => 'Laravel 9.x',
        '10' => 'Laravel 10.x LTS',
        '11' => 'Laravel 11.x',
        '12' => 'Laravel 12.x',
    ],

    /*
    |--------------------------------------------------------------------------
    | Project Types
    |--------------------------------------------------------------------------
    |
    | Supported project/application types.
    |
    */

    'project_types' => [
        'laravel' => 'Laravel Application',
        'wordpress' => 'WordPress Site',
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Paths
    |--------------------------------------------------------------------------
    |
    | Paths for project files, backups, and other storage.
    |
    */

    'paths' => [
        'projects' => storage_path('projects'),
        'backups' => storage_path('backups'),
        'git_repos' => storage_path('git-repos'),
        'ssl' => storage_path('ssl'),
        'docker_templates' => base_path('docker/templates'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource Limits
    |--------------------------------------------------------------------------
    |
    | System resource limits and quotas.
    |
    */

    'limits' => [
        'max_projects_per_user' => env('KBPANEL_MAX_PROJECTS', 10),
        'max_staging_per_project' => env('KBPANEL_MAX_STAGING', 2),
        'backup_retention_days' => env('KBPANEL_BACKUP_RETENTION', 30),
    ],

];
