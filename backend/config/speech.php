<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Speech-to-Text Service Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the speech-to-text service,
    | including cache settings, logging preferences, and provider configurations.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Provider
    |--------------------------------------------------------------------------
    |
    | The default speech-to-text provider to use when converting voice files.
    | Available options: vosk, whisper_cpp, deepspeech, huggingface, openai, google, azure
    |
    */
    'provider' => env('SPEECH_PROVIDER', 'vosk'),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cache settings for storing converted text results to avoid reprocessing
    | the same audio files multiple times.
    |
    */
    'cache' => [
        // Time to live for cached results (in seconds)
        'ttl' => env('SPEECH_CACHE_TTL', 1800), // 30 minutes

        // Maximum age for cache entries before automatic cleanup (in seconds)
        'max_age' => env('SPEECH_CACHE_MAX_AGE', 1800), // 30 minutes

        // Enable duplicate log prevention
        'prevent_duplicate_logs' => env('SPEECH_PREVENT_DUPLICATE_LOGS', true),

        // Enable processing flag to prevent duplicate processing
        'prevent_duplicate_processing' => env('SPEECH_PREVENT_DUPLICATE_PROCESSING', true),

        // Maximum wait time for processing completion (in seconds)
        'max_wait_time' => env('SPEECH_MAX_WAIT_TIME', 30),

        // Processing flag timeout (in seconds)
        'processing_timeout' => env('SPEECH_PROCESSING_TIMEOUT', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Logging preferences for the speech-to-text service to help with debugging
    | and monitoring.
    |
    */
    'logging' => [
        // Enable detailed logging for debugging
        'enabled' => env('SPEECH_LOGGING_ENABLED', true),

        // Log level for speech events
        'level' => env('SPEECH_LOG_LEVEL', 'info'),

        // Log cache hits (can be noisy if disabled)
        'log_cache_hits' => env('SPEECH_LOG_CACHE_HITS', true),

        // Log processing attempts
        'log_processing_attempts' => env('SPEECH_LOG_PROCESSING_ATTEMPTS', true),

        // Log conversion results
        'log_conversion_results' => env('SPEECH_LOG_CONVERSION_RESULTS', true),

        // Log errors and exceptions
        'log_errors' => env('SPEECH_LOG_ERRORS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Provider-Specific Configurations
    |--------------------------------------------------------------------------
    |
    | Configuration options for each speech-to-text provider.
    |
    */

    'vosk' => [
        'model_path' => env('VOSK_MODEL_PATH', '/usr/share/vosk-models/pt'),
        'path' => env('VOSK_BINARY_PATH', '/usr/local/bin/vosk'),
        'language' => env('VOSK_LANGUAGE', 'pt'),
    ],

    'whisper_cpp' => [
        'path' => env('WHISPER_CPP_PATH', '/usr/local/bin/whisper'),
        'model_path' => env('WHISPER_CPP_MODEL_PATH', '/usr/share/whisper-models/ggml-base.bin'),
        'language' => env('WHISPER_CPP_LANGUAGE', 'pt'),
    ],

    'deepspeech' => [
        'model_path' => env('DEEPSPEECH_MODEL_PATH', '/usr/share/deepspeech-models/model.pbmm'),
        'scorer_path' => env('DEEPSPEECH_SCORER_PATH', '/usr/share/deepspeech-models/scorer.scorer'),
    ],

    'huggingface' => [
        'api_url' => env('HUGGINGFACE_API_URL'),
        'api_key' => env('HUGGINGFACE_API_KEY'),
        'model' => env('HUGGINGFACE_MODEL', 'facebook/wav2vec2-large-xlsr-53-portuguese'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_WHISPER_MODEL', 'whisper-1'),
        'language' => env('OPENAI_LANGUAGE', 'pt'),
    ],

    'google' => [
        'api_key' => env('GOOGLE_SPEECH_API_KEY'),
        'speech_url' => env('GOOGLE_SPEECH_URL', 'https://speech.googleapis.com/v1/speech:recognize'),
        'language' => env('GOOGLE_LANGUAGE', 'pt-BR'),
        'encoding' => env('GOOGLE_ENCODING', 'OGG_OPUS'),
        'sample_rate' => env('GOOGLE_SAMPLE_RATE', 48000),
    ],

    'azure' => [
        'speech_key' => env('AZURE_SPEECH_KEY'),
        'speech_region' => env('AZURE_SPEECH_REGION'),
        'language' => env('AZURE_LANGUAGE', 'pt-BR'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audio Processing Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for audio file processing and conversion.
    |
    */
    'audio' => [
        // Preferred output format for audio conversion
        'preferred_format' => env('SPEECH_PREFERRED_FORMAT', 'wav'),

        // Target sample rate for audio processing
        'target_sample_rate' => env('SPEECH_TARGET_SAMPLE_RATE', 16000),

        // Target number of channels (1 = mono, 2 = stereo)
        'target_channels' => env('SPEECH_TARGET_CHANNELS', 1),

        // Target bit depth
        'target_bit_depth' => env('SPEECH_TARGET_BIT_DEPTH', 16),

        // Maximum file size for processing (in bytes)
        'max_file_size' => env('SPEECH_MAX_FILE_SIZE', 25 * 1024 * 1024), // 25MB

        // Supported audio formats
        'supported_formats' => [
            'ogg', 'opus', 'wav', 'mp3', 'm4a', 'aac', 'flac', 'webm'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | Performance-related settings for the speech-to-text service.
    |
    */
    'performance' => [
        // Enable parallel processing for multiple files
        'parallel_processing' => env('SPEECH_PARALLEL_PROCESSING', false),

        // Maximum number of parallel processes
        'max_parallel_processes' => env('SPEECH_MAX_PARALLEL_PROCESSES', 3),

        // Enable result caching
        'enable_caching' => env('SPEECH_ENABLE_CACHING', true),

        // Enable automatic cleanup of expired cache entries
        'auto_cleanup' => env('SPEECH_AUTO_CLEANUP', true),

        // Cleanup interval (in seconds)
        'cleanup_interval' => env('SPEECH_CLEANUP_INTERVAL', 3600), // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Security-related settings for the speech-to-text service.
    |
    */
    'security' => [
        // Enable rate limiting
        'rate_limiting' => env('SPEECH_RATE_LIMITING', true),

        // Maximum requests per minute per IP
        'max_requests_per_minute' => env('SPEECH_MAX_REQUESTS_PER_MINUTE', 10),

        // Enable file type validation
        'validate_file_types' => env('SPEECH_VALIDATE_FILE_TYPES', true),

        // Enable file size validation
        'validate_file_size' => env('SPEECH_VALIDATE_FILE_SIZE', true),

        // Allowed file extensions
        'allowed_extensions' => [
            'ogg', 'opus', 'wav', 'mp3', 'm4a', 'aac', 'flac', 'webm'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Monitoring and health check settings for the speech-to-text service.
    |
    */
    'monitoring' => [
        // Enable health checks
        'health_checks' => env('SPEECH_HEALTH_CHECKS', true),

        // Health check interval (in seconds)
        'health_check_interval' => env('SPEECH_HEALTH_CHECK_INTERVAL', 300), // 5 minutes

        // Enable performance metrics
        'performance_metrics' => env('SPEECH_PERFORMANCE_METRICS', true),

        // Enable error tracking
        'error_tracking' => env('SPEECH_ERROR_TRACKING', true),

        // Maximum error rate before alerting
        'max_error_rate' => env('SPEECH_MAX_ERROR_RATE', 0.1), // 10%
    ],
];
