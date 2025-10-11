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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'api_brasil' => [
        'url' => env('API_BRASIL_URL', 'https://gateway.apibrasil.io/api/v2/whatsapp'),
        'token' => env('API_BRASIL_TOKEN'),
        'profile_id' => env('API_BRASIL_PROFILE_ID'),
        'device_id' => env('API_BRASIL_DEVICE_ID'),
        'device_token' => env('API_BRASIL_DEVICE_TOKEN'),
        'timeout' => env('API_BRASIL_TIMEOUT', 25),
        'connect_timeout' => env('API_BRASIL_CONNECT_TIMEOUT', 10),
        'retry_times' => env('API_BRASIL_RETRY_TIMES', 1),
        'retry_sleep' => env('API_BRASIL_RETRY_SLEEP', 1000),
    ],

    'whatsapp' => [
        'test_number' => env('WHATSAPP_TEST_NUMBER'),
        'webhook_secret' => env('WHATSAPP_WEBHOOK_SECRET'),
    ],

];
