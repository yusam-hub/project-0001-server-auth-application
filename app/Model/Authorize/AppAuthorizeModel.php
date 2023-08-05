<?php

namespace App\Model\Authorize;

class AppAuthorizeModel
{
    protected static ?AppAuthorizeModel $instance = null;
    public static function Instance(): AppAuthorizeModel
    {
        if (is_null(static::$instance)) {
            static::$instance = new AppAuthorizeModel();
        }
        return static::$instance;
    }
    public ?int $appId = null;
}