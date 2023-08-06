<?php

namespace App\Model\Authorize;

class AccessAuthorizeModel
{
    protected static ?AccessAuthorizeModel $instance = null;
    public static function Instance(): AccessAuthorizeModel
    {
        if (is_null(static::$instance)) {
            static::$instance = new AccessAuthorizeModel();
        }
        return static::$instance;
    }

    public ?int $appId = null;
    public ?int $userId = null;
    public ?string $deviceUuid = null;
}