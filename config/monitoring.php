<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Monitoring Enabled
    |--------------------------------------------------------------------------
    */

    'enabled' => env('MONITORING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Collection Interval
    |--------------------------------------------------------------------------
    |
    | How often to collect metrics (in seconds)
    */

    'collection_interval' => env('MONITORING_INTERVAL', 60),

    /*
    |--------------------------------------------------------------------------
    | Data Retention
    |--------------------------------------------------------------------------
    |
    | How long to keep monitoring data (in days)
    */

    'retention_days' => env('MONITORING_RETENTION_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Alert Thresholds
    |--------------------------------------------------------------------------
    */

    'thresholds' => [
        'cpu_warning' => 70,
        'cpu_critical' => 90,
        'memory_warning' => 75,
        'memory_critical' => 90,
        'disk_warning' => 80,
        'disk_critical' => 95,
    ],

    /*
    |--------------------------------------------------------------------------
    | Metrics to Collect
    |--------------------------------------------------------------------------
    */

    'metrics' => [
        'system' => [
            'cpu',
            'memory',
            'disk',
            'network',
        ],
        'project' => [
            'container_cpu',
            'container_memory',
            'container_network',
            'disk_usage',
            'database_size',
        ],
    ],

];
