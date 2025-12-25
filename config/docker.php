<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Docker Socket Path
    |--------------------------------------------------------------------------
    |
    | Path to the Docker socket for API communication.
    |
    */

    'socket' => env('DOCKER_SOCKET', 'unix:///var/run/docker.sock'),

    /*
    |--------------------------------------------------------------------------
    | Docker Network
    |--------------------------------------------------------------------------
    |
    | The Docker network used for KBPanel containers.
    |
    */

    'network' => env('DOCKER_NETWORK', 'kbpanel_net'),

    /*
    |--------------------------------------------------------------------------
    | Container Naming Convention
    |--------------------------------------------------------------------------
    |
    | Pattern for naming Docker containers.
    |
    */

    'container_prefix' => env('DOCKER_CONTAINER_PREFIX', 'kbp'),

    /*
    |--------------------------------------------------------------------------
    | Default Images
    |--------------------------------------------------------------------------
    |
    | Default Docker images for different PHP versions and services.
    |
    */

    'images' => [
        'php' => [
            '7.4' => 'php:7.4-fpm-alpine',
            '8.0' => 'php:8.0-fpm-alpine',
            '8.1' => 'php:8.1-fpm-alpine',
            '8.2' => 'php:8.2-fpm-alpine',
            '8.3' => 'php:8.3-fpm-alpine',
        ],
        'nginx' => 'nginx:alpine',
        'mysql' => 'mysql:8.0',
        'redis' => 'redis:7-alpine',
        'wordpress' => 'wordpress:latest',
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource Limits
    |--------------------------------------------------------------------------
    |
    | Default resource constraints for containers.
    |
    */

    'limits' => [
        'memory' => env('DOCKER_MEMORY_LIMIT', '512m'),
        'cpu_quota' => env('DOCKER_CPU_QUOTA', 50000), // 0.5 CPU
        'cpu_period' => 100000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Port Range
    |--------------------------------------------------------------------------
    |
    | Range of ports available for project containers.
    |
    */

    'port_range' => [
        'start' => env('DOCKER_PORT_START', 8000),
        'end' => env('DOCKER_PORT_END', 8999),
    ],

    /*
    |--------------------------------------------------------------------------
    | Template Paths
    |--------------------------------------------------------------------------
    |
    | Paths to Docker Compose and config templates.
    |
    */

    'templates' => [
        'compose' => base_path('docker/templates'),
        'nginx' => base_path('docker/nginx'),
        'apache' => base_path('docker/apache'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-restart Policy
    |--------------------------------------------------------------------------
    |
    | Restart policy for project containers.
    |
    */

    'restart_policy' => env('DOCKER_RESTART_POLICY', 'unless-stopped'),

    /*
    |--------------------------------------------------------------------------
    | Logging Driver
    |--------------------------------------------------------------------------
    |
    | Docker logging driver configuration.
    |
    */

    'logging' => [
        'driver' => env('DOCKER_LOG_DRIVER', 'json-file'),
        'options' => [
            'max-size' => env('DOCKER_LOG_MAX_SIZE', '10m'),
            'max-file' => env('DOCKER_LOG_MAX_FILE', '3'),
        ],
    ],

];
