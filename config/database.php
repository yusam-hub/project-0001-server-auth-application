<?php

return [
    'connectionDefault' => 'app',

    'connections' => [
        'app' => [
            'user' => app_ext_env('DATABASE_USER'),
            'password' => app_ext_env('DATABASE_PASSWORD'),
            'host' => app_ext_env('DATABASE_HOST'),
            'port' => app_ext_env('DATABASE_PORT'),
            'dbName' => app_ext_env('DATABASE_DBNAME'),
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
