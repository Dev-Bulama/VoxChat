<?php

return [
    'name'    => env('APP_NAME', 'VoxChat'),
    'version' => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | Subscription Plans
    |--------------------------------------------------------------------------
    */
    'plans' => [
        'free' => [
            'name'            => 'Free',
            'price'           => 0,
            'storage_gb'      => 1,
            'max_groups'      => 5,
            'max_group_members' => 50,
            'ai_calls'        => 0,
            'voice_minutes'   => 100,
            'video_minutes'   => 30,
        ],
        'premium' => [
            'name'            => 'Premium',
            'price'           => 9.99,
            'storage_gb'      => 25,
            'max_groups'      => 50,
            'max_group_members' => 500,
            'ai_calls'        => 300,
            'voice_minutes'   => 1000,
            'video_minutes'   => 500,
        ],
        'business' => [
            'name'            => 'Business',
            'price'           => 29.99,
            'storage_gb'      => 100,
            'max_groups'      => -1,
            'max_group_members' => -1,
            'ai_calls'        => -1,
            'voice_minutes'   => -1,
            'video_minutes'   => -1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Providers
    |--------------------------------------------------------------------------
    */
    'ai_providers' => [
        'did'      => ['name' => 'D-ID', 'class' => \App\Services\AI\DIDProvider::class],
        'heygen'   => ['name' => 'HeyGen', 'class' => \App\Services\AI\HeyGenProvider::class],
        'tavus'    => ['name' => 'Tavus', 'class' => \App\Services\AI\TavusProvider::class],
        'simli'    => ['name' => 'Simli AI', 'class' => \App\Services\AI\SimliProvider::class],
        'openai'   => ['name' => 'OpenAI', 'class' => \App\Services\AI\OpenAIProvider::class],
    ],

    /*
    |--------------------------------------------------------------------------
    | Call Providers
    |--------------------------------------------------------------------------
    */
    'call_providers' => [
        'webrtc'  => ['name' => 'WebRTC (Built-in)', 'class' => \App\Services\Call\WebRTCProvider::class],
        'agora'   => ['name' => 'Agora', 'class' => \App\Services\Call\AgoraProvider::class],
        'livekit' => ['name' => 'LiveKit', 'class' => \App\Services\Call\LiveKitProvider::class],
        'twilio'  => ['name' => 'Twilio Video', 'class' => \App\Services\Call\TwilioProvider::class],
    ],

    /*
    |--------------------------------------------------------------------------
    | Media Settings
    |--------------------------------------------------------------------------
    */
    'media' => [
        'max_image_size'     => 10 * 1024 * 1024,  // 10MB
        'max_video_size'     => 100 * 1024 * 1024, // 100MB
        'max_audio_size'     => 25 * 1024 * 1024,  // 25MB
        'max_file_size'      => 50 * 1024 * 1024,  // 50MB
        'image_quality'      => 85,
        'thumbnail_width'    => 320,
        'thumbnail_height'   => 240,
        'allowed_images'     => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'allowed_videos'     => ['mp4', 'webm', 'mov', 'avi'],
        'allowed_audio'      => ['mp3', 'ogg', 'wav', 'm4a', 'aac'],
        'allowed_documents'  => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Story Settings
    |--------------------------------------------------------------------------
    */
    'stories' => [
        'expiry_hours'   => 24,
        'max_per_day'    => 30,
        'max_duration'   => 30, // seconds for video
    ],

    /*
    |--------------------------------------------------------------------------
    | Message Settings
    |--------------------------------------------------------------------------
    */
    'messages' => [
        'max_length'       => 4096,
        'edit_window'      => 15, // minutes
        'delete_for_all_window' => 60, // minutes
    ],
];
