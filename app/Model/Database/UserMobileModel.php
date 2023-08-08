<?php

namespace App\Model\Database;

use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

/**
 * @property int $id
 * @property int $userId
 * @property int $mobileId
 * @property string $createdAt
 * @property string|null $modifiedAt
 *
 * @method static UserMobileModel|null findModel(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static UserMobileModel findModelOrFail(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static UserMobileModel|null findModelByAttributes(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 * @method static UserMobileModel findModelByAttributesOrFail(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 */
class UserMobileModel extends BasePdoExtModel
{
    const CURRENT_CONNECTION_NAME =  DB_CONNECTION_DEFAULT;
    const CURRENT_TABLE_NAME = TABLE_USERS_MOBILES;

    protected ?string $connectionName = self::CURRENT_CONNECTION_NAME;
    protected string $tableName = self::CURRENT_TABLE_NAME;

    const ATTRIBUTE_NAME_ID = 'id';
    const ATTRIBUTE_NAME_USER_ID = 'userId';
    const ATTRIBUTE_NAME_MOBILE_ID = 'mobileId';
    const ATTRIBUTE_NAME_CREATED_AT = 'createdAt';
    const ATTRIBUTE_NAME_MODIFIED_AT = 'modifiedAt';

    protected function getDatabaseName(): string
    {
        return app_ext_config('database.connections.'.$this->getConnectionName().'.dbName');
    }

    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param string $mobilePrefix
     * @param string $num
     * @return UserModel|null
     */
    public static function findUserModelByMobile(
        PdoExtKernelInterface $pdoExtKernel,
        string $mobilePrefix,
        string $num
    ): ?UserModel
    {
        $sqlRow = <<<MYSQL
select 
    u.*
from 
    :current_table_name um, users u, mobiles m, country_mobile_prefixes cmp
where
    um.userId = u.id and um.mobileId = m.id and m.countryMobilePrefixId = cmp.id
    and cmp.mobilePrefix = ?
    and m.num = ?
limit 0,1
MYSQL;
        return $pdoExtKernel
            ->pdoExt()
            ->fetchOne(strtr($sqlRow, [
                ':current_table_name' => self::CURRENT_TABLE_NAME,
            ]), [
                $mobilePrefix,
                $num
            ], UserModel::class);
    }

    /**
     * @param PdoExtKernelInterface $pdoExtKernel
     * @param string $mobilePrefix
     * @param string $num
     * @return int|null
     */
    public static function findUserIdByMobile(
        PdoExtKernelInterface $pdoExtKernel,
        string $mobilePrefix,
        string $num
    ): ?int
    {
        $userModel = static::findUserModelByMobile($pdoExtKernel, $mobilePrefix, $num);
        if (!is_null($userModel)) {
            return $userModel->id;
        }
        return null;
    }
}
