<?php

return [

    /*
    |--------------------------------------------------------------------------
    | KBPanel Version
    |--------------------------------------------------------------------------
    |
    | The current version of KBPanel. Used for UI display and update checks.
    |
    */

    'version' => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | Docker Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for Docker containers and networking.
    |
    */

    'docker' => [
        'network' => env('DOCKER_NETWORK', 'kbpanel_net'),
        'db_host' => env('DOCKER_DB_HOST', 'kbpanel_db'),
        'db_port' => env('DOCKER_DB_PORT', 3306),
        'redis_host' => env('DOCKER_REDIS_HOST', 'kbpanel_redis'),
        'redis_port' => env('DOCKER_REDIS_PORT', 6379),
        'phpmyadmin_port' => env('DOCKER_PMA_PORT', 8080),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    |
    | Default values for new projects and users.
    |
    */

    'defaults' => [
        'php_version' => '8.2',
        'web_server' => 'apache', // apache or nginx
        'db_quota_mb' => 500,
        'disk_quota_mb' => 5000,
        'project_limit' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    |
    | Important directory paths for KBPanel operations.
    |
    */

    'paths' => [
        'projects' => storage_path('projects'),
        'backups' => storage_path('backups'),
        'git_repos' => storage_path('git-repos'),
        'ssl_certs' => storage_path('ssl'),
        'docker_templates' => base_path('docker/templates'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource Limits
    |--------------------------------------------------------------------------
    |
    | Default resource limits for containers.
    |
    */

    'resource_limits' => [
        'cpu_percent' => 50, // Max CPU percentage per container
        'memory_mb' => 512, // Max memory per container
        'disk_mb' => 5000, // Max disk space per project
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring
    |--------------------------------------------------------------------------
    |
    | Settings for resource monitoring and alerts.
    |
    */

    'monitoring' => [
        'enabled' => true,
        'interval_seconds' => 60,
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
    |
    | Settings for automatic backups.
    |
    */

    'backup' => [
        'enabled' => true,
        'retention_days' => 30,
        'schedule' => 'daily', // daily, weekly
        'types' => ['full', 'database', 'files'],
    ],

    /*
    |--------------------------------------------------------------------------
    | SSL Configuration
    |--------------------------------------------------------------------------
    |
    | Let's Encrypt and SSL certificate settings.
    |
    */

    'ssl' => [
        'provider' => 'letsencrypt', // letsencrypt or self-signed
        'email' => env('KBPANEL_ADMIN_EMAIL'),
        'auto_renew' => true,
        'renewal_days_before' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Git Integration
    |--------------------------------------------------------------------------
    |
    | Settings for Git repository integration.
    |
    */

    'git' => [
        'enabled' => true,
        'supported_providers' => ['github', 'gitlab', 'bitbucket'],
        'auto_deploy' => false, // Default auto-deploy setting
        'webhook_secret' => env('GIT_WEBHOOK_SECRET', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Staging Environment
    |--------------------------------------------------------------------------
    |
    | Settings for staging environments.
    |
    */

    'staging' => [
        'enabled' => true,
        'subdomain_prefix' => 'staging',
        'auto_sync' => false,
        'separate_database' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Laravel Versions
    |--------------------------------------------------------------------------
    |
    | Laravel versions supported for deployment.
    |
    */

    'laravel_versions' => [
        '8.x' => '8.*',
        '9.x' => '9.*',
        '10.x' => '10.*',
        '11.x' => '11.*',
        '12.x' => '12.*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported PHP Versions
    |--------------------------------------------------------------------------
    |
    | PHP versions available for projects.
    |
    */

    'php_versions' => [
        '7.4',
        '8.0',
        '8.1',
        '8.2',
        '8.3',
    ],

    /*
    |--------------------------------------------------------------------------
    | phpMyAdmin Configuration
    |--------------------------------------------------------------------------
    |
    | Database management interface settings.
    |
    */

    'phpmyadmin' => [
        'enabled' => true,
        'url' => env('PHPMYADMIN_URL', 'http://127.0.0.1:8080'),
        'timeout_minutes' => 60,
    ],

];
