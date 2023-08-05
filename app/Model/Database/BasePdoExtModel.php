<?php

namespace App\Model\Database;

use Psr\Log\LoggerInterface;
use YusamHub\AppExt\Db\Model\PdoExtModel;
use YusamHub\AppExt\Redis\RedisCacheUseFresh;
use YusamHub\AppExt\Redis\RedisKernel;
use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

abstract class BasePdoExtModel extends PdoExtModel
{
    public static function exists(
        RedisKernel $redisKernel,
        PdoExtKernelInterface $pdoExtKernel,
        LoggerInterface $logger,
        $pk,
        bool $cacheUse = true,
        bool $cacheFresh = false,
        int $cacheTtl = RedisCacheUseFresh::CACHE_TTL_DAY
    ): bool
    {
        return RedisCacheUseFresh::rememberExt(
            $redisKernel->redisExt(),
            $logger,
            md5(__METHOD__ . $pk),
            $cacheUse, $cacheFresh, $cacheTtl,
            function() use($pdoExtKernel, $pk) {
                $model = static::findModel($pdoExtKernel, $pk);
                return !is_null($model);
            });
    }
}