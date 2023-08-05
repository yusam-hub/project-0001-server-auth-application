<?php

namespace App\Model\Authorize;

class UserAuthorizeModel
{
    protected static ?UserAuthorizeModel $instance = null;
    public static function Instance(): UserAuthorizeModel
    {
        if (is_null(static::$instance)) {
            static::$instance = new UserAuthorizeModel();
        }
        return static::$instance;
    }

    public ?int $userId = null;

}