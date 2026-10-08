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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'speechace' => [
        'api_key' => env('SPEECHACE_API_KEY'),
        'base_url' => env('SPEECHACE_BASE_URL', 'https://api2.speechace.com'),
        'include_fluency' => env('SPEECHACE_INCLUDE_FLUENCY', false),
        'storage_disk' => env('SPEAKING_AUDIO_DISK', 'local'),
    ],

    'speaking_assessment' => [
        'provider' => env('SPEAKING_ASSESSMENT_PROVIDER', 'speechace'),
        'storage_disk' => env('SPEAKING_AUDIO_DISK', 'local'),
    ],

    'openpronounce' => [
        'url' => env('OPENPRONOUNCE_URL'),
        'timeout' => env('OPENPRONOUNCE_TIMEOUT', 600),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

];
