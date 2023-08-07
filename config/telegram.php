<?php

return [
    'connectionDefault' => TELEGRAM_CONNECTION_DEFAULT,

    'connections' => [
        TELEGRAM_CONNECTION_DEFAULT => [
            'isDebugging' => app_ext_env("TELEGRAM_DEFAULT_IS_DEBUGGING",false),
            'baseUrl' => 'https://api.telegram.org',
            'storageLogFile' => app_ext()->getStorageDir('/logs/telegram-default-sdk.log'),
            'token' => app_ext_env("TELEGRAM_DEFAULT_TOKEN",''),
        ]
    ],
];