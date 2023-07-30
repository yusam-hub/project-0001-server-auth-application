<?php

return [
    'templateDefault' => 'default',

    'templates' => [
        'default' => [
            'smarty' => [
                'debugging' => false,
                'force_compile' => true,
                'caching' => false,
                'cache_lifetime' => 120,
            ],
            'smartyDirs' => [
                'pluginDir' => __DIR__ .'/../resources/views/default/_smarty_plugins',
                'templateDir' => __DIR__ .'/../resources/views/default',
                'configDir' => __DIR__ .'/../resources/views/default',
                'compileDir' => __DIR__ .'/../storage/smarty/compiles/default',
                'cacheDir' => __DIR__ .'/../storage/smarty/caches/default',
            ],
            'smartyExt' => [
                'extension' => '.tpl',
                'vendorDir' => __DIR__ . '/../vendor',
            ],
        ],
    ],

    'debugSmartyException' => true,
];
