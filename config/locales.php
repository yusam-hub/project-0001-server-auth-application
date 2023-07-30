<?php

return [
    'default' => 'ru',
    'locales' => [
        'ru',
        'en'
    ],
    'setup' => [
        'fromHeaderAcceptLanguageEnabled' => true,
        'fromSession' => [
            'enabled' => true,
            'keyName' => 'locale',
        ],
        'fromCookie' => [
            'enabled' => false,
            'keyName' => 'locale',
        ]
    ]
];
