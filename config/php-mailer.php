<?php

return [
    'connectionDefault' => PHP_MAILER_CONNECTION_DEFAULT,

    'connections' => [
        PHP_MAILER_CONNECTION_DEFAULT => [
            'debug' => app_ext_env('PHP_MAILER_SMTP_DEFAULT_DEBUG',0),
            'host' => app_ext_env('PHP_MAILER_SMTP_DEFAULT_HOST','localhost'),
            'user' => app_ext_env('PHP_MAILER_SMTP_DEFAULT_USER',''),
            'pass' => app_ext_env('PHP_MAILER_SMTP_DEFAULT_PASS',''),
            'port' => app_ext_env('PHP_MAILER_SMTP_DEFAULT_PORT',25),
            'secure' => app_ext_env('PHP_MAILER_SMTP_DEFAULT_SECURE',''),
            'fromAddress' => app_ext_env('PHP_MAILER_SMTP_DEFAULT_FROM_ADDRESS',''),
            'fromName' => app_ext_env('PHP_MAILER_SMTP_DEFAULT_FROM_NAME',''),
        ],
    ],
];