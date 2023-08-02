<?php

namespace App\Model\Database;

use YusamHub\AppExt\Db\Model\PdoExtModel;
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
class UserMobileModel extends PdoExtModel
{
    protected ?string $connectionName = DB_CONNECTION_DEFAULT;
    protected string $tableName = TABLE_USERS_MOBILES;

    protected function getDatabaseName(): string
    {
        return app_ext_config('database.connections.'.$this->getConnectionName().'.dbName');
    }


}
