<?php

namespace App\Model\Authorize;

class AppUserAuthorizeModel
{
    protected static ?AppUserAuthorizeModel $instance = null;
    public static function Instance(): AppUserAuthorizeModel
    {
        if (is_null(static::$instance)) {
            static::$instance = new AppUserAuthorizeModel();
        }
        return static::$instance;
    }

    public ?int $userId = null;

}