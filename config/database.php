<?php

return [
    'connectionDefault' => DB_CONNECTION_DEFAULT,

    'connections' => [
        'default' => [
            'user' => app_ext_env('DATABASE_DEFAULT_USER'),
            'password' => app_ext_env('DATABASE_DEFAULT_PASSWORD'),
            'host' => app_ext_env('DATABASE_DEFAULT_HOST'),
            'port' => app_ext_env('DATABASE_DEFAULT_PORT'),
            'dbName' => app_ext_env('DATABASE_DEFAULT_NAME'),
        ],
    ],

    'migrations' => [
        'paths' => [
            app_ext()->getRootDir('/vendor/yusam-hub/app-ext/database/migrations'),
            app_ext()->getDatabaseDir('/migrations'),
        ],
        'savedDir' => app_ext()->getStorageDir('/app/migrations')
    ],
];
