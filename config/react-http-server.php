<?php

return [
    'httpServerConfigModel' => [
        'isDebugging' => (bool) app_ext_env('APP_IS_DEBUGGING', false),
        'limitConcurrentRequests' => intval(app_ext_env('HTTP_SERVER_LIMIT_CONCURRENT_REQUESTS', 100)),
        'limitRequestBodyBuffer' => intval(app_ext_env('HTTP_SERVER_LIMIT_REQUEST_BODY_BUFFER', 2097152)),
        'socketServerMode' => \YusamHub\AppExt\ReactHttpServer\HttpServerConfigModel::SOCKET_SERVER_MODE_UNIX_FILE,
        'socketServerPathUri' => '/tmp/react-http-server-socks/server.worker%d.sock',
        'socketServerIpUri' => '0.0.0.0:1808%d',
        'tmpFileDir' => '/tmp/react-http-server-files',
    ],
];
