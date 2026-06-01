<?php

return [

    'instagram' => [
        'access_token' => env('INSTAGRAM_ACCESS_TOKEN'),
        'user_id'      => env('INSTAGRAM_USER_ID', 'me'),
        'username'     => env('INSTAGRAM_USERNAME', ''),
        'post_count'   => (int) env('INSTAGRAM_POST_COUNT', 4),
    ],

    'tiktok' => [
        'access_token' => env('TIKTOK_ACCESS_TOKEN'),
        'username'     => env('TIKTOK_USERNAME', ''),
        'post_count'   => (int) env('TIKTOK_POST_COUNT', 4),
    ],

    'cache_ttl_hours' => (int) env('SOCIAL_CACHE_TTL_HOURS', 2),

];
