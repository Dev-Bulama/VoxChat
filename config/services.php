<?php

return [
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],
    'ses' => [
        'key'     => env('AWS_ACCESS_KEY_ID'),
        'secret'  => env('AWS_SECRET_ACCESS_KEY'),
        'region'  => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'resend' => [
        'key' => env('RESEND_KEY'),
    ],
    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI'),
    ],
    'facebook' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect'      => env('FACEBOOK_REDIRECT_URI'),
    ],
    'twitter-oauth-2' => [
        'client_id'     => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect'      => env('TWITTER_REDIRECT_URI'),
    ],
    'stripe' => [
        'model'   => App\Models\User::class,
        'key'     => env('STRIPE_KEY'),
        'secret'  => env('STRIPE_SECRET'),
        'webhook' => [
            'secret'    => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],
    'paystack' => [
        'public_key'  => env('PAYSTACK_PUBLIC_KEY'),
        'secret_key'  => env('PAYSTACK_SECRET_KEY'),
        'payment_url' => env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'),
    ],
    'flutterwave' => [
        'public_key'  => env('FLW_PUBLIC_KEY'),
        'secret_key'  => env('FLW_SECRET_KEY'),
        'secret_hash' => env('FLW_SECRET_HASH'),
    ],
    'paypal' => [
        'client_id'     => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'mode'          => env('PAYPAL_MODE', 'sandbox'),
    ],
    'openai' => [
        'api_key'      => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION'),
    ],
    'did' => [
        'api_key' => env('DID_API_KEY'),
        'api_url' => 'https://api.d-id.com',
    ],
    'heygen' => [
        'api_key' => env('HEYGEN_API_KEY'),
        'api_url' => 'https://api.heygen.com/v2',
    ],
    'tavus' => [
        'api_key' => env('TAVUS_API_KEY'),
        'api_url' => 'https://tavusapi.com/v2',
    ],
    'simli' => [
        'api_key' => env('SIMLI_API_KEY'),
        'api_url' => 'https://api.simli.ai',
    ],
    'agora' => [
        'app_id'          => env('AGORA_APP_ID'),
        'app_certificate' => env('AGORA_APP_CERTIFICATE'),
    ],
    'livekit' => [
        'api_key'    => env('LIVEKIT_API_KEY'),
        'api_secret' => env('LIVEKIT_API_SECRET'),
        'url'        => env('LIVEKIT_URL'),
    ],
    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token'  => env('TWILIO_AUTH_TOKEN'),
        'api_key'     => env('TWILIO_API_KEY'),
        'api_secret'  => env('TWILIO_API_SECRET'),
    ],
];
