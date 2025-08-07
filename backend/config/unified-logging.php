<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Unified Logging Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file controls the unified logging system that uses
    | Activity Log for all application logs with intelligent filtering.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default logging driver used by the application.
    | Available drivers: "activity", "laravel", "file"
    |
    */

    'driver' => env('LOGGING_DRIVER', 'activity'),

    /*
    |--------------------------------------------------------------------------
    | Log Filters
    |--------------------------------------------------------------------------
    |
    | These options control which types of logs are recorded. Set to true
    | to enable logging for that type, false to disable.
    |
    */

    'filters' => [
        // API Logging
        'api_requests' => env('LOG_API_REQUESTS', false),
        'api_responses' => env('LOG_API_RESPONSES', false),

        // Business Operations
        'business_operations' => env('LOG_BUSINESS_OPERATIONS', true),

        // Security Events
        'security_events' => env('LOG_SECURITY_EVENTS', true),

        // Performance Monitoring
        'performance' => env('LOG_PERFORMANCE', false),

        // Audit Trail
        'audit_events' => env('LOG_AUDIT_EVENTS', true),

        // Integration Events
        'telegram_events' => env('LOG_TELEGRAM_EVENTS', true),
        'whatsapp_events' => env('LOG_WHATSAPP_EVENTS', true),

        // System Events
        'exceptions' => env('LOG_EXCEPTIONS', true),

        'telegram_event' => env('LOG_TELEGRAM_EVENT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Retention
    |--------------------------------------------------------------------------
    |
    | Configure how long logs should be retained before automatic cleanup.
    | Values are in days.
    |
    */

    'retention' => [
        'default' => env('LOG_RETENTION_DAYS', 365),
        'api_requests' => env('LOG_API_RETENTION_DAYS', 30),
        'business_operations' => env('LOG_BUSINESS_RETENTION_DAYS', 90),
        'security_events' => env('LOG_SECURITY_RETENTION_DAYS', 365),
        'performance' => env('LOG_PERFORMANCE_RETENTION_DAYS', 30),
        'audit_events' => env('LOG_AUDIT_RETENTION_DAYS', 365),
        'telegram_events' => env('LOG_TELEGRAM_RETENTION_DAYS', 30),
        'whatsapp_events' => env('LOG_WHATSAPP_RETENTION_DAYS', 30),
        'exceptions' => env('LOG_EXCEPTIONS_RETENTION_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Thresholds
    |--------------------------------------------------------------------------
    |
    | Configure thresholds for performance monitoring and alerting.
    | Values are in milliseconds.
    |
    */

    'performance' => [
        'slow_operation_threshold' => env('LOG_SLOW_OPERATION_THRESHOLD', 1000), // 1 second
        'critical_operation_threshold' => env('LOG_CRITICAL_OPERATION_THRESHOLD', 5000), // 5 seconds
        'memory_warning_threshold' => env('LOG_MEMORY_WARNING_THRESHOLD', 128 * 1024 * 1024), // 128MB
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Configure security-related logging settings.
    |
    */

    'security' => [
        'log_failed_logins' => env('LOG_FAILED_LOGINS', true),
        'log_successful_logins' => env('LOG_SUCCESSFUL_LOGINS', false),
        'log_sensitive_operations' => env('LOG_SENSITIVE_OPERATIONS', true),
        'log_permission_changes' => env('LOG_PERMISSION_CHANGES', true),
        'log_data_access' => env('LOG_DATA_ACCESS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Sanitization
    |--------------------------------------------------------------------------
    |
    | Configure which fields should be sanitized (redacted) in logs.
    |
    */

    'sanitization' => [
        'sensitive_headers' => [
            'authorization',
            'cookie',
            'x-csrf-token',
            'x-api-key',
            'x-auth-token',
        ],

        'sensitive_fields' => [
            'password',
            'password_confirmation',
            'current_password',
            'token',
            'api_key',
            'secret',
            'credit_card',
            'ssn',
            'cpf',
            'cnpj',
        ],

        'sensitive_integration_fields' => [
            'telegram' => ['token', 'webhook_secret'],
            'whatsapp' => ['token', 'webhook_secret'],
            'payment' => ['card_number', 'cvv', 'expiry'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Batch Processing
    |--------------------------------------------------------------------------
    |
    | Configure batch processing for high-volume logging scenarios.
    |
    */

    'batch' => [
        'enabled' => env('LOG_BATCH_ENABLED', false),
        'size' => env('LOG_BATCH_SIZE', 100),
        'timeout' => env('LOG_BATCH_TIMEOUT', 30), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Alerting Configuration
    |--------------------------------------------------------------------------
    |
    | Configure alerting for critical log events.
    |
    */

    'alerting' => [
        'enabled' => env('LOG_ALERTING_ENABLED', false),
        'channels' => [
            'slack' => env('LOG_ALERT_SLACK_WEBHOOK'),
            'email' => env('LOG_ALERT_EMAIL'),
            'telegram' => env('LOG_ALERT_TELEGRAM_CHAT_ID'),
        ],
        'events' => [
            'security_violations' => true,
            'performance_critical' => true,
            'system_errors' => true,
            'data_breaches' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment-Specific Configurations
    |--------------------------------------------------------------------------
    |
    | Pre-configured settings for different environments.
    |
    */

    'environments' => [
        'production' => [
            'filters' => [
                'api_requests' => false,
                'api_responses' => false,
                'business_operations' => true,
                'security_events' => true,
                'performance' => false,
                'audit_events' => true,
                'telegram_events' => true,
                'whatsapp_events' => true,
                'exceptions' => true,
            ],
            'retention' => [
                'default' => 90,
                'security_events' => 365,
                'audit_events' => 365,
            ],
        ],

        'development' => [
            'filters' => [
                'api_requests' => true,
                'api_responses' => true,
                'business_operations' => true,
                'security_events' => true,
                'performance' => true,
                'audit_events' => true,
                'telegram_events' => true,
                'whatsapp_events' => true,
                'exceptions' => true,
            ],
            'retention' => [
                'default' => 30,
                'security_events' => 90,
                'audit_events' => 90,
            ],
        ],

        'testing' => [
            'filters' => [
                'api_requests' => false,
                'api_responses' => false,
                'business_operations' => false,
                'security_events' => false,
                'performance' => false,
                'audit_events' => false,
                'telegram_events' => false,
                'whatsapp_events' => false,
                'exceptions' => true,
            ],
            'retention' => [
                'default' => 7,
            ],
        ],
    ],
];
