<?php

namespace App\Model\Database;

use YusamHub\AppExt\Db\Model\PdoExtModel;
use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

/**
 * @property int $id
 * @property string $countryCode2
 * @property string $countryCode3
 * @property string $mobilePrefix
 * @property string $createdAt
 * @property string|null $modifiedAt
 *
 * @method static CountryMobilePrefixModel|null findModel(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static CountryMobilePrefixModel findModelOrFail(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static CountryMobilePrefixModel|null findModelByAttributes(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 * @method static CountryMobilePrefixModel findModelByAttributesOrFail(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 */
class CountryMobilePrefixModel extends PdoExtModel
{
    protected ?string $connectionName = DB_CONNECTION_DEFAULT;
    protected string $tableName = TABLE_COUNTRY_MOBILE_PREFIXES;

    protected function getDatabaseName(): string
    {
        return app_ext_config('database.connections.'.$this->getConnectionName().'.dbName');
    }


}