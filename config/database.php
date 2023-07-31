<?php

return [
    'connectionDefault' => DB_CONNECTION_DEFAULT,

    'connections' => [
        'default' => [
            'user' => app_ext_env('DATABASE_USER'),
            'password' => app_ext_env('DATABASE_PASSWORD'),
            'host' => app_ext_env('DATABASE_HOST'),
            'port' => app_ext_env('DATABASE_PORT'),
            'dbName' => DB_NAME_DEFAULT,
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
