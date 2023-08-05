<?php

namespace App\Model\Authorize;

class FrontAppAuthorizeModel
{
    protected static ?FrontAppAuthorizeModel $instance = null;
    public static function Instance(): FrontAppAuthorizeModel
    {
        if (is_null(static::$instance)) {
            static::$instance = new FrontAppAuthorizeModel();
        }
        return static::$instance;
    }

    public ?int $userId = null;

}