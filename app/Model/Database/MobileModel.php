<?php

namespace App\Model\Database;

use YusamHub\AppExt\Db\Model\PdoExtModel;
use YusamHub\DbExt\Interfaces\PdoExtKernelInterface;

/**
 * @property int $id
 * @property int $countryMobilePrefixId
 * @property string $num
 * @property string $verifiedAt
 * @property string $createdAt
 * @property string|null $modifiedAt
 *
 * @method static MobileModel|null findModel(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static MobileModel findModelOrFail(PdoExtKernelInterface $pdoExtKernel, $pk)
 * @method static MobileModel|null findModelByAttributes(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 * @method static MobileModel findModelByAttributesOrFail(PdoExtKernelInterface $pdoExtKernel, array $attributes)
 */
class MobileModel extends PdoExtModel
{
    protected ?string $connectionName = DB_CONNECTION_DEFAULT;
    protected string $tableName = TABLE_MOBILES;

    protected function getDatabaseName(): string
    {
        return app_ext_config('database.connections.'.$this->getConnectionName().'.dbName');
    }


}
