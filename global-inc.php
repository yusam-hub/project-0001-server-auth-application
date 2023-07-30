<?php

error_reporting(E_ALL);

const ROOT_DIR = __DIR__;

define('APP_START', microtime(true));

require __DIR__ . '/vendor/autoload.php';

\YusamHub\Debug\Debug::instance(__DIR__ . "/storage/logs", true);
\YusamHub\AppExt\Config::instance(__DIR__ . '/config');
\YusamHub\AppExt\Env::instance(__DIR__ . '/env');

