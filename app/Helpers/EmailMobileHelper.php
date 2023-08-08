<?php

namespace App\Helpers;

use App\Model\Database\CountryMobilePrefixModel;
use Psr\Log\LoggerInterface;
use YusamHub\AppExt\Redis\RedisCacheUseFresh;
use YusamHub\AppExt\Redis\RedisKernel;
use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;
use YusamHub\Helper\ArrayHelper;

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
     * @param RedisKernel $redisKernel
     * @param LoggerInterface $logger
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param string $value
     * @param $mobilePrefix
     * @param $num
     * @param $mobilePrefixId
     * @return bool
     */
    public static function isMobile(
        RedisKernel $redisKernel,
        LoggerInterface $logger,
        PdoExtKernelInterface $pdoExtKernel,
        string $value,
        &$mobilePrefix,
        &$num,
        &$mobilePrefixId
    ): bool
    {
        $mobilePrefix = null;
        $num = null;
        $mobilePrefixId = null;
        $prefixToId = CountryMobilePrefixModel::getAllPrefixToId(
            $redisKernel,
            $logger,
            $pdoExtKernel
        );

        $value = '+' . ltrim($value, '+');
        $_num = substr($value,-10);
        $_mobilePrefix = str_replace($_num, '', $value);
        $result = isset($prefixToId[$_mobilePrefix]) && strlen($_num) === 10;
        if ($result) {
            $mobilePrefix = $_mobilePrefix;
            $num = $_num;
            $mobilePrefixId = $prefixToId[$_mobilePrefix];
        }
        return $result;
    }
}