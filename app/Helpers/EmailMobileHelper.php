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
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param LoggerInterface $logger
     * @param string $value
     * @param $mobilePrefix
     * @param $num
     * @return bool
     */
    public static function isMobile(
        RedisKernel $redisKernel,
        PdoExtKernelInterface $pdoExtKernel,
        LoggerInterface $logger,
        string $value,
        &$mobilePrefix,
        &$num
    ): bool
    {
        $cc2Prefixes = static::getCountryMobilePrefix(
            $redisKernel,
            $pdoExtKernel,
            $logger
        );

        $onlyPrefixes = array_values($cc2Prefixes);

        $num = substr($value,-10);
        $mobilePrefix = str_replace($num, '', $value);

        return in_array($mobilePrefix, $onlyPrefixes) && strlen($num) === 10;
    }

    /**
     * @param RedisKernel $redisKernel
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param LoggerInterface $logger
     * @param string $key
     * @return array
     */
    public static function getCountryMobilePrefix(
        RedisKernel $redisKernel,
        PdoExtKernelInterface $pdoExtKernel,
        LoggerInterface $logger,
        string $key = CountryMobilePrefixModel::ATTRIBUTE_NAME_COUNTRY_CODE_2
    ): array
    {
        return RedisCacheUseFresh::rememberExt(
            $redisKernel->redisExt(),
            $logger,
            md5(__METHOD__ . $key),
            true, false, RedisCacheUseFresh::CACHE_TTL_DAY,
            function() use($pdoExtKernel, $key) {
                $sqlRows = <<<MYSQL
select 
    :key,
    mobilePrefix
from country_mobile_prefixes
order by :key
MYSQL;
                $rows = $pdoExtKernel->pdoExt()->fetchAll(strtr($sqlRows, [
                    ':key' => $key
                ]));

                return ArrayHelper::map(
                    $rows,
                    $key,
                    function($row) {
                        return $row['mobilePrefix'];
                    }
                );
            });
    }
}