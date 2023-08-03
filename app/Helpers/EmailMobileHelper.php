<?php

namespace App\Helpers;

use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

class EmailMobileHelper
{
    /**
     * @param string $value
     * @return bool
     */
    public static function isEmail(string $value): bool
    {
        return filter_var(strtolower($value), FILTER_VALIDATE_EMAIL);
    }

    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param string $value
     * @param $mobilePrefix
     * @param $num
     * @return bool
     */
    public static function isMobile(
        PdoExtKernelInterface $pdoExtKernel,
        string $value,
        &$mobilePrefix,
        &$num
    ): bool
    {
        /**
         * todo: 1) нужно подгрузить префиксы стран
         *       2) проверить префикс
         *       3) проверить 10 цифр после префикса
         */

        return false;
    }
}