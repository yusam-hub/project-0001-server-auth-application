<?php

namespace App\Model\Database;

use Psr\Log\LoggerInterface;
use YusamHub\AppExt\Redis\RedisCacheUseFresh;
use YusamHub\AppExt\Redis\RedisKernel;
use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;
use YusamHub\Helper\ArrayHelper;

/**
 * @property int $id
 * @property string $abbr
 * @property string $title
 * @property string $createdAt
 * @property string|null $modifiedAt
 *
 * @method static SocialModel|null findModel(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static SocialModel findModelOrFail(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static SocialModel|null findModelByAttributes(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 * @method static SocialModel findModelByAttributesOrFail(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 */
class SocialModel extends BasePdoExtModel
{
    const CURRENT_CONNECTION_NAME =  DB_CONNECTION_DEFAULT;
    const CURRENT_TABLE_NAME = TABLE_SOCIALS;

    protected ?string $connectionName = self::CURRENT_CONNECTION_NAME;
    protected string $tableName = self::CURRENT_TABLE_NAME;

    const ATTRIBUTE_NAME_ID = 'id';
    const ATTRIBUTE_NAME_ABBR = 'abbr';
    const ATTRIBUTE_NAME_TITLE = 'title';
    const ATTRIBUTE_NAME_CREATED_AT = 'createdAt';
    const ATTRIBUTE_NAME_MODIFIED_AT = 'modifiedAt';

    const SOCIAL_TELEGRAM_ABBR = 'telegram';
    const SOCIALS = [
        self::SOCIAL_TELEGRAM_ABBR
    ];

    protected function getDatabaseName(): string
    {
        return app_ext_config('database.connections.'.$this->getConnectionName().'.dbName');
    }

    /**
     * @param RedisKernel $redisKernel
     * @param LoggerInterface $logger
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param bool $cacheUse
     * @param bool $cacheFresh
     * @param int $cacheTtl
     * @return array
     */
    public static function getAll(
        RedisKernel $redisKernel,
        LoggerInterface $logger,
        PdoExtKernelInterface $pdoExtKernel,
        bool $cacheUse = true,
        bool $cacheFresh = false,
        int $cacheTtl = RedisCacheUseFresh::CACHE_TTL_DAY
    ): array
    {
        return RedisCacheUseFresh::rememberExt(
            $redisKernel->redisExt(),
            $logger,
            md5(__METHOD__),
            $cacheUse, $cacheFresh, $cacheTtl,
            function() use($pdoExtKernel) {
                $sqlRows = <<<MYSQL
select 
    id,
    abbr,
    title
from 
    :current_table_name
order 
    by id 
MYSQL;
                return $pdoExtKernel
                    ->pdoExt(self::CURRENT_CONNECTION_NAME)
                    ->fetchAll(
                        strtr($sqlRows, [
                            ':current_table_name' => self::CURRENT_TABLE_NAME
                        ]),
                    );
            });
    }

    /**
     * @param RedisKernel $redisKernel
     * @param LoggerInterface $logger
     * @param PdoExtKernelInterface $pdoExtKernel
     * @return array
     */
    public static function getAllAbbrToId(
        RedisKernel $redisKernel,
        LoggerInterface $logger,
        PdoExtKernelInterface $pdoExtKernel,
    ): array
    {
        $rows = static::getAll(
            $redisKernel,
            $logger,
            $pdoExtKernel
        );

        return ArrayHelper::map($rows,
            'abbr',
            function($row) {
                return $row['id'];
            });
    }

    /**
     * @param RedisKernel $redisKernel
     * @param LoggerInterface $logger
     * @param PdoExtKernelInterface $pdoExtKernel
     * @return array
     */
    public static function getAllIdToAbbr(
        RedisKernel $redisKernel,
        LoggerInterface $logger,
        PdoExtKernelInterface $pdoExtKernel,
    ): array
    {
        $rows = static::getAll(
            $redisKernel,
            $logger,
            $pdoExtKernel
        );

        return ArrayHelper::map($rows,
            'id',
            function($row) {
                return $row['abbr'];
            });
    }
}
