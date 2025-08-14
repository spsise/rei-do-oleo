<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Commands Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for the unified command system
    | including cache settings, learning parameters, and voice processing.
    |
    */

    'cache' => [
        'enabled' => env('TELEGRAM_COMMANDS_CACHE_ENABLED', true),
        'ttl' => env('TELEGRAM_COMMANDS_CACHE_TTL', 1800), // 30 minutes
        'max_size' => env('TELEGRAM_COMMANDS_CACHE_MAX_SIZE', 1000),
        'prefix' => env('TELEGRAM_COMMANDS_CACHE_PREFIX', 'telegram_command_cache'),
    ],

    'learning' => [
        'enabled' => env('TELEGRAM_COMMANDS_LEARNING_ENABLED', true),
        'max_data_points' => env('TELEGRAM_COMMANDS_LEARNING_MAX_DATA', 10000),
        'confidence_threshold' => env('TELEGRAM_COMMANDS_CONFIDENCE_THRESHOLD', 0.7),
        'training_interval' => env('TELEGRAM_COMMANDS_TRAINING_INTERVAL', 3600), // 1 hour
    ],

    'voice' => [
        'enabled' => env('TELEGRAM_VOICE_ENABLED', true),
        'noise_reduction' => env('TELEGRAM_VOICE_NOISE_REDUCTION', true),
        'similarity_threshold' => env('TELEGRAM_VOICE_SIMILARITY_THRESHOLD', 0.6),
        'priority_boost' => env('TELEGRAM_VOICE_PRIORITY_BOOST', 1.1),
    ],

    'matching' => [
        'fuzzy_threshold' => env('TELEGRAM_FUZZY_THRESHOLD', 0.6),
        'natural_language_threshold' => env('TELEGRAM_NL_THRESHOLD', 0.8),
        'exact_match_priority' => env('TELEGRAM_EXACT_MATCH_PRIORITY', 1.0),
        'jaro_winkler_weight' => env('TELEGRAM_JARO_WINKLER_WEIGHT', 0.6),
        'levenshtein_weight' => env('TELEGRAM_LEVENSHTEIN_WEIGHT', 0.4),
    ],

    'fallback' => [
        'enabled' => env('TELEGRAM_FALLBACK_ENABLED', true),
        'suggestions_limit' => env('TELEGRAM_FALLBACK_SUGGESTIONS', 3),
        'default_message' => env('TELEGRAM_FALLBACK_MESSAGE', 'Desculpe, não entendi esse comando.'),
    ],

    'permissions' => [
        'default' => ['all'],
        'admin' => ['admin', 'manager', 'user'],
        'manager' => ['manager', 'user'],
        'user' => ['user'],
    ],

    'categories' => [
        'navigation' => 'Navigation commands',
        'reports' => 'Report generation commands',
        'system' => 'System management commands',
        'help' => 'Help and support commands',
        'general' => 'General purpose commands',
    ],

    'providers' => [
        'command_registry' => App\Services\Telegram\Commands\CommandRegistry::class,
        'command_cache' => App\Services\Telegram\Commands\Cache\CommandCache::class,
        'command_learning' => App\Services\Telegram\Commands\Learning\CommandLearning::class,
        'command_repository' => App\Services\Telegram\Commands\Repositories\CommandConfigRepository::class,
    ],

    'logging' => [
        'enabled' => env('TELEGRAM_COMMANDS_LOGGING', true),
        'level' => env('TELEGRAM_COMMANDS_LOG_LEVEL', 'info'),
        'channels' => ['daily'],
    ],

    'performance' => [
        'batch_size' => env('TELEGRAM_COMMANDS_BATCH_SIZE', 100),
        'timeout' => env('TELEGRAM_COMMANDS_TIMEOUT', 30),
        'memory_limit' => env('TELEGRAM_COMMANDS_MEMORY_LIMIT', '256M'),
    ],
];
