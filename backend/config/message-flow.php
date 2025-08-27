<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Message Flow Tracking Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for the message flow tracking system
    | that monitors and logs the complete flow of messages through the system.
    |
    */

    'enabled' => env('MESSAGE_FLOW_TRACKING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Tracking Configuration
    |--------------------------------------------------------------------------
    |
    | Configure what and how to track in the message flow
    |
    */

    'tracking' => [
        'log_to_file' => env('MESSAGE_FLOW_LOG_TO_FILE', false),
        'send_to_user' => env('MESSAGE_FLOW_SEND_TO_USER', true),
        'cache_traces' => env('MESSAGE_FLOW_CACHE_TRACES', false),

        'stages_to_track' => [
            'webhook_received',
            'payload_validated',
            'duplicate_check',
            'message_processed',
            'database_operations',
            'external_api_calls',
            'response_sent',
            'error_handling'
        ],

        'categories_to_track' => [
            'system',
            'validation',
            'database',
            'external_api',
            'processing',
            'response',
            'error'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Output Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how and where to output the tracking information
    |
    */

    'output' => [
        'send_to_user' => env('MESSAGE_FLOW_SEND_TO_USER', true),
        'log_to_file' => env('MESSAGE_FLOW_LOG_TO_FILE', false),
        'store_in_database' => env('MESSAGE_FLOW_STORE_IN_DATABASE', false),
        'cache_for_monitoring' => env('MESSAGE_FLOW_CACHE_FOR_MONITORING', true),

        'formats' => [
            'telegram' => true,
            'whatsapp' => true,
            'email' => true,
            'console' => true
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | Configure performance thresholds and monitoring
    |
    */

    'performance' => [
        'slow_stage_threshold' => env('MESSAGE_FLOW_SLOW_THRESHOLD', 100), // milliseconds
        'very_slow_stage_threshold' => env('MESSAGE_FLOW_VERY_SLOW_THRESHOLD', 500), // milliseconds
        'memory_warning_threshold' => env('MESSAGE_FLOW_MEMORY_WARNING', 50), // MB
        'memory_critical_threshold' => env('MESSAGE_FLOW_MEMORY_CRITICAL', 100), // MB

        'track_query_count' => env('MESSAGE_FLOW_TRACK_QUERIES', true),
        'track_memory_usage' => env('MESSAGE_FLOW_TRACK_MEMORY', true),
        'track_execution_time' => env('MESSAGE_FLOW_TRACK_TIME', true)
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how to store and manage tracking data
    |
    */

    'storage' => [
        'cache_ttl' => env('MESSAGE_FLOW_CACHE_TTL', 1440), // minutes (24 hours)
        'database_ttl' => env('MESSAGE_FLOW_DB_TTL', 10080), // minutes (7 days)
        'file_ttl' => env('MESSAGE_FLOW_FILE_TTL', 43200), // minutes (30 days)

        'max_traces_per_user' => env('MESSAGE_FLOW_MAX_TRACES', 100),
        'max_stages_per_trace' => env('MESSAGE_FLOW_MAX_STAGES', 50),
        'cleanup_frequency' => env('MESSAGE_FLOW_CLEANUP_FREQ', 3600), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Report Configuration
    |--------------------------------------------------------------------------
    |
    | Configure report generation and formatting
    |
    */

    'reports' => [
        'user_report_enabled' => env('MESSAGE_FLOW_USER_REPORT', true),
        'developer_report_enabled' => env('MESSAGE_FLOW_DEV_REPORT', true),
        'performance_report_enabled' => env('MESSAGE_FLOW_PERF_REPORT', true),

        'user_report_format' => env('MESSAGE_FLOW_USER_FORMAT', 'telegram'),
        'developer_report_format' => env('MESSAGE_FLOW_DEV_FORMAT', 'json'),

        'include_performance_insights' => env('MESSAGE_FLOW_INCLUDE_INSIGHTS', true),
        'include_recommendations' => env('MESSAGE_FLOW_INCLUDE_RECOMMENDATIONS', true),
        'include_error_details' => env('MESSAGE_FLOW_INCLUDE_ERRORS', true)
    ]
];
