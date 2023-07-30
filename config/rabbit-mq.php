<?php

return [
    'connectionDefault' => 'app',
    'connections' => [
        'app' => [
            "host" => app_ext_env("RABBIT_MQ_HOST",'rabbit-host'),
            "port" => app_ext_env("RABBIT_MQ_PORT", 5672),
            "vhost" => app_ext_env("RABBIT_MQ_VHOST", '/'),
            "user" => app_ext_env("RABBIT_MQ_USER",'root'),
            "password" => app_ext_env("RABBIT_MQ_PASSWORD",'Qwertyu1'),
        ],
    ],

    'destinationDefault' => 'app',
    'destinations' => [
        'app' => [
            'queueName' => 'default',
            'exchangeName' => 'default',
            'routingKey' => 'default',
            'prefetchSize' => 0,
            'prefetchCount' => 0,
            'consumerTag' => 'default',
        ],
    ],
];