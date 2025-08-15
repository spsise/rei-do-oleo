<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'whatsapp' => [
        'enabled' => env('WHATSAPP_ENABLED', false),
        'api_url' => env('WHATSAPP_API_URL', 'https://graph.facebook.com'),
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'version' => env('WHATSAPP_VERSION', 'v18.0'),
        'deploy_recipients' => explode(',', env('WHATSAPP_DEPLOY_RECIPIENTS', '')),
    ],

    'telegram' => [
        'enabled' => env('TELEGRAM_ENABLED', false),
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET', ''),
        'recipients' => array_filter(explode(',', env('TELEGRAM_RECIPIENTS', ''))),
    ],

    /*
    |--------------------------------------------------------------------------
    | Speech-to-Text Services Configuration
    |--------------------------------------------------------------------------
    */

    'speech' => [
        'provider' => env('SPEECH_PROVIDER', 'vosk'),
        'cache_ttl' => env('SPEECH_CACHE_TTL', 1800), // 30 minutes instead of 1 hour
        'cache_max_age' => env('SPEECH_CACHE_MAX_AGE', 1800), // 30 minutes max age
        'cache_cleanup_interval' => env('SPEECH_CACHE_CLEANUP_INTERVAL', 900), // 15 minutes
        'max_concurrent_processing' => env('SPEECH_MAX_CONCURRENT', 5),
        'timeout' => env('SPEECH_TIMEOUT', 60),
    ],

    'vosk' => [
        'api_key' => env('VOSK_API_KEY'),
        'speech_url' => env('VOSK_SPEECH_URL'),
        'model_path' => env('VOSK_MODEL_PATH', storage_path('models/vosk')),
        'path' => env('VOSK_BINARY_PATH', '/usr/local/bin/vosk'),
    ],

    'whisper_cpp' => [
        'api_key' => env('WHISPER_CPP_API_KEY'),
        'speech_url' => env('WHISPER_CPP_SPEECH_URL'),
        'path' => env('WHISPER_CPP_PATH', '/usr/local/bin/whisper'),
        'model_path' => env('WHISPER_CPP_MODEL_PATH', storage_path('models/whisper')),
    ],

    'deepspeech' => [
        'api_key' => env('DEEPSPEECH_API_KEY'),
        'speech_url' => env('DEEPSPEECH_SPEECH_URL'),
        'model_path' => env('DEEPSPEECH_MODEL_PATH', storage_path('models/deepspeech')),
        'scorer_path' => env('DEEPSPEECH_SCORER_PATH', storage_path('models/deepspeech')),
    ],

    'huggingface' => [
        'api_key' => env('HUGGINGFACE_API_KEY'),
        'api_url' => env('HUGGINGFACE_API_URL'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'speech_url' => env('OPENAI_SPEECH_URL', 'https://api.openai.com/v1/audio/transcriptions'),
    ],

    'google' => [
        'api_key' => env('GOOGLE_SPEECH_API_KEY', ''),
        'speech_url' => env('GOOGLE_SPEECH_URL', 'https://speech.googleapis.com/v1/speech:recognize'),
    ],

    'azure' => [
        'speech_key' => env('AZURE_SPEECH_KEY'),
        'speech_region' => env('AZURE_SPEECH_REGION'),
    ],

];
