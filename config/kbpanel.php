<?php

return [

    /*
    |--------------------------------------------------------------------------
    | KBPanel Version
    |--------------------------------------------------------------------------
    */

    'version' => env('KBPANEL_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Docker Configuration
    |--------------------------------------------------------------------------
    */

    'docker' => [
        'network' => env('KBPANEL_DOCKER_NETWORK', 'kbpanel_net'),
        'db_container' => env('KBPANEL_DB_CONTAINER', 'kbpanel_db'),
        'redis_container' => env('KBPANEL_REDIS_CONTAINER', 'kbpanel_redis'),
        'pma_port' => env('KBPANEL_PMA_PORT', 8080),
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Paths
    |--------------------------------------------------------------------------
    */

    'paths' => [
        'projects' => env('KBPANEL_PROJECTS_PATH', storage_path('projects')),
        'backups' => env('KBPANEL_BACKUPS_PATH', storage_path('backups')),
        'git_repos' => storage_path('git-repos'),
        'ssl' => storage_path('ssl'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Project Settings
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'php_version' => env('KBPANEL_DEFAULT_PHP_VERSION', '8.2'),
        'web_server' => 'apache', // or 'nginx'
        'db_quota_mb' => env('KBPANEL_DEFAULT_DB_QUOTA_MB', 500),
        'disk_quota_mb' => env('KBPANEL_DEFAULT_DISK_QUOTA_MB', 5000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported PHP Versions
    |--------------------------------------------------------------------------
    */

    'php_versions' => [
        '7.4', '8.0', '8.1', '8.2', '8.3',
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Laravel Versions
    |--------------------------------------------------------------------------
    */

    'laravel_versions' => [
        '8.x', '9.x', '10.x', '11.x', '12.x',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    */

    'database' => [
        'host' => env('KBPANEL_DB_CONTAINER', 'kbpanel_db'),
        'port' => 3306,
        'shared_mode' => true, // Use single MySQL instance with per-project databases
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Configuration
    |--------------------------------------------------------------------------
    */

    'monitoring' => [
        'enabled' => true,
        'collection_interval' => 60, // seconds
        'retention_days' => 30,
        'alert_thresholds' => [
            'cpu_percent' => 80,
            'memory_percent' => 85,
            'disk_percent' => 90,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Configuration
    |--------------------------------------------------------------------------
    */

    'backup' => [
        'retention_days' => 30,
        'auto_backup' => false,
        'auto_backup_schedule' => 'daily', // daily, weekly, monthly
    ],

    /*
    |--------------------------------------------------------------------------
    | SSL Configuration
    |--------------------------------------------------------------------------
    */

    'ssl' => [
        'provider' => 'letsencrypt',
        'email' => env('ADMIN_EMAIL'),
        'auto_renew' => true,
    ],

];
