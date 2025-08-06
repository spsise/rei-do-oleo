<?php

return [
    'activity_model' => \Spatie\Activitylog\Models\Activity::class,

    'table_name' => 'activity_log',

    'database_connection' => env('ACTIVITY_LOGGER_DB_CONNECTION'),

    'default_log_name' => 'default',

    'default_auth_driver' => null,

    'subject_types' => [
        // Add your model classes here
    ],

    'causer_types' => [
        // Add your model classes here
    ],

    'enable_logging_models_events' => true,

    'activity_model_appends' => [
        'properties',
        'causer',
        'subject',
    ],

    'recording_model_events' => [
        'created',
        'updated',
        'deleted',
    ],

    'clean_records_older_than_days' => 365,

    'default_auth_driver' => null,

    'subject_types' => [
        // Add your model classes here
    ],

    'causer_types' => [
        // Add your model classes here
    ],

    'enable_logging_models_events' => true,

    'activity_model_appends' => [
        'properties',
        'causer',
        'subject',
    ],

    'recording_model_events' => [
        'created',
        'updated',
        'deleted',
    ],

    'clean_records_older_than_days' => 365,
];
