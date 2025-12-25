<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for system and resource monitoring.
    |
    */

    'enabled' => env('MONITORING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Collection Interval
    |--------------------------------------------------------------------------
    |
    | How often to collect metrics (in seconds).
    |
    */

    'interval' => env('MONITORING_INTERVAL', 60),

    /*
    |--------------------------------------------------------------------------
    | Metrics Retention
    |--------------------------------------------------------------------------
    |
    | How long to keep metrics data (in days).
    |
    */

    'retention_days' => env('MONITORING_RETENTION_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Alert Thresholds
    |--------------------------------------------------------------------------
    |
    | Thresholds for resource alerts.
    |
    */

    'thresholds' => [
        'cpu_percent' => env('ALERT_CPU_PERCENT', 80),
        'memory_percent' => env('ALERT_MEMORY_PERCENT', 80),
        'disk_percent' => env('ALERT_DISK_PERCENT', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Prometheus Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for Prometheus integration.
    |
    */

    'prometheus' => [
        'enabled' => env('PROMETHEUS_ENABLED', false),
        'host' => env('PROMETHEUS_HOST', 'localhost'),
        'port' => env('PROMETHEUS_PORT', 9090),
    ],

];
