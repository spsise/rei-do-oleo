<?php

return [
    'telegram' => [
        'enabled' => env('TELEGRAM_ENABLED', true),
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'recipients' => array_filter(explode(',', env('TELEGRAM_RECIPIENTS', ''))),
    ],

    'speech' => [
        'provider' => env('SPEECH_PROVIDER', 'openai'),
        'cache_enabled' => env('SPEECH_CACHE_ENABLED', true),
        'cache_ttl' => env('SPEECH_CACHE_TTL', 3600),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'speech_url' => 'https://api.openai.com/v1/audio/transcriptions',
    ],

    'google' => [
        'speech_api_key' => env('GOOGLE_SPEECH_API_KEY'),
        'speech_url' => 'https://speech.googleapis.com/v1/speech:recognize',
    ],

    'azure' => [
        'speech_key' => env('AZURE_SPEECH_KEY'),
        'speech_region' => env('AZURE_SPEECH_REGION'),
        'speech_url' => 'https://' . env('AZURE_SPEECH_REGION') . '.stt.speech.microsoft.com/speech/recognition/conversation/cognitiveservices/v1',
    ],

    // Free Speech-to-Text Providers
    'vosk' => [
        'model_path' => env('VOSK_MODEL_PATH', storage_path('app/vosk-models/vosk-model-small-pt-0.3')),
    ],

    'whisper_cpp' => [
        'path' => env('WHISPER_CPP_PATH', '/usr/local/bin/whisper'),
        'model_path' => env('WHISPER_CPP_MODEL_PATH', storage_path('app/whisper-models/ggml-base.bin')),
    ],

    'deepspeech' => [
        'model_path' => env('DEEPSPEECH_MODEL_PATH', storage_path('app/deepspeech-models/deepspeech-0.9.3-models.pbmm')),
        'scorer_path' => env('DEEPSPEECH_SCORER_PATH', storage_path('app/deepspeech-models/deepspeech-0.9.3-models.scorer')),
    ],

    'huggingface' => [
        'api_url' => env('HUGGINGFACE_API_URL', 'https://api-inference.huggingface.co/models/openai/whisper-base'),
        'api_key' => env('HUGGINGFACE_API_KEY'),
    ],
];
