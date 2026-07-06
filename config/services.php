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

    'wa_gateway' => [
        'url'      => env('WA_GATEWAY_URL', 'https://wa2.fath.my.id/send/message'),
        'username' => env('WA_GATEWAY_USERNAME', 'cecep'),
        'password' => env('WA_GATEWAY_PASSWORD', '126126'),
        'number'   => env('WHATSAPP_NUMBER', '6281394510605'),
    ],

    // AI Model — Primary: ZA126 via 9router, Fallback: mimo-v2.5
    'openrouter' => [
        'endpoint' => env('OPENROUTER_ENDPOINT', 'http://72.61.143.166:3007/v1/chat/completions'),
        'api_key'   => env('OPENROUTER_API_KEY', 'sk-228...12ef'),
        'model'     => env('OPENROUTER_MODEL', 'xiaomi/mimo-v2.5'),
    ],

    // di file config/services.php (tambah di array return)
    'gemini' => [
        'key'   => env('GEMINI_API_KEY', 'AIzaSyAPPtRju7KNXD30PJLJWdLMIHCOL0uJtN8'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
    ],

    // 'wa_gateway' => [
    //     'url'     => env('WA_GATEWAY_URL', 'http://wa4.fath.my.id/send-message'),
    //     'api_key' => env('WA_GATEWAY_API_KEY'),
    //     'sender'  => env('WA_GATEWAY_SENDER'),
    //     'owner'   => env('WA_GATEWAY_OWNER'),
    // ],


];
