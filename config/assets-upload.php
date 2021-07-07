<?php

return [
    'filesystem' => env('ASSETS_UPLOAD_FILESYSTEM', false),

    'cache-control' => [
        'css' => env('ASSETS_UPLOAD_CACHE_CONTROL_CSS', 604800), // 7 days
        'js' => env('ASSETS_UPLOAD_CACHE_CONTROL_JS', 604800), // 7 days
        'woff2' => env('ASSETS_UPLOAD_CACHE_CONTROL_WOFF2', 31536000), // 365 days
    ]
];
