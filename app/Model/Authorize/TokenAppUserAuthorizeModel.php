<?php

namespace App\Model\Authorize;

class TokenAppUserAuthorizeModel
{
    protected static ?TokenAppUserAuthorizeModel $instance = null;
    public static function Instance(): TokenAppUserAuthorizeModel
    {
        if (is_null(static::$instance)) {
            static::$instance = new TokenAppUserAuthorizeModel();
        }
        return static::$instance;
    }
    public ?int $userId = null;
    public ?int $appId = null;
    public ?string $deviceUuid = null;
}