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
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'passport' => [
        'endpoint' => env('PASSPORT_ENDPOINT', 'http://localhost:80/oauth/token'),
        'access_token_expires' => env('PASSPORT_PERSONAL_ACCESS_TOKEN_EXPIRES'),
        'refresh_token_expires' => env('PASSPORT_PERSONAL_REFRESH_TOKEN_EXPIRES'),
    ],

    'telegram-bot-api' => [
        'token' => env('TELEGRAM_DEFAULT_BOT_TOKEN'),
        'booking_chat_id' => env('TELEGRAM_BOOKING_CHAT_ID'),
    ],

    'instagram_feed_access_token' => env('INSTAGRAM_FEED_ACCESS_TOKEN', null),

    'merit' => [
        'secret_key' => env('MERIT_SECRET_KEY', 'secret_test_f8djpQBNDOj7CyjU1e5ej3h-tLYxOTeRAR41qrqoaPo'),
        'store_id' => env('MERIT_STORE_ID', 'myadv_online'),
        'url' => env('MERIT_URL', 'https://merit.stage.advplus.ae'),
        'minimum_charge_amount' => 1,
    ],

    'checkout' => [
        'public_key' => env('CHECKOUT_PUBLIC_KEY'),
        'secret_key' => env('CHECKOUT_SECRET_KEY'),
    ],

    'passkit' => [
        'api_url' => env('PASSKIT_API_URL', 'https://api.pub1.passkit.io'),
        'key' => env('PASSKIT_KEY'),
        'secret' => env('PASSKIT_SECRET'),
        'member_email_replace' => env('PASSKIT_MEMBER_EMAIL_REPLACE', 'ivan@parasol.me'),
        'pass_url' => env('PASSKIT_PASS_URL', 'https://pub1.pskt.io').'/',
        'webhook_token' => env(
            'PASSKIT_WEBHOOK_TOKEN',
            'wshkcp8tztqq4zkuogh6sobkhdj9hypr87sus5gquli5aekkko8m0ln39a8uflsd'
        ),
    ],

    'gems' => [
        'secure_key' => env('GEMS_SECURE_KEY'),
        'login' => env('GEMS_LOGIN'),
        'password' => env('GEMS_PASSWORD'),
    ],

    'amazon_payfort' => [
        'merchant_identifier' => env('AMAZON_PAYFORT_MERCHANT_IDENTIFIER'),
        'access_code' => env('AMAZON_PAYFORT_ACCESS_CODE'),
        'sha_request_phrase' => env('AMAZON_PAYFORT_SHA_REQUEST_PHRASE'),
        'sha_response_phrase' => env('AMAZON_PAYFORT_SHA_RESPONSE_PHRASE'),
        'sha_type' => env('AMAZON_PAYFORT_SHA_TYPE', 'sha256'),
    ],

    'rapid_instagram' => [
        'api_key' => env('RAPID_INSTAGRAM_API_KEY'),
    ],

    'tabby' => [
        'public_key' => env('TABBY_PUBLIC_KEY'),
        'secret_key' => env('TABBY_SECRET_KEY'),
        'check_pending_payments' => (bool)env('TABBY_CHECK_PENDING_PAYMENTS', false),
    ],

    'nocrm' => [
        'api_key' => env('NOCRM_API_KEY'),
        'subdomain' => env('NOCRM_SUBDOMAIN'),
    ],

    'plecto' => [
        'username' => env('PLECTO_USERNAME'),
        'password' => env('PLECTO_PASSWORD'),
    ],

    'zapier' => [
        'leads_webhook_token' => env('ZAPIER_LEADS_WEBHOOK_TOKEN', 'test'),
    ],

    'unbounce' => [
        'leads_webhook_token' => env('UNBOUNCE_LEADS_WEBHOOK_TOKEN', 'test'),
    ],

    'facebook' => [
        'leads_webhook_token' => env('FACEBOOK_LEADS_WEBHOOK_TOKEN', 'test'),
    ],

    'paytabs' => [
        'server_key' => env('PAYTABS_SERVER_KEY'),
        'profile_id' => env('PAYTABS_PROFILE_ID'),
    ],

    'mamo' => [
        'api_key' => env('MAMO_API_KEY'),
    ],
];
