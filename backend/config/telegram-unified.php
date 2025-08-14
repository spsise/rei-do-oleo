<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Unified Command System Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for the unified command system
    | that replaces the old hardcoded command system.
    |
    */

    'enabled' => env('TELEGRAM_UNIFIED_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | System Integration
    |--------------------------------------------------------------------------
    |
    | Configure how the unified system integrates with existing services
    |
    */

    'integration' => [
        'replace_old_system' => env('TELEGRAM_REPLACE_OLD_SYSTEM', true),
        'fallback_to_old' => env('TELEGRAM_FALLBACK_TO_OLD', false),
        'migration_mode' => env('TELEGRAM_MIGRATION_MODE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Command Processing
    |--------------------------------------------------------------------------
    |
    | Configure command processing behavior
    |
    */

    'processing' => [
        'default_timeout' => env('TELEGRAM_PROCESSING_TIMEOUT', 30),
        'max_retries' => env('TELEGRAM_MAX_RETRIES', 3),
        'retry_delay' => env('TELEGRAM_RETRY_DELAY', 1000), // milliseconds
        'enable_logging' => env('TELEGRAM_ENABLE_LOGGING', true),
        'log_level' => env('TELEGRAM_LOG_LEVEL', 'info'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Handler Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how handlers are resolved and executed
    |
    */

    'handlers' => [
        'namespace' => 'App\\Services\\Telegram\\Handlers',
        'default_method' => 'handle',
        'auto_resolve' => env('TELEGRAM_AUTO_RESOLVE_HANDLERS', true),
        'fallback_handler' => 'App\\Services\\Telegram\\Handlers\\FallbackHandler',
    ],

    /*
    |--------------------------------------------------------------------------
    | Voice Processing
    |--------------------------------------------------------------------------
    |
    | Configure voice command processing
    |
    */

    'voice' => [
        'enabled' => env('TELEGRAM_VOICE_ENABLED', true),
        'providers' => [
            'default' => env('TELEGRAM_VOICE_PROVIDER', 'openai'),
            'openai' => [
                'api_key' => env('OPENAI_API_KEY'),
                'model' => env('OPENAI_MODEL', 'whisper-1'),
                'timeout' => env('OPENAI_TIMEOUT', 30),
            ],
            'google' => [
                'api_key' => env('GOOGLE_SPEECH_API_KEY'),
                'language' => env('GOOGLE_SPEECH_LANGUAGE', 'pt-BR'),
            ],
        ],
        'noise_reduction' => env('TELEGRAM_VOICE_NOISE_REDUCTION', true),
        'similarity_threshold' => env('TELEGRAM_VOICE_SIMILARITY_THRESHOLD', 0.6),
    ],

    /*
    |--------------------------------------------------------------------------
    | Natural Language Processing
    |--------------------------------------------------------------------------
    |
    | Configure NLP features
    |
    */

    'nlp' => [
        'enabled' => env('TELEGRAM_NLP_ENABLED', true),
        'confidence_threshold' => env('TELEGRAM_NLP_CONFIDENCE', 0.7),
        'fuzzy_matching' => env('TELEGRAM_FUZZY_MATCHING', true),
        'fuzzy_threshold' => env('TELEGRAM_FUZZY_THRESHOLD', 0.6),
        'language_detection' => env('TELEGRAM_LANGUAGE_DETECTION', true),
        'supported_languages' => ['pt', 'en', 'es'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Optimization
    |--------------------------------------------------------------------------
    |
    | Configure performance settings
    |
    */

    'performance' => [
        'enable_cache' => env('TELEGRAM_ENABLE_CACHE', true),
        'cache_ttl' => env('TELEGRAM_CACHE_TTL', 1800), // 30 minutes
        'cache_max_size' => env('TELEGRAM_CACHE_MAX_SIZE', 1000),
        'batch_processing' => env('TELEGRAM_BATCH_PROCESSING', true),
        'batch_size' => env('TELEGRAM_BATCH_SIZE', 100),
        'async_processing' => env('TELEGRAM_ASYNC_PROCESSING', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security and Permissions
    |--------------------------------------------------------------------------
    |
    | Configure security settings
    |
    */

    'security' => [
        'enable_permissions' => env('TELEGRAM_ENABLE_PERMISSIONS', true),
        'default_permission' => 'user',
        'admin_permissions' => ['admin', 'manager'],
        'rate_limiting' => env('TELEGRAM_RATE_LIMITING', true),
        'max_requests_per_minute' => env('TELEGRAM_MAX_REQUESTS_PER_MINUTE', 60),
        'block_suspicious_commands' => env('TELEGRAM_BLOCK_SUSPICIOUS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring and Analytics
    |--------------------------------------------------------------------------
    |
    | Configure monitoring features
    |
    */

    'monitoring' => [
        'enable_metrics' => env('TELEGRAM_ENABLE_METRICS', true),
        'track_command_usage' => env('TELEGRAM_TRACK_USAGE', true),
        'track_response_times' => env('TELEGRAM_TRACK_RESPONSE_TIMES', true),
        'track_error_rates' => env('TELEGRAM_TRACK_ERROR_RATES', true),
        'alert_on_errors' => env('TELEGRAM_ALERT_ON_ERRORS', true),
        'error_threshold' => env('TELEGRAM_ERROR_THRESHOLD', 0.1), // 10%
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback and Error Handling
    |--------------------------------------------------------------------------
    |
    | Configure fallback behavior
    |
    */

    'fallback' => [
        'enable_fallback' => env('TELEGRAM_ENABLE_FALLBACK', true),
        'fallback_message' => env('TELEGRAM_FALLBACK_MESSAGE', 'Desculpe, não entendi esse comando.'),
        'suggest_alternatives' => env('TELEGRAM_SUGGEST_ALTERNATIVES', true),
        'max_suggestions' => env('TELEGRAM_MAX_SUGGESTIONS', 3),
        'log_fallback_usage' => env('TELEGRAM_LOG_FALLBACK', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Development and Debug
    |--------------------------------------------------------------------------
    |
    | Configure development settings
    |
    */

    'development' => [
        'enable_debug' => env('TELEGRAM_DEBUG_MODE', false),
        'log_all_commands' => env('TELEGRAM_LOG_ALL_COMMANDS', false),
        'show_confidence_scores' => env('TELEGRAM_SHOW_CONFIDENCE', false),
        'enable_test_mode' => env('TELEGRAM_TEST_MODE', false),
        'test_commands' => [
            'test_voice',
            'test_nlp',
            'test_permissions',
            'test_fallback',
        ],
    ],
];
