<?php

return [
    'connectionDefault' => 'app',

    'connections' => [
        'app' => [
            'host' => app_ext_env('REDIS_HOST', 'localhost'),
            'port' => app_ext_env('REDIS_PORT', 6379),
            'dbIndex' => 0,
            'prefix' => 'app_',
        ],
        'cache' => [
            'host' => app_ext_env('REDIS_HOST', 'localhost'),
            'port' => app_ext_env('REDIS_PORT', 6379),
            'dbIndex' => 1,
            'prefix' => 'cache_',
        ],
        'session' => [
            'host' => app_ext_env('REDIS_HOST', 'localhost'),
            'port' => app_ext_env('REDIS_PORT', 6379),
            'dbIndex' => 2,
            'prefix' => 'session_',
        ],
    ],
];
