<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Docker Socket
    |--------------------------------------------------------------------------
    |
    | Path to Docker socket for container management.
    |
    */

    'socket' => env('DOCKER_SOCKET', 'unix:///var/run/docker.sock'),

    /*
    |--------------------------------------------------------------------------
    | Base Images
    |--------------------------------------------------------------------------
    |
    | Docker base images for different PHP versions and services.
    |
    */

    'images' => [
        'php' => [
            '7.4' => 'php:7.4-fpm',
            '8.0' => 'php:8.0-fpm',
            '8.1' => 'php:8.1-fpm',
            '8.2' => 'php:8.2-fpm',
            '8.3' => 'php:8.3-fpm',
        ],
        'nginx' => 'nginx:alpine',
        'apache' => 'httpd:alpine',
        'mysql' => 'mysql:8.0',
        'redis' => 'redis:alpine',
        'wordpress' => 'wordpress:latest',
    ],

    /*
    |--------------------------------------------------------------------------
    | Container Resource Limits
    |--------------------------------------------------------------------------
    |
    | Default resource limits for containers.
    |
    */

    'resources' => [
        'memory_limit' => env('DOCKER_MEMORY_LIMIT', '512m'),
        'cpu_limit' => env('DOCKER_CPU_LIMIT', '1.0'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Network Configuration
    |--------------------------------------------------------------------------
    |
    | Docker network settings for project isolation.
    |
    */

    'network' => [
        'driver' => 'bridge',
        'subnet' => env('DOCKER_SUBNET', '172.20.0.0/16'),
    ],

];
