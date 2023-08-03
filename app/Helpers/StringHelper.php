<?php

namespace App\Helpers;

class StringHelper
{
    public static function isEmail(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    public static function isMobile(string $value): bool
    {
        /**
         * todo: 1) нужно подгрузить префиксы стран
         *       2) проверить префикс
         *       3) проверить 10 цифр после префикса
         */

        return false;
    }
}