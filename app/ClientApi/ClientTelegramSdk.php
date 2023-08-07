<?php

namespace App\ClientApi;

class ClientTelegramSdk extends \YusamHub\TelegramSdk\ClientTelegramSdk
{
    public function __construct(?string $connectionName = null)
    {
        if (is_null($connectionName)) {
            $connectionName = TELEGRAM_CONNECTION_DEFAULT;
        }
        parent::__construct(app_ext_config('telegram.connections.' . $connectionName));
    }
}