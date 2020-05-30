<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => docker_secret(env('MAILGUN_SECRET')),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => docker_secret(env('AWS_SECRET_ACCESS_KEY')),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => docker_secret(env('SPARKPOST_SECRET')),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => docker_secret(env('STRIPE_SECRET')),
        'webhook' => [
            'secret' => docker_secret(env('STRIPE_WEBHOOK_SECRET')),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    // github oauth specific settings
    'github' => [
        'client_id' => env('OAUTH_GITHUB_CLIENT_ID', env('OAUTH_CLIENT_ID')),
        'client_secret' => docker_secret(env('OAUTH_GITHUB_CLIENT_SECRET', env('OAUTH_CLIENT_SECRET'))),
        'redirect' => env('OAUTH_GITHUB_REDIRECT', env('OAUTH_REDIRECT')),
    ],
    // google oauth specific settings
    'google' => [
        'client_id' => env('OAUTH_GOOGLE_CLIENT_ID', env('OAUTH_CLIENT_ID')),
        'client_secret' => docker_secret(env('OAUTH_GOOGLE_CLIENT_SECRET', env('OAUTH_CLIENT_SECRET'))),
        'redirect' => env('OAUTH_GOOGLE_REDIRECT', env('OAUTH_REDIRECT')),
    ],
    // linkedin oauth specific settings
    'linkedin' => [
        'client_id' => env('OAUTH_LINKEDIN_CLIENT_ID', env('OAUTH_CLIENT_ID')),
        'client_secret' => docker_secret(env('OAUTH_LINKEDIN_CLIENT_SECRET', env('OAUTH_CLIENT_SECRET'))),
        'redirect' => env('OAUTH_LINKEDIN_REDIRECT', env('OAUTH_REDIRECT')),
    ],
];
