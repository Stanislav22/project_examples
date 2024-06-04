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

    'stripe' => [
        'client_id' => env('STRIPE_CLIENT_ID'),
        'public_key' => env('STRIPE_PUBLIC_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'platform_account_id' => env('STRIPE_PLATFORM_ACCOUNT_ID'),
        'test_client_id' => env('STRIPE_TEST_CLIENT_ID'),
        'test_public_key' => env('STRIPE_TEST_PUBLIC_KEY'),
        'test_secret' => env('STRIPE_TEST_SECRET'),
        'test_webhook_secret' => env('STRIPE_TEST_WEBHOOK_SECRET'),
        'test_platform_account_id' => env('STRIPE_TEST_PLATFORM_ACCOUNT_ID'),
    ],

    'webhooks_proxy' => [
        'url' => env('WEBHOOKS_PROXY_URL', 'https://webhooks.app'),
    ],
];
