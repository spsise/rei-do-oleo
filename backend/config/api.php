<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure settings specific to your API.
    |
    */

    'version' => env('API_VERSION', 'v1'),

    'prefix' => env('API_PREFIX', 'api'),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */

    'rate_limiting' => [
        'per_minute' => env('RATE_LIMIT_PER_MINUTE', 60),
        'login_per_minute' => env('RATE_LIMIT_LOGIN_PER_MINUTE', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | JWT Configuration
    |--------------------------------------------------------------------------
    */

    'jwt' => [
        'secret' => env('JWT_SECRET', 'rei_do_oleo_jwt_secret_key_2024'),
        'ttl' => env('JWT_TTL', 1440), // 24 hours
        'refresh_ttl' => env('JWT_REFRESH_TTL', 20160), // 2 weeks
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    */

    'upload' => [
        'max_file_size' => env('MAX_FILE_SIZE', 10240), // KB
        'allowed_types' => explode(',', env('ALLOWED_FILE_TYPES', 'jpg,jpeg,png,pdf,doc,docx')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Business Configuration
    |--------------------------------------------------------------------------
    */

    'business' => [
        'name' => env('BUSINESS_NAME', 'Rei do Óleo'),
        'email' => env('BUSINESS_EMAIL', 'contato@reidooleo.com'),
        'phone' => env('BUSINESS_PHONE', '+55 11 99999-9999'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    'pagination' => [
        'per_page' => 15,
        'max_per_page' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Format
    |--------------------------------------------------------------------------
    */

    'response' => [
        'format' => 'json',
        'include_timestamp' => true,
        'include_version' => true,
    ],

];
