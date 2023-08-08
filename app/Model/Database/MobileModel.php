<?php

namespace App\Model\Database;

use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

/**
 * @property int $id
 * @property int $countryMobilePrefixId
 * @property string $num
 * @property string|null $verifiedAt
 * @property string $createdAt
 * @property string|null $modifiedAt
 *
 * @method static MobileModel|null findModel(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static MobileModel findModelOrFail(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static MobileModel|null findModelByAttributes(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 * @method static MobileModel findModelByAttributesOrFail(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 */
class MobileModel extends BasePdoExtModel
{
    const CURRENT_CONNECTION_NAME =  DB_CONNECTION_DEFAULT;
    const CURRENT_TABLE_NAME = TABLE_MOBILES;

    protected ?string $connectionName = self::CURRENT_CONNECTION_NAME;
    protected string $tableName = self::CURRENT_TABLE_NAME;

    const ATTRIBUTE_NAME_ID = 'id';
    const ATTRIBUTE_NAME_COUNTRY_MOBILE_PREFIX_ID = 'countryMobilePrefixId';
    const ATTRIBUTE_NAME_NUM = 'num';
    const ATTRIBUTE_NAME_VERIFIED_AT = 'verifiedAt';
    const ATTRIBUTE_NAME_CREATED_AT = 'createdAt';
    const ATTRIBUTE_NAME_MODIFIED_AT = 'modifiedAt';

    protected function getDatabaseName(): string
    {
        return app_ext_config('database.connections.'.$this->getConnectionName().'.dbName');
    }


}
