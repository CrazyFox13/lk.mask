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

    'firebase' => [
        'key' => env("FIREBASE_KEY"),
        'json' => env("FIREBASE_ADMIN_FILE", "google_service.json"),
    ],

    'dadata' => [
        'key' => env("DADATA_KEY"),
        'secret' => env("DADATA_SECRET"),
    ],

    'smsru' => [
        'key' => env("SMSRU_KEY")
    ],

    'tg_bot' => [
        'url' => env("TG_BOT_URL", 'http://localhost:33033')
    ],
    'captcha' => env("CAPTCHA_KEY", "")
];
