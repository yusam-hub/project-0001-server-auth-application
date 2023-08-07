<?php

return [
    'channelDefault' => app_ext_env('LOGGING_CHANNEL', LOGGING_CHANNEL_APP),

    'channels' => [
        LOGGING_CHANNEL_APP => [
            'class' => \YusamHub\AppExt\Logger\FileLogger::class,
            'config' => [
                'logDir' => app_ext()->getStorageDir('/logs'),
                'name' => 'app',
                'fileMaxSize' => 10 * 1024 * 1024,
                'fileRotatorCount' => 10,
                'level' => app_ext_env('LOGGING_LEVEL', \Psr\Log\LogLevel::ERROR),
                'lineFormat' => \YusamHub\AppExt\Logger\FileLogger::LINE_FORMAT_NORMAL,
            ]
        ],
        LOGGING_CHANNEL_OTP => [
            'class' => \YusamHub\AppExt\Logger\FileLogger::class,
            'config' => [
                'logDir' => app_ext()->getStorageDir('/logs'),
                'name' => 'otp',
                'fileMaxSize' => 10 * 1024 * 1024,
                'fileRotatorCount' => 10,
                'level' => app_ext_env('LOGGING_LEVEL', \Psr\Log\LogLevel::ERROR),
                'lineFormat' => \YusamHub\AppExt\Logger\FileLogger::LINE_FORMAT_NORMAL,
            ]
        ],
        LOGGING_CHANNEL_TELEGRAM_DAEMON => [
            'class' => \YusamHub\AppExt\Logger\FileLogger::class,
            'config' => [
                'logDir' => app_ext()->getStorageDir('/logs'),
                'name' => 'telegram-daemon',
                'fileMaxSize' => 10 * 1024 * 1024,
                'fileRotatorCount' => 10,
                'level' => app_ext_env('LOGGING_LEVEL', \Psr\Log\LogLevel::ERROR),
                'lineFormat' => \YusamHub\AppExt\Logger\FileLogger::LINE_FORMAT_NORMAL,
            ]
        ],
        'react-http-server-0' => [
            'class' => \YusamHub\AppExt\Logger\FileLogger::class,
            'config' => [
                'logDir' => app_ext()->getStorageDir('/logs'),
                'name' => 'react-http-server-0',
                'fileMaxSize' => 10 * 1024 * 1024,
                'fileRotatorCount' => 10,
                'level' => app_ext_env('LOGGING_LEVEL', \Psr\Log\LogLevel::ERROR),
                'lineFormat' => \YusamHub\AppExt\Logger\FileLogger::LINE_FORMAT_NORMAL,
            ]
        ],
        'rabbit-mq-consumer-0' => [
            'class' => \YusamHub\AppExt\Logger\FileLogger::class,
            'config' => [
                'logDir' => app_ext()->getStorageDir('/logs'),
                'name' => 'rabbit-mq-consumer-0',
                'fileMaxSize' => 10 * 1024 * 1024,
                'fileRotatorCount' => 10,
                'level' => app_ext_env('LOGGING_LEVEL', \Psr\Log\LogLevel::ERROR),
                'lineFormat' => \YusamHub\AppExt\Logger\FileLogger::LINE_FORMAT_NORMAL,
            ]
        ],
    ],
];
