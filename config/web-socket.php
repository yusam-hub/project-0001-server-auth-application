<?php

return [
    'serverDefault' => 'default',

    'servers' => [
        'default' => [
            'class' =>  App\WebSocket\WebSocketServer::class,
            'connection' => [
                'bindAddress' => app_ext_env('WS_SERVER_BIND_ADDRESS', '0.0.0.0'),
                'bindPort' => app_ext_env('WS_SERVER_BIND_PORT', '9111'),
                'bindPullAddress' => app_ext_env('WS_SERVER_BIND_PULL_ADDRESS', '0.0.0.0'),
                'bindPullPort' => app_ext_env('WS_SERVER_BIND_PULL_PORT', '9222'),
            ],
            'incomingMessagesClass' => [
                \YusamHub\WebSocket\WsServer\IncomingMessages\PingPongIncomingMessage::class,
                \App\WebSocket\IncomingMessages\JsRtcPeerIncomingMessage::class,
            ],
            'externalMessagesClass' => [
                \YusamHub\WebSocket\WsServer\ExternalMessages\PingPongExternalMessage::class,
            ],
        ],
    ],

    'clientDefault' => 'default',
    'clients' => [
        'default' => [
            'connection' => [
                'bindAddress' => app_ext_env('WS_CLIENT_BIND_ADDRESS', '0.0.0.0'),
                'bindPort' => app_ext_env('WS_CLIENT_BIND_PORT', '9111'),
            ],
            'outgoingMessagesClass' => [
                \YusamHub\WebSocket\WsClient\OutgoingMessages\PingOutgoingMessage::class,
            ],
            'incomingMessagesClass' => [
                \YusamHub\WebSocket\WsClient\IncomingMessages\PongIncomingMessage::class,
            ],
        ],
    ],

    'externalDefault' => 'default',
    'externals' => [
        'default' => [
            'connection' => [
                'bindPullAddress' => app_ext_env('WS_CLIENT_BIND_PULL_ADDRESS', '0.0.0.0'),
                'bindPullPort' => app_ext_env('WS_CLIENT_BIND_PULL_PORT', '9222'),
            ],
        ],
    ]

];
