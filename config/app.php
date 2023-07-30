<?php

return [
    'isDebugging' => (bool) app_ext_env('APP_IS_DEBUGGING', false),
    'rootDir' => ROOT_DIR,
    'databaseDir' => ROOT_DIR . '/database',
    'publicDir' => ROOT_DIR . '/public',
    'storageDir' => ROOT_DIR . '/storage',
    'routesDir' => ROOT_DIR . '/routes',
];
