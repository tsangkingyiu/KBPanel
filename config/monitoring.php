<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Monitoring Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable resource monitoring.
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
    | Data Retention
    |--------------------------------------------------------------------------
    |
    | How long to keep monitoring data (in days).
    |
    */

    'retention_days' => env('MONITORING_RETENTION_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Alert Thresholds
    |--------------------------------------------------------------------------
    |
    | Resource usage thresholds that trigger alerts.
    |
    */

    'thresholds' => [
        'cpu_percent' => env('ALERT_CPU_THRESHOLD', 80),
        'memory_percent' => env('ALERT_MEMORY_THRESHOLD', 85),
        'disk_percent' => env('ALERT_DISK_THRESHOLD', 90),
        'inode_percent' => env('ALERT_INODE_THRESHOLD', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Metrics to Collect
    |--------------------------------------------------------------------------
    |
    | Which metrics should be collected.
    |
    */

    'metrics' => [
        'system' => [
            'cpu_usage',
            'memory_usage',
            'disk_usage',
            'load_average',
            'network_traffic',
        ],
        'project' => [
            'container_cpu',
            'container_memory',
            'container_disk',
            'http_requests',
            'response_time',
        ],
        'database' => [
            'connections',
            'queries_per_second',
            'slow_queries',
            'table_size',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Channels
    |--------------------------------------------------------------------------
    |
    | How to send alerts when thresholds are exceeded.
    |
    */

    'alert_channels' => [
        'email' => env('MONITORING_ALERT_EMAIL', true),
        'slack' => env('MONITORING_ALERT_SLACK', false),
        'database' => true, // Always log to database
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Refresh Rate
    |--------------------------------------------------------------------------
    |
    | How often the monitoring dashboard refreshes (in seconds).
    |
    */

    'dashboard_refresh' => env('MONITORING_DASHBOARD_REFRESH', 30),

];
